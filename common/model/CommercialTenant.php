<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;
use common\lib\DbOrmModel;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;

class CommercialTenant extends DbOrmModel implements IdentityInterface{
	
	protected $_mTenantLimit;
	protected $_mTenantApprove;

	protected $_aEncodeFields = ['other_info'];	//需要编码的字段
	
	const ONLINE_STATUS_PERFECT_INFOR = 1;	//完善资料中
	const ONLINE_STATUS_IN_APPROVE = 2;		//上线审核中
	const ONLINE_STATUS_ONLINE = 3;			//已上线
	const ONLINE_STATUS_OFFLINE = 4;		//已下架
	
	const DATA_PERFECT_TENANT_INFO = 1;	//商户信息完善中
	const DATA_PERFECT_SHOP_INFO = 3;	//商铺信息完善中
	const DATA_PERFECT_FINISH = 4;		//信息完善了
	
	const BANK_ACCOUNT_TYPE_PERSONAL = 1;	//个人
	const BANK_ACCOUNT_TYPE_COMMUNAL = 2;	//对公
	
	const ONLY_APPLY_MONEY_ONE_DAY = 5000000; //每天能提现的金额
	
	private $_authKey = 'umtang20170506fdsafeee@###!';	//身份验证密钥
	
	public static function tableName(){
        return Yii::$app->db->parseTable('_@commercial_tenant_index');
    }
	
	public static function shopTableName(){
		 return Yii::$app->db->parseTable('_@commercial_tenant_shop');
	}
	
	public static function accountTableName(){
		 return Yii::$app->db->parseTable('_@commercial_tenant_account');
	}
	
