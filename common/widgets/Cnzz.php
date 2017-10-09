<?php
namespace common\widgets;

/**
 * 站长统计代码部件
 * @author 黄文非
 */
class Cnzz extends \yii\base\Widget{
	public $mustOutput = false;

	public function run(){
		if(!$this->mustOutput && !YII_ENV_PROD){
			//非线上环境不输出该统计代码
			return;
		}

		//以后要优化成判断浏览器类型的
		return '<script src="http://s16.cnzz.com/stat.php?id=5670400&web_id=5670400" type="text/javascript" async="async"></script>';
	}
}