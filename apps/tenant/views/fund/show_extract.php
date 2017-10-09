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
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.encash-history'); ?>',
			encashment: '<?php echo $hasGetPrice; ?>',
			extracting: '<?php echo $waitgetPrice; ?>',
			encashUrl: '<?php echo Url::to(['fund/show-home']); ?>',
			orderUrl: '<?php echo Url::to(['fund/show-order']); ?>',
			historyUrl: '<?php echo Url::to(['fund/show-extract']); ?>',
			orderListUrl: '<?php echo Url::to(['fund/get-extract-list']); ?>',
		});
		oPage.show();
	});
</script>