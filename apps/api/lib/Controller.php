<?php
namespace api\lib;

use Yii;
use common\filter\UserAccessControl as Access;

class Controller extends \yii\web\Controller{
	
	public function behaviors(){
		return [
			'access' => [
				//登陆访问控制过滤
				'class' => Access::className(),
				'ruleConfig' => [
					'class' => 'yii\filters\AccessRule',
					'allow' => true,
				],
				'rules' => [
					[
						'roles' => [Access::USERS],  //'@'
					],
				]
			],
		];
	}
}