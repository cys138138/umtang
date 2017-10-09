<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.evaluate-detail');
$this->registerJsFile('@r.js.tools-date');
$this->setTitle('评价详情');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new EvaluateDetail();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.evaluate-detail'); ?>',
			aInfo: <?php echo json_encode($aCommentinfo); ?>,
			replyUrl: '<?php echo Url::to(['comment/reply-comment']); ?>'
		});
		oPage.show();
	});
</script>