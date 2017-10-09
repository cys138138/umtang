<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use common\lib\DbOrmModel;
use yii\helpers\ArrayHelper;

class CommercialTenantPhoto extends DbOrmModel{
	
	public static function tableName(){
        return Yii::$app->db->parseTable('_@commercial_tenant_photo');
    }
	
	public static function add($aData){
		(new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
		return Yii::$app->db->getLastInsertID();
	}
	
	public static function batchInsertData($aInsertList){
		return (new Query())->createCommand()->batchInsert(static::tableName(), ['tenant_id', 'resource_id', 'is_cover', 'create_time'], $aInsertList)->execute();
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
		$aResourceId = ArrayHelper::getColumn($aList, 'resource_id');
		$aResourceList = Resource::findAll(['id' => $aResourceId]);
		foreach($aList as $key => $value){
			$aList[$key]['path'] = '';
			foreach($aResourceList as $aResource){
				if($aResource['id'] == $value['resource_id']){
					$aList[$key]['path'] = $aResource['path'];
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
	
	public function setCover(){
		$sql = 'update ' . static::tableName() . ' set `is_cover`=0 where `tenant_id`=' . $this->tenant_id;
		Yii::$app->db->createCommand($sql)->execute();
		$this->set('is_cover', 1);
		$this->save();
		return true;
	}
	
	/**
	 *	获取列表-----商户端使用
	 *	$aCondition = [
	 *		'id' =>
	 *		'goods_id' =>
	 *	]
	 *	$aControl = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'page' =>
	 *		'page_size' =>
	 *		'with_xxx_info' => true/false
	 *	]
	 */
	public static function getListForTenant($aCondition = [], $aControl = []){
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
		$listCount = count($aList);
		if($listCount < $aControl['page_size']){
			$mTenantApprove = CommercialTenantApprove::findOne($aCondition['tenant_id']);
			if($mTenantApprove && isset($mTenantApprove->shop_info['photo'])){
				if($listCount > 0){
					//说明能拿到记录，但是不够
					$requireCount = $aControl['page_size'] - $listCount;
					for($i = 0; $i < $requireCount; $i++){
						if(!isset($mTenantApprove->shop_info['photo'][$i])){
							break;
						}
						$aList[] = $mTenantApprove->shop_info['photo'][$i];
					}
				}else{
					//什么都没拿到
					$allPhotoCount = static::getCount($aCondition);
					$approveOffset = $offset - $allPhotoCount;
					for($i = 0; $i < $aControl['page_size']; $i++){
						if(!isset($mTenantApprove->shop_info['photo'][$approveOffset])){
							break;
						}
						$aList[] = $mTenantApprove->shop_info['photo'][$approveOffset];
						$approveOffset++;
					}
				}
			}
		}
		if(!$aList){
			return [];
		}
		$aResourceId = ArrayHelper::getColumn($aList, 'resource_id');
		$aResourceList = Resource::findAll(['id' => $aResourceId]);
		foreach($aList as $key => $value){
			$aList[$key]['path'] = '';
			foreach($aResourceList as $aResource){
				if($aResource['id'] == $value['resource_id']){
					$aList[$key]['path'] = $aResource['path'];
				}
			}
		}
		return $aList;
	}
	
	/**
	 *	获取数量---商户端
	 */
	public static function getCountForTenant($aCondition = []){
		$aWhere = static::_parseWhereCondition($aCondition);
		$mTenantApprove = CommercialTenantApprove::findOne($aCondition['tenant_id']);
		$count = (new Query())->from(static::tableName())->where($aWhere)->count();
		if($mTenantApprove && isset($mTenantApprove->shop_info['photo'])){
			$count += count($mTenantApprove->shop_info['photo']);
		}
		return $count;
	}
}