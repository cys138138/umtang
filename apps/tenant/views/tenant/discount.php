<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.js.shop-cheap');
$this->registerJsFile('@r.js.tools-validata');
$this->setTitle('优惠信息');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new ShopCheap();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.shop-cheap'); ?>',
			phpData:<?php echo json_encode($aDiscountInfo)?>, 
			pageUrl:{
				info:'<?php echo Url::to(['tenant-shop/show-shop-info']); ?>',
				apply:'<?php echo Url::to(['tenant/show-approve-info']); ?>'
			},
			url:{
				saveUrl:'<?php echo Url::to(['tenant/save-discount-info']); ?>'
			}
		});
		oPage.show();
	});
</script>
