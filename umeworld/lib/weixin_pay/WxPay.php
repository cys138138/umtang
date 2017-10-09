<?php
namespace umeworld\lib\weixin_pay;

use Yii;

class WxPay extends \yii\base\Object{
	
	public $appId;
	public $mchId;	//微信支付分配的商户号
	public $key = '';	//商家密钥
	public $mchName; //商家名称
	public $tradeType = 'JSAPI'; //交易类型
	public $timeExpire = 86400; //交易过期时间
	public $notifyUrl;
	public $signType = 'MD5';
	public $unifiedOrderUrl = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
	public $orderQueryUrl = 'https://api.mch.weixin.qq.com/pay/orderquery';
	public $refundUrl = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
	public $refundQueryUrl = 'https://api.mch.weixin.qq.com/pay/refundquery';
	public $sslcentPath;
	public $sslkeyPath;

	const QUERY_SUCC_CODE_FAIL = 0;		//订单不存在
	const QUERY_SUCC_CODE_SUCCESS = 1;	//订单成功
	const QUERY_SUCC_CODE_PLAYING = 2;	//继续等待


	/**
	 * 统一下单接口
	 * $aPara = [
	 *		'goods_category' => 销售商品类目，是否必填【是】
	 *		'detail' => json格式(传入数组)，商品详细列表,是否必填【否】
	 *		[
	 *		'goods_detail'	=>	
	 *			[
	 *				'goods_id'	=>	'商品的编号',必填,String(32)
	 *				'wxpay_goods_id'	=>	'微信支付定义的统一商品编号',【可选】,String(32)
	 *				'goods_name'	=>	'商品名称',【必填】,String(256)
	 *				'quantity'	=>	'商品数量',【必填】,int
	 *				'price'	=>	'商品单价',【必填】,int,单位为分,注意：a、单品总金额应<=订单总金额total_fee，否则会导致下单失败。
	 *			],
	 *			[
	 *				'goods_id'	=>	'商品的编号',【必填】,String(32)
	 *				'wxpay_goods_id'	=>	'微信支付定义的统一商品编号',【可选】,String(32)
	 *				'goods_name'	=>	'商品名称',【必填】,String(256)
	 *				'quantity'	=>	'商品数量',【必填】,int
	 *				'price'	=>	'商品单价',【必填】,int,单位为分,注意：a、单品总金额应<=订单总金额total_fee，否则会导致下单失败。
	 *			],
	 *		]
	 *		'out_trade_no'		=>	商户系统内部订单号，要求32个字符内、且在同一个商户号下唯一。是否必填【是】,String(32)
	 *		'total_fee'		=>	订单总金额，单位为分【是】,Int
	 *		'spbill_create_ip'		=>	APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。【是】,String(16)
	 *		'openid'		=>	trade_type=JSAPI时（即公众号支付），此参数必传，此参数为微信用户在商户对应appid下的唯一标识。。【否】,String(128)
	 * 
	 *		-------------不建议填写的参数----------
	 *		'attach'		=>	附加数据，在查询API和支付通知中原样返回，可作为自定义参数使用。是否必填【否】,String(127)
	 *		'fee_type'		=>	符合ISO 4217标准的三位字母代码，默认人民币：CNY【否】,String(16)
	 *		'goods_tag'		=>	商品标记，使用代金券或立减优惠功能时需要的参数。【否】,String(32)
	 *		'trade_type'		=>	取值如下：JSAPI，NATIVE，APP等【是】,String(16)
	 *		'product_id'		=>	trade_type=NATIVE时（即扫码支付），此参数必传。此参数为二维码中包含的商品ID，商户自行定义。【否】,String(32)
	 *		'limit_pay'		=>	上传此参数no_credit--可限制用户不能使用信用卡支付。【否】,String(32)
	 *		'time_start'		=>	订单生成时间，格式为yyyyMMddHHmmss。【否】,String(14)
	 *		'time_expire'		=>	订单失效时间，格式为yyyyMMddHHmmss,注意：最短失效时间间隔必须大于5分钟。【否】,String(14)
	 *		'notify_url'		=>	异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数。。【是】,String(256)
	 * ];
	 */
	public function unifiedOrder($aPara){
		if(!isset($aPara['goods_category']) || !isset($aPara['detail']) || !isset($aPara['out_trade_no']) || !isset($aPara['total_fee']) || !isset($aPara['spbill_create_ip']) || !isset($aPara['openid'])){
			throw Yii::$app->buildError('缺少必要参数，下单失败！');
		}
		$aParaTemp = [
			'appid' => $this->appId,
			'mch_id' => $this->mchId,
			'device_info' => 'WEB',
			'nonce_str' => $this->_makeRandStr(),
			'body' => $this->mchName . '-' . $aPara['goods_category'],
			'detail' => json_encode($aPara['detail']),
			'out_trade_no' => $aPara['out_trade_no'],
			'total_fee' => $aPara['total_fee'],
			'spbill_create_ip' => $aPara['spbill_create_ip'],
			'openid' => $aPara['openid'],
			'notify_url' => isset($aPara['notify_url']) ? $aPara['notify_url'] : $this->notifyUrl,
			'trade_type' => $this->tradeType,
			'time_start' => date('YmdHis', NOW_TIME),
			'time_expire' => date('YmdHis', NOW_TIME + $this->timeExpire),
		];
		$oWxPayUnifiedOrder = new WxPayUnifiedOrder($aParaTemp, $this->key);
		$oWxPayUnifiedOrder->setSign();
		$xml = $oWxPayUnifiedOrder->toXml();
		if(YII_ENV == 'prod'){
			$response = $this->_postXmlCurl($xml, $this->unifiedOrderUrl);
		}else{
			$response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg><appid><![CDATA[wx2421b1c4370ec43b]]></appid><mch_id><![CDATA[10000100]]></mch_id><nonce_str><![CDATA[IITRi8Iabbblz1Jc]]></nonce_str><openid><![CDATA[oUpF8uMuAJO_M2pxb1Q9zNjWeS6o]]></openid><sign><![CDATA[7921E432F65EB8ED0CE9755F0E86D72F]]></sign><result_code><![CDATA[SUCCESS]]></result_code><prepay_id><![CDATA[wx201411101639507cbf6ffd8b0779950874]]></prepay_id><trade_type><![CDATA[JSAPI]]></trade_type></xml>';
		}
		$aUnifiedOrderResult = WxPayResults::init($response, $this->key);
		if($aUnifiedOrderResult['return_code'] != 'SUCCESS'){
			throw Yii::$app->buildError($aUnifiedOrderResult['return_msg']);
		}elseif($aUnifiedOrderResult['result_code'] != 'SUCCESS'){
			Yii::error($aUnifiedOrderResult['err_code_des']);
			return false;
		}
		return $this->_getJsApiParameters($aUnifiedOrderResult);
	}
	
