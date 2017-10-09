<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use common\lib\DbOrmModel;

class CommercialTenantNotice extends DbOrmModel{
	
	public static function tableName(){
        return Yii::$app->db->parseTable('_@commercial_tenant_notice');
    }
	
	public static function batchInsertData($aInsertList){
		return (new Query())->createCommand()->batchInsert(static::tableName(), ['tenant_id', 'title', 'content', 'is_read', 'create_time'], $aInsertList)->execute();
	}
	
	/**
	 *	获取列表
	 *	$aCondition = [
	 *		'id' =>
	 *		'tenant_id' =>
	 *		'is_read' =>
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

		if(isset($aCondition['is_read'])){
			$aWhere[] = ['is_read' => $aCondition['is_read']];
		}

		return $aWhere;
	}
	
	/**
	 *  发私信
	 *  $aData = [
	 *		'tenant_id' =>
	 *		'title' =>
	 *		'content' =>
	 *		'is_read' =>
	 *		'create_time' =>
	 *	]
	 */
	public static function add($aData = []){
		$aData['is_read'] = 0;
		$aData['create_time'] = NOW_TIME;
		(new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
		return Yii::$app->db->getLastInsertID();
	}
}