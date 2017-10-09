<?php
namespace manage\lib;

use manage\model\Manager as ManagerModel;
use Redis;
use umeworld\lib\Cookie;
use Yii;
use yii\base\InvalidValueException;
use yii\web\IdentityInterface;
use yii\web\User;

/**
 * 后台管理者角色登陆控制类
 * @author 黄文非
 */
class ManagerRole extends User{
	private $_oIdentity = false;	//false未检查过身份,null未登陆,object已经登陆
	private $_id = null;		//用户id

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

	 public function login(IdentityInterface $oTeacher, $duration = 0){
		Yii::info('开始进行登陆', 'login');
		$userId = (int)$oTeacher->getId();
		$this->setIdentity($oTeacher);
		$aLoginInfo = $this->getLoginInfo($userId);
		$clientIp = Yii::$app->request->getUserIP();
		//缓存处理
		if($aLoginInfo && $aLoginInfo['ip'] == $clientIp){
			Yii::info('服务端还有该用户' . $userId . '的口令信息', 'login');
			//已登陆
			$token = $aLoginInfo['token'];
		}else{
			//一个账号两地异地登录逼另外一个下线
			if($aLoginInfo && $aLoginInfo['ip'] != $clientIp){
				$this->_deleteLoginInfo($userId);
			}
			//登陆则计算登陆密钥
			$token = $this->_buildLoginToken([
				'user_id' => $userId,
				'ip' => $clientIp,
			]);

			//构造登陆信息
			$aLoginInfo = [
				'id' => $userId,
				'token' => $token,
				'ip' => $clientIp,
				'agent' => strtolower(Yii::$app->request->headers->get('user-agent')),
			];
		}
		//把数据往redis 写入
		if(!$this->_setLoginInfo($aLoginInfo, $duration)){
			throw Yii::$app->buildError($aLoginInfo['id'] . ' 该用户登陆失败!');
		}

		//写入cookie
		 Cookie::setEncrypt('managerId', $userId, NOW_TIME + 3000000);
		 Cookie::setEncrypt('managerToken', $token, NOW_TIME + 3000000);
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
		$userId = (int)Cookie::getDecrypt('managerId');
		if(!$userId){
			Yii::info('cookie里没有用户id', 'login');
			return false;
		}

		$token = (string)Cookie::getDecrypt('managerToken');
		if(!$token){
			Yii::info('cookie里没有token', 'login');
			return false;
		}

		if(!$aLoginInfo = $this->getLoginInfo($userId)){
			Yii::info('服务端没有登陆信息', 'login');
			return false;
		}

		Yii::info('客户端口令:' . $token . PHP_EOL . '服务端口令:' . $aLoginInfo['token'], 'login');
		if(!$this->enableMultipleLogin && $token != $aLoginInfo['token']){
			Yii::info('客户端ID:' . $userId . PHP_EOL . '服务端ID:' . $aLoginInfo['id'], 'login');
			Yii::info('客户端口令与服务端不匹配!', 'login');
			return false;
		}

		if(!$oIdentity = ManagerModel::findOne($userId)){
			Yii::info('登陆检查时找不到该用户!', 'login');
			return false;
		}

		$this->setIdentity($oIdentity);
		//延长cookie时间
		Cookie::setEncrypt('managerToken', $token, NOW_TIME + 86400);
		Cookie::setEncrypt('managerId', $userId, NOW_TIME + 86400);
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
		if($destroySession){
			//删除服务端的令牌池数据
			$this->_deleteLoginInfo($oIdentity->getId());
		}
		//删除客户端保存的令牌
		Cookie::delete('managerId');
		Cookie::delete('managerToken');
		$this->setIdentity(null);	//清除身份
		return $this->getIsGuest();
	}

	 /**
	 * 获取指定后台管理者的服务端登陆信息,比如令牌,IP等
	 * @param int $userId 后台管理者ID
	 * @return array array 服务器存档的登陆信息副本
	 */
	public function getLoginInfo($userId) {
		$oApp = Yii::$app;
		$oApp->redis->connect();
		$oApp->redis->selectPart($oApp->redis->loginPart['index']);
		$aLoginInfo = $oApp->redis->getOne(static::getLoginInfoKey($userId));
		return $aLoginInfo;
	}

	/**
	 * 设置登陆信息到登陆缓存中
	 * @param array $aLoginInfo
	 * @param int $duration
	 * @return bool 是否设置成功
	 */
	private function _setLoginInfo($aLoginInfo, $duration) {
		$key = $this->getLoginInfoKey($aLoginInfo['id']);
		$oRedis = Yii::$app->redis;
		//$oRedis->redis->multi(Redis::PIPELINE);
		//$oRedis->selectPart($oRedis->loginPart['index']);
		$oRedis->delete($key);
		if($oRedis->add($key, $aLoginInfo)){
			if($duration){
				$oRedis->expireOne($key, $duration) ? true : false;
			}
			return true;
		}
		return false;
		//return $oRedis->redis->exec() ? true : false;
	}

	/**
	 * 删除登陆缓存中的登陆信息
	 * @param int $userId
	 */
	private function _deleteLoginInfo($userId) {
		$oRedis = Yii::$app;
		$aLoginInfo = $this->getLoginInfo($userId);
		if (!$aLoginInfo) {
			return;
		}
		$oRedis->redis->delete($this->getLoginInfoKey($userId));
	}

	/**
	 * 获取登陆信息的键名
	 * @param type $studentId
	 * @return type
	 */
	public static function getLoginInfoKey($teacherId) {
		return 'manager_login:' . $teacherId;
	}

	/**
	 * 生成随机登陆密钥
	 * @param type $aElements
	 * @return type string 登陆密钥
	 */
	private function _buildLoginToken($aElements) {
		return md5($aElements['ip'] . $aElements['user_id'] . mt_rand($aElements['user_id'], 9999999999));
	}
	
	/**
	 * 获取默认科目
	 * @param type $aElements
	 * @return type mSubject 
	 */
	public function getDefaultSubject() {
		$oIdentity = $this->getIdentity();
		if(!$oIdentity->allowed_subject){
			throw new \yii\base\InvalidCallException('该用户未设置可以操作的科目');
		}
		$defaultSubjectId = current($oIdentity->allowed_subject);
		return Subject::findOne($defaultSubjectId);
	}
}