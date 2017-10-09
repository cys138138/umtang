<?php
namespace manage\controllers;

use Yii;
use manage\lib\Controller;
use common\model\CharacteristicServiceType;
use manage\model\CommercialTenantType;
use yii\data\Pagination;
use umeworld\lib\Response;

class TenantServiceController extends Controller{
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
	
	public function actionShowDefaultCharacteristicServiceTypeList(){
		$page = (int)Yii::$app->request->get('page', 1);
		if($page < 1){
			$page = 1;
		}
		$pageSize = 10;
		
		$aCondition = ['tenant_id' => 0];
		
		$aControl = [
			'order_by' => ['id' => SORT_ASC], 
			'page' => $page,
			'page_size' => $pageSize
		];
		
		$aServiceType = CharacteristicServiceType::getList($aCondition, $aControl);
		$serviceCount = CharacteristicServiceType::getCount($aCondition);
		$oPage = new Pagination(['totalCount' => $serviceCount, 'pageSize' => $pageSize]);
		
		return $this->render('characteristic_service_type', [
			'aServiceType' => $aServiceType,
			'oPage' => $oPage
		]);
	}
	
	public function actionTenantServiceEdit(){
		$id = (int)Yii::$app->request->post('id', 0);
		$serviceName = (string)trim(Yii::$app->request->post('serviceName', ''));
		if(!$id){
			return new Response('缺少必要参数id');
		}
		if(!$serviceName){
			return new Response('名称不为空');
		}
		
		$mTenantService = CharacteristicServiceType::findOne($id);
		if(!$mTenantService){
			return new Response('服务类型不存在');
		}
		
		$mTenantService->set('name', $serviceName);
		$mTenantService->set('create_time', NOW_TIME);
		
		if($mTenantService->save()){
			return new Response('修改成功', 1);
		}
		
		return new Response('修改失败');
	}
	
	public function actionTenantServiceDelete(){
		$id = (int)Yii::$app->request->post('id', 0);
		if(!$id){
			return new Response('缺少必要参数id');
		}
		
		$mTenantService = CharacteristicServiceType::findOne($id);
		if(!$mTenantService){
			return new Response('服务类型不存在');
		}
		
		$mTenantServiceRelation = \common\model\CommercialTenantCharacteristicServiceRelation::findOne(['service_type_id' => $id]);
		if($mTenantServiceRelation){
			return new Response('该服务已被使用');
		}
		
		if($mTenantService->delete()){
			return new Response('删除成功', 1);
		}
		
		return new Response('删除失败');
	}
	
	public function actionAddCharacteristicServiceType(){
		$serviceType = (string)trim(Yii::$app->request->post('serviceType', ''));
		if(!$serviceType){
			return new Response('服务名称不为空');
		}
		
		$mTenantService = CharacteristicServiceType::findOne(['name' => $serviceType, 'tenant_id' => 0]);
		if($mTenantService){
			return new Response('服务已存在');
		}
		
		$aData = [
			'tenant_id' => 0,
			'name' => $serviceType,
			'create_time' => NOW_TIME
		];
		
		if(CharacteristicServiceType::add($aData)){
			return new Response('服务新增成功', 1);
		}
		
		return new Response('服务新增失败');
	}
	
	public function actionShowCommercialTenantTypeList(){
		$page = (int)Yii::$app->request->get('page', 1);
		if($page < 1){
			$page = 1;
		}
		$pageSize = 10;
		
		$aCondition = [];
		
		$aControl = [
			'order_by' => ['id' => SORT_ASC], 
			'page' => $page,
			'page_size' => $pageSize
		];
		
		$aTenantType = CommercialTenantType::getList($aCondition, $aControl);
		$typeCount = CommercialTenantType::getCount($aCondition);
		$oPage = new Pagination(['totalCount' => $typeCount, 'pageSize' => $pageSize]);
		
		return $this->render('commercial_tenant_type', [
			'aTenantType' => $aTenantType,
			'oPage' => $oPage
		]);
	}
	
	public function actionTenantTypeEdit(){
		$id = (int)Yii::$app->request->post('id', 0);
		$typeName = (string)trim(Yii::$app->request->post('typeName', ''));
		if(!$id){
			return new Response('缺少必要参数id');
		}
		if(!$typeName){
			return new Response('类型名称不为空');
		}
		
		$mTenantType = CommercialTenantType::findOne($id);
		if(!$mTenantType){
			return new Response('商户类型不存在');
		}
		
		$mTenantType->set('name', $typeName);
		$mTenantType->set('create_time', NOW_TIME);
		
		if($mTenantType->save()){
			return new Response('修改成功', 1);
		}
		
		return new Response('修改失败');
	}
	
	public function actionTenantTypeDelete(){
		$id = (int)Yii::$app->request->post('id', 0);
		if(!$id){
			return new Response('缺少必要参数id');
		}
		
		$mCommercialTenantType = CommercialTenantType::findOne($id);
		if(!$mCommercialTenantType){
			return new Response('商户类型不存在');
		}
		
		$mCommercialTenantTypeRelation = \common\model\CommercialTenantTypeRelation::findOne(['type_id' => $id]);
		$mGoods = \common\model\Goods::findOne(['type_id' => $id]);
		if($mCommercialTenantTypeRelation || $mGoods){
			return new Response('该商户类型已被使用');
		}
		
		if($mCommercialTenantType->delete()){
			return new Response('删除成功', 1);
		}
		
		return new Response('删除失败');
	}
	
	public function actionAddCommercialTenantType(){
		$typeName = (string)trim(Yii::$app->request->post('typeName', ''));
		if(!$typeName){
			return new Response('类型名称不为空');
		}
		
		if(CommercialTenantType::findOne(['name' => $typeName])){
			return new Response('商户类型已存在');
		}
		
		if(CommercialTenantType::addTenantType($typeName)){
			return new Response('商户类型新增成功', 1);
		}
		
		return new Response('商户类型新增失败');
	}
}