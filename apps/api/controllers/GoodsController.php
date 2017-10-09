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
use common\model\UserCollect;
use common\model\UserAccumulatePointGetRecord;
use yii\web\UploadedFile;
use common\model\form\ImageUploadForm;
use umeworld\lib\PhoneValidator;
use common\model\Redis;
use common\model\UserNotice;

/*
 * 服务&下单&买单
 * @author 谭威力
 */
class GoodsController extends Controller{
	public $enableCsrfValidation = false;
	
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	
	public function behaviors(){
		return \yii\helpers\ArrayHelper::merge([
			'access' => [
				'rules' => [
					[
						'allow' => true,
						'actions' => ['get-goods-list', 'get-teacher-list', 'get-comment-list', 'notify-after-weixin-pay'],//'direct-pay','pay-order', 'get-home-data', 'get-goods-details', , 'before-pay-info'
					],
				],
			],
		], parent::behaviors());
	}
	
	private function _getUserMode(){
		return Yii::$app->user->identity;//User::findOne(1);//
		//return Yii::$app->commercialTenant->getIdentity();
	}
	
	/*
	 * 首页必须参数 api/goods/get-home-data.json
	 */
	public function actionGetHomeData(){
		//debug(Yii::$app->excel->setSheetDataFromArray('成绩模板.xls', [[1,2,3,4],[5,6,7,8]], true));
		$tenantId = (int)Yii::$app->request->post('tenantId');//1;//
		if(!$tenantId){
			return new Response('请选择商铺!');
		}
		$mCommercialTenant = CommercialTenant::findOne($tenantId);
		if(!$mCommercialTenant || $mCommercialTenant->online_status != CommercialTenant::ONLINE_STATUS_ONLINE){
			return new Response('商铺不存在');
		}
		$mUserCollect = UserCollect::findOne(['user_id' => $this->_getUserMode()->id, 'type' => UserCollect::TYPE_SHOP, 'data_id' => $tenantId]);
		$mUser = $this->_getUserMode();
		$aTenantInfo = [
			'name' => $mCommercialTenant->name,
			'avg_score' => $mCommercialTenant->avg_score,
			'pay_discount' => $mCommercialTenant->pay_discount,
			'description' => $mCommercialTenant->description,
			'characteristic_service' => [],
			'accumulate_points' => 0,
			'address' => $mCommercialTenant->address,
			'photo_list' => CommercialTenantPhoto::getList(['tenant_id' => $tenantId]),
			'lng' => $mCommercialTenant->lng,
			'lat' => $mCommercialTenant->lat,
			'is_collect' => $mUserCollect ? 1 : 0,
			'accumulate_points' => $mUser->accumulate_points,
			'contact_number' => $mCommercialTenant->contact_number,
			'preferential_info' => $mCommercialTenant->preferential_info,
		];
		$aCommercialTenantCharacteristicServiceRelation = CommercialTenantCharacteristicServiceRelation::getList(['tenant_id' => $tenantId]);
		if($aCommercialTenantCharacteristicServiceRelation){
			$aServiceTypeIds = ArrayHelper::getColumn($aCommercialTenantCharacteristicServiceRelation, 'service_type_id');
			$aTenantInfo['characteristic_service'] = CharacteristicServiceType::getList(['id' => $aServiceTypeIds], ['select' => ['id', 'name']]);
		}
		$aTenantInfo['accumulate_points'] = $mUser->accumulate_points;
		//debug($aTenantInfo,11);
		return new Response('请求成功', 1, ['aTenantInfo' => $aTenantInfo]);
	}
	