	/**
	 * 查询订单
	 * @param sting $transactionId  微信的订单号/商户系统内部的订单号
	 * @param int $succesCode 返回值结果码
	 * @param int $timeOut
	 * @param bool $isTransactionId 是否微信订单号
	 * @return boolean 查询成功返回查询结果，查询失败返回false
	 */
	public function orderQuery($transactionId, &$succesCode, $timeOut = 6, $isTransactionId = true){
		$aParams = [
			'appid'		=> $this->appId,
			'mch_id'	=> $this->mchId,
			'nonce_str'	=> $this->_makeRandStr(),
		];
		if($isTransactionId){
			$aParams['transaction_id'] = $transactionId;
		}else{
			$aParams['out_trade_no'] = $transactionId;
		}
		$oWxPayOrderQuery = new WxPayOrderQuery($aParams, $this->key);
		$oWxPayOrderQuery->setSign();
		$xml = $oWxPayOrderQuery->toXml();
		if(YII_ENV == 'prod'){
			$response = $this->_postXmlCurl($xml, $this->orderQueryUrl, false, $timeOut);
		}else{
			$response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg><appid><![CDATA[wx2421b1c4370ec43b]]></appid><mch_id><![CDATA[10000100]]></mch_id><device_info><![CDATA[1000]]></device_info><nonce_str><![CDATA[TN55wO9Pba5yENl8]]></nonce_str><sign><![CDATA[BDF0099C15FF7BC6B1585FBB110AB635]]></sign><result_code><![CDATA[SUCCESS]]></result_code><openid><![CDATA[oUpF8uN95-Ptaags6E_roPHg7AG0]]></openid><is_subscribe><![CDATA[Y]]></is_subscribe><trade_type><![CDATA[MICROPAY]]></trade_type><bank_type><![CDATA[CCB_DEBIT]]></bank_type><total_fee>1</total_fee><fee_type><![CDATA[CNY]]></fee_type><transaction_id><![CDATA[1008450740201411110005820873]]></transaction_id><out_trade_no><![CDATA[1415757673]]></out_trade_no><attach><![CDATA[订单额外描述]]></attach><time_end><![CDATA[20141111170043]]></time_end><trade_state><![CDATA[SUCCESS]]></trade_state></xml>';
		}
		$aOrderQueryResult = WxPayResults::init($response, $this->key);
		if($aOrderQueryResult['return_code'] != 'SUCCESS'){
			throw Yii::$app->buildError($aOrderQueryResult['return_msg']);
		}
		if($aOrderQueryResult['result_code'] != 'SUCCESS'){
			if($aOrderQueryResult['err_code'] == 'ORDERNOTEXIST'){
				$succesCode = static::QUERY_SUCC_CODE_FAIL;
			}else{
				$succesCode = static::QUERY_SUCC_CODE_PLAYING;
			}
			return false;
		}
		if($aOrderQueryResult['trade_state'] == 'SUCCESS'){
			$succesCode = static::QUERY_SUCC_CODE_SUCCESS;
			return $aOrderQueryResult;
		}
		$succesCode = static::QUERY_SUCC_CODE_PLAYING;
		return false;
	}
	
