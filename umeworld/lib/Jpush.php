<?php
namespace umeworld\lib;

use Yii;
use JPush\Model as M;
use JPush\JPushClient;
use JPush\JPushLog;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;

/**
 * 极光推送
 */
class Jpush extends \yii\base\Component {
	public $appKey;
	public $masterSecret;

	public function init() {
		parent::init();
		require_once Yii::getAlias('@umeworld') . '/lib/jpushVender/autoload.php';
	}

	/**
	 * 极光推送 Notification Api
	 * @author jay
	 * @param string $alert 提醒文案
	 * @param string $title 标题
	 * @param string $type 消息类型标识
	 * @param array $aReceiverAliases 接收者的客户端别名集合
	 * @param array $aExtras 额外数据
	 * @param int $builderId 指定客户端使用的通知栏样式ID
	 * @return null
	 */
	public function sendNotification($alert, $title, $type, $aReceiverAliases = [], $aExtras = []) {
		$oClient = new JPushClient($this->appKey, $this->masterSecret);
		try {
			$oClient->push()
				->setPlatform(M\Platform('android'))
				->setAudience(M\Audience(M\alias($aReceiverAliases)))
				->setMessage(M\message($alert, $title, (string)$type, $aExtras))
				->send();
		} catch (APIRequestException $e) {
			$br = '<br/>';
			$alert .= 'Push Fail.' . $br;
			$alert .= 'Http Code : ' . $e->httpCode . $br;
			$alert .= 'code : ' . $e->code . $br;
			$alert .= 'Error Message : ' . $e->message . $br;
			$alert .= 'Response JSON : ' . $e->json . $br;
			$alert .= 'rateLimitLimit : ' . $e->rateLimitLimit . $br;
			$alert .= 'rateLimitRemaining : ' . $e->rateLimitRemaining . $br;
			$alert .= 'rateLimitReset : ' . $e->rateLimitReset . $br;
			Yii::warning($alert);
		} catch (APIConnectionException $e) {
			$br = '<br/>';
			$alert .= 'Push Fail: ' . $br;
			$alert .= 'Error Message: ' . $e->getMessage() . $br;
			//response timeout means your request has probably be received by JPUsh Server,please check that whether need to be pushed again.
			$alert .= 'IsResponseTimeout: ' . $e->isResponseTimeout . $br;
			Yii::warning($alert);
		}
	}
}
