<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;


class WithdrawCashRecord extends \common\lib\DbOrmModel{
	
	public static function tableName() {
		return Yii::$app->db->parseTable('_@withdraw_cash_record');
	}
	
	public static function add($aData){
		$aData['create_time'] = NOW_TIME;
		(new Query())->createCommand()->insert(self::tableName(), $aData)->execute();
		return Yii::$app->db->getLastInsertID();
	}
	
	public static function getTotalPrice($aWhere){
		$aResult = (new Query())->select('sum(`amount`) as `total_money`')->from(static::tableName())->where($aWhere)->all();
		return (int)$aResult[0]['total_money'];
	}
	
	/**
	 *	获取列表
	 *	$aCondition = [
	 *		'id' =>
	 *		'tenant_id' => 
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

		return $aWhere;
	}
	
	public static function countMoneyByOneDay($tenantId){
		$startTime = strtotime(date('Y-m-d', NOW_TIME));//今天开始时间戳
		$endTime = $startTime + 86399;//今天结束时间戳
		$aReturn = (new Query())->select('sum(amount) as `sum`')->from(static::tableName())->where([
			'and', 
			['>=', 'create_time', $startTime],
			['<=', 'create_time', $endTime],
			['tenant_id' => $tenantId],
		])->one();
		return (int)$aReturn['sum'];
	}
}