	/*
	 * 获取商品卷列表 api/goods/get-goods-list.json
	 */
	public function actionGetGoodsList(){
		$tenantId = (int)Yii::$app->request->post('tenantId');//2;//
		if(!$tenantId){
			return new Response('请选择商铺!');
		}
		$mCommercialTenant = CommercialTenant::findOne($tenantId);
		if(!$mCommercialTenant || $mCommercialTenant->online_status != CommercialTenant::ONLINE_STATUS_ONLINE){
			return new Response('商铺不存在');
		}
		$aWhere = [
			'tenant_id' => $tenantId,
			'status' => Goods::HAS_PUT_ON,
			'no_validity' => 1,
		];
		$select = ['id', 'name', 'price', 'price', 'retail_price', 'sales_count'];
		$aGoods = Goods::getList($aWhere, ['select' => $select, 'order_by' => ['create_time' => SORT_DESC], 'with_photo_info' => 1]);
		//$aSaleCounts = [];
		/*if($aGoods){
			$aGoodsIds = ArrayHelper::getColumn($aGoods, 'id');
			$aSaleCounts = Order::getGoodsSaleCount(['tenant_id' => $tenantId, 'type' => 2, 'goods_id' => $aGoodsIds]);
		}
		foreach($aGoods as $key => $aGood){
			$aGoods[$key]['sale_num'] = 0;
			foreach($aSaleCounts as $aSaleCount){
				if($aSaleCount['goods_id'] == $aGood['id']){
					$aGoods[$key]['sale_num'] = (int)$aSaleCount['num'];
				}
			}
		}*/
		//debug(Goods::findOne(1)->toArray(),11);
		//debug($aGoods,11);
		return new Response('请求成功', 1, [
			'list' => $aGoods,
			'count' => Goods::getCount($aWhere),
		]);
	}
	
	/*
	 * 获取教师列表 api/goods/get-teacher-list.json
	 */
	public function actionGetTeacherList(){
		$tenantId = (int)Yii::$app->request->post('tenantId');//1;//
		$page = (int)Yii::$app->request->post('page', 1);
		$pageSize = (int)Yii::$app->request->post('pageSize', 4);
		if($page < 1){
			$page = 1;
		}
		if($pageSize < 0){
			$pageSize = 4;
		}
		if(!$tenantId){
			return new Response('请选择商铺!');
		}
		$mCommercialTenant = CommercialTenant::findOne($tenantId);
		if(!$mCommercialTenant || $mCommercialTenant->online_status != CommercialTenant::ONLINE_STATUS_ONLINE){
			return new Response('商铺不存在');
		}
		$select = ['id', 'name', 'profile', 'duty', 'seniority'];
		$aTeachers = Teacher::getList(['tenant_id' => $tenantId], ['select' => $select, 'page' => $page, 'page_size' => $pageSize, 'order_by' => ['seniority' => SORT_DESC]]);
		//debug($aTeachers,11);
		return new Response('请求成功', 1, [
			'list' => $aTeachers,
			'count' => Teacher::getCount(['tenant_id' => $tenantId]),
		]);
	}
	
	/*
	 * 获取评论列 api/goods/get-comment-list.json
	 */
	public function actionGetCommentList(){
		$tenantId = (int)Yii::$app->request->post('tenantId');//1;//
		$page = (int)Yii::$app->request->post('page', 1);
		$pageSize = (int)Yii::$app->request->post('pageSize', 4);
		if($page < 1){
			$page = 1;
		}
		if($pageSize < 0){
			$pageSize = 4;
		}
		if(!$tenantId){
			return new Response('请选择商铺!');
		}
		$mCommercialTenant = CommercialTenant::findOne($tenantId);
		if(!$mCommercialTenant || $mCommercialTenant->online_status != CommercialTenant::ONLINE_STATUS_ONLINE){
			return new Response('商铺不存在');
		}
		$aWhere = [
			'tenant_id' => $tenantId, 
			'user_id_no_0' => 1,
			'pid' => 0,
			'is_superaddition' => 0,
		];
		$aControl = [
			'page' => $page, 
			'page_size' => $pageSize, 
			'order_by' => ['create_time' => SORT_DESC],
			'with_user_info' => true,
			'with_content_info' => true,
			'with_resource_info' => true,
			'with_reply_list' => true,
		];
		$aOrderCommentIndexs = OrderCommentIndex::getList($aWhere, $aControl);
		foreach($aOrderCommentIndexs as &$aOrderCommentIndex){
			$aOrderCommentIndex = \umeworld\lib\ArrayFilter::fastFilter($aOrderCommentIndex, [
				'id',
				'pid',
				'score',
				'content',
				'user_info',
				'resource_info',
				'create_time',
				'reply_list',
			]);
		}
		//debug($aOrderCommentIndexs,11);
		
		return new Response('请求成功', 1, [
			'list' => $aOrderCommentIndexs,
			'count' => OrderCommentIndex::getCount($aWhere),
		]);
	}
	
