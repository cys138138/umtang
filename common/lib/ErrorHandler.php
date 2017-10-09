<?php
namespace common\lib;

use Yii;

class ErrorHandler extends \umeworld\lib\ErrorHandler{
	/**
	 * @inheritdoc
	 */
	public function logException($oException){
		if($oException instanceof \yii\base\ErrorException){
			$message = $oException->getMessage();
			if(strstr($message, 'fsockopen(): unable to connect to')
				&& strstr($oException->getFile(), 'common/xxt/gd/InterfaceGd.php')){
				return;
			}elseif(strstr($message, 'unserialize(): Error at offset')){
				/*$oUrlErr = Yii::$app->buildError('xx', false, [
					'no_rules' => !Yii::$app->urlManager->rules,
				]);
				\Yii::error((string)$oException, 'unserialize');
				\Yii::error((string)$oUrlErr, 'unserialize');*/
				return;
			}
		}

		parent::logException($oException);
	}
}