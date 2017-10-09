<?php
namespace common\role;

use common\model\CommercialTenant as CommercialTenantModel;
use Redis;
use umeworld\lib\Cookie;
use Yii;
use yii\base\InvalidValueException;
use yii\web\IdentityInterface;
use yii\web\User;
use common\model\LoginLog;
use common\filter\TenantAccessControl as AccessControl;

class CommercialTenantRole extends User{
	/**
	 * 前台商户,商户标识
	 */
	const ROLE_TYPE = 1;
	
	/**
	 * 角色名称
	 */
	const ROLE_NAME = '商户';
	
	/**
	 * 登陆会话缓存的KEY前缀
	 */
	const SESSION_CACHE_KEY_PREFIX = 'tenant_login';
	
	private $_oIdentity = false;
	private $_id = null;
	private $_access = [];
	private $_rememberLoginTime = 604800;

	public $enableMultipleLogin = false;

	/**
	 * 判断当前后台管理者是否游客
	 * @return type
	 */
    public function getIsGuest()
    {
		$oIdentity = $this->getIdentity();
		return !($oIdentity instanceof IdentityInterface);
    }

	/**
	 * 登陆检查不通过的回调
	 * @param type $checkAjax
	 */
	public function loginRequired($checkAjax = true){
		Yii::$app->response->redirect(Yii::$app->urlManagerLogin->createUrl('login/show-login'));
	}
	
	/**
	 * 商户未通过审核的回调
	 */
	public function tenantRequired(){
		$oTenant = $this->getIdentity();
		$redirectUrl = Yii::$app->urlManagerTenant->createUrl('tenant/show-fill-approve');
		//如果在审核中（表示资料已经完善了）
		if($oTenant->online_status == CommercialTenantModel::ONLINE_STATUS_IN_APPROVE){
			//重定向到等待审核页面
			$redirectUrl = Yii::$app->urlManagerTenant->createUrl('tenant/show-approve-status');
		}else{
		//如果在完善资料中（表示 资料未完善 或者 审核未通过）
			if($oTenant->data_perfect == CommercialTenantModel::DATA_PERFECT_TENANT_INFO){
				//重定向到商户信息页
				$redirectUrl = Yii::$app->urlManagerTenant->createUrl('tenant/show-fill-approve');
			}elseif($oTenant->data_perfect == CommercialTenantModel::DATA_PERFECT_SHOP_INFO){
				//重定向到商铺信息页
				$redirectUrl = Yii::$app->urlManagerTenant->createUrl('tenant-shop/show-fill-tenant-shop');
			}else{
				$oTenantApprove = $oTenant->getMTenantApprove();
				if($oTenantApprove->tenant_approve_status == \common\model\CommercialTenantApprove::STATUS_ONT_PASS_APPROVE){
					//重定向到商户信息页
					$redirectUrl = Yii::$app->urlManagerTenant->createUrl('tenant/show-fill-approve');
				}else{
					//重定向到商铺信息页
					$redirectUrl = Yii::$app->urlManagerTenant->createUrl('tenant-shop/show-fill-tenant-shop');
				}
			}
		}
		Yii::$app->response->redirect($redirectUrl)->send();
	}

	/**
	 * 获取当前用户ID
	 * @return type
	 */
	public function getId(){
		if($this->_oIdentity === false){
			$this->_oIdentity = $this->initLoginStatus();
		}

		if(!$this->_id && is_object($this->_oIdentity)){
			$this->_id = $this->_oIdentity->getId();
		}

		return $this->_id;
	}
	/**
	 * 获取身份
	 * @param bool $autoRenew
	 * @return \mange\model\Manageer
	 */
    public function getIdentity($autoRenew = true)
    {
		if($this->_oIdentity === false){
			//未加载过登陆口令
			$this->_oIdentity = $this->initLoginStatus();
		}
        return $this->_oIdentity;
    }

	 public function login(IdentityInterface $oUser, $duration = 0, $isRemember = false){
		$this->logout(true);
		$userId = (int)$oUser->getId();
		$this->setIdentity($oUser);
		$clientIp = Yii::$app->request->getUserIP();
		if($isRemember){
			//写入cookie
			Cookie::setEncrypt('commercialTenantId', $userId, NOW_TIME + $this->_rememberLoginTime);
		}else{
			Yii::$app->session->set('commercialTenantId', $userId);
		}
		
		$this->_afterLogin();
		
		return true;
	 }
	 