	/*
	 * 获取商品详情数据 api/goods/get-goods-details.json
	 */
	public function actionGetGoodsDetails(){
		//$tenantId = (int)Yii::$app->request->post('tenantId');//2;//
		$goodsId = (int)Yii::$app->request->post('goodsId');//1;//
		if(!$goodsId){
			return new Response('请选择服务!');
		}
		$mGoods = Goods::findOne(['id' => $goodsId, 'status' => Goods::HAS_PUT_ON]);
		if(!$mGoods || $mGoods->status != Goods::HAS_PUT_ON){
			return new Response('服务不存在!');
		}
		//if(!$tenantId){
		//	return new Response('请选择商铺!');
		//}
		$mCommercialTenant = CommercialTenant::findOne(['id' => $mGoods->tenant_id]);
		if(!$mCommercialTenant || $mCommercialTenant->online_status != CommercialTenant::ONLINE_STATUS_ONLINE){
			return new Response('服务不存在');
		}
		//$aSaleCounts = Order::getGoodsSaleCount(['tenant_id' => $mGoods->tenant_id, 'goods_id' => $mGoods->id]);
		$aReturn = $mGoods->toArray();
		/*$aReturn['sale_num'] = 0;
		foreach($aSaleCounts as $aSaleCount){
			if($aSaleCount['goods_id'] == $aReturn['id']){
				$aReturn['sale_num'] = (int)$aSaleCount['num'];
			}
		}*/
		$mUserCollect = UserCollect::findOne(['user_id' => $this->_getUserMode()->id, 'type' => UserCollect::TYPE_GOODS, 'data_id' => $goodsId]);
		$aReturn['tenant_info'] = $mCommercialTenant->toArray(['id', 'name', 'address', 'contact_number']);
		$aReturn['tenant_info']['lng'] = $mCommercialTenant->lng;
		$aReturn['tenant_info']['lat'] = $mCommercialTenant->lat;
		$aReturn['is_collect'] = $mUserCollect ? 1 : 0;
		$aReturn['goods_info'] = \common\model\GoodsPhoto::getList(['goods_id' => $goodsId], ['order_by' => ['is_cover' => SORT_DESC], 'select' => ['id', 'resource_id', 'is_cover']]);
		$aReturn['accumulate_points'] = $this->_getUserMode()->accumulate_points;
		//debug($aReturn);
		return new Response('请求成功', 1, [
			'aGoodsInfo' => $aReturn,
		]);
	}
	
