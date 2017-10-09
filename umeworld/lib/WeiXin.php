<?php

namespace umeworld\lib;

use Yii;
use umeworld\lib\Cookie;
use umeworld\lib\Xxtea;
use common\model\Redis;

class WeiXin extends \yii\base\Object{

	/**
	 * AppID(应用ID)
	 */
	public $appId = '';

	/**
	 * AppSecret(应用密钥)
	 */
	public $appSecret = '';
	
	/**
	 * 根据code获取openid
	 */
	public function getOpenIdByCode($code){
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->appId . '&secret=' . $this->appSecret . '&js_code=' . $code . '&grant_type=authorization_code';
		$result = file_get_contents($url);
		$aResult = json_decode($result, true);
		if(isset($aResult['openid']) && $aResult['openid']){
			return $aResult['openid'];
		}
		return false;
	}
}
