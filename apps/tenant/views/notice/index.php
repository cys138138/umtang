<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.notice');
$this->registerJsFile('@r.js.tools-date');
$this->registerJsFile('@r.pack.umt-page-num');
$this->setTitle('通知');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new Notice();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.notice'); ?>',
			noticeListUrl: '<?php echo Url::to(['notice/get-list']); ?>',
			readUrl: '<?php echo Url::to(['notice/set-read']); ?>'
		});
		oPage.show();
	});
</script>