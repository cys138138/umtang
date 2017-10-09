<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;

/**
 * 商品模型
 * @author 谭威力
 */
class Goods extends \common\lib\DbOrmModel{
	const NO_PUT_ON = 1;			//未上架
	const APPROVE_PUT_ON = 2;		//上架审核中
	const HAS_PUT_ON = 3;			//已上架
	const LAY_DOWN = 4;				//已下架
	//需要编译的字段
	protected $_aEncodeFields = ['description'];
	//要保存的字段
	protected $_aExtendFields = ['appointment_day', 'suit_people', 'max_class_people', 'notice', 'description'];
	private $_mGoodsApprove;

	//const APPROVE_WAIT = 1;			//未审核
	//const APPROVE_PASS = 2;			//审核通过
	//const APPROVE_DEFEATED = 3;		//审核不通过

	/**
     * @inheritdoc
     */
    public static function tableName(){
        return Yii::$app->db->parseTable('_@goods_index');
    }

	public static function dataTableName(){
        return Yii::$app->db->parseTable('_@goods');
    }

	public function fields(){
		$aMyFields = array_keys(Yii::getObjectVars($this));
		return array_merge($aMyFields, $this->_aExtendFields);
	}

	public function __get($name) {
		if(in_array($name, $this->_aExtendFields)){
			//如果是复合字段
			$aGoods = (new Query())->from(self::dataTableName())->where(['id' => $this->id])->one();
			if(!$aGoods){
				//补充从表goods的数据
				(new Query)->createCommand()->insert(static::dataTableName(), ['id' => $this->id])->execute();
				$aGoods = [
					'id' => $this->id,
					'appointment_day' => 0,
					'suit_people' => 0, 
					'max_class _people' => 0, 
					'notice' => '',
					'description' => '',
				];
			}
			foreach($aGoods as $field => $value){
				if(!isset($this->$field)){
					$this->$field = $value;
				}
			}
		}
		if($name == 'profile_path'){//封面
			$aGoodsPhotoResourceId = (new Query())->select(['resource_id'])->from(GoodsPhoto::tableName())->where(['goods_id' => $this->id])->groupBy(['goods_id'])->orderBy(['is_cover' => SORT_DESC, 'create_time' => SORT_ASC])->one();
			$this->$name = '';
			if($aGoodsPhotoResourceId){
				$mResource = Resource::findOne($aGoodsPhotoResourceId['resource_id']);
				if($mResource){
					$this->$name = $mResource->getUrl();
				}
			}
		}
		if(!in_array($name, array_keys(Yii::getObjectVars($this)))){
			return parent::__get($name);
		}else{
			return $this->$name;
		}
	}
	
	public static function findAll($xWhere = null, $aFields = null, $page = 0, $pageSize = 0, $aSortList = []){
		if(CommercialTenant::getExceptTenantId()){
			$aWhere = ['and'];
			if($xWhere){
				$aWhere[] = $xWhere;
			}
			$aWhere[] = ['not in', 'tenant_id', CommercialTenant::getExceptTenantId()];
			$xWhere = $aWhere;
		}
		return parent::findAll($xWhere, $aFields, $page, $pageSize, $aSortList);
	}

	/**
	 * 更新
	 * @return int
	 */
	public function save(){
		$aUpdateIndex = $this->_aSetFields;
		if(!$aUpdateIndex){
			return 0;
		}
		$aUpdateData = [];
		foreach($aUpdateIndex as $field => $xValue){
			if(in_array($field, $this->_aEncodeFields)){
				$aUpdateIndex[$field] = json_encode($aUpdateIndex[$field]);
			}
			if(in_array($field, $this->_aExtendFields)){
				$aUpdateData[$field] = $aUpdateIndex[$field];
				unset($aUpdateIndex[$field]);
			}
		}
		$resultIndex = $resultData = 0;
		if($aUpdateIndex){
			$resultIndex = (new Query())->createCommand()->update(self::tableName(), $aUpdateIndex, ['id' => $this->id])->execute();
		}
		if($aUpdateData){
			$resultData = (new Query())->createCommand()->update(self::dataTableName(), $aUpdateData, ['id' => $this->id])->execute();
		}
		$this->_aSetFields = [];
		return $resultIndex + $resultData;
	}

