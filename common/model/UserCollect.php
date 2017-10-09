<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;


class UserCollect extends \common\lib\DbOrmModel{
	const TYPE_SHOP = 1;	//商铺
	const TYPE_GOODS = 2;	//商品

	public static function tableName() {
		return Yii::$app->db->parseTable('_@user_collect');
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
	 *		'type' =>
	 *		'lng' =>
	 *		'lat' =>
	 *	]
	 *	$aControl = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'page' =>
	 *		'page_size' =>
	 *		'with_data_info' => true/false
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
		if(isset($aControl['with_data_info']) && $aControl['with_data_info']){
			$aShopId = [];
			$aGoodsId = [];
			foreach($aList as $key => $value){
				if($value['type'] == static::TYPE_SHOP){
					array_push($aShopId, $value['data_id']);
				}elseif($value['type'] == static::TYPE_GOODS){
					array_push($aGoodsId, $value['data_id']);
				}
			}
			$aShopList = [];
			$aGoodsList = [];
			if($aShopId){
				$aShopList = CommercialTenant::getTenantListWithDistance([
					'id' => $aShopId,
					'lng' => $aCondition['lng'],
					'lat' => $aCondition['lat'],
				], [
					'page' => 1,
					'page_size' => count($aShopId),
				]);
			}
			if($aGoodsId){
				$aGoodsList = Goods::findAll(['id' => $aGoodsId], ['id', 'tenant_id', 'name', 'price', 'retail_price', 'sales_count'], 0, 0);
				$aGoodsList = Goods::goodsListWithPhotoPath($aGoodsList);
			}
			foreach($aList as $key => $value){
				$aList[$key]['data_info'] = [];
				foreach($aShopList as $aShop){
					if($aShop['id'] == $value['data_id']){
						$aList[$key]['data_info'] = $aShop;
					}
				}
				foreach($aGoodsList as $aGoods){
					if($aGoods['id'] == $value['data_id']){
						$aList[$key]['data_info'] = $aGoods;
					}
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

		if(isset($aCondition['user_id'])){
			$aWhere[] = ['user_id' => $aCondition['user_id']];
		}
		if(isset($aCondition['type'])){
			$aWhere[] = ['type' => $aCondition['type']];
		}

		return $aWhere;
	}
}

