<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use common\lib\DbOrmModel;
use yii\helpers\ArrayHelper;

class CommercialTenantTypeRelation extends DbOrmModel{
	public static function tableName(){
        return Yii::$app->db->parseTable('_@commercial_tenant_type_relation');
    }
	
	public static function addRelation($aData){
		$aData['create_time'] = NOW_TIME;
		return (new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
	}
	
	public static function deleteByIds($aDeleteIds){
		return (bool)(new Query())->createCommand()->delete(static::tableName(), ['id' => $aDeleteIds])->execute();
	}
	
	public static function batchInsertRecord($aInsertList){
		return (new Query())->createCommand()->batchInsert(static::tableName(), ['tenant_id', 'type_id', 'create_time'], $aInsertList)->execute();
	}
	
	/**
	 *	获取列表
	 *	$aCondition = [
	 *		'id' =>
	 *		'tenant_id' =>
	 *		'type_id' =>
	 *	]
	 *	$aControl = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'page' =>
	 *		'page_size' =>
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
		if(isset($aControl['page']) && isset($aControl['page_size'])){
			$offset = ($aControl['page'] - 1) * $aControl['page_size'];
			$oQuery->offset($offset)->limit($aControl['page_size']);
		}
		$aList = $oQuery->all();
		if(!$aList){
			return [];
		}
		$aResourceId = ArrayHelper::getColumn($aList, 'resource_id');
		$aResourceList = Resource::findAll(['id' => $aResourceId]);
		foreach($aList as $key => $value){
			$aList[$key]['path'] = '';
			foreach($aResourceList as $aResource){
				if($aResource['id'] == $value['resource_id']){
					$aList[$key]['path'] = $aResource['path'];
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
	
	private static function _parseWhereCondition($aCondition = []){
		$aWhere = ['and'];
		if(isset($aCondition['id'])){
			$aWhere[] = ['id' => $aCondition['id']];
		}

		if(isset($aCondition['tenant_id'])){
			$aWhere[] = ['tenant_id' => $aCondition['tenant_id']];
		}
		if(isset($aCondition['type_id'])){
			$aWhere[] = ['type_id' => $aCondition['type_id']];
		}

		return $aWhere;
	}
}