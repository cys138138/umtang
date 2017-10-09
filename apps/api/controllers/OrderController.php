<?php
namespace api\controllers;

use Yii;
use api\lib\Controller;
use umeworld\lib\Response;
use common\model\Goods;
use common\model\Teacher;
use common\model\OrderCommentIndex;
use common\model\Resource;
use common\model\User;
use common\model\CommercialTenant;
use common\model\CommercialTenantCharacteristicServiceRelation;
use yii\helpers\ArrayHelper;
use common\model\Order;
use common\model\CharacteristicServiceType;
use common\model\form\order\OrderForm;
use umeworld\lib\weixin_pay\WxPay;
use common\model\CommercialTenantPhoto;
use common\model\UserNotice;
use common\model\UserAccumulatePointGetRecord;

/*
 * 订单
 * @author 谭威力
 */
class OrderController extends Controller{
	public $enableCsrfValidation = false;
	
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	/*public function behaviors(){
		return \yii\helpers\ArrayHelper::merge([
			'access' => [
				'rules' => [
					[
						'allow' => true,
						'actions' => ['get-home-data', 'get-order-list', 'get-order-details'],
					],
				],
			],
		], parent::behaviors());
	}*/
	
	private function _getUserMode(){
		return Yii::$app->user->identity;//User::findOne(1);//
		//return Yii::$app->commercialTenant->getIdentity();
	}
	
	private function _getShowStatusList(){
		return [
			[
				'key' => 0,
				'value' => '全部',
			],
			[
				'key' => 2,
				'value' => '待使用',
			],
			[
				'key' => 6,
				'value' => '待评价',
			],
			[
				'key' => 7,
				'value' => '投诉/退款',
			],
		];
	}

	/*
	 * 首页必须参数 api/order/get-home-data.json
	 */
	public function actionGetHomeData(){
		return new Response('请求成功!', 1, [
			'aOrderStatus' => $this->_getShowStatusList(),
			'aOrderAllStatus' => Order::getOrderStatusList(true),
		]);
	}
	
