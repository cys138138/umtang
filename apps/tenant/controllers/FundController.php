<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\Response;
use common\model\CommercialTenant;
use common\model\Order;
use common\model\OrderCommentIndex;
use common\model\Redis;
use common\model\WithdrawCashRecord;

/*
 * 资金池
 * @author 谭威力
 */
class FundController extends Controller{
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	private function _getTenantMode(){
		return Yii::$app->commercialTenant->getIdentity();
	}
	
	/*
	 * 首页 tenant/fund/show-home.html
	 */
	public function actionShowHome(){
		$mCommercialTenant = $this->_getTenantMode();
		$mCommercialTenantAction = $mCommercialTenant->getMTenantAction();
		$aNote = $mCommercialTenantAction->note;
		$lastCommentReadTime = isset($aNote['last_comment_read_time']) ? $aNote['last_comment_read_time'] : 0;
		$lastOrderReadTime = isset($aNote['last_order_read_time']) ? $aNote['last_order_read_time'] : 0;
		$newCommentCount = OrderCommentIndex::getCount([
			'tenant_id' => $mCommercialTenant->id,
			'start_time' => $lastCommentReadTime,
		]);
		$newOrderCount = Order::getCount([
			'tenant_id' => $mCommercialTenant->id,
			'start_time' => $lastOrderReadTime,
		]);
		$totalOrderPrice = Order::getTotalOrderPriceByTenantId($mCommercialTenant->id);
		
		$aStatisticsInfo = [
			'new_comment_count' => $newCommentCount,
			'new_order_count' => $newOrderCount,
			'total_order_price' => $totalOrderPrice,
		];
		//debug($mCommercialTenant->countRemainApplyMoney(),11);
		return $this->render('home', [
			'aStatisticsInfo' => $aStatisticsInfo,
			'aFundInfo' => [
				'money' => $mCommercialTenant->money,
				'bank_name' => $mCommercialTenant->bank_name,
				'bank_accout_type' => $mCommercialTenant->bank_accout_type,
				'bank_accout_type_name' => $mCommercialTenant->bank_accout_type == 1 ? '个人' : '',
				'bank_accout' => $mCommercialTenant->bank_accout,
				'bank_account_holder' => $mCommercialTenant->bank_account_holder,
			],
			'aLimt' => [
				'fullMoney' => CommercialTenant::ONLY_APPLY_MONEY_ONE_DAY,
				'remainMoney' => $mCommercialTenant->countRemainApplyMoney(),
			],
		]);
	}
	
	/*
	 * 获取提现验证码 tenant/fund/get-mobile-code.json
	 */
	public function actionGetMobileCode(){
		$money = (int)Yii::$app->request->post('money');
		$mCommercialTenant = $this->_getTenantMode();
		//判断银行信息是否完整
		$aBankName = explode('-', $mCommercialTenant->bank_name);
		if(count($aBankName) < 2){
			return new Response('请先补充开户支行信息', -1);
		}
		if(!$money){
			return new Response('请输入提取金额', 0);
		}
		if($money < 0){
			return new Response('请输入正确的金额', 0);
		}
		if($mCommercialTenant->countRemainApplyMoney() < $money){
			return new Response('每日只能提现' . (CommercialTenant::ONLY_APPLY_MONEY_ONE_DAY / 100) . '元金额', -1);
		}
		$mobile = $mCommercialTenant->mobile;
		if(!$mobile){
			return new Response('您还没绑定手机,请先去绑定再提现');
		}
		if($mCommercialTenant->money < $money){
			return new Response('您提现的金额超出余额', -1);
		}
		
		$id = 'get_money_mobile_' . $mobile;
		$mRedis = Redis::findOne(['id' => $id]);
		if($mRedis && $mRedis->expiration_time - NOW_TIME > 840){
			return new Response('验证码已经发送，请留意手机短信', -1);
		}
		$code = mt_rand(100000, 999999);

		//向手机发送短信
		$oSms = Yii::$app->sms;
		$oSms->sendTo = $mobile;
		$oSms->content = '您好，您在执行提现操作，您的验证码是 ' . $code . '此码在十五分钟内有效，请在十五分钟内完成操作。';
		if($oSms->send()){
			if(!$mRedis){
				Redis::add([
					'id' => $id,
					'value' => $code,
					'expiration_time' => NOW_TIME + 900,
				]);
			}else{
				$mRedis->set('value', $code);
				$mRedis->set('expiration_time', NOW_TIME + 900);
				$mRedis->save();
			}
			return new Response('发送验证码成功，请留意手机短信', 1);
		}
		return new Response('发送验证码失败');
	}
	
