<?php
namespace umeworld\lib\weixin_pay;

use Yii;

class WxPayJsApiPay extends WxPayDataBase{
	
	public function setAppId($value){
		$this->_aValues['appId'] = $value;
	}
	
	public function setTimeStamp($value){
		$this->_aValues['timeStamp'] = "$value";
	}
	
	public function setNonceStr($value){
		$this->_aValues['nonceStr'] = $value;
	}
	
	public function setPackage($value){
		$this->_aValues['package'] = $value;
	}
	
	public function setSignType($value){
		$this->_aValues['signType'] = $value;
	}
	
	public function setPaySign($value){
		$this->_aValues['paySign'] = $value;
	}
}