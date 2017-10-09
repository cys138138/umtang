<?php
namespace common\lib;

use Yii;
use umeworld\lib\Application;
use common\lib\event\AfterGetAppConfig;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * APP创建器
 * @author 黄文非
 */
class AppCreater extends Component{
	/**
	 * @var int 本次要创建App的ID
	 */
	public $appId = '';

	/**
	 * @var bool 创建APP过程中是否自动加载资源
	 */
	public $autoLoadResource = true;

	/**
	 * @var bool 创建APP过程中是否运行APP的预启动脚本
	 */
	public $isRunBootstrap = false;

	/**
	 * @var string App的目录
	 */
	private $_appPath = '';

	/**
	 * 获取到配置后
	 */
	const EVENT_AFTER_GET_APP_CONFIG = 'after-get-app-config';

	/**
	 * 获取APP的配置
	 * @param array $aConfig 另外增加的配置
	 * @return array
	 */
	public function getConfig(){
		global $aLocal;
		$aAppConfig = ArrayHelper::merge(
			require(PROJECT_PATH . '/common/config/main.php'),
			require(PROJECT_PATH . '/common/config/main-local.php'),
			require($this->appPath . '/config/main.php'),
			require($this->appPath . '/config/main-local.php')
		);
		return $aAppConfig;
	}

	/**
	 * 创建APP
	 * @param array $aConfig APP的配置
	 * @return Application APP对象
	 */
	public function createApp(array $aConfig = []){
		$this->initPaths();


		if($this->isRunBootstrap){
			include($this->appPath . '/config/bootstrap.php');
		}

		$aAppConfig = $this->getConfig();
		$oEvent = new AfterGetAppConfig([
			'aConfig' => $aAppConfig,
		]);
		$this->trigger(static::EVENT_AFTER_GET_APP_CONFIG, $oEvent);
		$oApp = new Application(ArrayHelper::merge($oEvent->aConfig, $aConfig));
		$this->autoLoadResource && $oApp->loadResource();

		return $oApp;
	}

	/**
	 * 初始化全局别名路径
	 */
	public function initPaths(){
		Yii::setAlias('p.resource',		PROJECT_PATH . '/web/resource');
	}

	/**
	 * 获取本次要创建的APP的程序目录路径
	 * @return string
	 */
	public function getAppPath(){
		if($this->_appPath == ''){
			if(YII_ENV_PROD){
				$this->_appPath = Yii::getAlias('@' . $this->appId);
			}else{
				$this->_appPath = PROJECT_PATH . '/apps/' . $this->appId;
			}
		}
		return $this->_appPath;
	}
}