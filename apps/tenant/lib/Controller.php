<?php
namespace tenant\lib;

use Yii;
use common\filter\TenantAccessControl as Access;

class Controller extends \yii\web\Controller{
	/**
	 * 返回一个登陆验证过滤器配置,要求是TEACHER级别的用户才能使用
	 * @see \common\filter\ParentAccessControl
	 * @return type array
	 */
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
						'roles' => [Access::TENANTS],  //'@'
					],
				]
			],
		];
	}
	
	public function render($view, $params = array()){
		if(!Yii::$app->client->isComputer && $this->layout == ''){
			$this->layout = 'mobile';
		}
		return parent::render($view, $params);
	}
}