	public static function add($aData){
		$aGoods = [];
		$aData['create_time'] = NOW_TIME;
		foreach($aData as $key => $value){
			if(in_array($key, ['appointment_day', 'suit_people', 'max_class _people', 'notice', 'description'])){
				$aGoods[$key] = $value;
				unset($aData[$key]);
			}
		}
		(new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
		$id = Yii::$app->db->getLastInsertID();
		if(!$id){
			return $id;
		}
		$aGoods['id'] = $id;
		(new Query)->createCommand()->insert(static::dataTableName(), $aGoods)->execute();
		return $id;
	}
	
	/*
	 * 判断是否可以编辑
	 */
	public function isCanEdit(){
		if($this->status == static::APPROVE_PUT_ON){
			return false;
		}
		return true;
	}
	
	/*
	 * 更改状态
	 */
	public function updateStatus($status){
		$this->set('status', $status);
		return $this->save();
	}
	
	/*
	 * 商品相关图片操作
	 * $aData = [
	 *		action => 1 添加 2 设置为封面 3删除
	 *		resource_id => 资源id
	 * ]
	 */
	public function goodsPhotoAction($aData){
		$mGoodsApprove = $this->getMGoodsApprove();
		$aApproveContent = $mGoodsApprove->content;
		if(!isset($aApproveContent[GoodsApprove::PHOTO_KEY_NAME])){
			$aApproveContent[GoodsApprove::PHOTO_KEY_NAME] = [];
		}
		if($aData['action'] == 1){
			$aApproveContent[GoodsApprove::PHOTO_KEY_NAME][] = [
				'id' => 0,
				'goods_id' => $this->id,
				'resource_id' => $aData['resource_id'],
				'is_cover' => 0,
				'create_time' => NOW_TIME,
			];
			$mGoodsApprove->set('approved_status', GoodsApprove::APPROVE_WAIT);
			$mGoodsApprove->set('content', $aApproveContent);
			return $mGoodsApprove->save();
		}
		if($aData['action'] == 2){
			$isApprove = 0;
			$mGoodsApprove->set('approved_status', GoodsApprove::APPROVE_WAIT);
			foreach($aApproveContent[GoodsApprove::PHOTO_KEY_NAME] as $key => $aPhoto){
				if($aPhoto['resource_id'] != $aData['resource_id']){
					if($aPhoto['id'] == 0){
						return false;//新增的图片是不能设置为封面的
					}
					if($aPhoto['is_cover'] == 1){
						$aApproveContent[GoodsApprove::PHOTO_KEY_NAME][$key]['is_cover'] = 0;//找到原先的封面图片 改成 非封面
					}
					continue;
				}
				$aApproveContent[GoodsApprove::PHOTO_KEY_NAME][$key]['is_cover'] = 1;
				$isApprove = 1;
			}
			if($isApprove == 1){
				$mGoodsApprove->set('content', $aApproveContent);
				return $mGoodsApprove->save();
			}
			$aGoodsPhotos = GoodsPhoto::findAll(['goods_id' => $this->id]);
			$aId = ArrayHelper::getColumn($aGoodsPhotos, 'resource_id');
			if(!in_array($aData['resource_id'], $aId)){
				return false;
			}
			foreach($aGoodsPhotos as $k => $aGoodsPhoto){
				$mGoodsPhoto = GoodsPhoto::toModel($aGoodsPhoto);
				if($aGoodsPhoto['resource_id'] != $aData['resource_id']){
					if($aGoodsPhoto['is_cover'] == 1){
						//找到原先的封面图片 改成 非封面
						$mGoodsPhoto->set('is_cover', 0);
					}
					continue;
				}
				$mGoodsPhoto->set('is_cover', 1);//找到资源图片 改成 封面
			}
			return $mGoodsPhoto->save();
		}
		if($aData['action'] == 3){//删除
			$isApprove = 0;
			$aPhotoList = [];
			foreach($aApproveContent[GoodsApprove::PHOTO_KEY_NAME] as $key => $aPhoto){
				if($aPhoto['resource_id'] != $aData['resource_id']){
					$aPhotoList[] = $aPhoto;
					continue;
				}
				unset($aApproveContent[GoodsApprove::PHOTO_KEY_NAME][$key]);
				$isApprove = 1;
			}
			$aApproveContent[GoodsApprove::PHOTO_KEY_NAME] = $aPhotoList;//重新排序，防止key断层，因为photo获取列表时用到
			$result = 0;
			if($isApprove == 1){
				$mGoodsApprove->set('content', $aApproveContent);
				$result = $mGoodsApprove->save();
			}else{
				$aGoodsPhotos = GoodsPhoto::findAll(['goods_id' => $this->id]);
				foreach($aGoodsPhotos as $k => $aGoodsPhoto){
					if($aGoodsPhoto['resource_id'] != $aData['resource_id']){
						continue;
					}
					$mGoodsApprove = GoodsPhoto::toModel($aGoodsPhoto);
					$result = $mGoodsApprove->delete();
				}
			}
			return $result;
		}
	}

	/**
	 * 删除
	 */
	public function deleteGoods(){
		if(!$this->delete()){
			return false;
		}
		$mGoodsApprove = $this->getMGoodsApprove();
		$mGoodsApprove->delete();
		(new Query())->createCommand()->delete(GoodsPhoto::tableName(), ['goods_id' => $this->id])->execute();
		(new Query())->createCommand()->delete(UserCollect::tableName(), ['and', ['data_id' => $this->id], ['type' => UserCollect::TYPE_GOODS]])->execute();
		(new Query())->createCommand()->delete(static::dataTableName(), ['id' => $this->id])->execute();
		return true;
	}
	
	/**
	 *	获取列表-----------小程序端
	 *	$aCondition = [
	 *		'id' =>
	 *		'tenant_id' => 
	 *		'status' => 
	 *	]
	 *	$aControl = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'page' =>
	 *		'page_size' =>
	 *		'with_photo_info' => true/false
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
		$aGoodsTypeIds = ArrayHelper::getColumn($aList, 'type_id');
		$aCommercialTenantTypeList = CommercialTenantType::findAll(['id' => $aGoodsTypeIds], ['id', 'name']);
		if(isset($aControl['with_photo_info']) && isset($aControl['with_photo_info'])){
			$aGoodsId = ArrayHelper::getColumn($aList, 'id');
			//$aGoodsPhotos = GoodsPhoto::findAll(['goods_id' => $aGoodsId]);
			$aGoodsPhotos = (new Query())->from(GoodsPhoto::tableName())->where(['goods_id' => $aGoodsId])->groupBy(['goods_id'])->orderBy(['is_cover' => SORT_DESC, 'create_time' => SORT_ASC])->all();
			$aResoureIds = ArrayHelper::getColumn($aGoodsPhotos, 'resource_id');
			$aResources = Resource::findAll(['id' => $aResoureIds]);
			foreach($aGoodsPhotos as $key => $value){
				$aList[$key]['resource_path'] = '';
				foreach($aResources as $aResource){
					if($aResource['id'] == $value['resource_id']){
						$mResource = Resource::toModel($aResource);
						$value['resource_path'] = $mResource->getUrl();
						$aGoodsPhotos[$key] = $value;
					}
				}
			}
			foreach($aList as $key => $aValue){
				$aList[$key]['photo'] = '';
				foreach($aGoodsPhotos as $aGoodsPhoto){
					if($aGoodsPhoto['goods_id'] == $aValue['id']){
						$aList[$key]['photo'] = $aGoodsPhoto['resource_path'];
					}
				}
			}
		}
		foreach($aList as $key => $aValue){
			$aList[$key]['type_name'] = '';
			foreach($aCommercialTenantTypeList as $aCommercialTenantType){
				if($aCommercialTenantType['id'] == $aValue['type_id']){
					$aList[$key]['type_name'] = $aCommercialTenantType['name'];
				}
			}
		}
		return $aList;
	}
	
	/**
	 *	获取列表-----------商户端
	 *	$aCondition = [
	 *		'id' =>
	 *		'tenant_id' => 
	 *		'status' => 
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
		if(!$aList){
			return $aList;
		}
		$aGoodsIds = ArrayHelper::getColumn($aList, 'id');
		$aGoodsApproveList = GoodsApprove::findAll(['id' => $aGoodsIds]);
		$aGoodsApproveList = ArrayHelper::index($aGoodsApproveList, 'id');
		foreach($aList as $key => $aGoods){
			$aList[$key]['type_name'] = '';
			$aList[$key]['is_pending'] = $aGoods['status'] == static::APPROVE_PUT_ON ? 1 : 0;
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
	
	/*
	 * 获取商品信息 + 审核
	 */
	public function getGoodsInfoWithApprove($select = []){
		$aGoodsInfo = $this->toArray($select);
		$aApproveContent = $this->getMGoodsApprove()->content;
		foreach($aGoodsInfo as $field => $value){
			if(isset($aApproveContent[$field])){
				$aGoodsInfo[$field] = $aApproveContent[$field];
			}
		}
		if(isset($aApproveContent['photo_list']) && $aApproveContent['photo_list']){
			$aGoodsInfo['photo_list'] = $aApproveContent['photo_list'];
			$aResourceIds = ArrayHelper::getColumn($aGoodsInfo['photo_list'], 'resource_id');
			$aResourceList = Resource::findAll(['id' => $aResourceIds]);
			foreach($aGoodsInfo['photo_list'] as $key => $aPhoto){
				$aGoodsInfo['photo_list'][$key]['path'] = '';
				foreach($aResourceList as $aResource){
					if($aResource['id'] == $aPhoto['resource_id']){
						$aGoodsInfo['photo_list'][$key]['path'] = $aResource['path'];
						break;
					}
				}
			}
		}
		return $aGoodsInfo;
	}
	
	public function getMGoodsApprove(){
		if($this->_mGoodsApprove){
			return $this->_mGoodsApprove;
		}
		$mGoodsApprove = GoodsApprove::findOne($this->id);
		if(!$mGoodsApprove){
			$aGoodsApprove = [
				'id' => $this->id,
				'approved_status' => GoodsApprove::APPROVE_WAIT,
				'content' => json_encode([]),
			];
			 GoodsApprove::add($aGoodsApprove);
			$this->_mGoodsApprove = $mGoodsApprove = GoodsApprove::toModel($aGoodsApprove);
		}
		return $mGoodsApprove;
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
		if(isset($aCondition['status'])){
			$aWhere[] = ['status' => $aCondition['status']];
		}
		if(isset($aCondition['no_validity']) && $aCondition['no_validity']){//不过期的
			$aWhere[] = ['>' ,'validity_time', NOW_TIME];
		}

		return $aWhere;
	}
	
	/**
	 * 根据商家id，获取每个商家第一个商品id
	 */
	public static function getGoodsIdListByTenantIds($aTenantId){
		if(!$aTenantId){
			return [];
		}
		$aList = (new Query())->select('id')->from(static::tableName())->where(['tenant_id' => $aTenantId, 'status' => static::HAS_PUT_ON])->groupBy('tenant_id')->all();
		if(!$aList){
			return [];
		}
		return ArrayHelper::getColumn($aList, 'id');
	}
	
	public static function goodsListWithTenantInfo($aGoodsList, $lng, $lat){
		if(!$aGoodsList){
			return [];
		}
		$aTenantId = ArrayHelper::getColumn($aGoodsList, 'tenant_id');
		$aTenantList = CommercialTenant::getTenantListWithDistance(['id' => $aTenantId, 'lng' => $lng, 'lat' => $lat], ['page' => 1, 'page_size' => count($aGoodsList)]);
		foreach($aGoodsList as $key => $value){
			$aGoodsList[$key]['tenant_info'] = [];
			foreach($aTenantList as $aTenant){
				if($aTenant['id'] == $value['tenant_id']){
					$aGoodsList[$key]['tenant_info'] = $aTenant;
				}
			}
		}
		return $aGoodsList;
	}
	
	public static function goodsListWithPhotoPath($aGoodsList){
		if(!$aGoodsList){
			return [];
		}
		$aGoodsId = ArrayHelper::getColumn($aGoodsList, 'id');
		$sql = 'SELECT * FROM(SELECT `t1`.`goods_id`,`t2`.path FROM ' . GoodsPhoto::tableName() . ' AS `t1` LEFT JOIN ' . Resource::tableName() . ' AS `t2` ON `t1`.resource_id=`t2`.id WHERE `t1`.`goods_id` IN(' . implode(',', $aGoodsId) . ') ORDER BY `t1`.`is_cover` DESC) AS `t3` GROUP BY `goods_id`';
		$aGoodsPhotoList = Yii::$app->db->createCommand($sql)->queryAll();
		foreach($aGoodsList as $key => $value){
			$aGoodsList[$key]['profile_path'] = '';
			foreach($aGoodsPhotoList as $aGoodsPhoto){
				if($aGoodsPhoto['goods_id'] == $value['id']){
					$aGoodsList[$key]['profile_path'] = $aGoodsPhoto['path'];
				}
			}
		}
		return $aGoodsList;
	}
	
}

