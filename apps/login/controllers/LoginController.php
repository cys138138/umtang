<?php
namespace login\controllers;

use Yii;
use login\lib\Controller;
use umeworld\lib\PhoneValidator;
use umeworld\lib\Response;
use common\model\form\LoginForm;
use common\model\Redis;
use common\model\CommercialTenant;

class LoginController extends Controller{
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}

	public function actionShowLogin(){
		//debug($this->layout,11);
		return $this->render('login', [
			'mLoginForm' => new LoginForm(),
		]);
	}
	
	public function actionLogin(){
		$mLoginForm = new LoginForm();
		$mLoginForm->login();
		if(!$mLoginForm->load(Yii::$app->request->post(), '') || !$mLoginForm->login()){
			return new Response(current($mLoginForm->getErrors())[0], 0, $mLoginForm);
		}
		return new Response('登陆成功', 1);
	}
	
	public function actionMobileLogin(){
		$mobile = trim((string)Yii::$app->request->post('mobile'));
		$verifyCode = trim((string)Yii::$app->request->post('verifyCode'));
		$isRemember = (int)Yii::$app->request->post('isRemember');
		
		$id = 'mobile_' . $mobile;
		$mRedis = Redis::findOne(['id' => $id]);
		if(!$mRedis){
			return new Response('登录失败', 0);
		}
		if($mRedis->expiration_time < NOW_TIME){
			return new Response('验证码过期', -1);
		}
		if($mRedis->value != $verifyCode){
			return new Response('验证码不正确', -1);
		}
		
		$mCommercialTenant = CommercialTenant::findOne(['mobile' => $mobile]);
		if(!$mCommercialTenant){
			return new Response('找不到账号', -1);
		}
		if(!Yii::$app->commercialTenant->login($mCommercialTenant, 0, $isRemember)){
			return new Response('登陆失败', 0);
		}
		return new Response('登陆成功', 1);
	}
	
	public function actionSendLoginVerifyCode(){
		$mobile = trim((string)Yii::$app->request->post('mobile'));
		$isRegister = (int)Yii::$app->request->post('is_register');
		
		$isMobile = (new PhoneValidator())->validate($mobile);
		if(!$isMobile){
			return new Response('手机格式不正确', 0);
		}
		if($isRegister){
			$mCommercialTenant = CommercialTenant::findOne(['mobile' => $mobile]);
			if($mCommercialTenant){
				return new Response('手机已被注册', 0);
			}
		}
		$id = 'mobile_' . $mobile;
		$mRedis = Redis::findOne(['id' => $id]);
		if($mRedis && $mRedis->expiration_time - NOW_TIME > 840){
			return new Response('请稍后再试', -1);
		}
		$code = mt_rand(100000, 999999);

		//向手机发送短信
		$oSms = Yii::$app->sms;
		$oSms->sendTo = $mobile;
		$oSms->content = '您好，您在优满堂使用手机账号验证，您的验证码是 ' . $code . '此码在十五分钟内有效，请在十五分钟内完成操作。';
		if($oSms->send()){
			if(!$mRedis){
				Redis::add([
					'id' => $id,
					'value' => $code,
					'expiration_time' => NOW_TIME + 900,
				]);
			}else{
				$mRedis->set('value', $code);
				$mRedis->set('expiration_time', NOW_TIME + 900);
				$mRedis->save();
			}
			return new Response('发送验证码成功，请留意手机短信', 1);
		}
		return new Response('发送验证码失败', 0);
	}
	
	public function actionShowRegister(){
		return $this->render('register');
	}
	
	public function actionRegister(){
		$mobile = trim((string)Yii::$app->request->post('mobile'));
		$password = trim((string)Yii::$app->request->post('password'));
		$verifyCode = trim((string)Yii::$app->request->post('verifyCode'));
		
		$isMobile = (new PhoneValidator())->validate($mobile);
		if(!$isMobile){
			return new Response('手机格式不正确', -1);
		}
		if(!$password){
			return new Response('请输入密码', -1);
		}
		if(strlen($password) > 16 || strlen($password) < 6){
			return new Response('密码长度不正确', -1);
		}
		$id = 'mobile_' . $mobile;
		$mRedis = Redis::findOne(['id' => $id]);
		if(!$mRedis){
			return new Response('注册失败', 0);
		}
		if($mRedis->expiration_time < NOW_TIME){
			return new Response('验证码过期', -1);
		}
		if($mRedis->value != $verifyCode){
			return new Response('验证码不正确', -1);
		}
		$mCommercialTenant = CommercialTenant::findOne(['mobile' => $mobile]);
		if($mCommercialTenant){
			return new Response('手机已被注册', -1);
		}
		$tenantId = CommercialTenant::initCommercialTenant(['mobile' => $mobile, 'password' => $password]);
		if(!$tenantId){
			return new Response('注册失败', 0);
		}
		$mCommercialTenant = CommercialTenant::findOne($tenantId);
		if(!Yii::$app->commercialTenant->login($mCommercialTenant)){
			return new Response('登陆失败', 0);
		}
		return new Response('注册成功', 1);
	}
	
	public function actionShowFindPassword(){
		return $this->render('find_password');
	}
	
	public function actionSendFindPasswordVerifyCode(){
		$mobile = trim((string)Yii::$app->request->post('mobile'));
		
		$isMobile = (new PhoneValidator())->validate($mobile);
		if(!$isMobile){
			return new Response('手机格式不正确', 0);
		}
		$mCommercialTenant = CommercialTenant::findOne(['mobile' => $mobile]);
		if(!$mCommercialTenant){
			return new Response('找不到账号信息', -1);
		}
		$id = 'mobile_' . $mobile;
		$mRedis = Redis::findOne(['id' => $id]);
		if($mRedis && $mRedis->expiration_time - NOW_TIME > 840){
			return new Response('请稍后再试', -1);
		}
		$code = mt_rand(100000, 999999);

		//向手机发送短信
		$oSms = Yii::$app->sms;
		$oSms->sendTo = $mobile;
		$oSms->content = '您好，您在优满堂使用手机账号找回密码，您的验证码是 ' . $code . '此码在十五分钟内有效，请在十五分钟内完成操作。';
		if($oSms->send()){
			if(!$mRedis){
				Redis::add([
					'id' => $id,
					'value' => $code,
					'expiration_time' => NOW_TIME + 900,
				]);
			}else{
				$mRedis->set('value', $code);
				$mRedis->set('expiration_time', NOW_TIME + 900);
				$mRedis->save();
			}
			return new Response('发送验证码成功，请留意手机短信', 1);
		}
		return new Response('发送验证码失败', 0);
	}
	
	public function actionVerifyFindPasswordVerifyCode(){
		$mobile = trim((string)Yii::$app->request->post('mobile'));
		$verifyCode = trim((string)Yii::$app->request->post('verifyCode'));
		
		$id = 'mobile_' . $mobile;
		$mRedis = Redis::findOne(['id' => $id]);
		if(!$mRedis){
			return new Response('验证失败', 0);
		}
		if($mRedis->expiration_time < NOW_TIME){
			return new Response('验证码过期', -1);
		}
		if($mRedis->value != $verifyCode){
			return new Response('验证码不正确', -1);
		}
		
		$mCommercialTenant = CommercialTenant::findOne(['mobile' => $mobile]);
		if(!$mCommercialTenant){
			return new Response('找不到账号', -1);
		}
		if(!Yii::$app->commercialTenant->login($mCommercialTenant)){
			return new Response('登陆失败', 0);
		}
		return new Response('验证成功', 1);
	}
	
	public function actionLogout(){
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		if(!Yii::$app->commercialTenant->logout()){
			return new Response('退出登录失败', 0);
		}
		return Yii::$app->response->redirect(\umeworld\lib\Url::to(['login/show-login']));
	}
	
	public function actionLogoutAsynchronous(){
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		if(!Yii::$app->commercialTenant->logout()){
			return new Response('退出登录失败', 0);
		}
		return new Response('退出登录成功', 1);
	}
	
	public function actionTestLogin(){
		$id = Yii::$app->request->get('id');
		$mCommercialTenant = CommercialTenant::findOne($id);
		if(!$mCommercialTenant){
			return new Response('找不到账号', -1);
		}
		if(!Yii::$app->commercialTenant->login($mCommercialTenant)){
			return new Response('登陆失败', 0);
		}
		return new Response('登陆成功', 1);
	}
	
}
