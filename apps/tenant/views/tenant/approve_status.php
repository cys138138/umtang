<?php
use common\model\CommercialTenant;
use common\model\CommercialTenantApprove;
use umeworld\lib\Url;

$this->setTitle('审核状态');
$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
if($mCommercialTenant){
	$mTenantApprove = $mCommercialTenant->getMTenantApprove();
	$tenantString = json_encode($mTenantApprove->tenant_info);
	$shopString = json_encode($mTenantApprove->shop_info);
	if($mCommercialTenant->online_status == CommercialTenant::ONLINE_STATUS_ONLINE){
		Yii::$app->response->redirect(Url::to(['site/show-index']));
	}elseif($mTenantApprove->tenant_approve_status == CommercialTenantApprove::STATUS_ONT_PASS_APPROVE && !(strpos($tenantString, 'reason') === false)){
		Yii::$app->response->redirect(Url::to(['tenant/show-fill-approve']));
	}elseif($mTenantApprove->shop_approve_status == CommercialTenantApprove::STATUS_ONT_PASS_APPROVE && !(strpos($shopString, 'reason') === false)){
		Yii::$app->response->redirect(Url::to(['tenant-shop/show-fill-tenant-shop']));
	}
}
?>
<div id="wrapPage">
	<div class="step pass-2">
	    <div>商户认证<i class="ok"></i></div><div>商铺信息<i class="ok"></i></div><div class="active">审核状态</div>
	</div>
	<div class="main-content">
	    <div class="wait">
	        <i class="wait-icon"></i>
	        <div>审核结果会以短信形式通知</div>
	        <div>若有问题，可拨打全国统一服务热线：400-900-9390</div>
	    </div>
	</div>
</div>


