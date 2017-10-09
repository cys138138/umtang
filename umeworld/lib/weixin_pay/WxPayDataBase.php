<?php
namespace umeworld\lib\weixin_pay;

use Yii;

class WxPayDataBase{
	
	protected $_aValues = [];
	protected $_key;
	
	public function setSign(){
		$sign = $this->makeSign();
		$this->_aValues['sign'] = $sign;
		return $sign;
	}
	
	public function getSign(){
		return $this->_aValues['sign'];
	}
	
	public function isSignSet(){
		return array_key_exists('sign', $this->_aValues);
	}
	
	public function toXml(){
		if(!is_array($this->_aValues) || count($this->_aValues) <= 0){
			throw Yii::$app->buildError('数组数据异常！');
		}
		
		$xml = "<xml>";
		foreach($this->_aValues as $key => $val){
			if(is_numeric($val)){
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			}else{
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}
		}
		$xml .= "</xml>";
		return $xml;
	}
	
	public function fromXml($xml){
		if(!$xml){
			throw Yii::$app->buildError('xml数据异常！');
		}
		//将XML转为array
        //禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$this->_aValues = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $this->_aValues;
	}
	
	public function toUrlParams(){
		$buff = '';
		foreach($this->_aValues as $key => $val){
			if($key != 'sign' && $val != '' && !is_array($val)){
				$buff .= $key . '=' . $val . '&';
			}
		}
		$buff = trim($buff, '&');
		return $buff;
	}
	
	public function makeSign(){
		if(!$this->_key && !is_string($this->_key)){
			throw Yii::$app->buildError('key格式不正确！');
		}
		//签名步骤一：按字典序排序参数
		ksort($this->_aValues);
		$string = $this->toUrlParams();
		$string = $string . '&key=' . $this->_key;
		$string = md5($string);
		$result = strtoupper($string);
		return $result;
	}
	
	public function getValues(){
		return $this->_aValues;
	}
	
	public function setKey($key){
		$this->_key = $key;
	}
}