<?php
namespace manage\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;

class CommercialTenant extends \common\model\CommercialTenant{	
	
	const SCENE_FIRST_APPROVE = 1;
	const SCENE_EDIT_APPROVE = 2;
	
	/**
	 * $aCondition = [
	 *		'online_status'	=> 
	 *		'tenant_approve_status' => 
	 *		'shop_approve_status'	=>	
	 * ]
	 * $aControl = [
	 *		'scene'	=>	SCENE_FIRST_APPROVE/SCENE_EDIT_APPROVE
	 *		'page'	=>	
	 *		'page_size'	=>
	 *		'order_by'	=>
	 *		'with_tenant_type'	=>
	 *		'with_type_characteristic_service'	=> true / false
	 * ]
	 * @param array $aCondition
	 * @param array $aControl
	 * @return array 商户列表
	 */
	public static function getCommercialTenantList($aCondition, $aControl){
		//分析条件：情况1：初审，情况2：上线后修改审核
		$aWhere = static::_parseWhereForTenantList($aCondition);
		$fields = '`t1`.*, `tenant_info`, `shop_info`, `tenant_approve_status`, `shop_approve_status`, `last_edit_time`';
		$aOrderBy = ['last_edit_time' => SORT_ASC];
		if(isset($aControl['order_by'])){
			$aOrderBy = $aControl['order_by'];
		}
		$oQuery = (new Query());
		
		$oQuery->select($fields)->from(static::tableName() . ' as `t1`')->leftJoin(\manage\model\CommercialTenantApprove::tableName() . ' as `t2`', '`t1`.`id`=`t2`.`id`')->where($aWhere)->orderBy($aOrderBy);
		if(isset($aControl['page']) && isset($aControl['page_size']) && $aControl['page_size']){
			$offset = ($aControl['page'] - 1) * $aControl['page_size'];
			$oQuery->offset($offset)->limit($aControl['page_size']);
		}
		$aTenantList = $oQuery->all();
		if(!$aTenantList){
			return $aTenantList;
		}
		foreach($aTenantList as $key => $aTenant){
			$mTenant = CommercialTenant::toModel($aTenant);
			$aTenantList[$key] = $mTenant->toArray();
			$aTenantList[$key]['tenant_info'] = json_decode($aTenant['tenant_info'], true);
			$aTenantList[$key]['shop_info'] = json_decode($aTenant['shop_info'], true);
		}
		return $aTenantList;
	}
	
	public static function getCommercialTenantCount($aCondition){
		$aWhere = static::_parseWhereForTenantList($aCondition);
		return (new Query())->from(static::tableName() . ' as `t1`')->leftJoin(\manage\model\CommercialTenantApprove::tableName() . ' as `t2`', '`t1`.`id`=`t2`.`id`')->where($aWhere)->count();
	}

	private static function _parseWhereForTenantList($aCondition){
		if(isset($aCondition['online_status']) && $aCondition['online_status']){
			$aWhere['online_status'] = $aCondition['online_status'];
		}
		if(isset($aCondition['tenant_approve_status']) && $aCondition['tenant_approve_status']){
			$aWhere['tenant_approve_status'] = $aCondition['tenant_approve_status'];
		}
		if(isset($aCondition['shop_approve_status']) && $aCondition['shop_approve_status']){
			$aWhere['shop_approve_status'] = $aCondition['shop_approve_status'];
		}
		return $aWhere;
	}
	
	public function getMTenantApprove(){
		if($this->_mTenantApprove){
			return $this->_mTenantApprove;
		}
		$mTenantApprove = CommercialTenantApprove::findOne($this->id);
		if(!$mTenantApprove){
			$aData = [
				'id'	=> $this->id,
				'tenant_info'	=> json_encode([]),
				'shop_info'	=> json_encode([]),
				'tenant_approve_status'	=> CommercialTenantApprove::STATUS_OFFLINE,
				'shop_approve_status'	=> CommercialTenantApprove::STATUS_OFFLINE,
				'last_edit_time'	=>	0,
			];
			(new Query())->createCommand()->insert(CommercialTenantApprove::tableName(), $aData)->execute();
			$mTenantApprove = static::toModel($aData);
		}
		return $this->_mTenantApprove = $mTenantApprove;
	}
	
