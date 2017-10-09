<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.goods-info');
$this->registerJsFile('@r.js.tools-date');
$this->registerAssetBundle('common\assets\EditorAsset');
$this->registerAssetBundle('common\assets\TimePickerAsset');
$this->setTitle('编辑服务');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new GoodsInfo();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.goods-info'); ?>',
			aType: <?php echo json_encode($aType); ?>,
			aGoodsInfo: <?php echo json_encode($aGoodsInfo); ?>,
			submitUrl: '<?php echo Url::to(['goods/edit-goods']); ?>',
			albumUrl: '<?php echo Url::to(['goods/show-goods-photo', 'goods_id'=>'goods_id']); ?>'
		});
		oPage.show();

		UmtEditor.uploadUrl = '<?php echo Url::to(['goods/upload-file']); ?>';
	});
</script>