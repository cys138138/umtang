<?php
namespace umeworld\lib\weixin_pay;

use Yii;

class WxReplyNotifyData extends WxPayDataBase{
	public function __construct($returnCode, $returnMessage){
		$this->_aValues['return_code'] = $returnCode;
		$this->_aValues['return_msg'] = $returnMessage;
	}
}