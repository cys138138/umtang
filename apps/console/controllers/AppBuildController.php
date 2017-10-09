<?php
namespace console\controllers;

use Yii;
use DOMDocument;

/**
 * APP构建控制器
 * @author 黄文非
 */
class AppBuildController extends \yii\console\Controller{
	/**
	 * 获取Jenkins服务器中指定构建ID的日志
	 */
	public function actionGetJenkinsBuildLog($logFile){
		if(!file_exists($logFile)){
			return 900;
		}

		$aChangeLogInfo = $this->_getChangeLogInfo($logFile);

		$aResultMessage = [];
		$aPuttedMessages = ['', 'test'];	//收集过的消息,以防输出重复的SVN日志内容
		foreach($aChangeLogInfo as $aChangeLog){
			$message = trim($aChangeLog['message']);
			if(in_array($message, $aPuttedMessages)){
				continue;
			}
			$aResultMessage[] = $aChangeLog['version'] . ' ' . $message;
			$aPuttedMessages[] = $message;
		}


		exit(implode(PHP_EOL, $aResultMessage));
	}

	/**
	 * 获取日志消息
	 * @param type $file
	 * @return type array 消息集合
	 */
	private function _getChangeLogInfo($file){
		$oXml = new DOMDocument();
		$oXml->load($file);

		$aResult = [];
		foreach($oXml->getElementsByTagName('logentry') as $oLogEntry){
			$aResultItem = [
				'version' => '?',
				'message' => '(no message)',
			];

			//获取SVN版本号
			if(!$oLogEntry->hasAttributes()){
				continue;
			}

			foreach($oLogEntry->attributes as $oAttribute){
				if($oAttribute->name == 'revision'){
					$aResultItem['version'] = $oAttribute->value;
					break;
				}
			}

			//获取SVN日志消息
			foreach($oLogEntry->getElementsByTagName('msg') as $oMsg){
				$aResultItem['message'] = $oMsg->nodeValue;
			}


			$aResult[] = $aResultItem;
		}
		rsort($aResult);
		return $aResult;
	}

	/**
	 * 构建静态资源文件
	 * @global type $aLocal
	 */
	public function actionBuildResource(){
		Yii::setAlias('@p.resource', PROJECT_PATH . '/web/resource');
		$this->stdout('开始构建静态资源文件' . PHP_EOL);
		//Yii::$app->params['app_list'] = ['parent'];
		foreach(Yii::$app->params['app_list'] as $appName){
			$this->stdout('正在构建模块:' . $appName . PHP_EOL);
			try{
				$aResourceList = $this->_buildResourceList($appName);
			}catch(\ErrorException $e){
				$this->stderr($e->getMessage());
				return $e->getCode();
			}
			file_put_contents(Yii::getAlias('@' . $appName) . '/config/resource.php', "<?php\nreturn " . var_export($aResourceList, true) . ';');
		}
		$this->stdout('静态资源文件构建完毕' . PHP_EOL);
	}

	/**
	 * 构建静态资源列表
	 * @param string $appName 资源所在的APP
	 * @return array 构建后的静态资源文件内容
	 */
	private function _buildResourceList($appName){
		$appAlias = '@' . $appName;
		$appPath = Yii::getAlias($appAlias);
		$appHost = '';
		$resourceUrl = Yii::getAlias('@r.url');
		$resourcePath = Yii::getAlias('@p.resource');
		if($appName == 'common'){
			$appHost = $resourceUrl;
		}else{
			$aAppUrlManagerConfig = include($appPath . '/config/url.php');
			$appHost = $aAppUrlManagerConfig['baseUrl'];
		}

		unset($aAppUrlManagerConfig);
		$aResourceList = include($appPath . '/config/resource.php');
		//$appWebPath = Yii::getAlias('@' . $appName . '/web');
		$appWebPath = PROJECT_PATH . '/web';
		foreach($aResourceList as $key => $aResource){
			if(is_string($aResource)){
				if(strpos($aResource, '?')){
					$aResourceList[$key] = $aResource;
					continue;
				}

				$aResource = [
					'ref' => $aResource,
				];
			}

			$file = '';
			if(!isset($aResource['path'])){
				if(isset($aResource['cdn'])){
					$aResource['ref'] = $aResource['cdn'];
				}

				$referer = $aResource['ref'];
				if($referer[0] == '@'){
					$referer = Yii::getAlias($referer);
					if(strpos($referer, $appHost) === 0 && $appName != 'common'){
						$referer = preg_replace('#^' . $appHost . '#', '', $referer);
						//没有本地path则认为是外部cdn资源等等
						$file = $appWebPath . '/' . ltrim($referer, '/');
					}elseif(strpos($referer, $resourceUrl) === 0){
						$referer = preg_replace('#^' . $resourceUrl . '#', '', $referer);
						$file = $resourcePath . '/' . ltrim($referer, '/');
					}elseif(strpos($referer, Yii::getAlias('@url.home')) === 0){
						$referer = preg_replace('#^' . Yii::getAlias('@url.home') . '#', '', $referer);
						$file = PROJECT_PATH . '/apps/www.umfun.com/' . ltrim($referer, '/');
					}
				}else{
					$file = $appWebPath . '/' . ltrim($referer, '/');
				}
			}else{
				$file = Yii::getAlias($aResource['path']);
			}

			if(!$file){
				throw new \ErrorException('构建 ' . $key . ' 时获取文件路径失败', 903);
			}

			if(!file_exists($file)){
				throw new \ErrorException('构建 ' . $key . ' 时资源文件 ' . $file . ' 不存在', 902);
			}

			$aResourceList[$key] = $aResource['ref'] . '?v=' . date('Y-m-d_H:i', filemtime($file));
		}

		return $aResourceList;
	}

	/**
	 * 实现检查非无BOM的UTF8编码脚本文件
	 * @param string $file 文件路径
	 */
	public function actionCheckNotUtf8WithoutBom($file) {
		if (!file_exists($file)) {
			$this->stderr($file . 'File does not exist' . PHP_EOL);
			return 1;
		}

		$contents = file_get_contents($file);
		if (!$this->_checkIsUtf8($contents)) {
			$this->stderr('File not is utf-8 charset ' . $file . PHP_EOL);
			return 1;
		}

		if ($this->_checkIsHaveBOM($contents)) {
			$this->stderr('File is found Bom  fileName is ' . $file . PHP_EOL);
			return 1;
		}
		return 0;
	}

	/**
	 * 通过字符检查是否含有bom
	 * @param type $filename 要检查的文件内容
	 * @return boolean 含有bom返回true 非返回false
	 */
	private function _checkIsHaveBOM($contents) {
		//获取前三个字符
		$charset[1] = substr($contents, 0, 1);
		$charset[2] = substr($contents, 1, 1);
		$charset[3] = substr($contents, 2, 1);
		//获得ASCII码值
		if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
			return true;
		}
		return false;
	}

	/**
	 * 检查是否符合utf8
	 */
	private function _checkIsUtf8($string) {
		return preg_match('%^(?:
			 [\x09\x0A\x0D\x20-\x7E]            # ASCII
		   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
		   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
		   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
		   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
		   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
		   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
		   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
	   )*$%xs', $string);
	}
}