<?php
namespace umeworld\lib;

use Yii;
use umeworld\lib\ServerErrorHttpException;
use umeworld\lib\BaseErrorException;

class ErrorAction extends \yii\web\ErrorAction{
	public function run(){
		//$this->id = 'error_mobile'; 通过设置此属性可以配合客户端不同来设置不同的错误模板
        if (($oException = Yii::$app->getErrorHandler()->exception) === null) {
			return '发生未知错误,请联系站点管理员!';
        }
        $message = $oException->getMessage();
		$isSendToUser = false;
		if(!YII_ENV_PROD){
			$isSendToUser = true;
		}elseif(
			($oException instanceof ServerErrorHttpException
			|| $oException instanceof BaseErrorException)
			&& $oException->isSendToUser
		){
			$isSendToUser = true;
		}

        if (Yii::$app->getRequest()->getIsAjax()){
			if($isSendToUser){
				return $message;
			}else{
				return Yii::$app->ui->getTips('error.common');
			}

        } else {
			/*$this->controller->id = 'error';
			$errorView = '404';
			if($oException instanceof ServerErrorHttpException){
				$errorView = '500';
			}*/

            return Yii::$app->view->renderFile('@p.system_view/error.php', [
                'message' => $message,
                'isSendToUser' => $isSendToUser,
                'oException' => $oException,
            ]);
        }
	}
}