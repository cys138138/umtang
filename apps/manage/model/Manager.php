<?php
namespace manage\model;

use Yii;
use umeworld\lib\Query;

class Manager extends \common\lib\DbOrmModel implements \yii\web\IdentityInterface{
	protected $_aEncodeFields = [];
	const MANAGER_IS_FORBIDDEN = 1;	//用户禁用
	const MANAGER_IS_ACTIVITY = 2;	//用户激活
	public $email;

	/**
	 * @var ManagerGroup 后台用户分组模型
	 */
	private $_mManagerGroup = null;

	public static function tableName(){
		return Yii::$app->db->parseTable('_@manager');
	}

	public static function encryptPassword($password){
		return md5($password);
	}

	public static function findByEmail($email){
		$aManager = (new Query())->from(static::tableName())->where(['email' => $email])->one();
		if(!$aManager){
			return false;
		}
		return static::toModel($aManager);
	}

	public function getAuthKey(){
		throw \yii\base\InvalidCallException('不支持该方法的调用');
	}

	public function getId(){
		return $this->id;
	}

	public static function findIdentity($id) {
		return static::findOne($id);
	}

	public function validateAuthKey($authKey) {
		throw \yii\base\InvalidCallException('不支持该方法的调用');
	}

	public static function findIdentityByAccessToken($token, $type = null) {
		throw \yii\base\InvalidCallException('不支持该方法的调用');
	}

	public function validatePassword($password) {
		return $this->password == md5($password);
	}
	
	/**
	 * 获取分组模型
	 * @return \manage\model\ManagerGroup
	 */
	public function getManagerGroup() {
		if(!$this->_mManagerGroup){
			$this->_mManagerGroup = ManagerGroup::findOne($this->group_id);
		}
		return $this->_mManagerGroup;
	}
}