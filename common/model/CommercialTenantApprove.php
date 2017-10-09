<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use common\lib\DbOrmModel;

class CommercialTenantApprove extends DbOrmModel{
	protected $_aEncodeFields = ['tenant_info', 'shop_info'];
	
	const STATUS_WAIT_APPROVE = 1;	//等待审核
	const STATUS_IN_APPROVE = 2;	//审核中
	const STATUS_PASS_APPROVE = 3;	//审核通过
	const STATUS_ONT_PASS_APPROVE = 4;	//审核未通过
	
	public static function tableName(){
        return Yii::$app->db->parseTable('_@commercial_tenant_approve');
    }
	
	public function getTenantInfoWithPath(){
		$aTenantInfo = $this->tenant_info;
		$aResourceIds = [];
		foreach($aTenantInfo as $field => $xValue){
			if(in_array($field, ['identity_card_front', 'identity_card_back', 'identity_card_in_hand', 'bank_card_photo', 'other_info'])){
				if($field == 'other_info'){
					foreach($xValue as $key => $value){
						$aTenantInfo[$field][$key]['path'] = '';
						$aResourceIds[] = $value['value'];
					}
				}else{
					$aTenantInfo[$field]['path'] = '';
					$aResourceIds[] = $xValue['value'];
				}
			}
		}
		$aResourceList = Resource::findAll(['id' => $aResourceIds]);
		if(!$aResourceList){
			return $aTenantInfo;
		}
		$aResourceList = \yii\helpers\ArrayHelper::index($aResourceList, 'id');
		foreach($aTenantInfo as $field => $xValue){
			if(in_array($field, ['identity_card_front', 'identity_card_back', 'identity_card_in_hand', 'bank_card_photo', 'other_info'])){
				if($field == 'other_info'){
					foreach($xValue as $key => $value){
						if(isset($aResourceList[$value['value']])){
							$aTenantInfo[$field][$key]['path'] = $aResourceList[$value['value']]['path'];
						}
					}
				}else{
					if(isset($aResourceList[$xValue['value']])){
						$aTenantInfo[$field]['path'] = $aResourceList[$xValue['value']]['path'];
					}
				}
			}
		}
		return $aTenantInfo;
	}
	
	public function getShopInfoWithPath(){
		$aShopInfo = $this->shop_info;
		$aResourceIds = [];
		foreach($aShopInfo as $field => $xValue){
			if($field == 'profile'){
				$aResourceIds[] = $xValue['value'];
				$aShopInfo[$field]['path'] = '';
			}elseif($field == 'photo'){
				foreach($xValue as $key => $value){
					$aResourceIds[] = $value['resource_id'];
					$aShopInfo[$field][$key]['path'] = '';
				}
			}elseif($field == 'teacher'){
				foreach($xValue as $key => $value){
					if(isset($value['profile'])){
						$aResourceIds[] = $value['profile'];
						$aShopInfo[$field][$key]['profile_path'] = '';
					}
				}
			}
		}
		$aResourceList = Resource::findAll(['id' => $aResourceIds]);
		if(!$aResourceList){
			return $aShopInfo;
		}
		$aResourceList = \yii\helpers\ArrayHelper::index($aResourceList, 'id');
		foreach($aShopInfo as $field => $xValue){
			if($field == 'profile'){
				if(isset($aResourceList[$xValue['value']])){
					$aShopInfo[$field]['path'] = $aResourceList[$xValue['value']]['path'];
				}
			}elseif($field == 'photo'){
				foreach($xValue as $key => $value){
					if(isset($aResourceList[$value['resource_id']])){
						$aShopInfo[$field][$key]['path'] = $aResourceList[$value['resource_id']]['path'];
					}
				}
			}elseif($field == 'teacher'){
				foreach($xValue as $key => $value){
					if(isset($value['profile']) && isset($aResourceList[$value['profile']])){
						$aShopInfo[$field][$key]['profile_path'] = $aResourceList[$value['profile']]['path'];
					}
				}
			}
		}
		return $aShopInfo;
	}
}