	/**
	 * 获取异步通知结果
	 * @param string $message 返回信息
	 * @return 成功返回通知结果，失败返回false
	 */
	public function getNotifyResult(&$message){
		//获取通知的数据
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		//$xml = '<xml><appid><![CDATA[wx2421b1c4370ec43b]]></appid><attach><![CDATA[支付测试]]></attach><bank_type><![CDATA[CFT]]></bank_type><fee_type><![CDATA[CNY]]></fee_type><is_subscribe><![CDATA[Y]]></is_subscribe><mch_id><![CDATA[10000100]]></mch_id><nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str><openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid><out_trade_no><![CDATA[1409811653]]></out_trade_no><result_code><![CDATA[SUCCESS]]></result_code><return_code><![CDATA[SUCCESS]]></return_code><sign><![CDATA[B552ED6B279343CB493C5DD0D78AB241]]></sign><sub_mch_id><![CDATA[10000100]]></sub_mch_id><time_end><![CDATA[20140903131540]]></time_end><total_fee>1</total_fee><trade_type><![CDATA[JSAPI]]></trade_type><transaction_id><![CDATA[1004400740201409030005092168]]></transaction_id></xml>';
		//如果返回成功则验证签名
		try{
			$aNotify = WxPayResults::init($xml, $this->key);
		}catch(\umeworld\lib\ServerErrorHttpException $e){
			$message = $e->getMessage();
			return false;
		}
		if($aNotify['result_code'] != 'SUCCESS'){
			$message = $aNotify['err_code_des'];
			return false;
		}
		return $aNotify;
	}
	
	/**
	 * 异步通知回复
	 * @param bool $isSuccess 订单是否完成
	 * @param string $failMessage 错误描述
	 */
	public function replyNotify($isSuccess, $failMessage = ''){
		$returnCode = $isSuccess ? 'SUCCESS' : 'FAIL';
		$oWxPayDataBase = new WxReplyNotifyData($returnCode, $failMessage);
		echo $oWxPayDataBase->toXml();
	}
	
