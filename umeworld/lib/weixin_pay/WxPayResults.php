<?php
namespace umeworld\lib\weixin_pay;

use Yii;

class WxPayResults extends WxPayDataBase{
	
	public static function init($xml, $key){
		$oPayResults = new self();
		$oPayResults->setKey($key);
		$oPayResults->fromXml($xml);
		if($oPayResults->_aValues['return_code'] != 'SUCCESS'){
			return $oPayResults->getValues();
		}
		$oPayResults->checkSign();
		return $oPayResults->getValues();
	}
	
	public function checkSign(){
		if(YII_ENV != 'prod'){
			return true;
		}
		if(!$this->isSignSet()){
			throw Yii::$app->buildError('签名错误！');
		}
		
		$sign = $this->makeSign();
		if($this->getSign() == $sign){
			return true;
		}
		throw Yii::$app->buildError('签名错误！');
	}
}
