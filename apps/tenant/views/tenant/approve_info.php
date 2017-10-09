<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.js.shop-apply');
$this->registerJsFile('@r.js.tools-validata');
$this->registerAssetBundle('common\assets\FileAsset');
$this->setTitle('认证信息');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new ShopApply();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.shop-apply'); ?>',
			phpData : <?php echo json_encode($aCommercialTenantAuthInfo)?>,
			countData : <?php echo json_encode($aRemainModifyCount)?>,
			pageUrl:{
				info:'<?php echo Url::to(['tenant-shop/show-shop-info']); ?>',
				cheap:'<?php echo Url::to(['tenant/show-discount-info']); ?>'
			},
			url:{
				bankPicUrl:'<?php echo Url::to(['tenant/upload-photo']); ?>',
				saveUrl:'<?php echo Url::to(['tenant/save-approve-info']); ?>',
				sendCoreUrl:'<?php echo Url::to(['tenant/send-mobile-verify-code']); ?>',
				bindPhoneUrl:'<?php echo Url::to(['tenant/bind-mobile']); ?>',
			}
		});
		oPage.show();
	});
</script>
