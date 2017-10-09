<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;


class UserAccumulatePointGetRecord extends \common\lib\DbOrmModel{
	const TYPE_FIRST_COMMENT_ORDER = 1;							//订单初评，500积分
	const TYPE_NEW_MOBILE_USER_REGISTER_TASK = 2;				//新手机用户注册任务（绑定手机），5000积分
	const TYPE_FIRST_ORDER_TASK = 3;							//用户首次下单任务，5000积分
	const TYPE_FIRST_PAY_ORDER_TASK = 4;						//用户首次买单任务，5000积分
	const TYPE_FIRST_COMMENT_ORDER_TASK = 5;					//订单初评任务，1000积分
	const TYPE_FIRST_SUPERADDITION_COMMENT_ORDER_TASK = 6;		//订单初次追评任务，1000积分
	const TYPE_FIRST_COLLECT_GOODS_TASK = 7;					//首次收藏商品任务，500积分
	const TYPE_FIRST_COLLECT_SHOP_TASK = 8;						//首次收藏商店任务，500积分
	const TYPE_FIRST_COMMENT_ORDER_TEN_COUNT_TASK = 9;			//累计进行10次初评任务，2000积分
	const TYPE_TOTAL_PAY_FIVE_THOUSAND_MONEY_TASK = 10;			//累计实际支付5000元任务，10000积分
	const TYPE_TOTAL_PAY_TEN_THOUSAND_MONEY_TASK = 11;			//累计实际支付10000元任务，25000积分
	const TYPE_TOTAL_PAY_TWENTY_THOUSAND_MONEY_TASK = 12;		//累计实际支付20000元任务，60000积分
	
	private static function _getTypeContentMap(){
		return [
			static::TYPE_FIRST_COMMENT_ORDER => '评价订单',
			static::TYPE_NEW_MOBILE_USER_REGISTER_TASK => '新用户注册',
			static::TYPE_FIRST_ORDER_TASK => '积分任务：首次下单',
			static::TYPE_FIRST_PAY_ORDER_TASK => '积分任务：首次买单',
			static::TYPE_FIRST_COMMENT_ORDER_TASK => '积分任务：订单首次评价',
			static::TYPE_FIRST_SUPERADDITION_COMMENT_ORDER_TASK => '积分任务：订单首次追评',
			static::TYPE_FIRST_COLLECT_GOODS_TASK => '积分任务：首次收藏商品',
			static::TYPE_FIRST_COLLECT_SHOP_TASK => '积分任务：首次收藏商店',
			static::TYPE_FIRST_COMMENT_ORDER_TEN_COUNT_TASK => '积分任务：累计进行10次订单首次评价',
			static::TYPE_TOTAL_PAY_FIVE_THOUSAND_MONEY_TASK => '积分任务：累计实际支付5000元',
			static::TYPE_TOTAL_PAY_TEN_THOUSAND_MONEY_TASK => '积分任务：累计实际支付10000元',
			static::TYPE_TOTAL_PAY_TWENTY_THOUSAND_MONEY_TASK => '积分任务：累计实际支付20000元',
		];
	}
		
	public static function tableName() {
		return Yii::$app->db->parseTable('_@user_accumulate_point_get_record');
	}
	
	public static function add($aData){
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

