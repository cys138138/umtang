<?php
namespace login\widgets;

use Yii;

class Menu extends \yii\base\Widget{

	//默认-关于我们
	const DEFAULT_ABOUT = 1;
	//默认-加入我们
	const DEFAULT_JOIN = 2;
	//默认-帮助中心
	const DEFAULT_HELP = 3;
	//商户-帮助中心
	const BUSINESS_HELP = 4;

	public $type = '';

	public function run(){
		return $this->render('menu',['type' => $this->type]);
	}
}
