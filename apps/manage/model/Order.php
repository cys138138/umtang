<?php
namespace manage\model;

use Yii;
use yii\helpers\ArrayHelper;
use umeworld\lib\Query;
use common\model\GoodsPhoto;
use common\model\User;

class Order extends \common\model\Order{
	public static function getServiceType(){
		$aTenantType = \common\model\CommercialTenantType::findAll();
		$aTenantType = ArrayHelper::index($aTenantType, 'id');
		return $aTenantType;
	}

	public static $aType = [
		self::DIRECT_PAY => '买单',
		self::ORDER_PAY => '服务'
	];

	public static $aStatuses = [
		self::STATUS_WAIT_PAY => '待支付',
		self::STATUS_PAID => '待使用',
		self::STATUS_APPLY_REFUND => '申请退款',
		self::STATUS_HAS_ACTIVATE => '已激活',
		self::STATUS_REFUNDED => '已退款',
		self::STATUS_WAIT_COMMENT => '待评价',
		self::STATUS_FINISH => '已完成'
	];
	
	private static $_aParseField = ['type', 'status', 'pay_time', 'refund_time', 'create_time', 'goods_info'];

	public static function getList($aCondition = [], $aControl = []){
		$aWhere = static::_parseWhereCondition($aCondition);
		$oQuery = new Query();
		if(isset($aControl['select'])){
			$oQuery->select($aControl['select']);
		}
		$oQuery->select('`t1`.*, `t2`.goods_info')->from(static::tableName() . ' as `t1`')->leftJoin(static::goodsInfoTableName() . ' as `t2`', '`t1`.`id` = `t2`.`id`')->where($aWhere);
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
			$aTenantIds = ArrayHelper::getColumn($aList, 'tenant_id');
			$aCommercialTenants = CommercialTenant::findAll(['id' => $aTenantIds]);
			$aCommercialTenants = ArrayHelper::index($aCommercialTenants, 'id');
			foreach($aList as $key => $value){
				$aList[$key]['tenant_info'] = [];
				if(isset($aCommercialTenants[$value['tenant_id']])){
					$mCommercialTenant = CommercialTenant::toModel($aCommercialTenants[$value['tenant_id']]);
					$aList[$key]['tenant_info'] = $mCommercialTenant->toArray(['id', 'name', 'profile_path', 'mobile', 'pay_discount']);
				}
			}
		}
		
		$aList = static::_parseField($aList);
		
		return $aList;
	}
	
	private static function _parseField($aList = []){
		foreach($aList as $key => $aOrder){
			foreach(static::$_aParseField as $field){
				if(in_array($field, ['type', 'status'])){
					$aFieldId = ArrayHelper::getColumn($aList, $field);
					$aFieldId = array_unique($aFieldId);

					$aList[$key][$field . '_name'] = '';
					if($field == 'type'){
						$aList[$key][$field . '_name'] = static::$aType[$aList[$key][$field]];
					}else{
						$aList[$key][$field . '_name'] = static::$aStatuses[$aList[$key][$field]];
					}
				}
				if($field == 'goods_info'){
					$aList[$key]['goods_info'] = json_decode($aOrder['goods_info'], true);
					$aList[$key]['goods_info']['type_name'] = '';
					if(isset($aList[$key]['goods_info']['type_id'])){
						$mTenantType = \common\model\CommercialTenantType::findOne($aList[$key]['goods_info']['type_id']);
						if($mTenantType){
							$aList[$key]['goods_info']['type_name'] = $mTenantType->name;
						}
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
		return (new Query())->from(static::tableName() . ' as `t1`')->leftJoin(static::goodsInfoTableName() . ' as `t2`', '`t1`.`id`=`t2`.`id`')->where($aWhere)->count();
	}
	
	private static function _parseWhereCondition($aCondition = []){
		$aWhere = '';
		if(isset($aCondition['type'])){
			$aWhere .= ' and type = ' . $aCondition['type'];
		}
		if(isset($aCondition['status']) && $aCondition['status']){
			if(is_array($aCondition['status'])){
				$aWhere .= ' and status in (' . implode(',', $aCondition['status']) . ')';
			}else{
				$aWhere .= ' and status = ' . $aCondition['status'];
			}
		}
		if(isset($aCondition['service']) && $aCondition['service']){
			$aWhere .= ' and (find_in_set(\'"type_id":"' .$aCondition['service']. '"\', t2.goods_info) or find_in_set(\'"type_id":' . $aCondition['service'] . '\', t2.goods_info))';
		}
		
		if($aWhere){
			$aWhere = substr($aWhere, 5);
		}

		return $aWhere;
	}
}	