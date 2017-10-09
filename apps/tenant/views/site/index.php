<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.index');
$this->registerJsFile('@r.js.tools-date');
$this->registerJsFile('@r.pack.umt-page-num');
$this->setTitle('首页');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new Index();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.index'); ?>',
			aStatus: <?php echo json_encode($aStatisticsInfo); ?>,
			notifyListUrl: '<?php echo Url::to(['announcement/get-list']); ?>',
			notifyDetailUrl: '<?php echo Url::to(['announcement/show-detail', 'id'=>'_id']); ?>'
		});
		oPage.show();
	});
</script>