	private function _afterLogin(){
		//半小时记录一次登录日志
		$aLoginLogList = LoginLog::findAll(['type' => LoginLog::TYPE_TENANT, 'user_id' => $this->_oIdentity->id], null, $page = 1, $pageSize = 1, ['id' => SORT_DESC]);
		if(!$aLoginLogList || NOW_TIME - $aLoginLogList[0]['create_time'] > LoginLog::LOGIN_RECORD_INTERVAL){
			LoginLog::add([
				'type' => LoginLog::TYPE_TENANT,
				'user_id' => $this->_oIdentity->id,
				'create_time' => NOW_TIME,
			]);
		}
	}
	
	 /**
	 * 设置身份
	 * @param \common\role\IdentityInterface $oIdentity
	 * @throws InvalidValueException
	 */
    public function setIdentity($oIdentity)
    {
        if ($oIdentity instanceof IdentityInterface) {
            $this->_oIdentity = $oIdentity;
        } elseif ($oIdentity === null) {
            $this->_oIdentity = null;
        } else {
            throw new InvalidValueException('设置用户身份时被传入了一个非 IdentityInterface 接口的实现参数!');
        }
    }

	/**
	 * 获取cookie如果cookie 中存在teacherId则设置teacher模型对象
	 * @return boolean
	 */
	public function initLoginStatus(){
		Yii::info('进行登陆检查', 'login');
		$isCookie = false;
		$userId = (int)Cookie::getDecrypt('commercialTenantId');
		if(!$userId){
			$userId = Yii::$app->session->get('commercialTenantId');
		}else{
			$isCookie = true;
		}
		if(!$userId){
			Yii::info('cookie里没有用户id', 'login');
			return false;
		}

		if(!$oIdentity = CommercialTenantModel::findOne($userId)){
			Yii::info('登陆检查时找不到该用户!', 'login');
			return false;
		}

		$this->setIdentity($oIdentity);
		//延长cookie时间
		/*if($isCookie){
			Cookie::setEncrypt('commercialTenantId', $userId, NOW_TIME + $this->_rememberLoginTime);
		}*/
		return $oIdentity;
	}

	/**
	 * 退出登陆
	 * @param type $destroySession
	 * @return bool 是否已经为退出状态
	 */
	public function logout($destroySession = true){
		$oIdentity = $this->getIdentity();
		if(!$oIdentity){
			$oIdentity = $this->initLoginStatus();
			if(!$oIdentity){
				return true;
			}
		}
		
		//删除客户端保存的令牌
		Cookie::delete('commercialTenantId');
		Yii::$app->session->remove('commercialTenantId');
		$this->setIdentity(null);	//清除身份
		return $this->getIsGuest();
	}

	/**
	 * 获取登陆信息的键名
	 * @param type $studentId
	 * @return type
	 */
	public static function getLoginInfoKey($userId) {
		return 'commercial_tenant_login:' . $userId;
	}

	/**
	 * 生成随机登陆密钥
	 * @param type $aElements
	 * @return type string 登陆密钥
	 */
	private function _buildLoginToken($aElements) {
		return md5($aElements['ip'] . $aElements['user_id'] . mt_rand($aElements['user_id'], 9999999999));
	}
	
	public function can($permissionName, $params = [], $allowCaching = true) {
		if($allowCaching && !$params && isset($this->_access[$permissionName])){
			 return $this->_access[$permissionName];
		}
		
		$oTenant = $this->getIdentity();
		if(!$oTenant){
			return false;
		}
		
		switch($permissionName){
			case AccessControl::TENANTS:
				if($oTenant->online_status != CommercialTenantModel::ONLINE_STATUS_ONLINE && $oTenant->online_status != CommercialTenantModel::ONLINE_STATUS_OFFLINE){
					return false;
				}
				break;
			default:
				throw Yii::$app->buildError('未知的检测权限: ' . $permissionName);
		}
		
		$allowAccess = true;
		if($allowCaching && !$params){
			//权限缓存
            $this->_access[$permissionName] = $allowAccess;
		}
		return $allowAccess;
	}
	
}