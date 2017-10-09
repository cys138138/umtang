<?php
namespace umeworld\lib;

class Url extends \yii\helpers\BaseUrl{
	public static function to($xUrl = '', $scheme = false){
		if(is_array($xUrl) && $xUrl[0][0] != '/'){
			//自动添加根URL标识，以防底层toRoute的时候自动添加模块名称
			$xUrl[0] = '/' . $xUrl[0];
		}

		$result = parent::to($xUrl, $scheme);
		if(!$scheme){
			$result = str_replace(\Yii::$app->getUrlManager()->baseUrl, '', $result);
		}

		return $result;
	}
}