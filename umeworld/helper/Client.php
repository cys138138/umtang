<?php
namespace umeworld\helper;

use Yii;

/**
 * 客户端类
 * @author 黄文非
 * @property-read bool $isComputer 是否PC客户端
 */
class Client extends \yii\base\Object{
	/**
	 * 客户端类型,电脑
	 */
	const CLIENT_PC = 1;

	/**
	 * 手机
	 */
	const CLIENT_PHONE = 2;

	/**
	 * 判断客户端是否为电脑
	 * @return boolean
	 */
	public function getIsComputer() {
		$agent = strtolower(Yii::$app->request->userAgent);
		if(!$agent){
			return false;
		}

		if(stripos($agent, 'android') || stripos($agent, 'mobile') || stripos($agent, 'ipad') || stripos($agent, 'iphone') || stripos($agent, 'phone') || stripos($agent, 'opera mini') || stripos($agent, 'netfront') || stripos($agent, 'midp-2.0') || stripos($agent, 'ucweb') || stripos($agent, 'windows ce') || stripos($agent, 'symbianos')){
			return false;
		}
		return true;
	}

	/**
	 * 获取客户端类型
	 * @return int
	 * @see CLIENT_X常量
	 */
	public function getType(){
		return $this->isComputer ? self::CLIENT_PC : self::CLIENT_PHONE;
	}

	/**
	 * 判断客户端是否安卓系统
	 * @return boolean
	 * @author 黄文非
	 */
	public function getIsAndroid() {
		$agent = strtolower(Yii::$app->request->userAgent);
		if(!$agent){
			return false;
		}else{
			return strpos($agent, 'android') >= 0;
		}
	}

	/**
	 * 判断客户端是否IOS系统
	 * @return boolean
	 * @author 黄文非
	 */
	public function getIsIos() {
		$agent = strtolower(Yii::$app->request->userAgent);
		if(!$agent){
			return false;
		}else{
			return strpos($agent, 'iphone') >= 0 || strpos($agent, 'ipad') >= 0;
		}
	}
}