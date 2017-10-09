<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use common\lib\DbOrmModel;
use yii\helpers\ArrayHelper;

class Teacher extends DbOrmModel{
	
	public static function tableName(){
        return Yii::$app->db->parseTable('_@teacher');
    }
	
	public function fields(){
		return array_merge(parent::fields(), ['profile_path']);
	}
	
	public static function batchInsertData($aInsertList){
		return (new Query())->createCommand()->batchInsert(static::tableName(), ['tenant_id', 'profile', 'name', 'duty', 'seniority', 'description', 'order', 'create_time'], $aInsertList)->execute();
	}
	
	public static function getMaxOrder($mCommercialTenant){
		$maxOrder =  (new Query())->from(static::tableName())->where(['tenant_id' => $mCommercialTenant->id])->max('`order`');
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aShopInfo = $mTenantApprove->shop_info;
		if(!isset($aShopInfo['teacher'])){
			return $maxOrder;
		}
		foreach($aShopInfo['teacher'] as $aWaitApproveTeacher){
			if(isset($aWaitApproveTeacher['order']) && $aWaitApproveTeacher['order'] > $maxOrder){
				$maxOrder = $aWaitApproveTeacher['order'];
			}
		}
		return $maxOrder;
	}
	
	public function __get($name){
		if($name == 'profile_path'){
			$this->$name = '';
			if($this->profile){
				$mResource = Resource::findOne($this->profile);
				if($mResource){
					$this->$name = $mResource->path;
				}
			}
		}
		return $this->$name;
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
		$aResourceId = ArrayHelper::getColumn($aList, 'profile');
		$aResourceList = Resource::findAll(['id' => $aResourceId]);
		foreach($aList as $key => $value){
			$aList[$key]['profile_path'] = '';
			foreach($aResourceList as $aResource){
				if($aResource['id'] == $value['profile']){
					$aList[$key]['profile_path'] = $aResource['path'];
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

		return $aWhere;
	}
	
	public static function getTeacherListForTenant($aCondition, $aControl){
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
		$mTenantApprove = CommercialTenantApprove::findOne($aCondition['tenant_id']);
		if($mTenantApprove && isset($mTenantApprove->shop_info['teacher'])){
			$aWaitApproveTeacherList = $mTenantApprove->shop_info['teacher'];
			foreach($aWaitApproveTeacherList as $waitKey => $aWaitApproveTeacher){	
				foreach($aList as $key => $aTeacher){
					if($aTeacher['id'] == $aWaitApproveTeacher['id']){
						$aList[$key] = array_merge($aTeacher, $aWaitApproveTeacher);
						unset($aWaitApproveTeacherList[$waitKey]);
						break;
					}
				}
			}
			$aList = array_merge($aList, $aWaitApproveTeacherList);
		}
		if(!$aList){
			return $aList;
		}
		$aResourceId = ArrayHelper::getColumn($aList, 'profile');
		$aResourceList = Resource::findAll(['id' => $aResourceId]);
		foreach($aList as $key => $value){
			$aList[$key]['profile_path'] = '';
			foreach($aResourceList as $aResource){
				if($aResource['id'] == $value['profile']){
					$aList[$key]['profile_path'] = $aResource['path'];
				}
			}
		}
		if(isset($aControl['order_by']['order'])){
			ArrayHelper::multisort($aList, 'order');
		}
		return $aList;
	}

	public static function getTeacherInfoForTenant($mCommercialTenant, $teacherId, $createTime = 0){
		$aTeacher = [];
		if($teacherId){
			$mTeacher = static::findOne($teacherId);
			if(!$mTeacher || $mTeacher->tenant_id != $mCommercialTenant->id){
				return $aTeacher;
			}
			$aTeacher = $mTeacher->toArray();
		}
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aShopInfo = $mTenantApprove->getShopInfoWithPath();
		if(isset($aShopInfo['teacher']) && $aShopInfo['teacher']){
			if($aTeacher){
				foreach($aShopInfo['teacher'] as $aWaitApproveTeacher){
					if($aWaitApproveTeacher['id'] == $aTeacher['id']){
						$aTeacher = array_merge($aTeacher, $aWaitApproveTeacher);
						break;
					}
				}
			}else{
				foreach($aShopInfo['teacher'] as $aWaitApproveTeacher){
					if($aWaitApproveTeacher['create_time'] == $createTime){
						$aTeacher = $aWaitApproveTeacher;
						break;
					}
				}
			}
		}
		return $aTeacher;
	}
	
}