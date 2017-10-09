<?php
namespace umeworld\lib\weixin_pay;

use Yii;

class WxPayOrderQuery extends WxPayDataBase{
	public function __construct(array $aParaTemp, $key){
		$this->_aValues = $aParaTemp;
		$this->setKey($key);
	}
}