	/*
	 * 发起订单微信支付 api/goods/start-pay-order.json
	 */
	public function actionPayOrder(){
		$aPost = Yii::$app->request->post();
//		$aPost = [//映射关系
//			'tenantId' => 13, 
//			'goodsId' => 19, 
//			'count' => 1,
//			'isIntegral' => 1, 
//		];
		$mOrderForm = new OrderForm();
		$mOrderForm->scenario = OrderForm::SCENE_ORDER;
		$mOrderForm->type = Order::ORDER_PAY;//订单类型
		$mOrderForm->mUser = $this->_getUserMode();
		$mOrderForm->isZero = 0;
		//积分检查月重置
		//Order::checkAccumulatePointsAndReset($this->_getUserMode()->id);
		if(!$mOrderForm->load($aPost, '') || !$mOrderForm->validate()){
			return new Response(current($mOrderForm->getErrors())[0]);
		}
		$mOrder = $mOrderForm->addData();
		//debug($mOrder,11);
		if($mOrder && $mOrderForm->isZero == 0){
			//调用微信支付组件获取 预支付 数据 返回给前端
			$orderResult = Order::weixinPay($mOrder, $this->_getUserMode()->openid);
			if(!$orderResult){
				Yii::info('订单号为: ' . $mOrder->order_num . ' 预支付流程失败');
				Yii::error('订单号为: ' . $mOrder->order_num . ' 预支付流程失败');
				return new Response('操作失败,请重试!');
			}
			$aReturn = [
				'jsApiParameters' => $orderResult,
				'orderId' => $mOrder->id,
			];
			if(!YII_ENV_PROD){
				unset($aReturn['jsApiParameters']);
			}
			return new Response('下单成功,请及时支付', 1, $aReturn);
		}elseif($mOrder && $mOrderForm->isZero == 1){
			//产生服务码
			$mOrder->set('activation_code', $mOrder->getOrderActivationCode());
			if($mOrder->save()){
				$mGoods = Goods::findOne($mOrder->goods_id);
				//服务销量增加
				if($mGoods){
					$mGoods->set('sales_count', ['add', $mOrder->quantity]);
					$mGoods->save();
				}
				//总销量量 增加
				$mCommercialTenant = CommercialTenant::findOne($mOrder->tenant_id);
				$mCommercialTenant->set('all_sales_count', ['add', 1]);
				$mCommercialTenant->save();
				//插入用户通知
				$aGoodsInfo = $mOrder->goods_info;
				UserNotice::add([
					'user_id' => $mOrder->user_id,
					'title' => '服务券购买成功',
					'content' => '你已成功购买 ' . $aGoodsInfo['name'] . ' <br>数量：' . $mOrder->quantity . '<br>有效期至： ' . date('Y-m-d', $aGoodsInfo['validity_time']) . '<br>服务码：' . $mOrder->activation_code . '<br>请在有效期内持激活码至线下商家激活服务',
					'is_read' => 0,
				]);
				return new Response('下单成功', 1, [
					//'jsApiParameters' => 0,
					'orderId' => $mOrder->id,
				]);
			}
		}
		return new Response('下单失败');
	}
	
