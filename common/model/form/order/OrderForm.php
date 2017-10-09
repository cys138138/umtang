<?php
namespace common\model\form\order;

use Yii;
use yii\helpers\ArrayHelper;
use common\model\Goods;
use common\model\GoodsApprove;
use common\model\CommercialTenant;
use common\model\Order;
use common\model\UserAccumulatePointUseRecord;
use common\model\GoodsPhoto;
use umeworld\lib\Query;
use common\model\CommercialTenantType;

/**
 * 订单表单
 */
class OrderForm extends \yii\base\Model{
	const SCENE_ORDER = 'order';//订单
	const SCENE_DIRECT = 'direct';//买单
	
	public $tenantId;
	public $goodsId;
	public $count;
	//public $integral;
	public $isIntegral = 0;
	public $money;	
	public $type;	//下单类型 1，买单。2，服务订单
	
	public $mUser;
	public $isZero = 0;
	private $_mGoods;
	private $_mTenant;
	//private $_integral = 0;
	private $_originalPrice = 0;
	//private $_payDiscount = 0;
	private $_accumulatePointsMoney = 0;
	private $_price = 0;

	public function rules(){
		return [
			[['mUser', 'goodsId', 'money'], 'required' , 'message'=>'参数不全'],
			//['priceType', 'in', 'range' => [ServiceContent::PRICE_TYPE_DAY, ServiceContent::PRICE_TYPE_MONTH, ServiceContent::PRICE_TYPE_TERM], 'message'=>'价格类型错误'],//日期范围
			//['monthCount', 'default', 'value' => -1],
			['tenantId', 'integer', 'message' => '商户错误'],
			['goodsId', 'integer', 'message' => '服务错误'],
			['count', 'integer', 'message' => '购买数量错误'],
			//['integral', 'integer', 'message' => '抵扣积分错误'],
			//['money', 'integer', 'message' => '购买金额错误'],
			//['appointmentDay', 'compare', 'compareValue' => 0, 'operator' => '>', 'message' => '提前预约天数必须大于0'],
			['count', 'compare', 'compareValue' => 0, 'operator' => '>', 'message' => '购买数量不能小于0'],
			//['integral', 'compare', 'compareValue' => 0, 'operator' => '>', 'message' => '抵扣积分必须大于0'],
			//['name', 'string', 'min' => 2, 'max' => 10, 'tooShort' => '服务名不低于2个字', 'tooLong' => '服务名不能超过10个字'],
			//['notice', 'string', 'min' => 2, 'max' => 200, 'tooShort' => '温馨提示不低于2个字', 'tooLong' => '温馨提示不能超过200个字'],
			//['mTenant', 'required', 'message' => '商户模型不能为空'],
			//检查typeId合法性
			['mUser', 'checkHasMobile'],
			['tenantId', 'checkTenantId'],
			//审核中的服务不能被编辑
			//检查id的合法性
			['goodsId', 'checkGoodsId'],
			//['integral', 'checkIntegral'],
			['money', 'checkMoney'],
			//['newPassword', 'compare', 'compareAttribute'=> 'rePassword', 'message'=>'请再输入确认密码'],
			['isIntegral', 'checkIsIntegral'],
		];
	}
	
	//通过这个方法返回场景验证设定
    public function scenarios(){
        return [
            //KEY就是场景标识,值就是一个数组,包含了要验证的属性名称
            static::SCENE_ORDER => ['mUser', 'goodsId', 'count', 'isIntegral'],	//订单
            static::SCENE_DIRECT => ['tenantId', 'mUser', 'money', 'count', 'isIntegral'],	//买单
        ];
    }
	
	public function checkHasMobile(){
		if(!$this->mUser->mobile){
			$this->addError('', '请先绑定手机号码再下单');
			return false;
		}
		return true;
	}
	
	public function checkMoney(){
		$this->money = (int)$this->money;
		if($this->money < 1){//这个1表示 一分钱
			$this->addError('money', '购买金额值错误');
			return false;
		}
		$this->_originalPrice = $this->money;//原价; * 100
		$payDiscount = $this->_mTenant->pay_discount == 0 ? 1 : $this->_mTenant->pay_discount * 0.01;//折扣
		$this->_price = $payDiscount * $this->_originalPrice;//订单价格
		return true;
	}
	
//	public function checkIntegral(){
//		if($this->mUser->accumulate_points < $this->integral){
//			$this->addError('integral', '积分不足');
//			return false;
//		}
//		return true;
//	}
	
	public function checkIsIntegral(){
		//debug($this->mUser->accumulate_points);
		$this->isIntegral = (int)$this->isIntegral;
		if($this->isIntegral && $this->mUser->accumulate_points <= 0){
			$this->addError('isIntegral', '积分不足');
			return false;
		}
		$this->_price = ceil($this->_price);//价格向上，因为单位是分
		if($this->isIntegral){
			//计算积分抵扣后的价格
			//debug($this->mUser->accumulate_points,11);
			if($this->_price > $this->mUser->accumulate_points){
				$this->_accumulatePointsMoney = $this->mUser->accumulate_points;
			}else{
				//如果积分高于 价格，那么价格变成0元，积分抵扣就是之前的价格
				$this->_accumulatePointsMoney = $this->_price;
				//$this->_price = 0;
				$this->isZero = 1;
			}
		}
		//if($this->_price < 1){//折后价格低于1分钱
			//$this->addError('money', '折后价格不能低于1分钱');
			//return false;
			//$this->_price = 1;
		//}
//		debug($this->_accumulatePointsMoney);
//		debug($this->_originalPrice);
//		debug($this->_price,11);
		return true;
	}
		
