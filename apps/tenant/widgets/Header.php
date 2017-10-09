<?php
namespace tenant\widgets;

use Yii;
use common\model\CommercialTenantNotice;

class Header extends \yii\base\Widget{
	public function run(){
		$aNotice = CommercialTenantNotice::findOne(['tenant_id' => Yii::$app->commercialTenant->id, 'is_read' => 0]);
		$hasUnreadMessage = $aNotice ? 1 : 0;

		$shopName = Yii::$app->commercialTenant->getIdentity()->getNewRegisterName();
		
		return $this->render('header', [
			'hasUnreadMessage' => $hasUnreadMessage,
			'shopName' => $shopName
			]);
	}
}