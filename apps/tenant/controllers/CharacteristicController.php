<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\Response;
use umeworld\lib\StringHelper;
use yii\helpers\ArrayHelper;
use common\model\CharacteristicServiceType;
use common\model\CommercialTenantCharacteristicServiceRelation;
use common\model\CommercialTenantApprove;

class CharacteristicController extends Controller{
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	public function actionIndex(){
		$aCharacteristicList = CharacteristicServiceType::getList(['tenant_id' => [0, Yii::$app->commercialTenant->id]]);
		$aCommercialTenantCharacteristicServiceRelation = CommercialTenantCharacteristicServiceRelation::getList(['tenant_id' => Yii::$app->commercialTenant->id]);
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aApproveRelationId = [];
		if(isset($mTenantApprove->shop_info['commercial_tenant_characteristic_service_relation'])){
			$aApproveRelationId = ArrayHelper::getColumn($mTenantApprove->shop_info['commercial_tenant_characteristic_service_relation'], 'value');
		}
		foreach($aApproveRelationId as $approveRelationId){
			array_push($aCommercialTenantCharacteristicServiceRelation, [
				'id' => 0,
				'tenant_id' => Yii::$app->commercialTenant->id,
				'service_type_id' => $approveRelationId,
			]);
		}
		return $this->render('index', [
			'aCharacteristicList' => $aCharacteristicList,
			'aCommercialTenantCharacteristicServiceRelation' => $aCommercialTenantCharacteristicServiceRelation,
		]);
	}

	public function actionSaveSetting(){
		$aIds = array_unique((array)Yii::$app->request->post('aIds'));
		
		$aCharacteristicList = CharacteristicServiceType::getList(['id' => $aIds]);
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aShopInfo = $mTenantApprove->shop_info;
		if($aCharacteristicList){
			//最终要保存的关系id
			$aCharacteristicId = ArrayHelper::getColumn($aCharacteristicList, 'id');
			$aCommercialTenantCharacteristicServiceRelation = CommercialTenantCharacteristicServiceRelation::getList(['tenant_id' => Yii::$app->commercialTenant->id]);
			//旧的关系id
			$aRelationCharacteristicId = ArrayHelper::getColumn($aCommercialTenantCharacteristicServiceRelation, 'service_type_id');

			$aShopInfo['commercial_tenant_characteristic_service_relation'] = [];
			//新的待审核id
			$aNewApproveRelationId = array_diff($aCharacteristicId, array_intersect($aCharacteristicId,$aRelationCharacteristicId));
			foreach($aNewApproveRelationId as $newApproveRelationId){
				array_push($aShopInfo['commercial_tenant_characteristic_service_relation'], ['value' => $newApproveRelationId]);
			}
			$mTenantApprove->set('shop_info', $aShopInfo);
			$mTenantApprove->set('shop_approve_status', CommercialTenantApprove::STATUS_WAIT_APPROVE);
			$mTenantApprove->save();
			//要删除的旧关系id
			$aDeleteOldRealtionId = array_diff($aRelationCharacteristicId, array_intersect($aCharacteristicId,$aRelationCharacteristicId));
			if($aDeleteOldRealtionId){
				CommercialTenantCharacteristicServiceRelation::deleteByCondition(['tenant_id' => Yii::$app->commercialTenant->id, 'service_type_id' => $aDeleteOldRealtionId]);
			}
		}else{
			//删除所有已有的
			CommercialTenantCharacteristicServiceRelation::deleteByCondition(['tenant_id' => Yii::$app->commercialTenant->id]);
			unset($aShopInfo['commercial_tenant_characteristic_service_relation']);
			$mTenantApprove->set('shop_info', $aShopInfo);
			if(!$aShopInfo){
				$mTenantApprove->set('shop_approve_status', CommercialTenantApprove::STATUS_PASS_APPROVE);
			}
			$mTenantApprove->save();
		}
		return new Response('保存成功', 1);
	}
	
	public function actionAdd(){
		$name = (string)Yii::$app->request->post('name');
		
		if(!$name){
			return new Response('请输入特色服务名称', -1);
		}
		$len = StringHelper::getStringLength($name);
		if($len < 1 || $len > 5){
			return new Response('特色服务名称1~5个字', -1);
		}
		$id = CharacteristicServiceType::add([
			'tenant_id' => Yii::$app->commercialTenant->id,
			'name' => $name,
			'create_time' => NOW_TIME,
		]);
		if(!$id){
			return new Response('添加失败', 0);
		}
		/*$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aShopInfo = $mTenantApprove->shop_info;
		if(isset($aShopInfo['characteristic_service_type'])){
			array_push($aShopInfo['characteristic_service_type'], ['value' => $id]);
		}else{
			$aShopInfo['characteristic_service_type'] = [['value' => $id]];
		}
		$mTenantApprove->set('shop_info', $aShopInfo);
		$mTenantApprove->save();*/
		
		return new Response('添加成功', 1, $id);
	}
	
}