<?php
namespace umeworld\lib;
use Yii;

/**
 * Cookie处理类
 * @author 黄文非
 */
class Cookie{
	/**
	 * 设置一个cookie
	 * @param string $name cookie的名称
	 * @param string|int $value 要被加密的值
	 * @param int $expire 过期时间的时间戳
	 * @param string $path 所属的目录
	 * @param string $domain 所属的域名
	 * @return bool
	 */
	public static function set($name, $value, $expire = 0, $path = '/', $domain = ''){
		if(!$domain){
			$domain = Yii::$app->domain;
		}

		$_COOKIE[$name] = $value;
		return setcookie($name, $value, $expire, $path, $domain);
    }

	/**
	 * 获取一个指定KEY的Cookie
	 * @param string $name cookie的名称
	 * @return string|int
	 */
	public static function get($name){
		if(self::isSetted($name)){
			return $_COOKIE[$name];
		}else{
			return null;
		}
	}

	/**
	 * 设置一个加密后的cookie值
	 * @param string $name cookie的名称
	 * @param string|int $value 要被加密的值
	 * @param int $expire 过期时间的时间戳
	 * @param string $path 所属的目录
	 * @param string $domain 所属的域名
	 * @return bool
	 */
	public static function setEncrypt($name, $value, $expire = 0, $path = '/', $domain = ''){
		if(!$domain){
			$domain = Yii::$app->domain;
		}
		return self::set($name, Xxtea::encrypt($value), $expire, $path, $domain);
    }

	/**
	 * 获取一个指定KEY的Cookie值并且解密后返回
	 * @param string $name cookie的名称
	 * @return string|int
	 */
	public static function getDecrypt($name){
		return self::isSetted($name) ? Xxtea::decrypt(self::get($name)) : null;
	}


	/**
	 * 设置一个加密后的cookie值,并且该值无法解密
	 * @param string $name cookie的名称
	 * @param string|int $value 值
	 * @param int $expire 过期时间的时间戳
	 * @param string $path 所属的目录
	 * @param string $domain 所属的域名
	 * @return bool
	 */
	public static function setXcrypt($name, $value, $expire = 0, $path = '/', $domain = ''){
		if(!$domain){
			$domain = Yii::$app->domain;
		}

		return self::set($name, Xxtea::xcrypt($value), $expire, $path, $domain);
	}

	/**
	 * 删除cookie
	 * @param string $name 要删除的cookie名称
	 * @param string $path 要删除的cookie所属路径
	 * @param string $domain 要删除的cookie所属域名
	 * @return boolean
	 */
	public static function delete($name = '', $path = '/', $domain = ''){
		if(!$domain){
			$domain = Yii::$app->domain;
		}

		if($name == ''){
			foreach($_COOKIE as $key => $val){
				setcookie($key, '', 0, $path, $domain);
			}
			unset($_COOKIE);
			return true;
		}else{
			unset($_COOKIE[$name]);
			return self::set($name, '', 0, $path, $domain);
		}
	}

	/**
	 * 判断一个cookie是否存在
	 * @param string $name cookie的名称
	 * @return bool
	 */
	public static function isSetted($name){
		return isset($_COOKIE[$name]);
	}
}
