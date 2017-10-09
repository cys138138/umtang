<?php
namespace umeworld\lib;

use Yii;

/**
 * Http请求类
 * @author 黄文非
 */
class Http extends \yii\base\Component{
	const CONTENT_TYPE_HTML = 'text/html';

	const CONTENT_TYPE_JSON = 'application/json';

	/**
	 * 要请求的URL
	 * @var string
	 */
	public $url = '';

	/**
	 * 最大的请求等待时间
	 * @var int
	 */
	public $timeOut = 30;

	/**
	 * 请求失败的重试次数
	 * @var int
	 */
	public $retryTimes = 1;

	private $_aHeaders = [];

	private $_acceptContentType = '';

	/**
	 * 构造方法
	 * @param type $url 要请求的URL
	 */
	public function __construct($url){
		$this->url = $url;
	}

	/**
	 * 增加HTTP请求头
	 * @param array $aHeaders 以key=>value形式表达的header集合
	 */
	public function addHeaders(array $aHeaders){
		$this->_aHeaders = array_merge($this->_aHeaders, $aHeaders);
	}

	/**
	 * 设置要接收的内容类型
	 * @param string $type 详见[[CONTENT_TYPE_HTML]],[[CONTENT_TYPE_JSON]]
	 */
	public function setAcceptType($type){
		$this->_acceptContentType = $type;
	}

	/**
	 * 发送post请求
	 * @param array $aParams 要POST的数据,键值对
	 * @return string 响应结果
	 */
	public function post($aParams = []){
		return $this->_request('post', $aParams);
	}


	/**
	 * 发送get请求
	 * @param array $aParams 要附加到url上的query_string的参数,键值对
	 * @return string 响应结果
	 */
	public function get($aParams = []){
		return $this->_request('get', $aParams);
	}

	/**
	 * 发送一个请求
	 * @param string $method 请求方法:get|post|put|delete
	 * @param array $aParams 要POST的数据,键值对
	 * @return string 响应结果
	 * @throws \Exception
	 */
	protected function _request($method, $aParams = []){
		$url = $this->url;
		$retryTimes = $this->retryTimes;
		$timeOut = $this->timeOut;

		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, $timeOut);

		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlHandle, CURLOPT_HEADER, 0);
		$this->_aHeaders && curl_setopt_array($curlHandle, $this->_aHeaders);

		$paramStr = '';
		if($aParams){
			if(!is_array($aParams)){
				throw new \Exception('错误的请求参数类型');
			}
			$paramStr = http_build_query($aParams);
		}

		if($method == 'post'){
			curl_setopt($curlHandle, CURLOPT_POST, 1);
			curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $paramStr);
		}elseif($method == 'get' && $paramStr){
			$aUrl = parse_url($url);
			if(empty($aUrl['query'])){
				$aUrl['query'] = $paramStr;
			}else{
				$aUrl['query'] .= '&' . $paramStr;
			}

			if(empty($aUrl['port'])){
				$aUrl['port'] = '';
			}

			if(empty($aUrl['path'])){
				$aUrl['path'] = '';
			}

			if(empty($aUrl['fragment'])){
				$aUrl['fragment'] = '';
			}

			$url = $aUrl['scheme'] . '://' . $aUrl['host'];
			if($aUrl['port']){
				$url .= ':' . $aUrl['port'];
			}
			$url .= $aUrl['path'] . '?' . $aUrl['query'];

			if($aUrl['fragment']){
				$url .=  '#' . $aUrl['fragment'];
			}
		}

		//设置选项，包括URL
		curl_setopt($curlHandle, CURLOPT_URL, $url);
		//执行并获取HTML文档内容
		$output = curl_exec($curlHandle);
		if(!$output){
			$errorOutput = curl_error($curlHandle);
			curl_close($curlHandle);
			throw new \ErrorException($errorOutput);
		}
		//释放curl句柄
		curl_close($curlHandle);
		return $this->_formatContent($output);
	}

	/**
	 * 格式化内容
	 * @param string $content
	 * @return mixed 看格式是什么了
	 */
	private function _formatContent($content){
		$result = '';
		switch($this->_acceptContentType){
			case static::CONTENT_TYPE_JSON:
				$result = json_decode($content, true);
				if(json_last_error()){
					throw Yii::$app->buildError('解析json失败', false, $content);
				}
				break;

			default:
				$result = $content;
				break;
		}

		return $result;

	}
}