	public static function authTableName(){
		 return Yii::$app->db->parseTable('_@commercial_tenant_auth');
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function allow($permissionName){
		return true;
	}
	
	public function getAuthKey(){
        return $this->_authKey;
    }
	
	public function validateAuthKey($authKey){
        return $this->getAuthKey() === $authKey;
    }
	
	public static function findIdentityByAccessToken($token, $type = null){
        throw new NotSupportedException('根据令牌找用户 的方法未实现');
    }
	
	public static function findIdentity($id){
        return static::findOne($id);
    }
	
	/**
	 * 加密密码
	 * @param type $password
	 * @return string
	 */
	public static function encryptPassword($password){
		return md5($password);
	}

	public static function initCommercialTenant($aData){
		if(!isset($aData['mobile']) || !isset($aData['password'])){
			throw Yii::$app->buildError('注册缺少必要的参数', false, $aData);
		}
		//初始化用户信息
		$aCommercialTenantIndex = [
			'name'			=>	'',
			'mobile'		=>	$aData['mobile'],
			'password'		=>	static::encryptPassword($aData['password']),
			'city_id'		=>	0,
			'lng'			=>	0,
			'lat'			=>	0,
			'pay_discount'	=>	0,
			'online_status'	=> static::ONLINE_STATUS_PERFECT_INFOR,
			'data_perfect'	=> static::DATA_PERFECT_TENANT_INFO,
			'avg_score'		=> 0,
			'all_sales_count'	=> 0,
			'all_score'		=> 0,
			'create_time'	=> NOW_TIME,
		];
		$rows = (new Query())->createCommand()->insert(static::tableName(), $aCommercialTenantIndex)->execute();
		if(!$rows){
			throw Yii::$app->buildError('插入商铺注册数据失败', false, $aCommercialTenantIndex);
		}
		$tenantId = Yii::$app->db->getLastInsertID();
		$aCommercialTenantIndex['id'] = $tenantId;
		$mTenant = static::toModel($aCommercialTenantIndex);
		$mTenant->_initCommercialTenantShop();
		$mTenant->_initCommercialTenantAccount();
		$mTenant->_initCommercialTenantAuth();
		$mTenant->_initCommercialTenantType();
		$mTenant->_initCommercialTenantApprove();
		return $tenantId;
	}
	
	/**
	 * 获取商户列表
	 * $aConditon = [	//筛选条件
	 *		'name'	=>		//商铺名字
	 *		'city_id'	=>		//城市id
	 *		''
	 * ]
	 * 
	 * $aControl = [
	 *		'order_by'	=> 
	 *		'page'	=>
	 *		'page_size'	=>
	 * ]
	 * 
	 * @param array $aConditon
	 * @param array $aControl
	 * @return array 商户列表
	 */
	public static function getCommercialTenantList($aConditon, $aControl){
		$aTenantList = [];
		//先拿出index表的内容
		
		return $aTenantList;
	}
	
	private static function _parseWhereForTenantList($aConditon){
		
	}
	
	public function fields(){
		return array_merge(parent::fields(), static::_getShopTableField(), static::_getAccountTableField(), static::_getAuthTableField(), ['profile_path', 'identity_card_front_path', 'identity_card_back_path', 'identity_card_in_hand_path', 'bank_card_photo_path', 'other_info_path']);
	}
	
	private static function _getIndexTableField(){
		return ['name', 'mobile', 'password', 'city_id', 'lng', 'lat', 'pay_discount', 'online_status', 'data_perfect', 'avg_score', 'all_sales_count', 'all_score', 'create_time', 'all_comment_count'];
	}
	
	private static function _getShopTableField(){
		return ['profile', 'contact_number', 'address', 'street', 'description', 'preferential_info'];
	}

	private static function _getAccountTableField(){
		return ['money', 'bank_name', 'bank_accout_type', 'bank_accout', 'bank_account_holder'];
	}
	
	private static function _getAuthTableField(){
		return ['leading_official', 'identity_card', 'email', 'identity_card_front', 'identity_card_back', 'identity_card_in_hand', 'bank_card_photo', 'other_info'];
	}


	public function __get($name){
		if(!isset($this->$name)){
			if(in_array($name, static::_getShopTableField())){
				$this->_loadTenantShop();
			}elseif(in_array($name, static::_getAccountTableField())){
				$this->_loadTenantAccount();
			}elseif(in_array($name, static::_getAuthTableField())){
				$this->_loadTenantAuth();
			}
		}
		if(in_array($name, ['profile_path', 'identity_card_front_path', 'identity_card_back_path', 'identity_card_in_hand_path', 'bank_card_photo_path'])){
			$this->$name = '';
			$resourceIdFiled = str_replace('_path', '', $name);
			if($this->$resourceIdFiled){
				$mResource = Resource::findOne($this->$resourceIdFiled);
				if($mResource){
					$this->$name = $mResource->path;
				}
			}
		}elseif($name == 'other_info_path'){
			$this->$name = [];
			$resourceIdFileds = str_replace('_path', '', $name);
			if($this->$resourceIdFileds){
				$aOtherInfoPathList = [];
				foreach($this->$resourceIdFileds as $resourceId){
					$aOtherInfoPathList[$resourceId] = '';
				}
				$aResourceList = Resource::findAll(['id' => $this->$resourceIdFileds]);
				if($aResourceList){
					foreach($aResourceList as $aResource){
						$aOtherInfoPathList[$aResource['id']] = $aResource['path'];
					}
				}
				$this->$name = $aOtherInfoPathList;
			}
		}
		return $this->$name;
	}
	
	public function getMTenantLimit(){
		if($this->_mTenantLimit){
			return $this->_mTenantLimit;
		}
		$mTenantLimit = CommercialTenantLimit::findOne($this->id);
		if(!$mTenantLimit){
			$aData = [
				'id' => $this->id,
				'note' => json_encode([]),
				'last_modify_time' => NOW_TIME,
			];
			(new Query())->createCommand()->insert(CommercialTenantLimit::tableName(), $aData)->execute();
			$mTenantLimit = CommercialTenantLimit::toModel($aData);
		}
		if(date('Ym', $mTenantLimit->last_modify_time) != date('Ym', NOW_TIME)){
			$mTenantLimit->set('note', []);
			$mTenantLimit->set('last_modify_time', NOW_TIME);
			$mTenantLimit->save();
		}
		return $this->_mTenantLimit = $mTenantLimit;
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
				'tenant_approve_status'	=> CommercialTenantApprove::STATUS_WAIT_APPROVE,
				'shop_approve_status'	=> CommercialTenantApprove::STATUS_WAIT_APPROVE,
				'last_edit_time'	=>	0,
			];
			(new Query())->createCommand()->insert(CommercialTenantApprove::tableName(), $aData)->execute();
			$mTenantApprove = static::toModel($aData);
		}
		return $this->_mTenantApprove = $mTenantApprove;
	}
	
	public function save(){
		$aTenantRecord = $this->_aSetFields;
		if(!$aTenantRecord){
			return 0;
		}
		foreach($aTenantRecord as $field => $value){
			if(in_array($field, $this->_aEncodeFields)){
				$aTenantRecord[$field] = json_encode($aTenantRecord[$field]);
			}elseif(array_key_exists($field, $this->_aEncodeFields)){
				$xType = $this->_aEncodeFields[$field];
				if($xType == 'json'){
					$aTenantRecord[$field] = json_encode($aTenantRecord[$field]);
				}elseif($xType == ','){
					$aTenantRecord[$field] = implode(',', $aTenantRecord[$field]);
				}
			}
		}
		$aTableFields = [
			static::tableName() => static::_getIndexTableField(),	//index表
			static::shopTableName() => static::_getShopTableField(),	//shop表
			static::accountTableName() => static::_getAccountTableField(),	//account表
			static::authTableName() => static::_getAuthTableField(),	//auth表
		];
		$aTableDataList = [];
		foreach($aTenantRecord as $field => $value){
			foreach($aTableFields as $tableName => $aFields){
				if(in_array($field, $aFields)){
					$aTableDataList[$tableName][$field] = $value;
				}
			}
		}
		if(!$aTableDataList){
			return 0;
		}
		$result = 0;
		foreach($aTableDataList as $tableName => $aData){
			$result += (new Query())->createCommand()->update($tableName, $aData, ['id' => $this->id])->execute();
		}
		$this->_aSetFields = [];
		return $result;
	}
	
	private function _initCommercialTenantShop(){
		$aData = [
			'id'				=> $this->id,
			'contact_number'	=> '',
			'address'			=> '',
			'street'			=> '',
			'description'		=> '',
			'preferential_info' => '',
		];
		return (new Query())->createCommand()->insert(static::shopTableName(), $aData)->execute();
	}
	
	private function _initCommercialTenantAccount(){
		$aData = [
			'id'				=> $this->id,
			'money'				=> 0,
			'bank_name'			=> '',
			'bank_accout_type'	=> 0,
			'bank_accout'		=> '',
			'bank_account_holder'	=> '',
		];
		return (new Query())->createCommand()->insert(static::accountTableName(), $aData)->execute();
	}
	
	private function _initCommercialTenantAuth(){
		$aData = [
			'id'				=> $this->id,
			'leading_official'	=> '',
			'identity_card'		=> '',
			'email'				=> '',
			'identity_card_front'	=> 0,
			'identity_card_back'	=> 0,
			'identity_card_in_hand'	=> 0,
			'bank_card_photo'		=> 0,
			'other_info'			=> json_encode([]),
		];
		return (new Query())->createCommand()->insert(static::authTableName(), $aData)->execute();
	}
	
	private function _initCommercialTenantType(){
		$aTenantTypeList = CommercialTenantType::findAll([], [], 1, 1, ['id' => SORT_ASC]);
		if(!$aTenantTypeList){
			//插入托管类型
			$typeId = CommercialTenantType::addTenantType('托管');
		}else{
			$typeId = $aTenantTypeList[0]['id'];
		}
		$aTypeRelation = [
			'tenant_id' => $this->id,
			'type_id' => $typeId,
		];
		$resultOne = CommercialTenantTypeRelation::addRelation($aTypeRelation);
		$mCommercialTenantType = CommercialTenantType::findOne(['name' => '其他']);
		if($mCommercialTenantType){
			$typeId = $mCommercialTenantType->id;
		}else{
			//插入 其他 类型
			$typeId = CommercialTenantType::addTenantType('其他');
		}
		$aTypeRelationTwo = [
			'tenant_id' => $this->id,
			'type_id' => $typeId,
		];
		return CommercialTenantTypeRelation::addRelation($aTypeRelationTwo) + $resultOne;
	}
	
	private function _initCommercialTenantApprove(){
		$this->getMTenantApprove();
	}


	private function _loadTenantShop(){
		$aShop = (new Query())->from(static::shopTableName())->where(['id' => $this->id])->one();
		if(!$aShop){
			$this->_initCommercialTenantShop();
			throw Yii::$app->buildError('读取shop信息失败!', false);
		}
		self::_addFields($this, $aShop);
	}
	
	private function _loadTenantAccount(){
		$aAccount = (new Query())->from(static::accountTableName())->where(['id' => $this->id])->one();
		if(!$aAccount){
			$this->_initCommercialTenantAccount();
			throw Yii::$app->buildError('读取account信息失败!', false);
		}
		self::_addFields($this, $aAccount);
	}
	
	private function _loadTenantAuth(){
		$aAuth = (new Query())->from(static::authTableName())->where(['id' => $this->id])->one();
		if(!$aAuth){
			$this->_initCommercialTenantAuth();
			throw Yii::$app->buildError('读取auth信息失败!', false);
		}
		self::_addFields($this, $aAuth);
	}
	
	public function getMTenantAction(){
		$mTenantAction = CommercialTenantAction::findOne($this->id);
		if(!$mTenantAction){
			$aData = [
				'id'	=> $this->id,
				'note'	=> json_encode([]),
			];
			(new Query())->createCommand()->insert(CommercialTenantAction::tableName(), $aData)->execute();
			$mTenantAction = static::toModel($aData);
		}
		return $mTenantAction;
	}
	
	/*
	 * 余额操作---新增
	 */
	public function addMoney($money){
		if(!$money){
			return false;
		}
		$this->set('money', ['add', $money]);
		return $this->save();
	}
	
	/*
	 * 余额操作---减少
	 */
	public function subMoney($money){
		if(!$money){
			return false;
		}
		$this->set('money', ['sub', $money]);
		return $this->save();
	}
	
	/**
	 * 搜索商家或商品列表
	 * aCondition[
	 *		lat=>
	 *		lng=>
	 *		search_value=>
	 * ]
	 *	$aControl = [
	 *		'page' =>
	 *		'page_size' =>
	 *	]
	 */
	public static function searchTenantOrGoods($aCondition, $aControl){
		$offset = ($aControl['page'] - 1) * $aControl['page_size'];
		$sql = 'SELECT DISTINCT(`t1`.id),ROUND(6378.138*2*ASIN(SQRT(POW(SIN((' . $aCondition['lat'] . '*PI()/180-lat*PI()/180)/2),2)+COS(' . $aCondition['lat'] . '*PI()/180)*COS(lat*PI()/180)*POW(SIN((' . $aCondition['lng'] . '*PI()/180-lng*PI()/180)/2),2)))*1000) AS `distance` FROM ' . static::tableName() . ' AS `t1` LEFT JOIN ' . Goods::tableName() . ' AS `t2` ON `t1`.`id`=`t2`.`tenant_id` WHERE `t1`.`online_status`=' . static::ONLINE_STATUS_ONLINE . ' AND ((`t1`.`name` LIKE "%' . $aCondition['search_value'] . '%") OR (`t2`.`status`=' . Goods::HAS_PUT_ON . ' AND `t2`.`name` LIKE "%' . $aCondition['search_value'] . '%")) ORDER BY `distance` ASC LIMIT ' . $offset . ',' . $aControl['page_size'];
		$aList = Yii::$app->db->createCommand($sql)->queryAll();
		if(!$aList){
			return [];
		}
		$aTenantId = ArrayHelper::getColumn($aList, 'id');
		$aList = static::getTenantListWithDistance(['id' => $aTenantId, 'lng' => $aCondition['lng'], 'lat' => $aCondition['lat']], ['page' => 1, 'page_size' => count($aList)]);
		$aWhere = [
			'and',
			['tenant_id' => $aTenantId],
			['status' => Goods::HAS_PUT_ON],
			//['like', 'name', $aCondition['search_value']],
		];
		$aGoodsList = Goods::findAll($aWhere, ['id', 'tenant_id', 'name', 'retail_price', 'price', 'sales_count']);
		$aHasSearchValueTenantId = array_unique(ArrayHelper::getColumn($aGoodsList, 'tenant_id'));
		foreach($aList as $key => $value){
			$aList[$key]['goods_list'] = [];
			foreach($aGoodsList as $aGoods){
				if($aGoods['tenant_id'] == $value['id']){
					if(!(strpos($aGoods['name'], '' . $aCondition['search_value']) === false)){
						array_push($aList[$key]['goods_list'], $aGoods);
					}
				}
			}
		}
		foreach($aList as $key => $value){
			if(!$value['goods_list']){
				foreach($aGoodsList as $aGoods){
					if($aGoods['tenant_id'] == $value['id']){
						array_push($aList[$key]['goods_list'], $aGoods);
					}
				}
			}
		}
		
		return $aList;
	}
	
	/**
	 * 获取商家列表
	 * aCondition[
	 *		id=>
	 *		city_id=>
	 *		lat=>
	 *		lng=>
	 *		in_distince => 在多少米范围内
	 * ]
	 *	$aControl = [
	 *		'page' =>
	 *		'page_size' =>
	 *	]
	 */
	public static function getTenantListWithDistance($aCondition, $aControl){
		$offset = ($aControl['page'] - 1) * $aControl['page_size'];
		$where = '`online_status`=' . static::ONLINE_STATUS_ONLINE;
		if(isset($aCondition['id'])){
			if(is_array($aCondition['id'])){
				$where .= ' AND `t1`.`id` IN(' . implode(',', $aCondition['id']) . ')';
			}else{
				$where .= ' AND `t1`.`id`=' . $aCondition['id'];
			}
		}
		if(static::getExceptTenantId()){
			$where .= ' AND `t1`.`id` NOT IN(' . implode(',', static::getExceptTenantId()) . ')';
		}
		if(isset($aCondition['city_id'])){
			$where .= ' AND `t1`.`city_id`=' . $aCondition['city_id'];
		}
		$sql = 'SELECT `t1`.`id`,`t1`.`name`,`t1`.`pay_discount`,`t1`.`avg_score`,`t1`.`all_sales_count`,`t2`.`preferential_info`,`t2`.`profile`,`t2`.`street`,ROUND(6378.138*2*ASIN(SQRT(POW(SIN((' . $aCondition['lat'] . '*PI()/180-lat*PI()/180)/2),2)+COS(' . $aCondition['lat'] . '*PI()/180)*COS(lat*PI()/180)*POW(SIN((' . $aCondition['lng'] . '*PI()/180-lng*PI()/180)/2),2)))*1000) AS `distance` FROM ' . static::tableName() . ' AS `t1` LEFT JOIN ' . static::shopTableName() . ' AS `t2` ON `t1`.`id`=`t2`.`id` WHERE ' . $where . ' ORDER BY `distance` ASC LIMIT ' . $offset . ',' . $aControl['page_size'];
		if(isset($aCondition['in_distince'])){
			//$where .= ' AND `distance`<' . $aCondition['in_distince'];
			$sql = 'SELECT * FROM(SELECT `t1`.`id`,`t1`.`name`,`t1`.`pay_discount`,`t1`.`avg_score`,`t1`.`all_sales_count`,`t2`.`profile`,`t2`.`street`,ROUND(6378.138*2*ASIN(SQRT(POW(SIN((' . $aCondition['lat'] . '*PI()/180-lat*PI()/180)/2),2)+COS(' . $aCondition['lat'] . '*PI()/180)*COS(lat*PI()/180)*POW(SIN((' . $aCondition['lng'] . '*PI()/180-lng*PI()/180)/2),2)))*1000) AS `distance` FROM ' . static::tableName() . ' AS `t1` LEFT JOIN ' . static::shopTableName() . ' AS `t2` ON `t1`.`id`=`t2`.`id` WHERE ' . $where . ') AS `t3` WHERE `distance`<' . $aCondition['in_distince'] . ' ORDER BY `distance` ASC LIMIT ' . $offset . ',' . $aControl['page_size'];
		}
		//$sql = 'SELECT `t1`.`id`,`t1`.`name`,`t1`.`pay_discount`,`t1`.`avg_score`,`t1`.`all_sales_count`,`t2`.`profile`,`t2`.`street`,ROUND(6378.138*2*ASIN(SQRT(POW(SIN((' . $aCondition['lat'] . '*PI()/180-lat*PI()/180)/2),2)+COS(' . $aCondition['lat'] . '*PI()/180)*COS(lat*PI()/180)*POW(SIN((' . $aCondition['lng'] . '*PI()/180-lng*PI()/180)/2),2)))*1000) AS `distance` FROM ' . static::tableName() . ' AS `t1` LEFT JOIN ' . static::shopTableName() . ' AS `t2` ON `t1`.`id`=`t2`.`id` WHERE ' . $where . ' ORDER BY `distance` ASC LIMIT ' . $offset . ',' . $aControl['page_size'];
		$aList = Yii::$app->db->createCommand($sql)->queryAll();
		if(!$aList){
			return [];
		}
		//没有坐标默认为0距离
		if(!$aCondition['lng'] && !$aCondition['lat']){
			foreach($aList as $key => $value){
				$aList[$key]['distance'] = 0;
			}
		}
		
		return static::_withOtherColumnTenantInfoList($aList);
	}
	
	/**
	 * 组装商家其它字段信息
	 */
	private static function _withOtherColumnTenantInfoList($aList){
		$aIds = ArrayHelper::getColumn($aList, 'id');
		$aCommercialTenantTypeRelationList = CommercialTenantTypeRelation::findAll(['tenant_id' => $aIds]);
		$aTenantTypeList = [];
		if($aCommercialTenantTypeRelationList){
			$aTypeIds = ArrayHelper::getColumn($aCommercialTenantTypeRelationList, 'type_id');
			$aTenantTypeList = CommercialTenantType::findAll(['id' => $aTypeIds]);
			foreach($aCommercialTenantTypeRelationList as $k => $v){
				foreach($aTenantTypeList as $aTenantType){
					if($aTenantType['id'] == $v['type_id']){
						$aCommercialTenantTypeRelationList[$k]['tenant_type_name'] = $aTenantType['name'];
					}
				}
			}
		}
		$aResourceIds = ArrayHelper::getColumn($aList, 'profile');
		$aResourceList = Resource::findAll(['id' => $aResourceIds]);
		foreach($aList as $key => $aValue){
			$mCommercialTenant = static::toModel($aValue);
			$aList[$key]['preferential_info'] = $mCommercialTenant->preferential_info;
			$aList[$key]['profile_path'] = '';
			$aList[$key]['tenant_type_name'] = '';
			foreach($aResourceList as $aResource){
				if($aResource['id'] == $aValue['profile']){
					$aList[$key]['profile_path'] = $aResource['path'];
				}
			}
			foreach($aCommercialTenantTypeRelationList as $aCommercialTenantTypeRelation){
				if($aCommercialTenantTypeRelation['tenant_id'] == $aValue['id']){
					$aList[$key]['tenant_type_name'] = $aCommercialTenantTypeRelation['tenant_type_name'];
				}
			}
		}
		return $aList;
	}
	
	public function getNewRegisterName(){
		if($this->name){
			return $this->name;
		}else{
			return '新商户' . str_pad($this->id, 5, 0, STR_PAD_LEFT);
		}
	}
	
	/**
	 * 根据用户手机号排除可见的商户id
	 */
	public static function getExceptTenantId(){
		$mUser = Yii::$app->user->getIdentity();
		if($mUser && in_array($mUser->mobile, ['15619410415', '18667170316', '13725478250', '18620772029'])){
			return false;
		}
		return [24];
	}
	
	/*
	 * 计算商户还能提现多少钱，
	 */
	public function countRemainApplyMoney(){
		$hasMoney = WithdrawCashRecord::countMoneyByOneDay($this->id);
		$fullMoney = $this->money;
		if(static::ONLY_APPLY_MONEY_ONE_DAY < $this->money){
			$fullMoney = static::ONLY_APPLY_MONEY_ONE_DAY;
		}
		if($hasMoney >= $fullMoney){
			return 0;
		}
		return $fullMoney - $hasMoney;
	}
}