<?php
namespace common\model\form\goods;

use Yii;
use yii\helpers\ArrayHelper;
use common\model\Goods;
use common\model\GoodsApprove;
use common\model\CommercialTenantTypeRelation;
use common\model\CommercialTenantType;
use common\model\Resource;

/**
 * 商品表单
 */
class GoodsForm extends \yii\base\Model{
	const SCENE_ADD_GOODS_DATA = 'add_goods_data';
	const SCENE_EDIT_GOODS_DATA = 'edit_goods_data';
	
	public $id;
	public $name;
	public $price;
	public $retailPrice = '';
	public $validityTime;
	public $typeId;
	public $appointmentDay;
	public $suitPeople;
	public $maxClassPeople;
	public $notice;
	public $description;
	public $aPost;
	public $resourceIds;


	public $mTenant;
	private $_mGoods;
	private $_aKeyValue = [//映射关系
		'name' => 'name', 
		'price' => 'price', 
		'retailPrice' => 'retail_price',
		'typeId' => 'type_id', 
		'validityTime' => 'validity_time', 
		'appointmentDay' => 'appointment_day', 
		'suitPeople' => 'suit_people', 
		'maxClassPeople' => 'max_class_people', 
		'notice' => 'notice', 
		'description' => 'description', 
	];
	
	private $_aKeyValueTwo = [//映射关系
		'name' => 'name', 
		'price' => 'price', 
		'retail_price' => 'retailPrice',
		'type_id' => 'typeId', 
		'validity_time' => 'validityTime', 
		'appointment_day' => 'appointmentDay', 
		'suit_people' => 'suitPeople', 
		'max_class_people' => 'maxClassPeople', 
		'notice' => 'notice', 
		'description' => 'description', 
	];

	public function rules(){
		return [
			[['id', 'name', 'price', 'validityTime', 'typeId', 'appointmentDay', 'suitPeople', 'maxClassPeople', 'notice', 'description'], 'required' , 'message'=>'参数不全'],
			//['priceType', 'in', 'range' => [ServiceContent::PRICE_TYPE_DAY, ServiceContent::PRICE_TYPE_MONTH, ServiceContent::PRICE_TYPE_TERM], 'message'=>'价格类型错误'],//日期范围
			//['monthCount', 'default', 'value' => -1],
			['resourceIds', 'required', 'message' => '请传递服务图片'],
			['price', 'integer', 'message' => '价格必须是整数'],
			['appointmentDay', 'integer', 'message' => '提前预约天数必须是数字'],
			['suitPeople', 'integer', 'message' => '适用人数必须是数字'],
			['maxClassPeople', 'integer', 'message' => '每班最大人数必须是数字'],
			['typeId', 'integer', 'message' => 'typeId必须是数字'],
			['validityTime', 'integer', 'message' => '有效期必须是数字'],
			['validityTime', 'compare', 'compareValue' => NOW_TIME, 'operator' => '>', 'message' => '有效期必须大于当前时间'],
			['retailPrice', 'integer', 'message' => '门市价必须是数字'],
			['appointmentDay', 'compare', 'compareValue' => 0, 'operator' => '>', 'message' => '提前预约天数必须大于0'],
			['price', 'compare', 'compareValue' => 0, 'operator' => '>', 'message' => '价格必须大于0'],
			['suitPeople', 'compare', 'compareValue' => 0, 'operator' => '>', 'message' => '适用人数必须大于0'],
			['maxClassPeople', 'compare', 'compareValue' => 0, 'operator' => '>', 'message' => '每班最大人数必须大于0'],
			['name', 'string', 'min' => 2, 'max' => 15, 'tooShort' => '服务名不低于2个字', 'tooLong' => '服务名不能超过15个字'],
			['notice', 'string', 'min' => 2, 'max' => 200, 'tooShort' => '温馨提示不低于2个字', 'tooLong' => '温馨提示不能超过200个字'],
			['mTenant', 'required', 'message' => '商户模型不能为空'],
			//检查typeId合法性
			['typeId', 'checkTypeId'],
			//审核中的服务不能被编辑
			//检查id的合法性
			['id', 'checkGoodsId'],
			//['newPassword', 'compare', 'compareAttribute'=> 'rePassword', 'message'=>'请再输入确认密码'],
			['resourceIds', 'checkResourceIds'],
		];
	}
	
	//通过这个方法返回场景验证设定
    public function scenarios(){
        return [
            //KEY就是场景标识,值就是一个数组,包含了要验证的属性名称
            static::SCENE_ADD_GOODS_DATA => ['name', 'price', 'validityTime', 'typeId', 'appointmentDay', 'suitPeople', 'maxClassPeople', 'notice', 'description', 'retailPrice', 'mTenant', 'resourceIds'],	//新增
            static::SCENE_EDIT_GOODS_DATA => ['mTenant', 'name', 'price', 'validityTime', 'typeId', 'appointmentDay', 'suitPeople', 'maxClassPeople', 'notice', 'description', 'retailPrice', 'id'],	//编辑
        ];
    }
	
	public function checkResourceIds(){
		if(!$this->resourceIds || !is_array($this->resourceIds)){
			$this->addError('resourceIds', '照片资源不正确');
			return false;
		}
		$aResources = Resource::findAll(['id' => $this->resourceIds]);
		$aResources = ArrayHelper::index($aResources, 'id');
		foreach($this->resourceIds as $resourceId){
			if(!isset($aResources[$resourceId]) || $aResources[$resourceId]['type'] != Resource::TYPE_GOODS_PHOTO){
				$this->addError('resourceIds', '资源id为 ' . $resourceId . '不正确');
				return false;
			}
		}
		return true;
	}
		