	/*
	 * 获取订单支付结果 api/goods/order-pay-result.json
	 */
	public function actionOrderPayResult(){
		$orderId = (int)Yii::$app->request->post('orderId');//1021;//
		//$tenantId = (int)Yii::$app->request->post('tenantId');//1;//
		if(!$orderId){
			return new Response('请输入订单id');
		}
		$mOrder = Order::findOne(['id' => $orderId, 'user_id' => $this->_getUserMode()->id]);
		if(!$mOrder || $mOrder->status < Order::STATUS_PAID){
			return new Response('无效订单');
		}
		$mCommercialTenant = CommercialTenant::findOne($mOrder->tenant_id);
		$aOrderInfo = $mOrder->toArray();
		$aOrderInfo = \umeworld\lib\ArrayFilter::fastFilter($aOrderInfo, [
				'type',
				'order_num',
				'goods_info',
				'quantity',
				'original_price',
				'accumulate_points_money',
				'pay_money',
				'activation_code',
				'price',
		]);
		if($mOrder->type == Order::ORDER_PAY){
			$aOrderInfo['goods_name'] = $aOrderInfo['goods_info']['name'];
			$aOrderInfo['goods_photo'] = $aOrderInfo['goods_info']['goods_photo'];
		}
		unset($aOrderInfo['goods_info']);
		$aOrderInfo['tenant_name'] = $mCommercialTenant->name;
		//需要调用微信组件 获取 支付结果
		$isOk = false;
		if($mOrder->status == Order::STATUS_WAIT_PAY){
			Yii::info('订单号为 :' . $mOrder->order_num . '在支付结果页面状态还是待支付,在微信支付后台查询支付结果....');
			$aOrderQueryResult = Yii::$app->wxPay->orderQuery($mOrder->order_num, $succesCode, $timeOut = 6, false);
			if($succesCode == WxPay::QUERY_SUCC_CODE_FAIL){
				Yii::error('订单号为 :' . $mOrder->order_num . '在微信支付后台没有查到');
				Yii::info('订单号为 :' . $mOrder->order_num . '在微信支付后台没有查到');
				return new Response('订单不存在!');
			}elseif($succesCode == WxPay::QUERY_SUCC_CODE_PLAYING){
				Yii::info('订单号为 :' . $mOrder->order_num . '在微信支付后台还在处理支付中.....');
				$isOk = true;
				//return new Response('订单正在处理,请稍后...');
			}elseif($succesCode == WxPay::QUERY_SUCC_CODE_SUCCESS){
				//更新订单状态
				//$outTradeNo = $aOrderQueryResult['out_trade_no'];	//商户订单号
				//$transactionId = $aOrderQueryResult['transaction_id'];	//微信订单号
				if(!$mOrder->modifyStatus(Order::STATUS_PAID)){
					//return new Response('你没有订单!');
				}
				$isOk = true;
			}
		}elseif($mOrder->status >= Order::STATUS_PAID){
			$isOk = true;
		}
		$aOrderInfo['is_success'] = (int)$isOk;
		return new Response('请求成功', 1, [
			'aResult' => $aOrderInfo,
		]);
	}
	
	/*
	 * 发起买单请求 api/goods/start-direct-pay.json
	 */
	public function actionDirectPay(){
		$aPost = Yii::$app->request->post();
//		$aPost = [//映射关系
//			'tenantId' => 13, 
//			'isIntegral' => 1,
//			'money' => 200, 
//		];
		$mOrderForm = new OrderForm();
		$mOrderForm->scenario = OrderForm::SCENE_DIRECT;
		$mOrderForm->type = Order::DIRECT_PAY;//订单类型;
		//debug($this->_getUserMode()->accumulate_points,11);
		$mOrderForm->mUser = $this->_getUserMode();
		$mOrderForm->isZero = 0;
		//积分检查月重置
		//Order::checkAccumulatePointsAndReset($this->_getUserMode()->id);
		if(!$mOrderForm->load($aPost, '') || !$mOrderForm->validate()){
			return new Response(current($mOrderForm->getErrors())[0]);
		}
		$mOrder = $mOrderForm->addData();
		//debug($mOrder,11);
		if($mOrder && $mOrderForm->isZero == 0){
			//调用微信支付组件获取 预支付 数据 返回给前端
			$orderResult = Order::weixinPay($mOrder, $this->_getUserMode()->openid);
			if(!$orderResult){
				Yii::info('订单号为: ' . $mOrder->order_num . ' 预支付流程失败');
				Yii::error('订单号为: ' . $mOrder->order_num . ' 预支付流程失败');
				return new Response('操作失败,请重试!');
			}
			Yii::info('订单号为: ' . $mOrder->order_num . ' 预支付流程开始发起');
			$aReturn = [
				'jsApiParameters' => $orderResult,
				'orderId' => $mOrder->id,
			];
			if(!YII_ENV_PROD){
				unset($aReturn['jsApiParameters']);
			}
			return new Response('下单成功,请及时支付', 1, $aReturn);
		}elseif($mOrder && $mOrderForm->isZero == 1){
			Yii::info('订单号为: ' . $mOrder->order_num . ' 预支付流程开始发起,因积分抵扣,实际支付0元');
			//商户余额增加
			$mCommercialTenant = CommercialTenant::findOne($mOrder->tenant_id);
			if($mCommercialTenant){
				$mCommercialTenant->addMoney($mOrder->price);
			}else{
				Yii::error('订单号为: ' . $mOrder->orderNum . ' 订单 找不到商家数据 ,余额增加操作失败');
			}
			//总销量量 增加
			$mCommercialTenant->set('all_sales_count', ['add', 1]);
			$mCommercialTenant->save();
			Yii::info('订单号为: ' . $mOrder->order_num . ' 总销量量增加');
			//通知
			UserNotice::add([
				'user_id' => $mOrder->user_id,
				'title' => '买单成功',
				'content' => '买单商户：' . $mCommercialTenant->name . '<br>买单金额：¥ ' . $mOrder->price / 100 . '<br>实际支付：¥ ' . $mOrder->pay_money / 100,
				'is_read' => 0,
			]);
			Yii::info('订单号为: ' . $mOrder->order_num . '买单成功信息通知用户');
			return new Response('下单成功', 1, [
				//'jsApiParameters' => 0,
				'orderId' => $mOrder->id,
			]);
		}
		return new Response('下单失败');
	}
	
