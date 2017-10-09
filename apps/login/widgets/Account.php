<?php
namespace login\widgets;

use Yii;

class Account extends \yii\base\Widget{
	public function run(){
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$shopName = '';
		if($mCommercialTenant){
			$shopName = $mCommercialTenant->getNewRegisterName();
		}
		return $this->render('account', ['shopName' => $shopName]);
	}
}
