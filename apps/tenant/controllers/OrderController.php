<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\Response;
use common\model\CommercialTenant;
use common\model\Order;


/*
 * 订单
 * @author 谭威力
 */
class OrderController extends Controller{
	private function _getTenantMode(){
		return Yii::$app->commercialTenant->getIdentity();
	}
	
	private function _getOrderTypeList(){
		return [		
			[
				'key' => 2,
				'value' => '服务订单',
			],		
			[
				'key' => 1,
				'value' => '买单',
			],
		];
	}
	
	/*
	 * 首页 tenant/order/show-home.html
	 */
	public function actionShowHome(){
		$mCommercialTenant = $this->_getTenantMode();
		$mCommercialTenantAction = $mCommercialTenant->getMTenantAction();
		$aNote = $mCommercialTenantAction->note;
		$aNote['last_order_read_time'] = NOW_TIME;
		$mCommercialTenantAction->set('note', $aNote);
		$mCommercialTenantAction->save();
		return $this->render('home', [
			'aTimeStatus' => Order::getTimeTypeList(),
			'aOrderStatus' => Order::getOrderStatusList(),
			'aOrderType' => $this->_getOrderTypeList(),
			'aOrderAllStatus' => Order::getOrderStatusList(true),
		]);
	}
	
	/*
	 * 获取相关的订单数据 tenant/order/get-order-data.json
	 */
	public function actionGetOrderList(){
		$time = (int)Yii::$app->request->post('time');0;//
		$type = (int)Yii::$app->request->post('type');2;//
		$status = (int)Yii::$app->request->post('status');0;//
		$page = (int)Yii::$app->request->post('page', 1);1;//
		$pageSize = (int)Yii::$app->request->post('pageSize', 10);
		if($page < 1){
			$page = 1;
		}
		if($pageSize < 0){
			$pageSize = 10;
		}
		$aTimeTypeKeys = \yii\helpers\ArrayHelper::getColumn(Order::getTimeTypeList(), 'key');
		if(!in_array($time, $aTimeTypeKeys)){
			return new Response('请选择正确的时间范围');
		}
		$aOrderTypeKeys = \yii\helpers\ArrayHelper::getColumn($this->_getOrderTypeList(), 'key');
		if(!in_array($type, $aOrderTypeKeys)){
			return new Response('请选择正确的订单类型');
		}
		$aOrderStatusKeys = \yii\helpers\ArrayHelper::getColumn(Order::getOrderStatusList(), 'key');
		if(!in_array($status, $aOrderStatusKeys)){
			return new Response('请选择正确的订单状态');
		}
		$aWhere = [
			'type' => $type,
			'tenant_id' => $this->_getTenantMode()->id,
		];
		if($time){
			$aWhere['start_time'] = Order::getTimeTypeList('behind')[$time]['start'];
			$aWhere['end_time'] = Order::getTimeTypeList('behind')[$time]['end'];
		}else{
			$aWhere['activation_time_no_null'] = true;
		}
		if($status){
			$aWhere['status'] = $status;
		}else{
			$aWhere['status'] = [Order::STATUS_WAIT_COMMENT, Order::STATUS_FINISH];
		}
		$select = ['id', 'order_num', 'goods_id', 'type', 'price', 'quantity', 'user_id', 'mobile', 'pay_time', 'status', 'activation_time'];
		$aOrder = Order::getList($aWhere, ['page' => $page, 'page_size' => $pageSize, 'order_by' => ['activation_time' => SORT_DESC], 'select' => $select]);
		return new Response('请求成功', 1, [
			'list' => $aOrder,
			'count' => Order::getCount($aWhere),
		]);
	}
}

