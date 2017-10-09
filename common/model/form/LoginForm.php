<?php
namespace common\model\form;

use Yii;
use common\model\CommercialTenant;
use umeworld\lib\PhoneValidator;

class LoginForm extends \yii\base\Model{
	public $mobile;	//手机
	public $password;	//密码
	public $verifyCode;	//验证码
	public $isRemember = 0;	//是否记住密码

	private $_mCommercialTenant = null;

	public function rules(){
		$aRules = [
			[['mobile', 'password', 'verifyCode'], 'required'],
			['mobile', 'validateMobile'],
			[
				'password',
				'string',
				'length' => [6, 16],
				'tooShort' => '密码最少是6位字符哦',
				'tooLong' => '密码最多是16位字符哦',
			],
			['password', 'validatePassword'],
			['isRemember', 'validatePassword'],
		];
		$aRules[] = ['verifyCode', 'captcha', 'message' => '验证码错误'];
		return $aRules;
	}

	public function attributeLabels() {
		return [
			'mobile' => '手机',
			'password' => '密码',
			'verifyCode' => '验证码',
		];
	}

	public function validateMobile() {
		$isMobile = (new PhoneValidator())->validate($this->mobile);
		if(!$isMobile){
			$this->addError('mobile', '登陆失败');
			return false;
		}
		return true;
	}

	public function validatePassword($attribute, $params) {
		if(!$this->hasErrors()){
			$mCommercialTenant = $this->mCommercialTenant;
			if(!$mCommercialTenant || $mCommercialTenant->password != CommercialTenant::encryptPassword($this->password)){
				$this->addError($attribute, '账号或密码错误' . $params);
			}
		}
	}

	public function login() {
		if(!$this->validate()){
			return false;
		}

		$mCommercialTenant = $this->mCommercialTenant;
		if(!$mCommercialTenant){
			$this->addError('mobile', '登陆失败');
			return false;
		}

		return Yii::$app->commercialTenant->login($mCommercialTenant, 86400, $this->isRemember);
	}

	public function getMCommercialTenant(){
		if(!$this->_mCommercialTenant){
			$this->_mCommercialTenant = CommercialTenant::findOne(['mobile' => $this->mobile]);
		}
		return $this->_mCommercialTenant;
	}
}