	/*
	 * 获取相关的订单数据 api/order/get-order-data.json
	 */
	public function actionGetOrderList(){
		$status = (int)Yii::$app->request->post('status');//2;//
		$page = (int)Yii::$app->request->post('page', 1);
		$pageSize = (int)Yii::$app->request->post('pageSize');
		if($page < 1){
			$page = 1;
		}
		if($pageSize <= 0){
			$pageSize = 8;
		}
		if($status < 0){
			return new Response('请选择订单状态!');
		}
		$aStatusId = ArrayHelper::getColumn($this->_getShowStatusList(), 'key');
		if(!in_array($status, $aStatusId)){
			return new Response('请选择正确订单状态!');
		}
		$aWhere = [
			'user_id' => $this->_getUserMode()->id,
		];
		if($status != 7 && $status > 0){
			$aWhere['status'] = $status;
		}elseif($status == 7){
			$aWhere['status'] = [Order::STATUS_APPLY_REFUND, Order::STATUS_REFUNDED];
		}else{
			$aWhere['status'] = [
				Order::STATUS_PAID,
				Order::STATUS_APPLY_REFUND,
				Order::STATUS_HAS_ACTIVATE,
				Order::STATUS_REFUNDED,
				Order::STATUS_WAIT_COMMENT,
				Order::STATUS_FINISH,
			];
		}
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
			'select' => ['id', 'order_num', 'type', 'goods_id', 'quantity', 'price', 'status', 'tenant_id'],
			'order_by' => ['pay_time' => SORT_DESC],
			'with_tenant_info' => true,
		];
		$aOrders = Order::getList($aWhere, $aControl);
		//debug($aOrders);
		return new Response('请求成功!', 1, [
			'list' => $aOrders,
			'count' => Order::getCount($aWhere),
		]);
	}
	
	/*
	 * 获取订单详情 api/order/get-order-details.json
	 */
	public function actionGetOrderDetails(){
		$orderId = (int)Yii::$app->request->post('orderId');//1011;//
		if(!$orderId){
			return new Response('请传递订单id');
		}
		$select = ['id', 'order_num', 'type', 'goods_id', 'quantity', 'original_price', 'price', 'pay_money', 'tenant_id', 'mobile', 'pay_time', 'status', 'activation_time', 'activation_code', 'accumulate_points_money', 'user_id'];
		$aOrder = Order::getList(['id' => $orderId, 'user_id' => $this->_getUserMode()->id], ['select' => $select, 'with_tenant_info' => true]);
		if(!$aOrder || $aOrder[0]['status'] < Order::STATUS_PAID){//
			return new Response('请传递正确的订单id');
		}
		//debug($aOrder[0],11);
		return new Response('请求成功!', 1, [
			'order' => $aOrder[0],
		]);
	}
	
	/*
	 * 评价订单 api/order/comment-order.json
	 */
	public function actionCommentOrder(){
		$orderId = (int)Yii::$app->request->post('orderId');//1011;//
		$score = (int)Yii::$app->request->post('score');
		$content = trim((string)Yii::$app->request->post('content'));
		$aResourceIds = Yii::$app->request->post('resource_ids');
		if(!$orderId){
			return new Response('请传递订单id');
		}
		if(!$score){
			return new Response('请输入评分');
		}
		if($score < 0 || $score > 100){
			return new Response('请输入正确评分');
		}
		$score = (int)($score / 10) * 10;
		if(!$content){
			return new Response('请输入评价内容');
		}
		if(mb_strlen($content, 'utf8') < 5 || mb_strlen($content, 'utf8') > 100){
			return new Response('字数为5至100个字');
		}
		if($aResourceIds && !is_array($aResourceIds)){
			return new Response('请传递正确资源');
		}
		if($aResourceIds){
			$aResources = Resource::findAll(['id' => $aResourceIds]);
			$aResourceId = ArrayHelper::getColumn($aResources, 'id');
			foreach($aResourceIds as $resourceId){
				if(!in_array($resourceId, $aResourceId)){
					return new Response('图片资源不存在');
				}
			}
			foreach($aResources as $aResource){
				if($aResource['type'] != Resource::TYPE_COMMENT_PHOTO){
					return new Response('图片资源不存在');
				}
			}
		}
		$mOrder = Order::findOne(['id' => $orderId, 'user_id' => $this->_getUserMode()->id]);
		if(!$mOrder){
			return new Response('订单不存在');
		}
		if($mOrder->status != Order::STATUS_WAIT_COMMENT){
			return new Response('订单不是待评价状态');
		}
		$mOrderCommentIndex = OrderCommentIndex::findOne(['and', ['order_id' => $orderId], ['user_id' => $this->_getUserMode()->id], ['is_superaddition' => 0], ['>', 'score', 0]]);
		if($mOrderCommentIndex){
			return new Response('你已经评价过了');
		}
		$aData = [
			'order_id' => $orderId,
			'pid' => 0,
			'is_reply' => 0,
			'tenant_id' => $mOrder->tenant_id,
			'is_superaddition' => 0,
			'user_id' => $this->_getUserMode()->id,
			'score' => $score,
			'content' => $content,
			'resource_ids' => $aResourceIds,
		];
		if(OrderCommentIndex::add($aData)){
			$mOrder->set('status', Order::STATUS_FINISH);//评价后订单 状态改成 已完成
			$mOrder->save();
			//商家 平均分 和 总分 计算
			$mCommercialTenant = CommercialTenant::findOne($mOrder->tenant_id);
			$mCommercialTenant->set('all_comment_count', ['add', 1]);
			$mCommercialTenant->set('all_score', ['add', $score]);
			$mCommercialTenant->set('avg_score', (int)($mCommercialTenant->all_score / $mCommercialTenant->all_comment_count));
			if(!$mCommercialTenant->save()){
				Yii::error('订单 ' . $mOrder->id . ' 初评成功,但是商户总分,平均分和售出数量更新失败');
			}
			//评价后 每次初评 都 + 500 积分 并告诉用户
			$resultAddRecord = UserAccumulatePointGetRecord::add([
				'user_id' => $this->_getUserMode()->id,
				'type' => UserAccumulatePointGetRecord::TYPE_FIRST_COMMENT_ORDER,
				'amount' => 50,
				'create_time' => NOW_TIME,
			]);
			if($resultAddRecord){
				UserNotice::add([
					'user_id' => $this->_getUserMode()->id,
					'title' => '积分获取成功',
					'content' => '获得500积分成功！每次订单评价（不包括追评）都可获得500积分，可在下次下单时抵扣相应金额',
					'is_read' => 0,
				]);
			}else{
				Yii::error('订单 ' . $mOrder->id . ' 初评成功,但是积分新增失败');
			}
			
			return new Response('订单评价成功', 1);
		}
		return new Response('订单评价失败');
	}
	
	/*
	 * 申请退款 api/order/apply-refund.json
	 */
	public function actionApplyRefund(){
		$orderId = (int)Yii::$app->request->post('orderId');//1;//
		if(!$orderId){
			return new Response('请传递订单id');
		}
		$mOrder = Order::findOne(['id' => $orderId, 'user_id' => $this->_getUserMode()->id]);
		if(!$mOrder){
			return new Response('订单不存在');
		}
		if($mOrder->type != 2){
			return new Response('订单不是待退款状态');
		}
		if($mOrder->status != Order::STATUS_PAID){
			return new Response('订单不是待退款状态');
		}
		if($mOrder->pay_money == 0){
			$aGoodsInfo = $mOrder->goods_info;
			$mOrder->set('status', Order::STATUS_REFUNDED);
			$mOrder->save();
			UserNotice::add([
				'user_id' => $mOrder->user_id,
				'title' => '退款成功',
				'content' => '你的服务 ' . $aGoodsInfo['name'] . ' 已退款' .$mOrder->pay_money / 100 . '元',
				'is_read' => 0,
			]);
			return new Response('退款成功', 1);
		}
		$mOrder->set('status', Order::STATUS_APPLY_REFUND);
		if($mOrder->save()){
			$aGoodsInfo = $mOrder->goods_info;
			UserNotice::add([
				'user_id' => $mOrder->user_id,
				'title' => '退款请求已提交',
				'content' => '你的服务 ' . $aGoodsInfo['name'] . ' 退款请求已提交，7个工作日内将完成退款',
				'is_read' => 0,
			]);
			return new Response('退款请求已提交', 1, [
				'order_status' => $mOrder->status,
			]);
		}
		return new Response('申请退款失败');
	}
	
	/*
	 * 取消退款 api/order/cancel-refund.json
	 */
	public function actionCancelRefund(){
		$orderId = (int)Yii::$app->request->post('orderId');//1;//
		if(!$orderId){
			return new Response('请传递订单id');
		}
		$mOrder = Order::findOne(['id' => $orderId, 'user_id' => $this->_getUserMode()->id]);
		if(!$mOrder){
			return new Response('订单不存在');
		}
		if($mOrder->type != Order::ORDER_PAY){
			return new Response('订单不是待退款状态');
		}
		if($mOrder->status != Order::STATUS_APPLY_REFUND){
			return new Response('订单不是待退款状态');
		}
		$mOrder->set('status', Order::STATUS_PAID);
		if($mOrder->save()){
			return new Response('取消退款成功', 1, [
				'order_status' => $mOrder->status,
			]);
		}
		return new Response('取消退款失败');
	}
	
	/*
	 * 追评订单 api/order/superaddition-comment-order.json
	 */
	public function actionSuperadditionComment(){
		$orderId = (int)Yii::$app->request->post('orderId');//1011;//
		$content = trim((string)Yii::$app->request->post('content'));
		if(!$orderId){
			return new Response('请传递订单id');
		}
		if(!$content){
			return new Response('请输入评价内容');
		}
		if(mb_strlen($content, 'utf8') < 5 || mb_strlen($content, 'utf8') > 100){
			return new Response('字数为5至100个字');
		}
		$mOrder = Order::findOne(['id' => $orderId, 'user_id' => $this->_getUserMode()->id]);
		if(!$mOrder){
			return new Response('订单不存在');
		}
		if($mOrder->status != Order::STATUS_FINISH){
			return new Response('订单不是待追评状态');
		}
		$mOrderCommentIndexFirst = OrderCommentIndex::findOne(['and', ['order_id' => $orderId], ['user_id' => $this->_getUserMode()->id], ['is_superaddition' => 0], ['>', 'score', 0]]);
		if(!$mOrderCommentIndexFirst){
			return new Response('订单不是待追评状态');
		}
		$mOrderCommentIndex = OrderCommentIndex::findOne(['order_id' => $orderId, 'user_id' => $this->_getUserMode()->id, 'is_superaddition' => 1]);
		if($mOrderCommentIndex){
			return new Response('你已经追评价过了');
		}
		$aData = [
			'order_id' => $orderId,
			'pid' => $mOrderCommentIndexFirst->id,
			'is_reply' => 0,
			'tenant_id' => $mOrder->tenant_id,
			'is_superaddition' => 1,
			'user_id' => $this->_getUserMode()->id,
			'score' => 0,
			'content' => $content,
			'resource_ids' => [],
		];
		if(OrderCommentIndex::add($aData)){
			$mOrderCommentIndexFirst->set('is_reply', 0);//评价后 初评的 is_reply 改成未回复状态
			$mOrderCommentIndexFirst->save();
			return new Response('订单评价成功', 1);
		}
		return new Response('订单评价失败');
	}
}

