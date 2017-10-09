<?php
namespace manage\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;
use common\model\GoodsApprove;
use common\model\CommercialTenantType;

/**
 * 商品模型
 * @author 
 */
class Goods extends \common\model\Goods{
	public static function getWaitApproveList($aControl = []){
		$aWhere = [
			'or',
			['`t1`.`status`' => static::APPROVE_PUT_ON],
			[
				'`t1`.`status`' => static::HAS_PUT_ON,
				'`t2`.`approved_status`' => \common\model\GoodsApprove::APPROVE_WAIT,
			],
		];
		$oQuery = new Query();
		$oQuery->select('`t1`.*')->from(static::tableName() . ' as `t1`')->leftJoin(\common\model\GoodsApprove::tableName() . ' as `t2`', '`t1`.`id`=`t2`.`id`')->where($aWhere);
		if(isset($aControl['page']) && isset($aControl['page_size'])){
			$offset = ($aControl['page'] - 1) * $aControl['page_size'];
			$oQuery->offset($offset)->limit($aControl['page_size']);
		}
		$aList = $oQuery->all();
		if(!$aList){
			return $aList;
		}
		$aGoodsIds = ArrayHelper::getColumn($aList, 'id');
		$aGoodsApproveList = GoodsApprove::findAll(['id' => $aGoodsIds]);
		$aGoodsApproveList = ArrayHelper::index($aGoodsApproveList, 'id');
		foreach($aList as $key => $aGoods){
			$aList[$key]['type_name'] = '';
			if(!isset($aGoodsApproveList[$aGoods['id']])){
				continue;
			}
			$mGoodsApprove = GoodsApprove::toModel($aGoodsApproveList[$aGoods['id']]);
			foreach($aGoods as $field => $value){
				if(isset($mGoodsApprove->content[$field])){
					$aList[$key][$field] = $mGoodsApprove->content[$field];
				}
			}
		}
		
		$aGoodsTypeIds = ArrayHelper::getColumn($aList, 'type_id');
		$aCommercialTenantTypeList = CommercialTenantType::findAll(['id' => $aGoodsTypeIds], ['id', 'name']);
		foreach($aList as $key => $aGoods){
			foreach($aCommercialTenantTypeList as $aCommercialTenantType){
				if($aCommercialTenantType['id'] == $aGoods['type_id']){
					$aList[$key]['type_name'] = $aCommercialTenantType['name'];
				}
			}
		}
		return $aList;
	}
	
	public static function getWaitApproveCount(){
		$aWhere = [
			'or',
			['`t1`.`status`' => static::APPROVE_PUT_ON],
			[
				'`t1`.`status`' => static::HAS_PUT_ON,
				'`t2`.`approved_status`' => \common\model\GoodsApprove::APPROVE_WAIT,
			],
		];
		return (new Query())->from(static::tableName() . ' as `t1`')->leftJoin(\common\model\GoodsApprove::tableName() . ' as `t2`', '`t1`.`id`=`t2`.`id`')->where($aWhere)->count();
	}
}

