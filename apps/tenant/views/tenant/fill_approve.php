<?php
use common\model\CommercialTenant;
use common\model\CommercialTenantApprove;
use umeworld\lib\Url;
$this->registerJsFile('@r.js.approve-tenant');
$this->registerJsFile('@r.js.tools-validata');
$this->registerAssetBundle('common\assets\FileAsset');
$this->setTitle('商户认证');
$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
if($mCommercialTenant){
	$mTenantApprove = $mCommercialTenant->getMTenantApprove();
	$string = json_encode($mTenantApprove->tenant_info);
	if($mCommercialTenant->online_status != CommercialTenant::ONLINE_STATUS_ONLINE && $mTenantApprove->tenant_approve_status == CommercialTenantApprove::STATUS_ONT_PASS_APPROVE && strpos($string, 'reason') === false){
		Yii::$app->response->redirect(Url::to(['tenant-shop/show-fill-tenant-shop']));
	}
}
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new ApproveTenant();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.approve-tenant'); ?>',
			phpData : <?php echo json_encode($aCommercialTenantAuthInfo)?>,
			url:{
				saveUrl: '<?php echo Url::to(['tenant/save-fill-approve']); ?>',
				uploadPicUrl: '<?php echo Url::to(['tenant/upload-photo']); ?>',
				shopUrl:'<?php echo Url::to(['tenant-shop/show-fill-tenant-shop']); ?>',
			}
		});
		oPage.show();
	});
</script>
