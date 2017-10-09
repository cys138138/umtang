<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.js.order');
$this->registerJsFile('@r.js.tools-date');
$this->registerJsFile('@r.pack.umt-page-num');
$this->setTitle('订单中心');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new Order();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.order'); ?>',
			aTimeStatus: <?php echo json_encode($aTimeStatus); ?>,
			aOrderStatus: <?php echo json_encode($aOrderStatus); ?>,
			aTabType: <?php echo json_encode($aOrderType); ?>,
			aStatusDesc: <?php echo json_encode($aOrderAllStatus); ?>,
			orderListUrl: '<?php echo Url::to(['order/get-order-list']); ?>'
		});
		oPage.show();
	});
</script>