	//检查
	public function checkGoodsId(){
		$this->_mGoods = Goods::findOne($this->id);
		if(!$this->_mGoods){
			$this->addError('id', '服务不存在');
			return false;
		}
		if($this->_mGoods->tenant_id != $this->mTenant->id){
			$this->addError('id', '服务不存在');
			return false;
		}
		if($this->scenario == static::SCENE_EDIT_GOODS_DATA && !$this->_mGoods->isCanEdit()){
			$this->addError('id', '审核中的服务不能编辑');
			return false;
		}
		return true;
	}
	
	public function checkTypeId(){
		$mCommercialTenantTypeRelation = CommercialTenantTypeRelation::findOne(['tenant_id' => $this->mTenant->id, 'type_id' => $this->typeId]);
		if(!$mCommercialTenantTypeRelation){
			$this->addError('id', '对应的类型不存在');
			return false;
		}
	}
	
	public function addData(){
		$aData = [
			'tenant_id' => $this->mTenant->id,
			'status' => Goods::NO_PUT_ON,
			//'sales_count' => 0,
		];
		$aDataApprove = [
			'name' => $this->name, 
			'price' => $this->price,// * 100, 
			'retail_price' => $this->retailPrice,// * 100,
			'type_id' => $this->typeId, 
			'validity_time' => strtotime(date('Y-m-d', $this->validityTime)) + 86399, 
			'appointment_day' => $this->appointmentDay, 
			'suit_people' => $this->suitPeople, 
			'max_class_people' => $this->maxClassPeople, 
			'notice' => $this->notice, 
			'description' => $this->description, 
			GoodsApprove::PHOTO_KEY_NAME => [],
		];
		$goodsId = Goods::add($aData);
		if($goodsId){
			$aPhoto = [];
			foreach($this->resourceIds as $resourceIds){
				$aPhoto[] = [
					'id' => 0,
					'goods_id' => $goodsId,
					'resource_id' => $resourceIds,
					'is_cover' => 0,
					'create_time' => NOW_TIME,
				];
			}
			$aDataApprove[GoodsApprove::PHOTO_KEY_NAME] = $aPhoto;
			$result = GoodsApprove::add([
				'id' => $goodsId,
				'approved_status' => GoodsApprove::APPROVE_WAIT,
				'content' => json_encode($aDataApprove),
			]);
			if(!$result){
				Yii::error('服务id为:' . $goodsId . ' 的插入审核表失败；数据是：' . json_encode($aDataApprove));
			}
		}else{
			return false;
		}
		return $goodsId;
	}
	
	public function editData(){
		$isUpdata = $isSaveOk = 0;
		$aData = [];
		//$this->aPost['price'] = $this->aPost['price'];// * 100;
		//$this->aPost['retailPrice'] = $this->aPost['retailPrice'];// * 100;
		$this->aPost['validityTime'] = strtotime(date('Y-m-d', $this->aPost['validityTime'])) + 86399;// * 100;
		foreach($this->aPost as $key => $vale){
			$tabelKey = isset($this->_aKeyValue[$key]) ? $this->_aKeyValue[$key] : '';
			if(!$tabelKey){
				continue;
			}
			if($this->_mGoods->$tabelKey != $this->$key){
				//$this->_mGoods->set($tabelKey, $vale);
				$aData[$tabelKey] = $vale;
				$isUpdata = 1;
			}
		}
		$mGoodsApprove = GoodsApprove::findOne($this->id);
		//这是新增的服务未上架时编辑后出现的情况
		foreach($mGoodsApprove->content as $oldKey => $oldVale){
			if($oldKey == GoodsApprove::PHOTO_KEY_NAME){
				$aData[$oldKey] = $oldVale;
				continue;
			}
			$dataKey = $this->_aKeyValueTwo[$oldKey];
			if(isset($this->aPost[$dataKey])){
				if($this->aPost[$dataKey] != $oldVale){
					$aData[$oldKey] = $this->aPost[$dataKey];
				}
			}
		}
		//debug($this->aPost);
		//debug($aData,11);
		if(!$isUpdata){
			//return -1;
		}
//		$aData = [
//			'name' => $this->name, 
//			'price' => $this->price, 
//			'retail_price' => $this->retailPrice,
//			'type_id' => $this->typeId, 
//			'validity_time' => $this->validityTime, 
//			'appointment_day' => $this->appointmentDay, 
//			'suit_people' => $this->suitPeople, 
//			'max_class_people' => $this->maxClassPeople, 
//			'notice' => $this->notice, 
//			'description' => $this->description, 
//		];
		
		$mGoodsApprove->set('content', $aData);
		//if($this->_mGoods->status == Goods::HAS_PUT_ON){
			//$this->_mGoods->updateStatus(Goods::NO_PUT_ON);//上架的服务将变成下架
		//}
		$isSaveOk = false;
		if($mGoodsApprove->save()){
			$mGoodsApprove->updateApprove(GoodsApprove::APPROVE_WAIT);
			$isSaveOk = true;
		}
		return [
			'isUpate' => $isUpdata,
			'isSaveOk' => $isSaveOk,
		];
	}
}

