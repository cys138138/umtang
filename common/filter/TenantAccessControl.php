<?php
namespace common\filter;

use Yii;

class TenantAccessControl extends \yii\filters\AccessControl{
	public $user = 'commercialTenant';	//控制哪个APP用户组件的访问
	public $aUmRules = [];		//自定义的规则
	public $denyMessage = '';
	public $aNoCsrfActions = [];

	//用户角色标记
	const TENANTS = 'tenant';

	public function beforeAction($action){
		
		if(in_array($action->id, $this->aNoCsrfActions)){
			$action->controller->enableCsrfValidation = false;
		}
		$oUserRole = $this->user;
		$mUser = $oUserRole->getIdentity();
		return parent::beforeAction($action);
	}

	/**
	 * 权限验证不通过的回调
	 * @param type $oWebUser WEB用户对象,未登陆的时候任何人都可能是,登陆的时候就是学生
	 * @throws ForbiddenHttpException
	 * @return type mixed
	 */
    protected function denyAccess($oWebUser){
		$isGuest = $oWebUser->getIsGuest();
        if($isGuest){
            return $oWebUser->loginRequired();
		}elseif(!$oWebUser->can(self::TENANTS)){
			return $oWebUser->tenantRequired();
		}else{
            throw new \yii\web\ForbiddenHttpException(\Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }
}