	/**
	 * 退款
	 * $aPara = [
	 *		'out_refund_no'	=>	退款单号
	 *		'total_fee'	=>	订单总金额
	 *		'refund_fee'	=>	退款金额
	 *		'transaction_id'	=>	微信订单号(商户订单号2选1)
	 *		'out_trade_no'	=>	商户订单号
	 * ]
	 * @param array $aPara
	 * @return 退款失败返回false，否则返回退款信息
	 */
	public function refund($aPara){
		if(!isset($aPara['out_refund_no']) || !isset($aPara['total_fee']) || !isset($aPara['refund_fee']) || !(isset($aPara['transaction_id']) || isset($aPara['out_trade_no']))){
			throw Yii::$app->buildError('缺少必要参数，退款失败！');
		}
		
		$aParaTemp = [
			'appid' => $this->appId,
			'mch_id' => $this->mchId,
			'device_info' => 'WEB',
			'nonce_str' => $this->_makeRandStr(),
			'out_refund_no' => $aPara['out_refund_no'],
			'total_fee' => $aPara['total_fee'],
			'refund_fee' => $aPara['refund_fee'],
			'op_user_id' => $this->mchId,
			'transaction_id' => isset($aPara['transaction_id']) ? $aPara['transaction_id'] : '',
			'out_trade_no' => isset($aPara['out_trade_no']) ? $aPara['out_trade_no'] : '',
			//'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
		];
		$oWxPayRefund = new WxPayRefund($aParaTemp, $this->key);
		$oWxPayRefund->setSign();
		$xml = $oWxPayRefund->toXml();
		$response = $this->_postXmlCurl($xml, $this->refundUrl, true);
		$aRefundResult = WxPayResults::init($response, $this->key);
		if($aRefundResult['return_code'] != 'SUCCESS'){
			throw Yii::$app->buildError($aRefundResult['return_msg']);
		}elseif($aRefundResult['result_code'] != 'SUCCESS'){
			Yii::error($aRefundResult['err_code_des']);
			return false;
		}
		return $aRefundResult;
	}
	
	/**
	 * 退款查询
	 * $aPara = [
	 *		//下列参数4选1
	 *		'transaction_id'	=>	微信订单号
	 *		'out_trade_no'	=>	商户订单号
	 *		'out_refund_no'	=>	商户退单号
	 *		'refund_id'	=>	微信退单号
	 * ]
	 * @param array $aPara
	 * @return 查询失败返回false,成功返回查询结果
	 */
	public function refundQuery($aPara){
		if(!isset($aPara['transaction_id']) && !isset($aPara['out_trade_no']) && !isset($aPara['out_refund_no']) && !isset($aPara['refund_id'])){
			throw Yii::$app->buildError('缺少必要参数，退款失败！');
		}
		$aParaTemp = [
			'appid' => $this->appId,
			'mch_id' => $this->mchId,
			'device_info' => 'WEB',
			'nonce_str' => $this->_makeRandStr(),
			
			'transaction_id' => isset($aPara['transaction_id']) ? $aPara['transaction_id'] : '',
			'out_trade_no' => isset($aPara['out_trade_no']) ? $aPara['out_trade_no'] : '',
			'out_refund_no' => isset($aPara['out_refund_no']) ? $aPara['out_refund_no'] : '',
			'refund_id' => isset($aPara['refund_id']) ? $aPara['refund_id'] : '',
		];
		$oWxPayRefundQuery = new WxPayRefundQuery($aParaTemp, $this->key);
		$oWxPayRefundQuery->setSign();
		$xml = $oWxPayRefundQuery->toXml();
		$response = $this->_postXmlCurl($xml, $this->refundQueryUrl);
		$aRefundQueryResult = WxPayResults::init($response, $this->key);
		if($aRefundQueryResult['return_code'] != 'SUCCESS'){
			throw Yii::$app->buildError($aRefundQueryResult['return_msg']);
		}elseif($aRefundQueryResult['result_code'] != 'SUCCESS'){
			Yii::error($aRefundQueryResult['err_code_des']);
			return false;
		}
		return $aRefundQueryResult;
	}
	
