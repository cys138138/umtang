<?php
namespace common\widgets;

use Yii;

/**
 * 微信js-sdk引入代码
 * @author hong
 */
class WxJsSdk extends \yii\base\Widget{

	const API_PREVIEW_IMAGE = 'previewImage';	//预览图片

	public $apiList = '';

	public function run(){

		//非微信环境不输出该代码
		$userAgent = strtolower(Yii::$app->request->headers['user-agent']);
		if(strpos($userAgent, 'micromessenger') === false){
			return;
		}

		$protocol =  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';

		$jsSdkParam = Yii::$app->weiXin->jSDDKInfo;
		// $jsSdkParam['debug'] = true;
		$jsSdkParam['jsApiList'] = $this->apiList;

		return $this->render('wx_js_sdk', [
			'protocol' => $protocol,
			'jsSdkParam' => $jsSdkParam,
		]);
	}
}