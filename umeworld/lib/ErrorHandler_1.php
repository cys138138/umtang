<?php
namespace umeworld\lib;

use Yii;

class ErrorHandler extends \yii\web\ErrorHandler{

	/**
	 * 记录异常到日志中
	 * @param \Exception|\ErrorException $oException 异常对象
	 */
    public function logException($oException)
    {
        $category = get_class($oException);
        if ($oException instanceof HttpException) {
            $category = 'yii\\web\\HttpException:' . $oException->statusCode;
        } elseif ($oException instanceof \ErrorException) {
            $category .= ':' . $oException->getSeverity();
		}else if($oException instanceof \Exception){
			if($errorCode = $oException->getCode()){
				$category .= ':' . $errorCode;
			}
		}

		\Yii::error((string) $oException, $category);
    }
}