	public function updateTenantInfo(){
		$mTenantApprove = $this->getMTenantApprove();
		$aTenantInfo = $mTenantApprove->tenant_info;
		foreach($aTenantInfo as $field => $value){
			if($field == 'other_info'){
				$aOtherInfo = [];
				foreach($value as $aOther){
					$aOtherInfo[] = $aOther['value'];
				}
				$this->set('other_info', $aOtherInfo);
			}else{
				$this->set($field, $value['value']);
			}
		}
		if($this->save()){
			$mTenantApprove->set('tenant_info', []);
			$mTenantApprove->set('tenant_approve_status', CommercialTenantApprove::STATUS_PASS_APPROVE);
			return $mTenantApprove->save();
		}
		return false;
	}
	
	public function updateShopInfo(){
		$mTenantApprove = $this->getMTenantApprove();
		$aShopInfo = $mTenantApprove->shop_info;
		$count = 0;
		foreach($aShopInfo as $field => $value){
			if($field == 'photo'){
				foreach($value as $aPhoto){
					$aData = [
						'tenant_id' => $this->id,
						'resource_id' => $aPhoto['value'],
						'is_cover' => 0,
						'create_time' => NOW_TIME,
					];
					if(\common\model\CommercialTenantPhoto::add($aData)){
						$count++;
					}else{
						return false;
					}
				}
			}elseif($field == 'characteristic_service_type_relation'){
				foreach ($value as $aService) {
					$mService = \common\model\CommercialTenantType::findOne(['id' => $aService['id']]);
					$serviceTypeId = \yii\helpers\ArrayHelper::getColumn('id', $mService);
					$aData = [
						'tenant_id' => $aService['id'],
						'service_type_id' => $serviceTypeId,
						'create_time' => NOW_TIME,
					];
					if(\common\model\CommercialTenantCharacteristicServiceRelation::add($aData)){
						$count++;
					}else{
						return false;
					}
				}
			}elseif($field == 'commercial_tenant_type'){
				foreach($value as $aType){
					$typeName = $aType['value'];
					if(\common\model\CommercialTenantType::addTenantType($typeName)){
						$count++;
					}else{
						return false;
					}
				}
			}else{
				$this->set($field, $value['value']);
			}
		}
		$rows = $this->save();
		if($count + $rows){
			$mTenantApprove->set('shop_info', []);
			$mTenantApprove->set('shop_approve_status', CommercialTenantApprove::STATUS_PASS_APPROVE);
			return $mTenantApprove->save();
		}
		return false;
	}
	
	public static function getOnlineTenantList($aCondition, $aControl){
		$aWhere = static::_parseWhereForOnlineList($aCondition);
		$aTenantList = static::findAll($aWhere, ['id', 'name', 'mobile', 'create_time', 'online_status'], $aControl['page'], $aControl['page_size'], ['id' => SORT_DESC]);
		if(!$aTenantList){
			return $aTenantList;
		}
		$aTenantIds = ArrayHelper::getColumn($aTenantList, 'id');
		$aShopList = (new Query())->select(['id', 'contact_number', 'address'])->from(static::shopTableName())->where(['id' => $aTenantIds])->all();
		$aAuthList = (new Query())->select(['id', 'leading_official', 'email'])->from(static::authTableName())->where(['id' => $aTenantIds])->all();
		foreach($aTenantList as $key => $aTenant){
			foreach($aShopList as $aShop){
				if($aShop['id'] == $aTenant['id']){
					$aTenantList[$key]['contact_number'] = $aShop['contact_number'];
					$aTenantList[$key]['address'] = $aShop['address'];
					break;
				}
			}
			foreach($aAuthList as $aAuth){
				if($aAuth['id'] == $aTenant['id']){
					$aTenantList[$key]['leading_official'] = $aAuth['leading_official'];
					$aTenantList[$key]['email'] = $aAuth['email'];
					break;
				}
			}
		}
		return $aTenantList;
	}


	public static function getOnlineTenantCount($aCondition){
		$aWhere = static::_parseWhereForOnlineList($aCondition);
		return (new Query())->from(static::tableName())->where($aWhere)->count();
	}
	
