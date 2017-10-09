<?php
namespace umeworld\lib\weixin_pay;

class WxPayUnifiedOrder extends WxPayDataBase{
	public function __construct(array $aParaTemp, $key){
		$this->_aValues = $aParaTemp;
		$this->setKey($key);
	}
}