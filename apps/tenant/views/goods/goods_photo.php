<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.album');
$this->registerJsFile('@r.pack.umt-page-num');
$this->registerAssetBundle('common\assets\FileAsset');
$this->setTitle('服务列表');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new Album();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.album'); ?>',
			goodsId: '<?php echo $goodsId; ?>',
			listUrl: '<?php echo Url::to(['goods/get-goods-photo-list']); ?>',
			uploadUrl: '<?php echo Url::to(['goods/upload-file']); ?>',
			addUrl: '<?php echo Url::to(['goods/add-photo']); ?>',
			setUrl: '<?php echo Url::to(['goods/operate-photo']); ?>',
			delUrl: '<?php echo Url::to(['goods/operate-photo']); ?>',
			editUrl: '<?php echo Url::to(['goods/show-edit-goods', 'goods_id'=>$goodsId]); ?>'
		});
		oPage.show();
	});
</script>