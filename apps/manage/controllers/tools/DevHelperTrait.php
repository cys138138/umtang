<?php
namespace manage\controllers\tools;

use Yii;
use umeworld\lib\Response;
use common\model\CommercialTenant;
use common\role\CommercialTenantRole;

/*
 * 后门辅助工具
 */
trait DevHelperTrait{
	//后门工具首页
	public function actionShowHome(){
		return $this->renderPartial('home');
	}
	
	//登陆
	public function actionLoginTenant(){
		$oYii = Yii::$app;
		$tenantId = trim((int)$oYii->request->post('tenantId'));
		$typeId = trim((int)$oYii->request->post('type'));

		$isLogin = false;
		if($typeId == 1){
		//商户登陆
			$mTenant = CommercialTenant::findOne($tenantId);
			if($mTenant){
				$result = $oYii->commercialTenant->login($mTenant, 0, 1);
				if($result){
					$isLogin = true;
				}
			}else{
				return new Response('请检查id是否正确', -1);
			}
		}else{
			return new Response('未知的登陆类型', -1);
		}

		if($isLogin){
			return new Response('登陆成功', 1);
		}else{
			return new Response('登陆失败', 0);
		}
	}
	
	//注销登陆
	public function actionLogoutTenant(){
		$oYii = Yii::$app;
		$tenantId = trim((int)$oYii->request->post('tenantId'));
		$typeId = trim((int)$oYii->request->post('type'));

		$isLogout = false;
		if($typeId == 1){
			if($tenantId === '*'){
				return $this->_logoutAllTenants(CommercialTenantRole::ROLE_TYPE);
			}

			$mTenant = CommercialTenant::findOne($tenantId);
			if($mTenant){
				//将商户模型放入到setIdentity
				$oYii->commercialTenant->setIdentity($mTenant);
				$result = $oYii->commercialTenant->logout();
				if($result){
					$isLogout = true;
				}
			}else{
				return new Response('请检查id是否正确', -1);
			}
		}else{
			return new Response('未知的登陆类型', 0);
		}

		if($isLogout){
			return new Response('注销成功', 1);
		}else{
			return new Response('注销失败', 0);
		}
	}
	
	private function _logoutAllTenants($roleType){
		$keyPrefix = $roleName = '';
		if($roleType == CommercialTenantRole::ROLE_TYPE){
			$keyPrefix = CommercialTenantRole::SESSION_CACHE_KEY_PREFIX;
			$roleName = CommercialTenantRole::ROLE_NAME;
		}

		//注销全部
		Yii::$app->redis->selectPart(Yii::$app->redis->loginPart['index']);
		$aKeys = Yii::$app->redis->redis->keys($keyPrefix . ':*');
		$delCount = 0;
		foreach ($aKeys as $key) {
			Yii::$app->redis->redis->del($key) && $delCount++;
		}
		return new Response('已经T掉' . $delCount . '个' . $roleName . '下线', -1);
	}
}