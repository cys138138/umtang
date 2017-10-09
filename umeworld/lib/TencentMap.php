<?php
namespace umeworld\lib;

use Yii;

/**
 * 腾讯地图
 */
class TencentMap extends \yii\base\Component {
	public $secretKey;

	public function getCityNameByLocation($lng, $lat){
		$apiUrl = 'http://apis.map.qq.com/ws/geocoder/v1/?location=' . $lat . ',' . $lng . '&key=' . $this->secretKey . '&get_poi=1';
		$resultString = file_get_contents($apiUrl);
		
		$aResult = json_decode($resultString, 1);
		
		if(is_array($aResult) && isset($aResult['result']['address_component']['city'])){
			return $aResult['result']['address_component']['city'];
		}
		return '';
	}
	
	public function getStreetNameByLocation($lng, $lat){
		$apiUrl = 'http://apis.map.qq.com/ws/geocoder/v1/?location=' . $lat . ',' . $lng . '&key=' . $this->secretKey . '&get_poi=1';
		$resultString = file_get_contents($apiUrl);
		
		$aResult = json_decode($resultString, 1);
		
		if(is_array($aResult) && isset($aResult['result']['address_component']['street'])){
			return $aResult['result']['address_component']['street'];
		}
		return '';
	}
	
	public function getAdcodeByLocation($lng, $lat){
		$apiUrl = 'http://apis.map.qq.com/ws/geocoder/v1/?location=' . $lat . ',' . $lng . '&key=' . $this->secretKey . '&get_poi=1';
		$resultString = file_get_contents($apiUrl);
		
		$aResult = json_decode($resultString, 1);
		
		if(is_array($aResult) && isset($aResult['result']['ad_info']['adcode'])){
			return $aResult['result']['ad_info']['adcode'];
		}
		return 0;
	}
	
}
