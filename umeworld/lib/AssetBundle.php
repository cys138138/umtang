<?php
namespace umeworld\lib;

use Yii;
use yii\helpers\Html;

/**
 * 基础资源包，支持别包解析CSS和JS路径
 * @author 黄文非
 */
class AssetBundle extends \yii\web\AssetBundle{
	/**
	 * @inheritdoc
	 */
	public function init(){
		parent::init();

		//解析JS别名
		foreach($this->js as &$js){
			$js = $this->parseResourceAlias($js);
		}

		//解析CSS别名
		foreach($this->css as &$css){
			$css = $this->parseResourceAlias($css);
		}
	}

	/**
	 * 解析资源别名
	 * @param string $resourceAlias 别名
	 * @return string 解析后的资源
	 */
	public function parseResourceAlias($resourceAlias){
		try{
			$publishUrl = \Yii::getAlias($resourceAlias);
			if($publishUrl[0] == '/'){
				//解析后去掉最前面的 / 号,因为底层发布资源时会自动加 / 号,若连接成 // 后就无法加载到资源了
				$publishUrl = substr($publishUrl, 1);
			}
		}catch(\yii\base\InvalidParamException $e){
			if(!YII_ENV_PROD){
				throw $e;
			}else{
				Yii::error('无法解析资源 ' . $resourceAlias);
				return '';
			}
		}
		return $publishUrl;
	}

	/**
	 * 输出资源引用标签,该方法是为了给旧框架学生端用,重构完后可删除
	 * @author 黄文非
	 */
	public function outputReferer(){
		$this->publish(Yii::$app->assetManager);
		foreach($this->css as $cssFile){
			echo Html::cssFile($cssFile);
		}
		foreach($this->js as $jsFile){
			echo Html::jsFile($jsFile);
		}
	}
}