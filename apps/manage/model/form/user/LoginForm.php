<?php
namespace manage\model\form\user;

use Yii;
use manage\model\Manager;

/**
 * @property-read \manage\model\Manager $mManager
 */
class LoginForm extends \yii\base\Model{
	public $aa;	//账号
	public $bb;	//密码
	public $vv;	//验证码
	public $newListenerSessionId = '';

	private $_mManager = null;

	public function rules(){
		$aRules = [
			[['aa', 'bb', 'vv'], 'required'],
			['aa', 'email', 'message' => '账号必须是邮箱格式'],
			[
				'bb',
				'string',
				'length' => [6, 16],
				'tooShort' => '密码最少是6位字符哦',
				'tooLong' => '密码最多是16位字符哦',
			],
			['bb', 'validatePassword'],
		];
		if(Yii::$app->request->post('_from') != 'app'){
			$aRules[] = ['vv', 'captcha', 'message' => '验证码错误'];

		}
		return $aRules;
	}

	public function attributeLabels() {
		return [
			'aa' => '账号',
			'bb' => '密码',
			'vv' => '验证码',
		];
	}

	public function validatePassword($attribute, $params) {
		if(!$this->hasErrors()){
			$mManager = $this->mManager;
			if(!$mManager || !$mManager->validatePassword($this->bb)){
				$this->addError($attribute, '账号或密码错误' . $params);
			}
		}
	}

	public function login() {
		if(!$this->validate()){
			return false;
		}

		$mManager = $this->mManager;
		if(!$mManager){
			$this->addError('aa', '登陆失败');
			return false;
		}
		if($mManager->is_forbidden == Manager::MANAGER_IS_FORBIDDEN){
			$this->addError('aa', '用户已禁用');
			return false;
		}
		if((string)Yii::$app->request->post('_from') == 'app'){
			//如果是APP登陆则返回一个新的审核通知监听会话ID
//			if(!$mManager->getManagerGroup()->allow('manage_bbs')){
//				return false;
//			}
			$this->newListenerSessionId = Yii::$app->bbsManageListener->addListener($mManager);
		}
		return Yii::$app->manager->login($mManager, 86400);
	}

	public function getMManager(){
		if(!$this->_mManager){
			$this->_mManager = Manager::findByEmail($this->aa);
		}
		return $this->_mManager;
	}
}