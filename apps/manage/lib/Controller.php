<?php
namespace manage\lib;

use yii\filters\AccessControl;

class Controller extends \yii\web\Controller{
	public function behaviors() {
		return [
			'access' => [
				'class' => AccessControl::className(),
				'user' => 'manager',
			],
		];
	}
}