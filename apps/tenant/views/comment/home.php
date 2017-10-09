<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.evaluate-list');
$this->registerJsFile('@r.js.tools-date');
$this->registerJsFile('@r.pack.umt-page-num');
$this->setTitle('评价列表');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new EvaluateList();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.evaluate-list'); ?>',
			aTabType: <?php echo json_encode($aCommentStatus); ?>,
			listUrl: '<?php echo Url::to(['comment/get-comment-list']); ?>',
			detailUrl: '<?php echo Url::to(['comment/comment-details', 'order_id' => 'order_id']); ?>'
		});
		oPage.show();
	});
</script>