	//订单回调
	public function actionNotifyAfterWeixinPay(){
		$aResult = Yii::$app->wxPay->getNotifyResult($message);
		Yii::info('异步通知成功');
		if($aResult){
			Yii::info('异步通知成功, 获取到结果');
			$orderNum = $aResult['out_trade_no'];//商户订单号
			$serialNumber = $aResult['transaction_id'];//微信订单号
			$mOrder = Order::findOne(['order_num' => $orderNum]);
			if(!$mOrder){
				Yii::info('订单号为: ' . $orderNum . ' 不存在,流水号为: '. $serialNumber);
				Yii::error('订单号为: ' . $orderNum . ' 不存在,流水号为: '. $serialNumber);
			}
			if($mOrder->serial_number){
				//如果有微信订单号 说明是第二次调用，不用发微信通知了
				return Yii::$app->wxPay->replyNotify(true);
			}
			//买单
			$mCommercialTenant = CommercialTenant::findOne($mOrder->tenant_id);
			if($mOrder->type == Order::DIRECT_PAY){
				$mOrder->set('status', Order::STATUS_WAIT_COMMENT);
				$mOrder->set('activation_time', NOW_TIME);//激活时间要写上
				//商户余额增加
				if($mCommercialTenant){
					$mCommercialTenant->addMoney($mOrder->price);
				}else{
					Yii::error('订单号为: ' . $orderNum . ' 订单 找不到商家数据 ,余额增加操作失败');
				}
			}else{
			//订单
				$mGoods = Goods::findOne($mOrder->goods_id);
				//服务销量增加
				if($mGoods){
					$mGoods->set('sales_count', ['add', $mOrder->quantity]);
					$mGoods->save();
				}
				$mOrder->set('status', Order::STATUS_PAID);//订单状态变成已支付，还没开始激活
				//产生服务码
				$mOrder->set('activation_code', $mOrder->getOrderActivationCode());
			}
			$mOrder->set('serial_number', $serialNumber);
			$mOrder->set('pay_time', NOW_TIME);
			$mOrder->set('pay_money', $aResult['total_fee']);
			//积分操作已经在表单中处理了
			if(!$mOrder->save()){
				Yii::info('订单号为: ' . $orderNum . ' 状态修改失败,流水号为: '. $serialNumber);
				Yii::error('订单号为: ' . $orderNum . ' 状态修改失败,流水号为: '. $serialNumber);
			}
			//买单
			if($mOrder->type == Order::DIRECT_PAY){
				UserNotice::add([
					'user_id' => $mOrder->user_id,
					'title' => '买单成功',
					'content' => '买单商户：' . $mCommercialTenant->name . '<br>买单金额：¥ ' . $mOrder->price / 100 . '<br>实际支付：¥ ' . $mOrder->pay_money / 100,
					'is_read' => 0,
				]);
			}else{
				$aGoodsInfo = $mOrder->goods_info;
				UserNotice::add([
					'user_id' => $mOrder->user_id,
					'title' => '服务券购买成功',
					'content' => '你已成功购买 ' . $aGoodsInfo['name'] . '<br>数量：' . $mOrder->quantity . '<br>有效期至： ' . date('Y-m-d', $aGoodsInfo['validity_time']) . '<br>服务码：' . $mOrder->activation_code . '<br>请在有效期内持激活码至线下商家激活服务',
					'is_read' => 0,
				]);
			}
			//总销量量 增加
			$mCommercialTenant->set('all_sales_count', ['add', 1]);
			$mCommercialTenant->save();
			//支付成功 将结果 微信 推送 告知教师
			//SnsWeixin::sendNewOrderInform(['inform_role' => 'teacher', 'order_id' => $mOrder->id]);//sendCancelOrderInform
			//支付成功 将结果 微信 推送 告知家长
			//SnsWeixin::sendNewOrderInform(['inform_role' => 'parent', 'order_id' => $mOrder->id]);//sendCancelOrderInform
			Yii::info('订单号为: ' . $orderNum . ' 支付状态,流水号以及支付时间修改完成,流水号为: '. $serialNumber);
			Yii::$app->wxPay->replyNotify(true);
		}else{
			Yii::$app->wxPay->replyNotify(false, $message);
			Yii::error($message);
		}
	}
	
