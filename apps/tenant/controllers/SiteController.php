<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\PhoneValidator;
use umeworld\lib\Response;
use common\model\OrderCommentIndex;
use common\model\Order;

class SiteController extends Controller{
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	public function actionShowIndex(){
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mCommercialTenantAction = $mCommercialTenant->getMTenantAction();
		$aNote = $mCommercialTenantAction->note;
		$lastCommentReadTime = isset($aNote['last_comment_read_time']) ? $aNote['last_comment_read_time'] : 0;
		$lastOrderReadTime = isset($aNote['last_order_read_time']) ? $aNote['last_order_read_time'] : 0;
		$newCommentCount = OrderCommentIndex::getCount([
			'tenant_id' => $mCommercialTenant->id,
			'start_time' => $lastCommentReadTime,
		]);
		$newOrderCount = Order::getCount([
			'tenant_id' => $mCommercialTenant->id,
			'start_time' => $lastOrderReadTime,
		]);
		$totalOrderPrice = Order::getTotalOrderPriceByTenantId($mCommercialTenant->id);
		
		$aStatisticsInfo = [
			'new_comment_count' => $newCommentCount,
			'new_order_count' => $newOrderCount,
			'total_order_price' => $totalOrderPrice,
		];
		return $this->render('index', [
			'aStatisticsInfo' => $aStatisticsInfo
		]);
	}
	
}