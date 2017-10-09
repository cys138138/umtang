<?php
namespace umeworld\lib\weixin_pay;

use Yii;

class WxPayDownloadbill extends WxPayDataBase{
	public function __construct(array $aParaTemp, $key){
		$this->_aValues = $aParaTemp;
		$this->setKey($key);
	}
}