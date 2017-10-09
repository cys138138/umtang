<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\Response;
use common\model\CommercialTenant;
use common\model\Order;
use common\model\UserNotice;


/*
 * 服务卷
 * @author 谭威力
 */
class GoodsVolumeController extends Controller{
	private function _getTenantMode(){
		return Yii::$app->commercialTenant->getIdentity();
	}
	
	/*
	 * 首页 tenant/goods-volume/show-home.html
	 */
	public function actionShowHome(){
		return $this->render('home');
	}
	
	/*
	 * 获取服务卷数据 tenant/goods-volume/get-activate-info.json
	 */
	public function actionGetActivateInfo(){
		$goodsCode = trim((string)Yii::$app->request->post('goodsCode'));
		if(!$goodsCode){
			return new Response('请传递正确服务码');
		}
		$aWhere = [
			'activation_code' => $goodsCode,
			'tenant_id' => $this->_getTenantMode()->id,
		];
		$select = ['id', 'order_num', 'goods_id', 'type', 'price', 'quantity', 'user_id', 'mobile', 'pay_time', 'status', 'activation_time', 'validity_time'];
		$aOrderInfo = Order::getList($aWhere, ['select' => $select]);
		if(!$aOrderInfo){
			return new Response('请传递正确的商卷码');
		}
		return new Response('请求成功', 1, [
			'order' => current($aOrderInfo)
		]);
	}
	
	/*
	 * 激活服务品 tenant/goods-volume/activate.json
	 */
	public function actionActivate(){
		$goodsCode = trim((string)Yii::$app->request->post('goodsCode'));
		//$goodsId = (int)Yii::$app->request->post('goodsId');
		if(!$goodsCode){
			return new Response('请传递正确服务码');
		}
		$aWhere = [
			'activation_code' => $goodsCode,
			'tenant_id' => $this->_getTenantMode()->id,
		];
		$aOrder = current(Order::getList($aWhere));
		if(!$aOrder){
			return new Response('请传递正确服务码');
		}
		if($aOrder['activation_time']){
			return new Response('该服务码已经使用过');
		}
		if($aOrder['status'] != Order::STATUS_PAID){//已经支付 状态才行
			return new Response('该服务码无效');
		}
		if($aOrder['validity_time'] < NOW_TIME){
			return new Response('该服务码已经过期');
		}
		$mOrder = Order::toModel($aOrder);
		$mOrder->set('activation_time', NOW_TIME);
		//$mOrder->set('activation_code', '');
		$mOrder->set('status', Order::STATUS_WAIT_COMMENT);
		if($mOrder->save()){
			//将账户余额提高
			$mCommercialTenant = $this->_getTenantMode();
			if(!$mCommercialTenant->addMoney($mOrder->price - $mOrder->fee)){//余额添加操作, 减去手续费
				Yii::error('服务码激活成功,但是商户余额并没有修改成功 订单号为:' . $mOrder->order_num . '.');
			}
			UserNotice::add([
				'user_id' => $mOrder->user_id,
				'title' => '服务激活成功',
				'content' => '你的服务 ' . $aOrder['goods_info']['name'] . ' 已激活',
				'is_read' => 0,
			]);
			return new Response('激活成功', 1);
		}
		return new Response('激活失败');
	}
}

