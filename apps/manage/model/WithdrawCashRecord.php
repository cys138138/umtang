<?php
namespace manage\model;

use umeworld\lib\Query;
use yii\helpers\ArrayHelper;

class WithdrawCashRecord extends \common\model\WithdrawCashRecord{
	/**
	 *	获取列表
	 *	$aCondition = [
	 *		'id' =>
	 *		'tenant_id' =>
	 *		'is_finish' =>
	 *		'start_time' =>
	 *		'end_time' =>
	 *	]
	 *	$aControl = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'page' =>
	 *		'page_size' =>
	 *		'with_tenant_info' =>
	 *	]
	 */
	public static function getList($aCondition = [], $aControl = []) {
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
		
		if(isset($aControl['with_tenant_info']) && $aControl['with_tenant_info']){
			$aTenantId = ArrayHelper::getColumn($aList, 'tenant_id');
			$aTenantId = array_unique($aTenantId);
			$aCommercialTenant = CommercialTenant::findAll(['id' => $aTenantId]);
			$aCommercialTenant = ArrayHelper::index($aCommercialTenant, 'id');
			foreach($aList as $key => $value){
				$aList[$key]['tenant_info'] = [];
				if(isset($aCommercialTenant[$value['tenant_id']])){
					$mCommercialTenant = CommercialTenant::toModel($aCommercialTenant[$value['tenant_id']]);
					$aList[$key]['tenant_info'] = $mCommercialTenant->toArray(['id', 'name', 'money', 'bank_name', 'bank_accout_type', 'bank_accout', 'bank_account_holder']);
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
		if(isset($aCondition['is_finish'])){
			$aWhere[] = ['is_finish' => $aCondition['is_finish']];
		}
		if(isset($aCondition['start_time']) && $aCondition['start_time']){
			$aWhere[] = ['>=', 'create_time', $aCondition['start_time']];
		}
		if(isset($aCondition['end_time']) && $aCondition['end_time']){
			$aWhere[] = ['<', 'create_time', $aCondition['end_time']];
		}

		return $aWhere;
	}
}