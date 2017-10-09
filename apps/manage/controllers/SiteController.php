<?php

namespace manage\controllers;

use Yii;
use manage\lib\Controller;
use umeworld\lib\Response;
use umeworld\lib\Url;
use manage\model\form\user\LoginForm;

class SiteController extends Controller{
	public function actions() {
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
			'captcha' => [
				'class' => 'yii\captcha\CaptchaAction',
				'offset' => -1,
				'fixedVerifyCode' => !YII_ENV_PROD ? '121212' : null,
			],
		];
	}
	
	public function behaviors(){
		return \yii\helpers\ArrayHelper::merge([
			'access' => [
				'rules' => [
					[
						'allow' => true,
						'actions' => ['show-login', 'login', 'error', 'captcha', 'logout'],
					],
					[
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		], parent::behaviors());
	}
	
	public function actionIndex() {
		return $this->render('index');
	}
	
	public function actionShowLogin() {
		if(!Yii::$app->manager->isGuest){
			$this->goHome();
			return;
		}
		return $this->renderPartial('login', [
			'mLoginForm' => new LoginForm(),
		]);
	}
	
	public function actionLogin() {
		if(!Yii::$app->manager->isGuest){
			$this->goHome();
			return;
		}
		$mLoginForm = new LoginForm();
		if(!$mLoginForm->load(Yii::$app->request->post(), '') || !$mLoginForm->login()){
			return new Response(current($mLoginForm->getErrors())[0], 0, $mLoginForm);
		}
		$data = '';
		if((string)Yii::$app->request->post('_from') == 'app'){
			$data = $mLoginForm->newListenerSessionId;
		}else{
			$data = Url::to(Yii::$app->homeUrl);
		}
		return new Response('登陆成功', 1, $data);
	}
	
	public function actionLogout() {
		Yii::$app->manager->logout();
		return $this->redirect(Url::to(['site/show-login']));
	}
}

