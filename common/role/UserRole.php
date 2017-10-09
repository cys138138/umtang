<?php
namespace common\role;

use Yii;
use yii\base\InvalidValueException;
use yii\web\IdentityInterface;
use yii\web\User;
use common\model\User as UserModel;
use common\model\LoginLog;
use umeworld\lib\Xxtea;
use umeworld\lib\Response;
use common\filter\UserAccessControl as AccessControl;

class UserRole extends User{
	private $_oIdentity = false;
	private $_id = null;
	private $_access = [];


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
		return (new Response('验证登录失败', 0))->send();
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
		$this->_afterLogin($oUser);
		return $oUser->createUserToken();
	 }
	 
	private function _afterLogin($oUser){
		//半小时记录一次登录日志
		$aLoginLogList = LoginLog::findAll(['type' => LoginLog::TYPE_USER, 'user_id' => $oUser->id], null, $page = 1, $pageSize = 1, ['id' => SORT_DESC]);
		if(!$aLoginLogList || NOW_TIME - $aLoginLogList[0]['create_time'] > LoginLog::LOGIN_RECORD_INTERVAL){
			LoginLog::add([
				'type' => LoginLog::TYPE_USER,
				'user_id' => $oUser->id,
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
	 * 获取post过来的code设置模型对象
	 * @return boolean
	 */
	public function initLoginStatus(){
		$token = (string)Yii::$app->request->post('token');
		//$token = 'AnahKI4sa7rIaBQumweyDw_e83ce__e83ce_';
		if(!$token){
			return false;
		}
		if(!$oIdentity = UserModel::getUserByToken($token)){
			Yii::info('登陆检查时找不到该用户!', 'login');
			return false;
		}

		$this->setIdentity($oIdentity);
		
		return $oIdentity;
	}

	public function can($permissionName, $params = [], $allowCaching = true) {
		if($allowCaching && !$params && isset($this->_access[$permissionName])){
			 return $this->_access[$permissionName];
		}
		
		$oUser = $this->getIdentity();
		if(!$oUser){
			return false;
		}
		
		$allowAccess = true;
		if($allowCaching && !$params){
			//权限缓存
            $this->_access[$permissionName] = $allowAccess;
		}
		return $allowAccess;
	}
	
}