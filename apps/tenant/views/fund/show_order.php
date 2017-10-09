<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.encash-list');
$this->registerJsFile('@r.js.tools-date');
$this->registerJsFile('@r.pack.umt-page-num');
$this->setTitle('资金池');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new EncashList();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.encash-order'); ?>',
			aTimeStatus: <?php echo json_encode($aTimeStatus ); ?>,
			aOrderStatus: <?php echo json_encode($aOrderStatus ); ?>,
			aStatusDesc: <?php echo json_encode($aOrderAllStatus ); ?>,
			encashUrl: '<?php echo Url::to(['fund/show-home']); ?>',
			orderUrl: '<?php echo Url::to(['fund/show-order']); ?>',
			historyUrl: '<?php echo Url::to(['fund/show-extract']); ?>',
			orderListUrl: '<?php echo Url::to(['fund/get-order-list']); ?>',
		});
		oPage.show();
	});
</script>