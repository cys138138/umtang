<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;


class Order extends \common\lib\DbOrmModel{
	//需要编译的字段
	protected $_aEncodeFields = ['goods_info'];
	//要保存的字段
	protected $_aExtendFields = ['goods_info'];
	//订单状态
	const STATUS_WAIT_PAY = 1;		//待支付
	const STATUS_PAID = 2;			//已支付
	const STATUS_APPLY_REFUND = 3;	//申请退款
	const STATUS_HAS_ACTIVATE = 4;	//已激活
	const STATUS_REFUNDED = 5;		//已退款
	const STATUS_WAIT_COMMENT = 6;	//待评价
	const STATUS_FINISH = 7;		//已完成

	//删除状态
	const DIRECT_PAY = 1;			//买单
	const ORDER_PAY = 2;			//订单
	
	
	public static function tableName(){
		return Yii::$app->db->parseTable('_@order');
	}

	public static function goodsInfoTableName() {
		return Yii::$app->db->parseTable('_@order_goods_info');
	}

	/**
	 * 设定扩展字段
	 * @return array
	 */
	public function fields(){
		$aMyFields = array_keys(Yii::getObjectVars($this));
		return array_merge($aMyFields, $this->_aExtendFields);
	}

	/**
	 * @inheritdoc
	 */
	public function __get($name) {
		if($name == 'goods_info' || in_array($name, $this->_aExtendFields)){
			//如果是复合字段
			$aGoodsData = (new Query())->from(self::goodsInfoTableName())->where(['id' => $this->id])->one();
			if(!$aGoodsData){
				(new Query)->createCommand()->insert(static::goodsInfoTableName(), ['id' => $this->id, 'goods_info' => ''])->execute();
				$aGoodsData['goods_info'] = '';
			}
			$this->$name = json_decode($aGoodsData['goods_info'], true);
		}
		if(!in_array($name, array_keys(Yii::getObjectVars($this)))){
			return parent::__get($name);
		}else{
			return $this->$name;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function save(){
		$aUpdateIndex = $this->_aSetFields;
		if(!$aUpdateIndex){
			return 0;
		}
		$aUpdateData = [];
		foreach($aUpdateIndex as $field => $xValue){
			if($field == 'goods_info'){
				$aUpdateData['goods_info'] = json_encode($aUpdateIndex['goods_info']);
				unset($aUpdateIndex['goods_info']);
			}
		}
		$resultIndex = $resultData = 0;
		if($aUpdateIndex){
			$resultIndex = (new Query())->createCommand()->update(self::tableName(), $aUpdateIndex, ['id' => $this->id])->execute();
		}
		if($aUpdateData){
			$resultData = (new Query())->createCommand()->update(self::goodsInfoTableName(), $aUpdateData, ['id' => $this->id])->execute();
		}
		$this->_aSetFields = [];
		return $resultIndex + $resultData;
	}
	
	/*
	 * 新增订单
	 * @parem $aData = [
	 *		.........order常规的字段
	 *		goods_info => order_goods_info表goods_info
	 * ]
	 * return 
	 */
	public static function addOrder($aData){
		$aOrderGoodsInfo['goods_info'] = json_encode($aData['goods_info']);
		unset($aData['goods_info']);
		$aData['create_time'] = NOW_TIME;
		(new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
		$id = Yii::$app->db->getLastInsertID();
		if(!$id){
			return false;
		}
		$aData['id'] = $id;
		$aOrderGoodsInfo['id'] = $id;
		(new Query())->createCommand()->insert(static::goodsInfoTableName(), $aOrderGoodsInfo)->execute();
		return $id;
	}
	
	/*
	 * 订单生成订单号并保存
	 */
	public function createOrderNum(){
		$this->set('order_num', $this->id . mt_rand(1000, 9999));
		return $this->save();
	}
	
	/*
	 * 随机商品码,未保存
	 */
	public function getOrderActivationCode(){
		$code = $this->_buildIdStr();
		$strRate = count($this->_getStrArray()) + 10;
		$max = $strRate - 1;
		for($i = 0; $i < 4; $i++){
			$code .= $this->_getStrByNum(mt_rand(0, $max));
		}
		return $code;
	}
	
	public function _buildIdStr(){
		$id = $this->id;
		$idStr = '';
		$strRate = count($this->_getStrArray()) + 10;
		while($id >= $strRate){
			$idStr = $this->_getStrByNum($id % $strRate) . $idStr;
			$id = floor($id / $strRate);
		}
		return $idStr = $this->_getStrByNum($id) . $idStr;
	}
	
	private function _getStrByNum($num){
		if($num < 10){
			return $num;
		}
		return $this->_getStrArray()[$num];
	}
	
	private function _getStrArray(){
		return [
			10 => 'a',
			11 => 'b',
			12 => 'c',
			13 => 'd',
			14 => 'e',
			15 => 'f',
			16 => 'g',
			17 => 'h',
			18 => 'i',
			19 => 'j',
			20 => 'k',
			//21 => 'l',
			21 => 'm',
			22 => 'n',
			//24 => 'o',
			23 => 'p',
			24 => 'q',
			25 => 'r',
			26 => 's',
			27 => 't',
			28 => 'u',
			29 => 'v',
			30 => 'w',
			31 => 'x',
			32 => 'y',
			33 => 'z',
		];
	}


	/**
	 *	获取列表
	 *	$aCondition = [
	 *		'id' =>
	 *		'tenant_id' => 
	 *		'user_id' => 
	 *		'goods_id' =>
	 *		'status' => 
	 *		'type' => 
	 *		'activation_code' => 
	 *		'activation_time' => 
	 *	]
	 *	$aControl = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'page' =>
	 *		'page_size' =>
	 *		'with_tenant_info' => true/false //是否要商品封面url
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
		$aOrderIds = ArrayHelper::getColumn($aList, 'id');
		$aUserIds = ArrayHelper::getColumn($aList, 'user_id');
		//$aGoodsList = Goods::findAll(['id' => $aGoodsIds], ['id', 'name']);
		$aGoodsList = (new Query())->from(self::goodsInfoTableName())->where(['id' => $aOrderIds])->all();
		
		//拿商品的封面
		$aGoodsIds = ArrayHelper::getColumn($aList, 'goods_id');
		$aGoodss = GoodsPhoto::getList(['goods_id' => $aGoodsIds], ['group_by' => ['goods_id'], 'order_by' => ['is_cover' => SORT_DESC, 'create_time' =>SORT_ASC]]);
		$aGoodss = ArrayHelper::index($aGoodss, 'goods_id');
		
		$aUsers = User::findAll(['id' => $aUserIds]);
		//$aOrderStatus = static::getOrderStatusList(true);
		if(isset($aControl['with_tenant_info']) && $aControl['with_tenant_info']){
			$aTenantIds = ArrayHelper::getColumn($aList, 'tenant_id');
			$aCommercialTenants = CommercialTenant::findAll(['id' => $aTenantIds]);
			$aCommercialTenants = ArrayHelper::index($aCommercialTenants, 'id');
			foreach($aList as $key => $value){
				$aList[$key]['tenant_info'] = [];
				if(isset($aCommercialTenants[$value['tenant_id']])){
					$mCommercialTenant = CommercialTenant::toModel($aCommercialTenants[$value['tenant_id']]);
					//debug($mCommercialTenant->profile_path,11);
					//$mCommercialTenant->profile_path;
					$aList[$key]['tenant_info'] = $mCommercialTenant->toArray(['id', 'name', 'profile_path', 'mobile', 'pay_discount']);
				}
			}
		}
		foreach($aList as $key => $value){
			$aList[$key]['goods_info'] = [];
//			$aList[$key]['validity_time'] = '';
//			$aList[$key]['is_validity'] = 0;
			//$aList[$key]['status_name'] = $aOrderStatus[$value['status']];
			foreach($aGoodsList as $aGoods){
				if($aGoods['id'] == $value['id']){
					$aGoodsInfo = json_decode($aGoods['goods_info'], true);
					//$aList[$key]['goods_info'] = $aGoodsInfo;
					if($aGoodsInfo){
						$aList[$key]['goods_info']['name'] = $aGoodsInfo['name'];
						$aList[$key]['goods_info']['sales_count'] = $aGoodsInfo['sales_count'];
						$aList[$key]['goods_info']['retail_price'] = $aGoodsInfo['retail_price'];
						$aList[$key]['goods_info']['price'] = $aGoodsInfo['price'];
						$aList[$key]['goods_info']['photo'] = isset($aGoodss[$value['goods_id']]) ? $aGoodss[$value['goods_id']]['resource_path'] : '';//拿商品的封面
						if(isset($value['validity_time'])){
							$aList[$key]['goods_info']['validity_time'] = $value['validity_time'];
							$aList[$key]['goods_info']['is_validity'] = $value['validity_time'] > NOW_TIME ? 1 : 0;//是否有效期内
						}else{
							$aList[$key]['goods_info']['validity_time'] = $aGoodsInfo['validity_time'];
							$aList[$key]['goods_info']['is_validity'] = $aGoodsInfo['validity_time'] > NOW_TIME ? 1 : 0;//是否有效期内
						}
					}
				}
			}
			$aList[$key]['user_name'] = '';
			foreach($aUsers as $aUser){
				if($aUser['id'] == $value['user_id']){
					$aList[$key]['user_name'] = $aUser['name'];
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
		if(isset($aCondition['user_id'])){
			$aWhere[] = ['user_id' => $aCondition['user_id']];
		}
		if(isset($aCondition['goods_id'])){
			$aWhere[] = ['goods_id' => $aCondition['goods_id']];
		}
		if(isset($aCondition['type'])){
			$aWhere[] = ['type' => $aCondition['type']];
		}
		if(isset($aCondition['status']) && $aCondition['status']){
			$aWhere[] = ['status' => $aCondition['status']];
		}
		if(isset($aCondition['activation_code'])){
			$aWhere[] = ['activation_code' => $aCondition['activation_code']];
		}
		if(isset($aCondition['start_time']) && $aCondition['start_time']){
			$aWhere[] = ['>=', 'activation_time', $aCondition['start_time']];
		}
		if(isset($aCondition['end_time']) && $aCondition['end_time']){
			$aWhere[] = ['<=', 'activation_time', $aCondition['end_time']];
		}
		if(isset($aCondition['activation_time_no_null']) && $aCondition['activation_time_no_null']){
			$aWhere[] = ['!=', 'activation_time', 0];
		}
		if(isset($aCondition['has_validity_time'])){
			$aWhere[] = ['>=', 'validity_time', $aCondition['has_validity_time']['min']];
			$aWhere[] = ['<=', 'validity_time', $aCondition['has_validity_time']['max']];
		}

		return $aWhere;
	}
	
	/*
	 * 获取总流水帐
	 */
	public static function getTotalOrderPriceByTenantId($tenantId, $aOtherCondition = []){
		//$sql = 'select sum(`price`) as `total_money` from `' . static::tableName() . '` where `tenant_id`=' . $tenantId . ' and `pay_time`>0';
		//$aResult = Yii::$app->db->createCommand($sql)->queryAll();
		$aWhere = [
			'and',
			['tenant_id' => $tenantId],
			['>' ,'pay_time', 0],
			['>' ,'activation_time', 0],//总流水是表示激活过的订单，未激活的还没有进入商户的流水
		];
		if($aOtherCondition){
			$aWhere = array_merge($aWhere, $aOtherCondition);
		}
		$aResult = (new Query())->select('sum(`price`) as `total_money`')->from(static::tableName())->where($aWhere)->all();
		return (int)$aResult[0]['total_money'];
	}
	
	/*
	 * 根据前端或后台场景 返回对应的时间数据
	 */
	public static function getTimeTypeList($scenario = 'front'){
		if($scenario == 'front'){//前端
			return [
				[
					'key' => 1,
					'value' => '今日',
				],	
				[
					'key' => 2,
					'value' => '本周',
				],
				[
					'key' => 3,
					'value' => '本月',
				],
				[
					'key' => 0,
					'value' => '全部',
				],
			];
		}elseif($scenario == 'behind'){//后台
			$nowDayStartTime = strtotime(date('Y-m-d', NOW_TIME));//今天开始时间戳 1494864000
			$nowDayEndTime = $nowDayStartTime + 86399;			  //今天结束时间戳 1494864000
			return [
				1 => [
					'start' => $nowDayStartTime,//mktime(0, 0, 0, date('m', NOW_TIME), date('d', NOW_TIME), date('Y', NOW_TIME)),
					'end' => $nowDayEndTime,// mktime(23, 59, 59, date('m', NOW_TIME), date('d', NOW_TIME), date('Y', NOW_TIME)),
				],
				2 => [
					'start' => $nowDayStartTime - (date('w', $nowDayStartTime) - 1) * 86400,
					//'start' => mktime(0, 0, 0, date('m', NOW_TIME), date('d', NOW_TIME) - date('w', NOW_TIME) + 1, date('Y', NOW_TIME)),
					//'end' => mktime(23, 59, 59, date('m', NOW_TIME), date('d', NOW_TIME) - date('w', NOW_TIME) + 7, date('Y', NOW_TIME)),
					'end' => (7 - date('w', $nowDayStartTime)) * 86400 + $nowDayEndTime, 
				],
				3 => [
					'start' => mktime(0, 0 , 0, date('m', NOW_TIME), 1, date('Y', NOW_TIME)),
					'end' => mktime(23,59,59, date('m', NOW_TIME), date('t', NOW_TIME), date('Y', NOW_TIME)),
				],
			];
		}
	}
	
	public static function getOrderStatusList($isShowAll = false){
		if(!$isShowAll){
			return [
				[
					'key' => 0,
					'value' => '全部',
				],	
				[
					'key' => 6,
					'value' => '待评价',
				],	
				[
					'key' => 7,
					'value' => '已完成',
				],
			];
		}elseif($isShowAll){
			return [
				static::STATUS_WAIT_PAY => '待支付',
				static::STATUS_PAID => '待使用',
				static::STATUS_APPLY_REFUND => '退款中',
				static::STATUS_HAS_ACTIVATE => '已激活',
				static::STATUS_REFUNDED => '已退款',
				static::STATUS_WAIT_COMMENT => '待评价',
				static::STATUS_FINISH => '已完成',
			];
		}
	}
	
	
	/**
	 * 获取用户购买过的商品id列表
	 */
	public static function getUserBoughtGoodsIdList($aCondition, $aControl){
		$offset = 0;
		if(isset($aControl['page']) && isset($aControl['page_size'])){
			$offset = ($aControl['page'] - 1) * $aControl['page_size'];
		}
		$aList = (new Query())->select('goods_id')->from(static::tableName())->where(['user_id' => $aCondition['user_id']])->groupBy('goods_id')->orderBy($aControl['order_by'])->offset($offset)->limit($aControl['page_size'])->all();
		if(!$aList){
			return [];
		}
		return ArrayHelper::getColumn($aList, 'goods_id');
	}
	
	/*
	 * 获取商品的已售数量
	 * $aCondition = [
	 *		tenant_id => 
	 *		goods_id =>
	 *		type => 
	 * ]
	 */
	public static function getGoodsSaleCount($aCondition){
		$aWhere = static::_parseWhereCondition($aCondition);
		return (new Query())->select('count(*) as `num`, `goods_id`')->from(static::tableName())->where($aWhere)->groupBy(['goods_id'])->all();
	}
	
	/*
	 * 微信支付
	 */
	public static function weixinPay($mOrder, $openid){//
		$price = $mOrder->price - $mOrder->accumulate_points_money;//减去积分抵扣
		//统一下单
		$aPara = [
			'goods_category' => '优满堂',
			'detail' => [
				'goods_detail' => [
					[
						'goods_id' => $mOrder->goods_id,
						'goods_name' => $mOrder->type == Order::ORDER_PAY ? $mOrder->goods_info['name'] : CommercialTenant::findOne($mOrder->tenant_id)->name,
						'quantity' => 1,
						'price' => $price,//,//测试价格 1
					],
				],
			],
			'out_trade_no' => $mOrder->order_num,
			'total_fee' => $price,
			'spbill_create_ip' => Yii::$app->request->userIP,
			'openid' => $openid,
			'notify_url' => \umeworld\lib\Url::to(['goods/notify-after-weixin-pay'], true),
		];
		Yii::info('订单号为: ' . $mOrder->order_num . ' 开始支付流程');
		//debug($mOrder);
		//debug($aPara,11);
		if(!YII_ENV_PROD){
			return 1;
		}
		return Yii::$app->wxPay->unifiedOrder($aPara);
	}
}

