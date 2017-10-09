<?php
namespace manage\controllers;

use Yii;
use manage\model\Manager;
use common\model\CommercialTenant;
use yii\filters\auth\HttpBasicAuth;

class ToolsController extends \yii\web\Controller{
	use \manage\controllers\tools\DevHelperTrait;
	
	public $enableCsrfValidation = false;

	public function init() {
		Yii::$app->set('user', Yii::$app->manager);
		set_time_limit(0);
	}

	public function behaviors(){
		return !YII_ENV_PROD
		|| in_array($this->action->id, [
			//不需要验证的方法id列表
			'test-app',
			'test-upload',
		])
		? [] : [
			'basicAuth' => [
				'class' => HttpBasicAuth::className(),
				'auth' => function($username, $password){
					if($mManager = Manager::findOne([
						'email' => $username,
						'password' => CommercialTenant::encryptPassword($password),
					])){
						return $mManager;
					}else{
						return null;
					}

				}
			],
		];
	}
}