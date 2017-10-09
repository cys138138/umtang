<?php
namespace umeworld\lib;

/**
 * 基础的错误异常
 * @author 黄文非
 */
class BaseErrorException extends \yii\base\UserException{
	/**
	 * @var int 异常代码
	 */
	public $statusCode = 599;

	/**
	 * @var bool 是否将这个异常的消息打印给用户看
	 */
	public $isSendToUser = false;
}