	/*
	 * 图片传输接口 api/goods/upload-file.json
	 */
	public function actionUploadFile(){
		$oForm = new ImageUploadForm();
		$oForm->fCustomValidator = function($oForm){
			/*list($width, $height) = getimagesize($oForm->oImage->tempName);
			if($width != $height){
				$oForm->addError('oImage', '图片宽高比例应为1:1');
				return false;
			}
			return true;*/
			$fileSize = filesize($oForm->oImage->tempName);
			if($fileSize > 2097152){
				//限制2MB
				$oForm->addError('oImage', '图片不能超过2MB');
				return false;
			}
		};
		
		$isUploadFromUEditor = false;
		$savePath = Yii::getAlias('@p.api_comment_upload');

		$oForm->oImage = UploadedFile::getInstanceByName('filecontent');
		//$oForm->toWidth = 300;
		//$oForm->toHeight = 300;
		if(!$oForm->upload($savePath)){
			$message = current($oForm->getErrors())[0];
			return new Response($message, 0);
		}else{
			$id = Resource::add([
				'type' => Resource::TYPE_COMMENT_PHOTO,
				'path' => $oForm->savedFile,
				'create_time' => NOW_TIME,
			]);
			if(!$id){
				return new Response('上传失败', 0);
			}
			return new Response('', 1, [
				'resource_id' => $id,
				'path' => $oForm->savedFile,
			]);
		}
	}
	
	/*
	 * 获取信息 api/goods/before-pay-info.json
	 */
	public function actionBeforePayInfo(){
		$type = (int)Yii::$app->request->post('type');//2;//
		$id = (int)Yii::$app->request->post('id');//1;//
		
		if(!$type || !in_array($type, [Order::ORDER_PAY, Order::DIRECT_PAY])){
			return new Response('请传递正确的类型');
		}
		if(!$id){
			return new Response('请传递id');
		}
		$aReturn = [];
		$iSmobile = $this->_getUserMode()->mobile ? 1 : 0;
		if($type == Order::DIRECT_PAY){
			$mCommercialTenant = CommercialTenant::findOne(['id' => $id, 'online_status' => CommercialTenant::ONLINE_STATUS_ONLINE]);
			if($mCommercialTenant){
				$aReturn = [
					'id' => $id,
					'name' => $mCommercialTenant->name,
					'profile_path' => $mCommercialTenant->profile_path,
				];
			}
		}elseif($type == Order::ORDER_PAY){
			$mGoods = Goods::findOne(['id' => $id, 'status' => Goods::HAS_PUT_ON]);
			if($mGoods){
				$aReturn = [
					'id' => $id,
					'name' => $mGoods->name,
					'profile_path' => $mGoods->profile_path,
				];
			}
		}
		$aReturn['has_mobile'] = $iSmobile;
		return new Response('请求成功', 1, ['info' => $aReturn]);
	}
	
