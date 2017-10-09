<?php
namespace tenant\widgets;

use Yii;

class Footer extends \yii\base\Widget{
	public function run(){
		return $this->render('footer');
	}
}