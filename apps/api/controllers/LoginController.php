<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use umeworld\lib\Response;
use common\model\User;
use yii\helpers\ArrayHelper;

class LoginController extends Controller{
	public $enableCsrfValidation = false;
	
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}

	public function actionLogin(){
		$code = (string)Yii::$app->request->post('code');
		
		if(!$code){
			return new Response('缺少Code', 0);
		}
		$openId = Yii::$app->weiXin->getOpenIdByCode($code);
		if(!$openId){
			return new Response('登录验证失败', 0);
		}
		//$openId = 'o88sa0fzQ6btODVee1lMVoCrlTv4';
		$mUser = User::findOne(['openid' => $openId]);
		if(!$mUser){
			$mUser = User::registerByOpenId($openId);
		}
		$token = Yii::$app->user->login($mUser);
		if(!$token){
			return new Response('登录失败', 0);
		}
		
		return new Response('登录成功', 1, ['token' => $token]);
	}
			
}