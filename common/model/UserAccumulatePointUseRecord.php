<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;


class UserAccumulatePointUseRecord extends \common\lib\DbOrmModel{
	const TYPE_PAY_ORDER = 1;							//购买商品积分抵扣
	
	private static function _getTypeContentMap(){
		return [
			static::TYPE_PAY_ORDER => '下单积分抵扣',
		];
	}
	
	public static function tableName() {
		return Yii::$app->db->parseTable('_@user_accumulate_point_use_record');
	}
	
	public static function add($aData){
		$aData['create_time'] = NOW_TIME;
		(new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
		return Yii::$app->db->getLastInsertID();
	}
	
	/**
	 *	获取列表
	 *	$aCondition = [
	 *		'id' =>
	 *		'user_id' =>
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
				
		return static::_parseListTypeToContent($aList);
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

		if(isset($aCondition['user_id'])){
			$aWhere[] = ['user_id' => $aCondition['user_id']];
		}

		return $aWhere;
	}
	
	private static function _parseListTypeToContent($aList){
		$aTypeContentMap = static::_getTypeContentMap();
		foreach($aList as $key => $value){
			if(isset($aTypeContentMap[$value['type']])){
				$aList[$key]['content'] = $aTypeContentMap[$value['type']];
			}else{
				$aList[$key]['content'] = '';
			}
		}
		return $aList;
	}
	
}

