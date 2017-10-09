<?php
error_reporting(-1);
$appPath = PROJECT_PATH;
Yii::setAlias('common', dirname(__DIR__));

//APP别名设置 start
if(!YII_ENV_PROD){
	Yii::setAlias('dev', $appPath . '/apps/dev');
}
$domainSuffix = $aLocal['domain_suffix'][YII_ENV];

Yii::setAlias('console',			$appPath . '/apps/console');

Yii::setAlias('login',				$appPath . '/apps/login');
Yii::setAlias('url.base',			'http://www.umtang.' . $domainSuffix);

Yii::setAlias('manage',				$appPath . '/apps/manage');
Yii::setAlias('url.manage',			'http://m.umtang.' . $domainSuffix);

Yii::setAlias('tenant',				$appPath . '/apps/tenant');

Yii::setAlias('api',				$appPath . '/apps/api');

//APP别名设置 end

Yii::setAlias('umeworld',			$appPath . '/umeworld');
Yii::setAlias('r.url', Yii::getAlias('@url.base') . '/resource/');
$aLocal['resource_url'] = Yii::getAlias('@r.url');
Yii::setAlias('p.system_view',		$appPath . '/common/views/system');
Yii::setAlias('@p.tenant_goods_photo', 'data/tenant/image/');
Yii::setAlias('@p.complain_img', 'data/complain_img/');
Yii::setAlias('@p.album', 'data/album/');
Yii::setAlias('@p.bright_spot_img', 'data/bright_spot/');
Yii::setAlias('@p.reception_send_img', 'data/reception_send/');
Yii::setAlias('@p.temp_upload', 'data/temp/image/');
Yii::setAlias('@p.tenant_upload', 'data/tenant/upload/');
Yii::setAlias('@p.api_comment_upload', 'data/api/upload/');
Yii::setAlias('@p.announcement_upload', 'data/tenant/announcement/');

Yii::setAlias('@p.banner_api', 'banner/api/');
Yii::setAlias('@p.banner_tenant', 'banner/tenant/');

defined('NOW_TIME') || define('NOW_TIME', time());
unset($appPath, $domainSuffix);

if(!defined('UMFUN_TESTING')){
	/**
	 * 调试输出函数
	 * @param type mixed $xData 要调试输出的数据
	 * @param type int $mode 11=输出并停止运行,111=停止并输出运行轨迹,12=以PHP代码方式输出,13=dump方式输出,其中第十位数为0的时候表示不停止运行,前面的参数样例十位都是1所以会停止运行,个位用于控制输出模式 @see \umeworld\lib\Debug
	 */
	function debug($xData, $mode = null){
		if($mode === null){
			$mode = \umeworld\lib\Debug::MODE_NORMAL;
		}
		\umeworld\lib\Debug::dump($xData, $mode, true);
	}
}

if(isset($_GET['__SQS'])){
	unset($_GET['__SQS']);
}
