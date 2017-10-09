<?php
use common\model\CommercialTenant;
use common\model\CommercialTenantApprove;
use umeworld\lib\Url;
$this->registerJsFile('@r.js.approve-shop');
$this->registerJsFile('@r.js.tools-validata');
$this->registerAssetBundle('common\assets\MapAsset');
$this->registerAssetBundle('common\assets\FileAsset');

$this->setTitle('商铺信息');
$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
if($mCommercialTenant){
	$mTenantApprove = $mCommercialTenant->getMTenantApprove();
	$tenantString = json_encode($mTenantApprove->tenant_info);
	$shopString = json_encode($mTenantApprove->shop_info);
	if($mCommercialTenant->online_status != CommercialTenant::ONLINE_STATUS_ONLINE && $mTenantApprove->shop_approve_status == CommercialTenantApprove::STATUS_ONT_PASS_APPROVE && strpos($shopString, 'reason') === false){
		if($mCommercialTenant->online_status != CommercialTenant::ONLINE_STATUS_IN_APPROVE && strpos($tenantString, 'reason') === false){
			$mCommercialTenant->set('online_status', CommercialTenant::ONLINE_STATUS_IN_APPROVE);
			$mCommercialTenant->save();
		}
		Yii::$app->response->redirect(Url::to(['tenant/show-approve-status']));
	}
}
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new ApproveShop();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.approve-shop'); ?>',
			phpData : <?php echo json_encode($aCommercialTenantShop)?>,
			adcode : <?php echo json_encode($adcode)?>,
			commercialTenantTypeList:<?php echo json_encode($aCommercialTenantTypeList)?>,
			url:{
				saveUrl: '<?php echo Url::to(['tenant-shop/save-fill-tenant-shop']); ?>',
				uploadPicUrl: '<?php echo Url::to(['tenant/upload-photo']); ?>',
				uploadShopPicUrl: '<?php echo Url::to(['photo/upload']); ?>',
				delShopPicUrl: '<?php echo Url::to(['photo/delete']); ?>',
				statusUrl:'<?php echo Url::to(['tenant/show-approve-status']); ?>',
			}
		});
		oPage.show();
	});
</script>
