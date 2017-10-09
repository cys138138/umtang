<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.goods-info');
$this->registerAssetBundle('common\assets\EditorAsset');
$this->registerAssetBundle('common\assets\TimePickerAsset');
$this->setTitle('新建服务');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new GoodsInfo();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.goods-info'); ?>',
			aType: <?php echo json_encode($aType); ?>,
			submitUrl: '<?php echo Url::to(['goods/add-goods']); ?>',
			uploadUrl: '<?php echo Url::to(['goods/upload-file']); ?>'
		});
		oPage.show();

		UmtEditor.uploadUrl = '<?php echo Url::to(['goods/upload-file']); ?>';
	});
</script>