<?php
namespace common\lib;

use Yii;

/**
 * 基于配置的ORM模型
 * @author 黄文非
 */
abstract class ConfigOrmModel extends \yii\base\Model{
	protected static $_dataFile = '';	//配置文件名称,需要放在common/config/model目录下
	protected static $_originalData = [];	//读出来的原始配置数据

	public static function getConfigFile(){
		return Yii::getAlias('@common') . '/config/model/' . static::$_dataFile . '.php';
	}

	/**
	 * 查找一个模型实例,其实是用配置模拟了数据库的数据表,只是数据源是来自配置文件了
	 * @param mixed $xCondition 条件,如果是数字则为配置数据列表的唯一标识符查询,具体要看 _parseConfig 方法的实现
	 * @return static 模型实例
	 */
	public static function findOne($xCondition){
		static::_importConfig();
		$mInstance = new static();
		if(!$mInstance->_parseConfig($xCondition)){
			return false;
		}

		return $mInstance;
	}

	/**
	 * 查找多条数据记录,但不是实例,要做业务处理需要用toModel转换成实例
	 * @see toModel()
	 * @return array 配置数据记录集合
	 */
	public static function findAll($xCondition = null){
		static::_importConfig();
		return static::$_originalData;
	}

	/**
	 * 将一条配置记录数组转换成模型
	 * @param array $aData 配置记录数组,一个 key => value 集合
	 * @return static 该模型的实例
	 */
	public static function toModel($aData){
		$mInstance = new static();
		foreach($aData as $key => $value){
			$mInstance->$key = $value;
		}
		return $mInstance;
	}

	/**
	 * 读入配置数据到模型中
	 * @see _originalData
	 * @throws \umeworld\lib\ServerErrorHttpException
	 */
	protected static function _importConfig(){
		if(!static::$_originalData){
			$configFile = static::getConfigFile();
			if(!file_exists($configFile)){
				throw Yii::$app->buildError('找不到 ' . __CLASS__ . '模型 的配置文件');
			}
			static::$_originalData = require($configFile);
		}
	}

	/**
	 * 实现从读入的配置数据中按条件查找到相关的配置记录导入到模型自身
	 * @param mixed $xCondition 查找条件
	 * @return static 本模型的模型实例
	 * @see _originalData
	 */
	protected function _parseConfig($xCondition){
		throw new \yii\base\InvalidCallException('子类未实现该方法');
	}

	/**
	 * 设定该模型使用toArray的时候支持哪些字段toArray
	 * @return array 每个数组元素是一个字符串,这个字符串与类的属性名称对应
	 */
	public function fields(){
		return array_keys(Yii::getObjectVars($this));
	}

	public function __set($name, $xValue){
		$this->$name = $xValue;
	}
}