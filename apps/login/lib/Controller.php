<?php
namespace login\lib;

use Yii;

class Controller extends \yii\web\Controller{

	public function render($view, $params = array()){
		if(!Yii::$app->client->isComputer && $this->layout == ''){
			$this->layout = 'mobile';
		}

		return parent::render($view, $params);
	}
}
