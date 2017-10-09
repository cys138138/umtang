<?php
namespace manage\model;

use Yii;
use umeworld\lib\Query;

class CommercialTenantApprove extends \common\model\CommercialTenantApprove{
	public function fillTenantNotPassReason($aReason){
		$aTenantApproveField = ['bank_name', 'bank_accout', 'bank_account_holder', 'leading_official', 'identity_card', 'email', 'identity_card_front', 'identity_card_back', 'identity_card_in_hand', 'bank_card_photo', 'other_info'];
		$aTenantInfo = $this->tenant_info;
		$isChange = false;
		foreach($aReason as $key => $value){
			if(in_array($key, $aTenantApproveField)){
				if($key == 'other_info'){
					foreach($value as $key2 => $value2){
						if(!$value2){
							continue;
						}
						$isChange = true;
						$aTenantInfo[$key][$key2]['reason'] = $value2;
					}
				}else{
					if($value){
						$isChange = true;
						$aTenantInfo[$key]['reason'] = $value;
					}
				}
			}
		}
		if($isChange){
			$this->set('tenant_approve_status', static::STATUS_ONT_PASS_APPROVE);
		}else{
			$this->set('tenant_approve_status', static::STATUS_PASS_APPROVE);
		}
		return $this->set('tenant_info', $aTenantInfo);
	}
	
	public function fillShopNotPassReaSon($aReason){
		$aShopApproveField = ['name', 'profile', 'contact_number', 'address', 'description', 'photo', 'characteristic_service_type', 'commercial_tenant_type'];
		$aShopInfo = $this->shop_info;
		$isChange = false;
		foreach($aReason as $key => $value){
			if(in_array($key, $aShopApproveField)){
				if(in_array($key, ['photo', 'characteristic_service_type', 'commercial_tenant_type'])){
					foreach($value as $key2 => $value2){
						if(!$value2){
							continue;
						}
						$isChange = true;
						$aShopInfo[$key][$key2]['reason'] = $value2;
					}
				}else{
					if($value){
						$isChange = true;
						$aShopInfo[$key]['reason'] = $value;
					}
				}
			}
		}
		if($isChange){
			$this->set('shop_approve_status', static::STATUS_ONT_PASS_APPROVE);
		}else{
			$this->set('shop_approve_status', static::STATUS_PASS_APPROVE);
		}
		return $this->set('shop_info', $aShopInfo);
	}
	
	public function changeApproveStatus($approveInfo){
		$aScences = ['firstApprove', 'tenantApprove', 'shopApprove'];
		if(!isset($approveInfo['button']) || !$approveInfo['button']){
			return new Response('缺少必要参数');
		}
		if(!isset($approveInfo['scene']) || !$approveInfo['scene']){
			return new Response('缺少必要参数');
		}
		if(in_array($approveInfo['scene'], $aScences)){
			if($approveInfo['button'] == '审核'){
				if($approveInfo['scene'] == 'firstApprove'){
					$this->set('tenant_approve_status', static::STATUS_IN_APPROVE);
					$this->set('shop_approve_status', static::STATUS_IN_APPROVE);
				}elseif($approveInfo['scene'] == 'tenantApprove'){
					$this->set('tenant_approve_status', static::STATUS_IN_APPROVE);
				}else{
					$this->set('shop_approve_status', static::STATUS_IN_APPROVE);
				}
			}else{
				if($approveInfo['scene'] == 'firstApprove'){
					$this->set('tenant_approve_status', static::STATUS_WAIT_APPROVE);
					$this->set('shop_approve_status', static::STATUS_WAIT_APPROVE);
				}elseif($approveInfo['scene'] == 'tenantApprove'){
					$this->set('tenant_approve_status', static::STATUS_WAIT_APPROVE);
				}else{
					$this->set('shop_approve_status', static::STATUS_WAIT_APPROVE);
				}
			}
		}
	}
}