	//检查
	public function checkGoodsId(){
		$this->_mGoods = Goods::findOne(['id' => $this->goodsId, 'status' => Goods::HAS_PUT_ON]);
		if(!$this->_mGoods){
			$this->addError('id', '服务不存在');
			return false;
		}
		if($this->_mGoods->validity_time < NOW_TIME){
			$this->addError('id', '服务已经过期');
			return false;
		}
		$this->_mTenant = CommercialTenant::findOne($this->_mGoods->tenant_id);
		if(!$this->_mTenant){
			$this->addError('id', '服务不存在');
			return false;
		}
		//if($this->_mGoods->tenant_id != $this->tenantId){
		//	$this->addError('id', '服务不存在');
		//	return false;
		//}
		//if($this->scenario == static::SCENE_EDIT_GOODS_DATA && !$this->_mGoods->isCanEdit()){
		//	$this->addError('id', '审核中的服务不能编辑');
		//	return false;
		//}
		$this->_originalPrice = $this->_mGoods->price * $this->count;//原价;
		$this->_price = $this->_originalPrice;//订单价格
		return true;
	}
	
	public function checkTenantId(){
		$this->_mTenant = CommercialTenant::findOne(['id' => $this->tenantId, 'online_status' => CommercialTenant::ONLINE_STATUS_ONLINE]);
		if(!$this->_mTenant){
			$this->addError('id', '商铺不存在或已下架');
			return false;
		}
	}
	
	public function addData(){
		if($this->type == 1){//买单
			$aData = [
				'order_num' => 0,
				'type' => $this->type,
				'tenant_id' => $this->tenantId,
				'user_id' => $this->mUser->id,
				'mobile' => $this->mUser->mobile,
				'original_price' => $this->_originalPrice,//原价
				'price' => $this->_price,//订单价格
				'accumulate_points_money' => $this->_accumulatePointsMoney,//积分抵扣
				'status' => $this->isZero ? Order::STATUS_WAIT_COMMENT : Order::STATUS_WAIT_PAY,
				'fee' => 0,
				'goods_id' => 0,
				'quantity' => 0,
				'pay_time' => $this->isZero ? NOW_TIME : 0,
				'pay_money' => 0,
				'activation_code' => 0,
				'activation_time' => $this->isZero ? NOW_TIME : 0,
				'goods_info' => [],
			];
		}else{//订单
			$aGoodsInfo = $this->_mGoods->toArray();
			$aGoodsInfo['goods_photo'] = [];
			$aGoodsPhoto = (new Query())->from(GoodsPhoto::tableName())->where(['goods_id' => $this->goodsId])->orderBy(['is_cover' => SORT_DESC, 'create_time' => SORT_ASC])->one();
			//订单 要 存储 goods完整信息 包括 图片 goods_photo 封面
			if($aGoodsPhoto){
				$mGoodsPhoto = GoodsPhoto::toModel($aGoodsPhoto);
				$aGoodsPhoto['resource_path'] = $mGoodsPhoto->resource_path;
				$aGoodsPhoto['goods_photo'] = $aGoodsPhoto;
			}
			//计算手续费用
			$mCommercialTenantType = CommercialTenantType::findOne(['id' => $this->_mGoods->type_id]);
			$fee = 0;
			if($mCommercialTenantType && $mCommercialTenantType->fee_rate){
				$fee = (int)($this->_price * $mCommercialTenantType->fee_rate * 0.001); 
				if($fee < 1){
					$fee = 0;
				}
			}
			$aData = [
				'order_num' => 0,
				'type' => $this->type,
				'tenant_id' => $this->_mGoods->tenant_id,
				'user_id' => $this->mUser->id,
				'mobile' => $this->mUser->mobile,
				'original_price' => $this->_originalPrice,//原价
				'price' => $this->_price,//订单价格
				'accumulate_points_money' => $this->_accumulatePointsMoney,//积分抵扣
				'status' => $this->isZero ? Order::STATUS_PAID : Order::STATUS_WAIT_PAY,
				'goods_id' => $this->goodsId,
				'fee' => $fee,//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				'quantity' => $this->count,
				'pay_time' => $this->isZero ? NOW_TIME : 0,
				'pay_money' => 0,
				'validity_time' => $aGoodsInfo['validity_time'],
				'activation_code' => 0,
				'activation_time' => 0,
				'goods_info' => $aGoodsInfo,
			];
		}
		$orderId = Order::addOrder($aData);
		if($orderId){
			$aData['id'] = $orderId;
			$mOrder = Order::toModel($aData);
			$mOrder->createOrderNum();
			//用户积分抵扣减少操作
			if($this->isIntegral && $this->_accumulatePointsMoney > 0){
				$this->mUser->subAccumulatePoints($this->_accumulatePointsMoney);
				//积分减少记录
				if(!UserAccumulatePointUseRecord::add([
					'user_id' => $this->mUser->id,
					'type' => UserAccumulatePointUseRecord::TYPE_PAY_ORDER,
					'amount' => $this->_accumulatePointsMoney,
					'data_id' => $orderId,
				])){
					Yii::error('订单' . $orderId . '提交成功,用户积分减少记录插入失败，用户id是: ' . $this->mUser->id);
				}
			}
		}else{
			Yii::error('订单提交失败,用户id是: ' . $this->mUser->id);
			return false;
		}
		return $mOrder;
	}
}

