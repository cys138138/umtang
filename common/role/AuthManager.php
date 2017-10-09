<?php
namespace common\role;


class AuthManager extends \yii\base\Component{
	public $aPermissionList = [];

	public function init() {
		parent::init();
	}
	
	public function	checkAccess($userId, $permissionName){
		if(!$userId){
			return false;
		}
		
		$mUser = null;
		if($permissionName == \common\filter\TenantAccessControl::TENANTS){
			$mUser = \common\model\CommercialTenant::findOne($userId);
		}

		if(!$mUser){
			throw new \yii\base\InvalidParamException('无效的用户ID');
		}

		return $mUser->allow($permissionName);
	}
}