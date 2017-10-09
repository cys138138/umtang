<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.encash');
$this->setTitle('资金池');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new Encash();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.encash'); ?>',
			aInfo: <?php echo json_encode($aStatisticsInfo); ?>,
			aFund: <?php echo json_encode($aFundInfo); ?>,
			aLimit: <?php echo json_encode($aLimt); ?>,
			encashUrl: '<?php echo Url::to(['fund/show-home']); ?>',
			orderUrl: '<?php echo Url::to(['fund/show-order']); ?>',
			historyUrl: '<?php echo Url::to(['fund/show-extract']); ?>',
			verifyUrl: '<?php echo Url::to(['fund/get-mobile-code']); ?>',
			moneyUrl: '<?php echo Url::to(['fund/extract-money']); ?>'
		});
		oPage.show();
	});
</script>