<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\Response;
use common\model\CommercialTenantAnnouncement;

class AnnouncementController extends Controller{
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	public function actionGetList(){
		$page = (int)Yii::$app->request->post('page');
		$pageSize = (int)Yii::$app->request->post('pageSize');
		
		if(!$page || $page <= 0){
			$page = 1;
		}
		if(!$pageSize || $pageSize <= 0){
			$pageSize = 10;
		}
		
		$aCondition = [];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
			'order_by' => ['id' => SORT_DESC],
		];
		$aList = CommercialTenantAnnouncement::getList($aCondition, $aControl);
		$count = CommercialTenantAnnouncement::getCount($aCondition);
		
		return new Response('', 1, [
			'totalCount' => $count,
			'aList' => $aList,
		]);
	}
	
	public function actionShowDetail(){
		$id = (int)Yii::$app->request->get('id');
		
		if(!$id){
			return new Response('缺少公告id', 0);
		}
		$mCommercialTenantAnnouncement = CommercialTenantAnnouncement::findOne($id);
		if(!$mCommercialTenantAnnouncement){
			return new Response('找不到公告信息', 0);
		}
		
		$aAnnouncement = $mCommercialTenantAnnouncement->toArray();
		
		return $this->renderPartial('detail', [
			'aAnnouncement' => $aAnnouncement
		]);
	}
		
}