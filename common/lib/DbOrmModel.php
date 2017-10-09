<?php
namespace common\lib;

use Yii;
use umeworld\lib\Query;
use yii\data\Pagination;
/**
 * 数据库ORM模型
 * @author 黄文非
 * @see http://www.cnblogs.com/chenkai/archive/2011/01/06/1929040.html 关于ORM模型的介绍
 */
abstract class DbOrmModel extends \yii\base\Model{
	protected $_aEncodeFields = [];	//需要编码的字段
	protected $_aSetFields = [];	//要保存的字段

	public static function find(){
		return (new Query())->from(static::tableName());
	}

	/**
	 * 查找一个模型实例
	 * @param type $xCondition 条件,如果是数字则为主键查询,其它条件表达式详情Yii的QueryBuilder使用章节
	 * @return static 模型实例
	 */
	public static function findOne($xCondition){
		if(is_numeric($xCondition)){
			$xCondition = ['id' => (int)$xCondition];
		}elseif(!is_array($xCondition)){
			throw new \yii\base\InvalidParamException('错误的查询条件');
		}
		$aData = (new Query())->from(static::tableName())->where($xCondition)->limit(1)->one();
		if(!$aData){
			return false;
		}

		return static::_addFields(new static(), $aData);
	}

	/**
	 * 查找多条数据记录,但不是实例,要做业务处理需要用toModel转换成实例
	 * @author 黄文非
	 * @param mixed $xWhere 查找条件
	 * @param array $aFields	 要查找的字段集合
	 * @param int $page 页码
	 * @param int $pageSize 一页要多少条
	 * @param array $aSortList 排序集合
	 * @return array 数据记录列表
	 * @see toModel()
	 */
	public static function findAll($xWhere = null, $aFields = null, $page = 0, $pageSize = 0, $aSortList = []){
		$oQuery = (new Query())->from(static::tableName());
		$aFields && $oQuery->select($aFields);
		$xWhere && $oQuery->where($xWhere);
		$pageSize > 0 && $oQuery->limit($pageSize);
		$page > 0 && $pageSize > 0 && $oQuery->offset(($page - 1) * $pageSize);
		$aSortList && $oQuery->orderBy($aSortList);

		$aDataList =  static::_beforeFindAll($oQuery, [
			'fileds' => $aFields,
			'where' => $xWhere,
			'page' => $page,
			'page_size' => $pageSize,
			'sort_list' => $aSortList,
		])->all();
		return static::_afterFindAll($aDataList, $aFields);
	}

	/**
	 * 查找多条记录前的回调
	 * @param Query $oQuery 查询器
	 * @param type $aCondition 条件
	 * @return Query 查询器
	 */
	protected static function _beforeFindAll(Query $oQuery, $aCondition){
		return $oQuery;
	}
	
	/**
	 * 查找多条记录后的回调
	 * @param array $aDataList 查询结果
	 * @return $aDataList 处理后结果
	 */
	protected static function _afterFindAll($aDataList, $aFields){
		return $aDataList;
	}

	/**
	 * 设置该模型所关联的数据库表名,也可以带数据库名称,比如 umfun.user
	 * @return type string
	 * @throws \yii\base\InvalidCallException
	 */
	public static function tableName(){
		throw new \yii\base\InvalidCallException('数据库ORM模型必须定义 public static function tableName 方法返回主表名');
	}

	public function __set($name, $xValue){
		if(in_array($name, $this->_aEncodeFields)){
			if($xValue && is_string($xValue)){
				$xValue = json_decode($xValue, true);
				if($jsonErrCode = json_last_error()){
					throw Yii::$app->buildError('模型字段 ' . $name . ' 解码失败', false, $jsonErrCode);
				}
			}elseif(!$xValue){
				$xValue = [];
			}

		}elseif(array_key_exists($name, $this->_aEncodeFields)){
			$xType = $this->_aEncodeFields[$name];
			if($xType == 'json' && is_string($xValue)){
				if($xValue){
					$xValue = json_decode($xValue, true);
					if($jsonErrCode = json_last_error()){
						throw Yii::$app->buildError('模型字段 ' . $name . ' 解码失败', false, $jsonErrCode);
					}
				}else{
					$xValue = [];
				}

			}elseif($xType == ',' && is_string($xValue)){
				if($xValue){
					$xValue = explode(',', $xValue);
				}else{
					$xValue = [];
				}

			}elseif(gettype($xType) == 'function'){
				$xValue = $xType($this, $name, $xValue);
			}elseif(is_array($xType)){
				$oParser = Yii::createObject($xType);
				$xValue = $oParser->{$xType['method']}();
			}
		}
		$this->$name = $xValue;
	}

	/**
	 * 设定该模型使用toArray的时候支持哪些字段toArray
	 * @return array 每个数组元素是一个字符串,这个字符串与类的属性名称对应
	 */
	public function fields(){
		return array_keys(Yii::getObjectVars($this));
	}

	public function set($field, $value){
		$this->_aSetFields[$field] = $value;
		if(is_array($value) && isset($value[0]) && ($value[0] === 'add' || $value[0] === 'sub')){
			if($value[0] === 'add'){
				$this->$field += $value[1];
			}else{
				$this->$field -= $value[1];
			}
		}else{
			$this->$field = $value;
		}
	}

	/**
	 * 保存模型数据
	 * @return bool 是否保存成功
	 */
	public function save(){
		$aRecord = $this->_aSetFields;
		if(!$aRecord){
			return 0;
		}
		foreach($aRecord as $field => $value){
			if(in_array($field, $this->_aEncodeFields)){
				$aRecord[$field] = json_encode($aRecord[$field]);
			}elseif(array_key_exists($field, $this->_aEncodeFields)){
				$xType = $this->_aEncodeFields[$field];
				if($xType == 'json'){
					$aRecord[$field] = json_encode($aRecord[$field]);
				}elseif($xType == ','){
					$aRecord[$field] = implode(',', $aRecord[$field]);
				}
			}
		}
		$result = (new Query())->createCommand()->update(static::tableName(), $aRecord, ['id' => $this->id])->execute();
		$this->_aSetFields = [];
		return (bool)$result;
	}

	/**
	 * 为模型实例追加动态属性,就是将数据库记录的字段注入模型属性使得可被外界访问
	 * @param static $mInstance 模型实例
	 * @param array $aData 数据,是一个 key => value 集合
	 * @return static 注入数据库字段后的模型实例
	 */
	protected static function _addFields($mInstance, $aData){
		foreach($aData as $field => $value){
			$mInstance->$field = $value;
		}
		return $mInstance;
	}

	/**
	 * 将一条数据库记录数组转换成模型
	 * @param array $aData 记录数组,一个 key => value 集合
	 * @return static 该模型的实例
	 */
	public static function toModel($aData){
		$mInstance = new static();
		foreach($aData as $field => $value){
			$mInstance->$field = $value;
		}
		return $mInstance;
	}

	/**
	 * 删除记录
	 * 当模型是一表多模型时就不能使用这个方法了!要重写方法自己联表删除之类的
	 * @return bool 删除是否成功
	 * @author 黄文非
	 */
	public function delete(){
		return (bool)(new Query())->createCommand()->delete(static::tableName(), ['id' => $this->id])->execute();
	}
}