	private static function _parseWhereForOnlineList($aCondition){
		$aWhere = ['and'];
		if(isset($aCondition['id']) && $aCondition['id']){
			$aWhere[] = ['id' => [$aCondition['id']]];
		}
		if(isset($aCondition['name']) && $aCondition['name']){
			$aWhere[] = ['like', 'name', $aCondition['name']];
		}
		return $aWhere;
	}
	
	/**
	 * $aCondition = [
	 *		'id' => 
	 *		'status' =>
	 * ]
	 * $aControl = [
	 *		'with_tenant_type'	=> true / false
	 *		'with_type_characteristic_service'	=> true / false
	 * ]
	 * @param array $aCondition
	 * @param array $aControl
	 * @return array 商户详细信息
	 */
	public static function getTenantDetailById($aCondition, $aControl){
		$aWhere = static::_parseWhereForTenantDetail($aCondition);
		$fields = '`t1`.*, `t2`.*, `t3`.*, `t4`.*';
		
		$oQuery = (new Query());
		
		$oQuery->select($fields)->from(static::tableName() . ' as `t1`')->leftJoin(static::shopTableName() . ' as `t2`', '`t1`.`id`=`t2`.`id`')->leftJoin(static::accountTableName() . ' as `t3`', '`t1`.`id`=`t3`.`id`')->leftJoin(static::authTableName() . ' as `t4`', '`t1`.`id`=`t4`.`id`')->where($aWhere);
		
		$aTenantDetail = $oQuery->one();
		if(!$aTenantDetail){
			return $aTenantDetail;
		}
		
		if(isset($aControl['with_tenant_type']) && $aControl['with_tenant_type']){
			$aTenantDetail['tenant_type_name'] = [];
			$aTenantTypeRelation = \common\model\CommercialTenantTypeRelation::findAll(['tenant_id' => $aTenantDetail['id']]);
			if($aTenantTypeRelation){
				foreach($aTenantTypeRelation as $key => $tenantTypeRelation){
					$mTenantType = \common\model\CommercialTenantType::findOne($tenantTypeRelation['type_id']);
					if($mTenantType){
						$aTenantDetail['tenant_type_name'][$key] = $mTenantType->name;
					}
				}
			}
		}
		
		if(isset($aControl['with_type_characteristic_service']) && $aControl['with_type_characteristic_service']){
			$aTenantDetail['characteristic_service_name'] = [];
			$aServiceRelation = \common\model\CommercialTenantCharacteristicServiceRelation::findAll(['tenant_id' => $aTenantDetail['id']]);
			if($aServiceRelation){
				foreach($aServiceRelation as $key => $serviceRelation){
					$mServiceType = \common\model\CharacteristicServiceType::findOne($serviceRelation['service_type_id']);
					if($mServiceType){
						$aTenantDetail['characteristic_service_name'][$key] = $mServiceType->name;
					}
				}
			}
		}
		
		foreach($aTenantDetail as $key => $value){
			if($key == 'city_id' && $value) {
				$aTenantDetail['city_name'] = '';
				$mCity = \common\model\City::findOne($value);
				if($mCity){
					$aTenantDetail['city_name'] = $mCity->name;
				}
			}
			if(in_array($key, ['profile', 'identity_card_front', 'identity_card_back', 'identity_card_in_hand', 'bank_card_photo'])){
				$aTenantDetail[$key . '_path'] = '';
				$mResource = \common\model\Resource::findOne($value);
				if($mResource){
					$aTenantDetail[$key . '_path'] = $mResource->path;
				}
			}
			if($key == 'other_info' && !empty($value)){
				$value = json_decode($value, true);
				foreach($value as $k => $val){
					$aTenantDetail[$key . '_path'][$k] = '';
					$mResource = \common\model\Resource::findOne($val);
					if($mResource){
						$aTenantDetail[$key . '_path'][$k] = $mResource->path;
					}
				}
			}
		}
		
		return $aTenantDetail;
	}
	
	private static function _parseWhereForTenantDetail($aCondition){
		$aWhere = ['and'];
		if(isset($aCondition['id']) && $aCondition['id']){
			$aWhere[] = ['`t1`.`id`' => $aCondition['id']];
		}
		if(isset($aCondition['status']) && $aCondition['status']){
			$aWhere[] = ['status' => $aCondition['status']];
		}
		return $aWhere;
	}
}