	/*
	 * 获取手机验证码 api/goods/get-mobile-code.json
	 */
	public function actionGetMobileCode(){
		$mobile = (string)Yii::$app->request->post('mobile');//'17620753487';//
		if(!$mobile){
			return new Response('请输入手机号码', 0);
		}
		$isMobile = (new PhoneValidator())->validate($mobile);
		if(!$isMobile){
			return new Response('手机格式不正确', 0);
		}
		$mUser = $this->_getUserMode();
		if($mUser->mobile == $mobile){
			return new Response('该手机号已绑定', 0);
		}
		if(User::findOne(['and', ['!=', 'id', $mUser->id], ['mobile' => $mobile]])){
			return new Response('该手机号已绑定', 0);
		}
		$id = 'bind_u_mobile_' . $mobile;
		$mRedis = Redis::findOne(['id' => $id]);
		if($mRedis && $mRedis->expiration_time - NOW_TIME > 840){
			return new Response('验证码已经发送', -1);
		}
		$code = mt_rand(100000, 999999);

		//向手机发送短信
		$oSms = Yii::$app->sms;
		$oSms->sendTo = $mobile;
		$oSms->content = '您好，您在执行绑定操作，您的验证码是 ' . $code . '此码在十五分钟内有效，请在十五分钟内完成操作。';
		if($oSms->send()){
			if(!$mRedis){
				Redis::add([
					'id' => $id,
					'value' => $code,
					'expiration_time' => NOW_TIME + 900,
				]);
			}else{
				$mRedis->set('value', $code);
				$mRedis->set('expiration_time', ['add', 900]);
				$mRedis->save();
			}
			return new Response('发送验证码成功', 1);
		}
		return new Response('发送验证码失败');
	}
	
	/*
	 * 绑定手机 api/goods/bind-mobile.json
	 */
	public function actionBindMobile(){
		$mobile = (string)Yii::$app->request->post('mobile');//'13725478250';//
		$verifyCode = (string)Yii::$app->request->post('verifyCode');//'390691';//
		if(!$mobile){
			return new Response('请输入手机号码', 0);
		}
		$isMobile = (new PhoneValidator())->validate($mobile);
		if(!$isMobile){
			return new Response('手机格式不正确', 0);
		}
		$id = 'bind_u_mobile_' . $mobile;
		$mRedis = Redis::findOne(['id' => $id]);
		if(!$verifyCode){
			return new Response('请输入验证码', 0);
		}
		//$money = $money * 100;//分转成元 的 单位
		$mUser = $this->_getUserMode();
		if($mUser->mobile == $mobile){
			return new Response('该手机号已绑定', 0);
		}
		if(User::findOne(['and', ['!=', 'id', $mUser->id], ['mobile' => $mobile]])){
			return new Response('该手机号已绑定', 0);
		}
		if(!$mRedis){
			return new Response('验证失败,请重新验证', 0);
		}
		if($mRedis->expiration_time < NOW_TIME){
			return new Response('验证码过期', -1);
		}
		if($mRedis->value != $verifyCode){
			return new Response('验证码不正确', -1);
		}
		
		$mUser->set('mobile', $mobile);
		if($mUser->save()){
			//要清除验证码记录
			$mRedis->delete();
			return new Response('手机绑定成功', 1);
		}
		return new Response('手机绑定失败');
	}
	
	//获取广告 api/goods/get-banner-list.json
	public function actionGetBannerList(){
		return new Response('获取广告列表', 1, ['list' => Yii::$app->params['ui']]);
	}
}