	private function _getJsApiParameters($aUnifiedOrderResult){
		if(!array_key_exists('appid', $aUnifiedOrderResult) || !array_key_exists('prepay_id', $aUnifiedOrderResult) || $aUnifiedOrderResult['prepay_id'] == ''){
			throw Yii::$app->buildError('参数错误！');
		}
		$oJsApi = new WxPayJsApiPay();
		$oJsApi->setKey($this->key);
		$oJsApi->setAppId($aUnifiedOrderResult["appid"]);
		$oJsApi->setTimeStamp(time());
		$oJsApi->setNonceStr($this->_makeRandStr());
		$oJsApi->setPackage("prepay_id=" . $aUnifiedOrderResult['prepay_id']);
		$oJsApi->setSignType($this->signType);
		$oJsApi->setPaySign($oJsApi->makeSign());
		$parameters = json_encode($oJsApi->getValues());
		return $parameters;
	}


	private function _postXmlCurl($xml, $url, $useCert = false, $second = 30){
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		if($useCert == true){
			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLCERT, $this->sslcentPath);
			curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLKEY, $this->sslkeyPath);
		}
		
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		if($errorNo = curl_errno($ch)){
			$error = curl_error($ch);
			curl_close($ch);
			throw Yii::$app->buildError('下单失败，错误码：' . $errorNo . ',错误：' . $error);
		}
		curl_close($ch);
		return $data;
	}
	
	private function _makeRandStr($length = 32){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$max = strlen($chars) - 1;
		$randStr = '';
		for($i = 0; $i < $length; $i++){
			$randStr .= $chars[mt_rand(0, $max)];
		}
		return $randStr;
	}
	
	
	public function sandboxnewUnifiedOrder($aPara){
		if(!isset($aPara['goods_category']) || !isset($aPara['detail']) || !isset($aPara['out_trade_no']) || !isset($aPara['total_fee']) || !isset($aPara['spbill_create_ip']) || !isset($aPara['openid']) || !isset($aPara['notify_url'])){
			throw Yii::$app->buildError('缺少必要参数，下单失败！');
		}
		//获取沙箱密钥
		$sandboxnewSignkey = $this->_getSandboxnewSignkey();
		
		
		$aParaTemp = [
			'appid' => $this->appId,
			'mch_id' => $this->mchId,
			'device_info' => 'WEB',
			'nonce_str' => $this->_makeRandStr(),
			'body' => $this->mchName . '-' . $aPara['goods_category'],
			'detail' => json_encode($aPara['detail']),
			'out_trade_no' => $aPara['out_trade_no'],
			'total_fee' => $aPara['total_fee'],
			'spbill_create_ip' => $aPara['spbill_create_ip'],
			'openid' => $aPara['openid'],
			'notify_url' => $aPara['notify_url'],
			'trade_type' => $this->tradeType,
			'time_start' => date('YmdHis', NOW_TIME),
			'time_expire' => date('YmdHis', NOW_TIME + $this->timeExpire),
		];
		$oWxPayUnifiedOrder = new WxPayUnifiedOrder($aParaTemp, $sandboxnewSignkey);
		$oWxPayUnifiedOrder->setSign();
		$xml = $oWxPayUnifiedOrder->toXml();
		if(YII_ENV == 'prod'){
			$response = $this->_postXmlCurl($xml, 'https://api.mch.weixin.qq.com/sandboxnew/pay/unifiedorder');
			Yii::info($response);
		}else{
			$response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg><appid><![CDATA[wx2421b1c4370ec43b]]></appid><mch_id><![CDATA[10000100]]></mch_id><nonce_str><![CDATA[IITRi8Iabbblz1Jc]]></nonce_str><openid><![CDATA[oUpF8uMuAJO_M2pxb1Q9zNjWeS6o]]></openid><sign><![CDATA[7921E432F65EB8ED0CE9755F0E86D72F]]></sign><result_code><![CDATA[SUCCESS]]></result_code><prepay_id><![CDATA[wx201411101639507cbf6ffd8b0779950874]]></prepay_id><trade_type><![CDATA[JSAPI]]></trade_type></xml>';
		}
		$aUnifiedOrderResult = WxPayResults::init($response, $sandboxnewSignkey);
		if($aUnifiedOrderResult['return_code'] != 'SUCCESS'){
			throw Yii::$app->buildError($aUnifiedOrderResult['return_msg']);
		}elseif($aUnifiedOrderResult['result_code'] != 'SUCCESS'){
			Yii::error($aUnifiedOrderResult['err_code_des']);
			return false;
		}
		return $this->_getSandboxnewJsApiParameters($aUnifiedOrderResult);
	}
	
	private function _getSandboxnewSignkey(){
		$aGetsignkeyPara = [
			'mch_id' => $this->mchId,
			'nonce_str' => $this->_makeRandStr(),
		];
		$oGetsignkey = new SandboxnewKeyGetsignkey($aGetsignkeyPara, $this->key);
		$oGetsignkey->setSign();
		$xml = $oGetsignkey->toXml();
		$response = $this->_postXmlCurl($xml, 'https://api.mch.weixin.qq.com/sandboxnew/pay/getsignkey');
		$oBaseData = new WxPayDataBase();
		$aReturn = $oBaseData->fromXml($response);
		if(!isset($aReturn['return_code'])){
			throw Yii::$app->buildError('未知错误');
		}elseif($aReturn['return_code'] != 'SUCCESS'){
			throw Yii::$app->buildError($aReturn['return_msg']);
		}
		$this->_sandboxnewSignKey = $aReturn['sandbox_signkey'];
		return $aReturn['sandbox_signkey'];
	}
	
	private function _getSandboxnewJsApiParameters($aUnifiedOrderResult){
		if(!array_key_exists('appid', $aUnifiedOrderResult) || !array_key_exists('prepay_id', $aUnifiedOrderResult) || $aUnifiedOrderResult['prepay_id'] == ''){
			throw Yii::$app->buildError('参数错误！');
		}
		//获取沙箱密钥
		$sandboxnewSignkey = $this->_getSandboxnewSignkey();
		$oJsApi = new WxPayJsApiPay();
		$oJsApi->setKey($sandboxnewSignkey);
		$oJsApi->setAppId($aUnifiedOrderResult["appid"]);
		$oJsApi->setTimeStamp(time());
		$oJsApi->setNonceStr($this->_makeRandStr());
		$oJsApi->setPackage("prepay_id=" . $aUnifiedOrderResult['prepay_id']);
		$oJsApi->setSignType($this->signType);
		$oJsApi->setPaySign($oJsApi->makeSign());
		$parameters = json_encode($oJsApi->getValues());
		return $parameters;
	}
	
	public function sandboxnewOrderQuery($transactionId, &$succesCode, $timeOut = 6, $isTransactionId = true){
		$sandboxnewSignkey = $this->_getSandboxnewSignkey();
		
		$aParams = [
			'appid'		=> $this->appId,
			'mch_id'	=> $this->mchId,
			'nonce_str'	=> $this->_makeRandStr(),
		];
		if($isTransactionId){
			$aParams['transaction_id'] = $transactionId;
		}else{
			$aParams['out_trade_no'] = $transactionId;
		}
		$oWxPayOrderQuery = new WxPayOrderQuery($aParams, $sandboxnewSignkey);
		$oWxPayOrderQuery->setSign();
		$xml = $oWxPayOrderQuery->toXml();
		if(YII_ENV == 'prod'){
			$response = $this->_postXmlCurl($xml, 'https://api.mch.weixin.qq.com/sandboxnew/pay/orderquery', false, $timeOut);
		}else{
			$response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg><appid><![CDATA[wx2421b1c4370ec43b]]></appid><mch_id><![CDATA[10000100]]></mch_id><device_info><![CDATA[1000]]></device_info><nonce_str><![CDATA[TN55wO9Pba5yENl8]]></nonce_str><sign><![CDATA[BDF0099C15FF7BC6B1585FBB110AB635]]></sign><result_code><![CDATA[SUCCESS]]></result_code><openid><![CDATA[oUpF8uN95-Ptaags6E_roPHg7AG0]]></openid><is_subscribe><![CDATA[Y]]></is_subscribe><trade_type><![CDATA[MICROPAY]]></trade_type><bank_type><![CDATA[CCB_DEBIT]]></bank_type><total_fee>1</total_fee><fee_type><![CDATA[CNY]]></fee_type><transaction_id><![CDATA[1008450740201411110005820873]]></transaction_id><out_trade_no><![CDATA[1415757673]]></out_trade_no><attach><![CDATA[订单额外描述]]></attach><time_end><![CDATA[20141111170043]]></time_end><trade_state><![CDATA[SUCCESS]]></trade_state></xml>';
		}
		$aOrderQueryResult = WxPayResults::init($response, $sandboxnewSignkey);
		if($aOrderQueryResult['return_code'] != 'SUCCESS'){
			throw Yii::$app->buildError($aOrderQueryResult['return_msg']);
		}
		if($aOrderQueryResult['result_code'] != 'SUCCESS'){
			if($aOrderQueryResult['err_code'] == 'ORDERNOTEXIST'){
				$succesCode = static::QUERY_SUCC_CODE_FAIL;
			}else{
				$succesCode = static::QUERY_SUCC_CODE_PLAYING;
			}
			return false;
		}
		if($aOrderQueryResult['trade_state'] == 'SUCCESS'){
			$succesCode = static::QUERY_SUCC_CODE_SUCCESS;
			return $aOrderQueryResult;
		}
		$succesCode = static::QUERY_SUCC_CODE_PLAYING;
		return false;
	}
	
	public function sandboxnewGetNotifyResult(&$message){
		$sandboxnewSignkey = $this->_getSandboxnewSignkey();
		//获取通知的数据
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		//$xml = '<xml><appid><![CDATA[wx2421b1c4370ec43b]]></appid><attach><![CDATA[支付测试]]></attach><bank_type><![CDATA[CFT]]></bank_type><fee_type><![CDATA[CNY]]></fee_type><is_subscribe><![CDATA[Y]]></is_subscribe><mch_id><![CDATA[10000100]]></mch_id><nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str><openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid><out_trade_no><![CDATA[1409811653]]></out_trade_no><result_code><![CDATA[SUCCESS]]></result_code><return_code><![CDATA[SUCCESS]]></return_code><sign><![CDATA[B552ED6B279343CB493C5DD0D78AB241]]></sign><sub_mch_id><![CDATA[10000100]]></sub_mch_id><time_end><![CDATA[20140903131540]]></time_end><total_fee>1</total_fee><trade_type><![CDATA[JSAPI]]></trade_type><transaction_id><![CDATA[1004400740201409030005092168]]></transaction_id></xml>';
		Yii::info($xml);
		//如果返回成功则验证签名
		try{
			$aNotify = WxPayResults::init($xml, $sandboxnewSignkey);
		}catch(\umeworld\lib\ServerErrorHttpException $e){
			$message = $e->getMessage();
			return false;
		}
		if($aNotify['result_code'] != 'SUCCESS'){
			$message = $aNotify['err_code_des'];
			return false;
		}
		return $aNotify;
	}
	
	public function sandboxnewRefund($aPara){
		if(!isset($aPara['out_refund_no']) || !isset($aPara['total_fee']) || !isset($aPara['refund_fee']) || !(isset($aPara['transaction_id']) || isset($aPara['out_trade_no']))){
			throw Yii::$app->buildError('缺少必要参数，退款失败！');
		}
		$sandboxnewSignkey = $this->_getSandboxnewSignkey();
		$aParaTemp = [
			'appid' => $this->appId,
			'mch_id' => $this->mchId,
			'device_info' => 'WEB',
			'nonce_str' => $this->_makeRandStr(),
			'out_refund_no' => $aPara['out_refund_no'],
			'total_fee' => $aPara['total_fee'],
			'refund_fee' => $aPara['refund_fee'],
			'op_user_id' => $this->mchId,
			'transaction_id' => isset($aPara['transaction_id']) ? $aPara['transaction_id'] : '',
			'out_trade_no' => isset($aPara['out_trade_no']) ? $aPara['out_trade_no'] : '',
			//'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
		];
		$oWxPayRefund = new WxPayRefund($aParaTemp, $sandboxnewSignkey);
		$oWxPayRefund->setSign();
		$xml = $oWxPayRefund->toXml();
		$response = $this->_postXmlCurl($xml, 'https://api.mch.weixin.qq.com/sandboxnew/secapi/pay/refund', true);
		$aRefundResult = WxPayResults::init($response, $sandboxnewSignkey);
		if($aRefundResult['return_code'] != 'SUCCESS'){
			throw Yii::$app->buildError($aRefundResult['return_msg']);
		}elseif($aRefundResult['result_code'] != 'SUCCESS'){
			Yii::error($aRefundResult['err_code_des']);
			return false;
		}
		return $aRefundResult;
	}
	
	public function sandboxnewRefundQuery($aPara){
		if(!isset($aPara['transaction_id']) && !isset($aPara['out_trade_no']) && !isset($aPara['out_refund_no']) && !isset($aPara['refund_id'])){
			throw Yii::$app->buildError('缺少必要参数，退款失败！');
		}
		$sandboxnewSignkey = $this->_getSandboxnewSignkey();
		$aParaTemp = [
			'appid' => $this->appId,
			'mch_id' => $this->mchId,
			'device_info' => 'WEB',
			'nonce_str' => $this->_makeRandStr(),
			
			'transaction_id' => isset($aPara['transaction_id']) ? $aPara['transaction_id'] : '',
			'out_trade_no' => isset($aPara['out_trade_no']) ? $aPara['out_trade_no'] : '',
			'out_refund_no' => isset($aPara['out_refund_no']) ? $aPara['out_refund_no'] : '',
			'refund_id' => isset($aPara['refund_id']) ? $aPara['refund_id'] : '',
		];
		$oWxPayRefundQuery = new WxPayRefundQuery($aParaTemp, $sandboxnewSignkey);
		$oWxPayRefundQuery->setSign();
		$xml = $oWxPayRefundQuery->toXml();
		$response = $this->_postXmlCurl($xml, 'https://api.mch.weixin.qq.com/sandboxnew/pay/refundquery');
		$aRefundQueryResult = WxPayResults::init($response, $sandboxnewSignkey);
		debug($aRefundQueryResult, 11);
		if($aRefundQueryResult['return_code'] != 'SUCCESS'){
			throw Yii::$app->buildError($aRefundQueryResult['return_msg']);
		}elseif($aRefundQueryResult['result_code'] != 'SUCCESS'){
			Yii::error($aRefundQueryResult['err_code_des']);
			return false;
		}
		return $aRefundQueryResult;
	}
	
	public function sandboxnewDownloadbill($aPara){
		$aParaTemp = [
			'appid' => $this->appId,
			'mch_id' => $this->mchId,
			'device_info' => 'WEB',
			'nonce_str' => $this->_makeRandStr(),
			'bill_date' => $aPara['bill_date'],
			'bill_type' => $aPara['bill_type'],
			'tar_type' => $aPara['tar_type'],
		];
		$url = 'https://api.mch.weixin.qq.com/sandboxnew/pay/downloadbill';
		$sandboxnewSignkey = $this->_getSandboxnewSignkey();
		$oWxPayDownloadbill = new WxPayDownloadbill($aParaTemp, $sandboxnewSignkey);
		$oWxPayDownloadbill->setSign();
		$xml = $oWxPayDownloadbill->toXml();
		$response = $this->_postXmlCurl($xml, $url);
	}
}