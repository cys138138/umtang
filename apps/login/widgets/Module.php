<?php
namespace login\widgets;

use Yii;

class Module extends \yii\base\Widget{
	/**
	 * 场景:默认
	 */
	const SCENE_DEFAULT = 1;
	/**
	 * 场景:商家
	 */
	const SCENE_BUSINESS = 2;
	/**
	 * 场景:默认
	 */
	const TYPE_HEADER = 'home_header';
	/**
	 * 场景:商家
	 */
	
	const TYPE_FOOTER = 'footer';
	public $scene = '';
	public $type = '';
	public $sideType = '';

	public function run(){
		if($this->type != static::TYPE_HEADER && $this->type != static::TYPE_FOOTER){
			return '';
		}

		return $this->render($this->type, ['scene' => $this->scene,'sideType'=> $this->sideType]);
	}
}
