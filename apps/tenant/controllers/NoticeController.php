<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\Response;
use common\model\CommercialTenantNotice;

class NoticeController extends Controller{
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	public function actionIndex(){
		return $this->render('index');
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
		
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$aCondition = ['tenant_id' => $mCommercialTenant->id];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
			'order_by' => ['id' => SORT_DESC],
		];
		$aList = CommercialTenantNotice::getList($aCondition, $aControl);
		$count = CommercialTenantNotice::getCount($aCondition);
		
		return new Response('', 1, [
			'totalCount' => $count,
			'aList' => $aList,
		]);
	}
	
	public function actionSetRead(){
		$id = (int)Yii::$app->request->post('id');
		
		if(!$id){
			return new Response('id不能空', 0);
		}
		$mCommercialTenantNotice = CommercialTenantNotice::findOne($id);
		if(!$mCommercialTenantNotice){
			return new Response('找不到通知', 0);
		}
		if($mCommercialTenantNotice->tenant_id != Yii::$app->commercialTenant->id){
			return new Response('出错啦', 0);
		}
		if(!$mCommercialTenantNotice->is_read){
			$mCommercialTenantNotice->set('is_read', 1);
			$mCommercialTenantNotice->save();
		}
		return new Response('ok', 1);
	}
}