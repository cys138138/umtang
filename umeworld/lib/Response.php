<?php
namespace umeworld\lib;

use Yii;

class Response extends \yii\web\Response{
	public function __construct($message = '', $status = 0, $xData = '') {
		parent::__construct();
		$aData = [
			'msg' => $message,
			'status' => $status,
			'notice' => Yii::$app->notifytion->aData,
			'data' => $xData,
			'token' => Yii::$app->request->csrfToken,
		];

		$oRequest = Yii::$app->request;
		$isAjax = $oRequest->isAjax || $oRequest->post('_is_ajax') || $oRequest->get('_is_ajax');
		if(!Yii::$app->ui->isRequestByPageManager && $isAjax){
			$this->format = self::FORMAT_JSON;
		}else{
			$this->format = self::FORMAT_HTML;
			$aData = Yii::$app->view->renderFile('@p.system_view/response.php', [
				'msg' => $message,
				'status' => $status,
				'xData' => $xData,
			]);
		}

		$this->data = $aData;
	}
}