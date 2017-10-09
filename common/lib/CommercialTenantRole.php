<?php
namespace common\lib;

use common\model\CommercialTenant as CommercialTenantModel;
use Redis;
use umeworld\lib\Cookie;
use Yii;
use yii\base\InvalidValueException;
use yii\web\IdentityInterface;
use yii\web\User;

class CommercialTenantRole extends User{
	private $_oIdentity = false;
	private $_id = null;

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
		Yii::$app->response->redirect($this->loginUrl);
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

	 public function login(IdentityInterface $oUser, $duration = 0){
		$userId = (int)$oUser->getId();
		$this->setIdentity($oUser);
		$clientIp = Yii::$app->request->getUserIP();
		
		//写入cookie
		 Cookie::setEncrypt('commercialTenantId', $userId, NOW_TIME + 3000000);
		 return true;
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
		$userId = (int)Cookie::getDecrypt('commercialTenantId');
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
		Cookie::setEncrypt('commercialTenantId', $userId, NOW_TIME + 86400);
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
	
}