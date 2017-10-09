<?php
namespace manage\model;

use umeworld\lib\Query;

class CommercialTenantType extends \common\model\CommercialTenantType{
	/**
	 * 获取列表
	 * $aCondition = ['id'=> ];
	 * $aController = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'page' =>
	 *		'page_size' =>
	 * ]
	 */
	public static function getList($aCondition = [], $aControl = []){
		$oQuery = new Query();
		if(isset($aControl['select'])){
			$oQuery->select($aControl['select']);
		}
		$oQuery->from(static::tableName())->where($aCondition);
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
	
	public static function getCount($aCondition = []){
		return (new Query())->from(static::tableName())->where($aCondition)->count();
	}
}