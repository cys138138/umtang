<?php
namespace umeworld\lib;

/**
 * 服务器通用错误异常
 * @author 黄文非
 */
class ServerErrorHttpException extends \yii\web\ServerErrorHttpException{
	/**
	 * @var bool 是否要将错误消息发送给用户
	 */
	public $isSendToUser = false;

	public $data = null;
	/**
	 * 异常初始化
	 * @param string $message 异常消息
	 * @param bool $isSendToUser 是否显示给用户,否则显示系统通用错误提示
	 * @param mixed $xData 异常附加数据
	 * @param int $errorCode 错误代码,在500到599以内
	 */
	public function __construct($message, $isSendToUser = false, $xData = null, $errorCode = 500){
		parent::__construct($message, $errorCode);
		if($errorCode){
			$this->statusCode = $errorCode;
		}
		$this->isSendToUser = $isSendToUser;
		if($xData){
			$this->data = $xData;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function __toString(){
		$string = parent::__toString();
		if($this->data){
			$string .= "\n\nAdditional Information:\n" . var_export($this->data, 1);
		}
		return $string;
	}
}