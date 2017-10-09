<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.album');
$this->registerJsFile('@r.pack.umt-page-num');
$this->registerAssetBundle('common\assets\FileAsset');
$this->setTitle('商户相册');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new Album();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.album'); ?>',
			listUrl: '<?php echo Url::to(['photo/get-list']); ?>',
			uploadUrl: '<?php echo Url::to(['photo/upload']); ?>',
			setUrl: '<?php echo Url::to(['photo/set-cover']); ?>',
			delUrl: '<?php echo Url::to(['photo/delete']); ?>'
		});
		oPage.show();
	});
</script>
