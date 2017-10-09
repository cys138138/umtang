<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.activate');
$this->registerJsFile('@r.js.tools-date');
$this->setTitle('服务劵激活');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new Activate();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.activate'); ?>',
			infoUrl: '<?php echo Url::to(['goods-volume/get-activate-info']); ?>',
			activateUrl: '<?php echo Url::to(['goods-volume/activate']); ?>'
		});
		oPage.show();
	});
</script>
