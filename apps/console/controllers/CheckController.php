<?php
namespace console\controllers;

use Yii;
use common\model\Order;
use yii\helpers\ArrayHelper;
use common\model\UserNotice;

/*
 * 检查
 * twl
 */
class CheckController extends \yii\console\Controller{
	public function actionCheck(){
		$nowTime = strtotime(date('Y-m-d', NOW_TIME));
		$afterThreeDayStartTime = 86400 * 3 + $nowTime;//3天后开始时间
		$afterThreeDayEndTime = $afterThreeDayStartTime + 86399;//3天后结束时间
		$aOrderList = Order::getList(['status' => Order::STATUS_PAID, 'type' => Order::ORDER_PAY, 'has_validity_time' => ['min' => $afterThreeDayStartTime, 'max' => $afterThreeDayEndTime]]);
		if(!$aOrderList){
			return false;
		}
		foreach($aOrderList as $aOrder){
			UserNotice::add([
				'user_id' => $aOrder['user_id'],
				'title' => '商品即将过期',
				'content' => '你的商品 ' . $aOrder['goods_info']['name'] . ' 还有3天就要过期了，为保证你的利益，请尽快使用',
				'is_read' => 0,
			]);
		}
		return true;
	}
}