	/*
	 * 提取余额 tenant/fund/extract-money.json
	 */
	public function actionExtractMoney(){
		$money = (int)Yii::$app->request->post('money');
		$verifyCode = (string)Yii::$app->request->post('verifyCode');
		
		$mCommercialTenant = $this->_getTenantMode();
		//判断银行信息是否完整
		$aBankName = explode('-', $mCommercialTenant->bank_name);
		if(count($aBankName) < 2){
			return new Response('请先补充开户支行信息', -1);
		}
		if(!$money){
			return new Response('请输入提取金额', 0);
		}
		if($money < 0){
			return new Response('请输入正确的金额', 0);
		}
		if($mCommercialTenant->countRemainApplyMoney() < $money){
			return new Response('每日只能提现' . (CommercialTenant::ONLY_APPLY_MONEY_ONE_DAY / 100) . '元金额', -1);
		}
		$mobile = $mCommercialTenant->mobile;
		
		$id = 'get_money_mobile_' . $mobile;
		$mRedis = Redis::findOne(['id' => $id]);
		if(!$verifyCode){
			return new Response('请输入验证码', 0);
		}
		if(!$money){
			return new Response('请输入提取金额', 0);
		}
		if($money < 0){
			return new Response('请输入正确的金额', 0);
		}
		//$money = $money * 100;//分转成元 的 单位
		if(!$mRedis){
			return new Response('验证失败,请重新验证', 0);
		}
		if($mRedis->expiration_time < NOW_TIME){
			return new Response('验证码过期', -1);
		}
		if($mRedis->value != $verifyCode){
			return new Response('验证码不正确', -1);
		}
		
		if($mCommercialTenant->money < $money){
			return new Response('您提现的金额超出余额', -1);
		}
		$mCommercialTenant->subMoney($money);//余额减少操作
		if(WithdrawCashRecord::add([
			'tenant_id' => $mCommercialTenant->id,
			'amount' => $money,
			'balance' => $mCommercialTenant->money,//余额 = 总流水-已提现-提现中
		])){
			//要清除验证码记录
			$mRedis->delete();
			return new Response('提现申请已经提交', 1);
		}
		Yii::error('id为: ' . $mCommercialTenant->id . '商户申请提取余额失败');
		return new Response('提现申请提交失败,请重试或联系客服');
	}
	
	/*
	 * 进账记录页 tenant/fund/show-order.html
	 */
	public function actionShowOrder(){
		return $this->render('show_order', [
			'aTimeStatus' => Order::getTimeTypeList(),
			'aOrderStatus' => Order::getOrderStatusList(),
			'aOrderAllStatus' => Order::getOrderStatusList(true),
		]);
	}
	
	/*
	 * 获取相关的订单数据 tenant/fund/get-order-data.json
	 */
	public function actionGetOrderList(){
		$time = (int)Yii::$app->request->post('time');//2;//
		$status = (int)Yii::$app->request->post('status');
		$page = (int)Yii::$app->request->post('page', 1);
		$pageSize = (int)Yii::$app->request->post('pageSize', 10);
		if($page < 1){
			$page = 1;
		}
		if($pageSize < 1){
			$pageSize = 10;
		}
		$aTimeTypeKeys = \yii\helpers\ArrayHelper::getColumn(Order::getTimeTypeList(), 'key');
		if(!in_array($time, $aTimeTypeKeys)){
			return new Response('请选择正确的时间范围');
		}
		$aOrderStatusKeys = \yii\helpers\ArrayHelper::getColumn(Order::getOrderStatusList(), 'key');
		if(!in_array($status, $aOrderStatusKeys)){
			return new Response('请选择正确的订单状态');
		}
		$mCommercialTenant = $this->_getTenantMode();
		$aWhere = [
			'tenant_id' => $mCommercialTenant->id,
		];
		$aWhereCount = [];
		if($time){
			$startTime = Order::getTimeTypeList('behind')[$time]['start'];
			$endTime = Order::getTimeTypeList('behind')[$time]['end'];
			$aWhere['start_time'] = $startTime;
			$aWhere['end_time'] = $endTime;
			$aWhereCount[] = ['>=', 'activation_time', $startTime];
			$aWhereCount[] = ['<=', 'activation_time', $endTime];
		}else{
			$aWhere['activation_time_no_null'] = true;
		}
		if($status){
			$aWhere['status'] = $status;
		}else{
			$aWhere['status'] = [Order::STATUS_WAIT_COMMENT, Order::STATUS_FINISH];
		}
		$aWhereCount[] = ['status' => $aWhere['status']];
		$totalOrderPrice = Order::getTotalOrderPriceByTenantId($mCommercialTenant->id, $aWhereCount);
		$select = ['id', 'order_num', 'goods_id', 'type', 'price', 'quantity', 'fee', 'status'];
		$aOrder = Order::getList($aWhere, ['page' => $page, 'page_size' => $pageSize, 'order_by' => ['activation_time' => SORT_DESC], 'select' => $select]);
		//debug($aOrder,11);
		return new Response('请求成功', 1, [
			'totalOrderPrice' => $totalOrderPrice,
			'list' => $aOrder,
			'count' => Order::getCount($aWhere),
		]);
	}
	
	/*
	 * 提现记录页 tenant/fund/show-extract.html
	 */
	public function actionShowExtract(){
		$mCommercialTenant = $this->_getTenantMode();
		return $this->render('show_extract', [
			'hasGetPrice' => WithdrawCashRecord::getTotalPrice(['tenant_id' => $mCommercialTenant->id, 'is_finish' => 1]),
			'waitgetPrice' => WithdrawCashRecord::getTotalPrice(['tenant_id' => $mCommercialTenant->id, 'is_finish' => 0]),
		]);
	}
	
	/*
	 * 获取提现记录 tenant/fund/get-extract-data.json
	 */
	public function actionGetExtractList(){
		$page = (int)Yii::$app->request->post('page', 1);
		$pageSize = (int)Yii::$app->request->post('pageSize', 10);
		if($page < 1){
			$page = 1;
		}
		if($pageSize < 1){
			$pageSize = 10;
		}
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
			'order_by' => ['create_time' => SORT_DESC],
		];
		$mCommercialTenant = $this->_getTenantMode();
		$aWithdrawCashRecordList = WithdrawCashRecord::getList(['tenant_id' => $mCommercialTenant->id], $aControl);
		return new Response('请求成功', 1, [
			'list' => $aWithdrawCashRecordList,
			'count' => WithdrawCashRecord::getCount(),
		]);
	}
}

