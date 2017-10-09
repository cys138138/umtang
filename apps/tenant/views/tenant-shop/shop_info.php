<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.js.shop-info');
$this->registerJsFile('@r.js.tools-validata');
$this->registerAssetBundle('common\assets\FileAsset');
$this->registerAssetBundle('common\assets\MapAsset');
$this->setTitle('商铺信息');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new ShopInfo();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.shop-info'); ?>',
			phpData:{
				aCommercialTenantShop :<?php echo json_encode($aCommercialTenantShop); ?>,
				aModifyLimitCount: <?php echo json_encode($aModifyLimitCount); ?>,
				aRemainModifyCount: <?php echo json_encode($aRemainModifyCount); ?>,
				aCommercialTenantTypeList: <?php echo json_encode($aCommercialTenantTypeList); ?>,
			},
			adcode : <?php echo json_encode($adcode)?>,
			pageUrl:{
				apply:'<?php echo Url::to(['tenant/show-approve-info']); ?>',
				cheap:'<?php echo Url::to(['tenant/show-discount-info']); ?>'
			},
			url:{
				headerUrl:'<?php echo Url::to(['tenant-shop/upload-profile']); ?>',
				saveUrl:'<?php echo Url::to(['tenant-shop/save-shop-info']); ?>',
			}
		});
		oPage.show();
	});
</script>
