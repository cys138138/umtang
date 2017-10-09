<?php
namespace manage\controllers;

use Yii;
use manage\lib\Controller;
use manage\model\Order;
use yii\data\Pagination;
use umeworld\lib\Response;

class OrderController extends Controller{
	public function actions() {
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
						'roles' => ['@'],
					],
				],
			],
		], parent::behaviors());
	}
	
	public function actionShowHome(){
		$service = (int)Yii::$app->request->get('service', 0);
		$status = (int)Yii::$app->request->get('status', 0);
		$type = (int)Yii::$app->request->get('type', 0);
		$page = (int)Yii::$app->request->get('page', 1);
		if($page < 1){
			$page = 1;
		}
		$pageSize = 10;
		
		$aCondition = [];
		if($service){
			$aCondition['service'] = $service;
		}
		if($status){
			$aCondition['status'] = $status;
			if($status == Order::STATUS_HAS_ACTIVATE){
				$aCondition['status'] = [Order::STATUS_HAS_ACTIVATE, Order::STATUS_WAIT_COMMENT, Order::STATUS_FINISH];
			}
		}
		if($type){
			$aCondition['type'] = $type;
		}
		$aControl = [
			'order_by' => ['create_time' => SORT_DESC],
			'with_tenant_info' => true,
			'page' => $page,
			'page_size' => $pageSize
		];
		
		$aServiceList = Order::getServiceType();
		$aStatusList = Order::$aStatuses;
		$aTypeList = Order::$aType;
		
		$aOrderList = Order::getList($aCondition, $aControl);
		$orderCount = Order::getCount($aCondition);
		$oPage = new Pagination(['totalCount' => $orderCount, 'pageSize' => $pageSize]);

		return $this->render('home', [
			'aServiceList' => $aServiceList,
			'aStatusList' => $aStatusList,
			'aTypeList' => $aTypeList,
			'service' => $service,
			'status' => $status,
			'type' => $type,
			'aOrderList' => $aOrderList,
			'oPage' => $oPage
		]);
	}
	
	public function actionShowRefundMoneyList(){
		$page = (int)Yii::$app->request->get('page', 1);
		if($page < 1){
			$page = 1;
		}
		$pageSize = 10;
		
		$aCondition = [
			'status' => Order::STATUS_APPLY_REFUND,
			'type' => Order::ORDER_PAY
		];
		
		$aControl = [
			'order_by' => ['pay_time' => SORT_ASC],
			'with_tenant_info' => true,
			'page' => $page,
			'page_size' => $pageSize
		];
		
		$aOrderList = Order::getList($aCondition, $aControl);
		$orderCount = Order::getCount($aCondition);
		$oPage = new Pagination(['totalCount' => $orderCount, 'pageSize' => $pageSize]);
		
		return $this->render('refund_money_list', [
			'aOrderList' => $aOrderList,
			'oPage' => $oPage
		]);
	}

	public function actionRefundMoney(){
		$id = (int)Yii::$app->request->post('id', 0);
		$refundMoney = round((float)Yii::$app->request->post('refundMoney', 0), 2);
		$payMoney = round((float)Yii::$app->request->post('payMoney', 0), 2);
		if(!$id){
			return new Response('缺少必要参数id');
		}
		if(!$refundMoney || $refundMoney < 0){
			return new Response('退款金额应大于0');
		}
		if($refundMoney > $payMoney){
			return new Response('退款金额不应大于实际支付金额');
		}
		
		$mOrder = Order::findOne($id);
		if(!$mOrder){
			return new Response('该订单不存在');
		}
		if(!$mOrder->goods_info){
			return new Response('服务不存在');
		}
		
		$orderRefundMoney = $refundMoney * 100;
		$mOrder->set('refund_money', $orderRefundMoney);
		$mOrder->set('refund_time', NOW_TIME);
		$mOrder->set('status', Order::STATUS_REFUNDED);
		
		$aData=[
			'user_id' => $mOrder->user_id,
			'title' => '退款成功',
			'content' => '你的服务 ' . $mOrder->goods_info['name'] . ' 已退款' . $refundMoney . '元.',
			'is_read' => 0
		];
		
		if($mOrder->save()){
			\common\model\UserNotice::add($aData);
			
			Yii::$app->wxPay->refund([
				'out_refund_no' => $mOrder->order_num,
				'total_fee' => $mOrder->pay_money,
				'refund_fee' => $orderRefundMoney,
				'transaction_id' => $mOrder->serial_number
			]);
			
			return new Response('退款成功', 1);
		}
		
		return new Response('退款失败');
	}
	
	public function actionGenerateExcel(){
		$status = (int)Yii::$app->request->get('status', 0);
		$type = (int)Yii::$app->request->get('type', 0);
		
		$aCondition = [];
		if($status){
			$aCondition['status'] = $status;
		}
		if($type){
			$aCondition['type'] = $type;
		}
		
		$aControl = [
			'order_by' => ['status' => SORT_ASC],
			'with_tenant_info' => true
		];
		
		$aOrderList = Order::getList($aCondition, $aControl);
		
		$aFieldName = ['订单号', '所属商户', '购买服务', '服务类型', '订单类型', '订单状态', '购买人手机号', '原价格（元）', '折扣减免价格（元）', '积分抵扣价格（元）', '实际支付金额（元）', '付款时间', '创建时间'];
		
		$aField = ['order_num', 'tenant_name', 'goods_info', 'service_name', 'type_name', 'status_name', 'mobile', 'original_price', 'pay_discount', 'accumulate_points_money', 'pay_money', 'pay_time', 'create_time'];
		
		$aFieldValue = [];
		foreach($aOrderList as $key => $order){
			foreach($aField as $value){
				switch($value) {
					case 'tenant_name':
						$aFieldValue[$key][$value] = $order['tenant_info']['name'];
						break;
					case 'goods_info':
						if(isset($order['goods_info']['name'])){
							$aFieldValue[$key][$value] = $order['goods_info']['name'];
						}else{
							$aFieldValue[$key][$value] = '';
						}
						break;
					case 'original_price':
					case 'accumulate_points_money':
					case 'pay_money':
						$aFieldValue[$key][$value] = $order[$value]/100;
						break;
					case 'pay_discount':
						if($order['type'] == Order::DIRECT_PAY){
							$aFieldValue[$key][$value] = $order['original_price']/100*(1-$order['tenant_info']['pay_discount']/100);
						}else{
							$aFieldValue[$key][$value] = '-';
						}
						break;
					case 'service_name':
						$aFieldValue[$key][$value] = $order['goods_info']['type_name'];
						break;
					case 'pay_time':
						if($order['pay_time']){
							$aFieldValue[$key][$value] = date('Y-m-d H:i:s', $order['pay_time']);
						}else{
							$aFieldValue[$key][$value] = '';
						}
						break;
					case 'create_time':
						if($order['create_time']){
							$aFieldValue[$key][$value] = date('Y-m-d H:i:s', $order['create_time']);
						}else{
							$aFieldValue[$key][$value] = '';
						}
						break;
					default:
						$aFieldValue[$key][$value] = $order[$value];
						break;
				}
			}
		}
		array_unshift($aFieldValue, $aFieldName);
		$time = date('Ymd');
		return Yii::$app->excel->setSheetDataFromArray('订单列表' . $time . '.xls', $aFieldValue, true);
	}
}