<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;

/**
 * 商品照片表模型
 * @author 谭威力
 */
class GoodsPhoto extends \common\lib\DbOrmModel{
	//需要编译的字段
	protected $_aEncodeFields = [];
	//要保存的字段
	protected $_aExtendFields = [];

	/**
     * @inheritdoc
     */
    public static function tableName(){
        return Yii::$app->db->parseTable('_@goods_photo');
    }
	
	/**
	 * @inheritdoc
	 */
	public function __get($name) {
		if($name == 'resource_path'){
			$mResource = Resource::findOne(['id' => $this->resource_id]);
			if(!$mResource){
				$this->$name = '';
			}else{
				$this->$name = $mResource->getUrl();
			}
			return $this->$name;
		}
		if(!in_array($name, array_keys(Yii::getObjectVars($this)))){
			return parent::__get($name);
		}else{
			return $this->$name;
		}
	}
	
	public static function add($aData){
		$aData['create_time'] = NOW_TIME;
		(new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
		return Yii::$app->db->getLastInsertID();
	}
	
	public static function addBatch($aDataList){
		$aColumn = ['goods_id', 'resource_id', 'is_cover', 'create_time'];
		$aRows = [];
		foreach($aDataList as $aData){
			$aData['is_cover'] = 0;
			$aData['create_time'] = NOW_TIME;
			$aTemp = [];
			foreach($aColumn as $field){
				$aTemp[] = $aData[$field];
			}
			$aRows[] = $aTemp;
		}
		return (new Query)->createCommand()->batchInsert(self::tableName(), $aColumn, $aRows)->execute();
	}


	/**
	 *	获取列表-----商户端使用
	 *	$aCondition = [
	 *		'id' =>
	 *		'goods_id' =>
	 *	]
	 *	$aControl = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'page' =>
	 *		'page_size' =>
	 *		'with_xxx_info' => true/false
	 *	]
	 */
	public static function getListForTenant($aCondition = [], $aControl = []){
		$aWhere = static::_parseWhereCondition($aCondition);
		$oQuery = new Query();
		if(isset($aControl['select'])){
			$oQuery->select($aControl['select']);
		}
		$oQuery->from(static::tableName())->where($aWhere);
		if(isset($aControl['order_by'])){
			$oQuery->orderBy($aControl['order_by']);
		}
		if(isset($aControl['page']) && isset($aControl['page_size'])){
			$offset = ($aControl['page'] - 1) * $aControl['page_size'];
			$oQuery->offset($offset)->limit($aControl['page_size']);
		}
		$aList = $oQuery->all();
		$listCount = count($aList);
		if($listCount < $aControl['page_size']){
			$mGoodsApprove = GoodsApprove::findOne($aCondition['goods_id']);
			if($mGoodsApprove && isset($mGoodsApprove->content[GoodsApprove::PHOTO_KEY_NAME])){
				if($listCount > 0){
					//说明能拿到记录，但是不够
					$requireCount = $aControl['page_size'] - $listCount;
					for($i = 0; $i < $requireCount; $i++){
						if(!isset($mGoodsApprove->content[GoodsApprove::PHOTO_KEY_NAME][$i])){
							break;
						}
						$aList[] = $mGoodsApprove->content[GoodsApprove::PHOTO_KEY_NAME][$i];
					}
				}else{
					//什么都没拿到
					$allPhotoCount = static::getCount($aCondition);
					$approveOffset = $offset - $allPhotoCount;
					for($i = 0; $i < $aControl['page_size']; $i++){
						if(!isset($mGoodsApprove->content[GoodsApprove::PHOTO_KEY_NAME][$approveOffset])){
							break;
						}
						$aList[] = $mGoodsApprove->content[GoodsApprove::PHOTO_KEY_NAME][$approveOffset];
						$approveOffset++;
					}
				}
			}
		}
		if(!$aList){
			return [];
		}
		$aResoureIds = ArrayHelper::getColumn($aList, 'resource_id');
		$aResources = Resource::findAll(['id' => $aResoureIds]);
		foreach($aList as $key => $value){
			$aList[$key]['resource_path'] = '';
			foreach($aResources as $aResource){
				if($aResource['id'] == $value['resource_id']){
					$mResource = Resource::toModel($aResource);
					$value['resource_path'] = $mResource->getUrl();
					$aList[$key] = $value;
				}
			}
		}
		return $aList;
	}
	
	/**
	 *	获取数量
	 */
	public static function getCount($aCondition = []){
		$aWhere = static::_parseWhereCondition($aCondition);
		return (new Query())->from(static::tableName())->where($aWhere)->count();
	}
	
	/**
	 *	获取数量---商户端
	 */
	public static function getCountForTenant($aCondition = []){
		$aWhere = static::_parseWhereCondition($aCondition);
		$mGoodsApprove = GoodsApprove::findOne($aCondition['goods_id']);
		$count = (new Query())->from(static::tableName())->where($aWhere)->count();
		if($mGoodsApprove && isset($mGoodsApprove->content[GoodsApprove::PHOTO_KEY_NAME])){
			$count += count($mGoodsApprove->content[GoodsApprove::PHOTO_KEY_NAME]);
		}
		return $count;
	}
	
	/**
	 *	获取列表-----小程序
	 *	$aCondition = [
	 *		'id' =>
	 *		'goods_id' =>
	 *	]
	 *	$aControl = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'group_by' => 
	 *		'page' =>
	 *		'page_size' =>
	 *		'with_xxx_info' => true/false
	 *	]
	 */
	public static function getList($aCondition = [], $aControl = []){
		$aWhere = static::_parseWhereCondition($aCondition);
		$oQuery = new Query();
		if(isset($aControl['select'])){
			$oQuery->select($aControl['select']);
		}
		$oQuery->from(static::tableName())->where($aWhere);
		if(isset($aControl['order_by'])){
			$oQuery->orderBy($aControl['order_by']);
		}
		if(isset($aControl['group_by'])){
			$oQuery->groupBy($aControl['group_by']);
		}
		if(isset($aControl['page']) && isset($aControl['page_size'])){
			$offset = ($aControl['page'] - 1) * $aControl['page_size'];
			$oQuery->offset($offset)->limit($aControl['page_size']);
		}
		$aList = $oQuery->all();
		if(!$aList){
			return [];
		}
		$aResoureIds = ArrayHelper::getColumn($aList, 'resource_id');
		$aResources = Resource::findAll(['id' => $aResoureIds]);
		foreach($aList as $key => $value){
			$aList[$key]['resource_path'] = '';
			foreach($aResources as $aResource){
				if($aResource['id'] == $value['resource_id']){
					$mResource = Resource::toModel($aResource);
					$value['resource_path'] = $mResource->getUrl();
					$aList[$key] = $value;
				}
			}
		}
		return $aList;
	}
	
	private static function _parseWhereCondition($aCondition = []){
		$aWhere = ['and'];
		if(isset($aCondition['id'])){
			$aWhere[] = ['id' => $aCondition['id']];
		}
		if(isset($aCondition['goods_id'])){
			$aWhere[] = ['goods_id' => $aCondition['goods_id']];
		}

		return $aWhere;
	}
}

