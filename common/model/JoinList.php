<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;

class JoinList extends \common\lib\DbOrmModel{
	public static function tableName(){
		return Yii::$app->db->parseTable('_@join_list');
	}
	
	public static function add($aData){
		$aData['create_time'] = NOW_TIME;
		return (new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
	}
	
	/**
	 *	获取服务内容列表
	 *	$aCondition = [
	 *		'id' =>
	 *		'category_id' => 
	 *		'name' =>
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
		$aCityId = ArrayHelper::getColumn($aList, 'city_id');
		$aCitys = Area::findAll(['id' => $aCityId]);
		$aCitys = ArrayHelper::index($aCitys, 'id');
		$aCategoryId = ArrayHelper::getColumn($aList, 'category_id');
		$aJoinCategory = JoinCategory::findAll(['id' => $aCategoryId], ['id', 'name']);
		$aJoinCategory = ArrayHelper::index($aJoinCategory, 'id');
		foreach($aList as $key => $aValue){
			$aList[$key]['number'] = '';
			if($aValue['number_min'] == $aValue['number_max']){
				$aList[$key]['number'] = $aValue['number_min'];
			}elseif($aValue['number_min'] < $aValue['number_max']){
				$aList[$key]['number'] = $aValue['number_min'] . '-' . $aValue['number_max'];
			}
			$aList[$key]['city_name'] = isset($aCitys[$aValue['city_id']]) ? $aCitys[$aValue['city_id']]['name'] : '';
			$aList[$key]['category_name'] = $aJoinCategory[$aValue['category_id']]['name'];
		}
		return $aList;
	}
	
	/**
	 *	获取服务内容数量
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
		
		if(isset($aCondition['name'])){
			$aWhere[] = ['name' => $aCondition['name']];
		}
		
		if(isset($aCondition['category_id'])){
			$aWhere[] = ['category_id' => $aCondition['category_id']];
		}
		
		return $aWhere;
	}
}

