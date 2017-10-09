<?php
use umeworld\lib\Url;

$this->registerJsFile('@r.js.goods-list');
$this->registerJsFile('@r.js.tools-date');
$this->registerJsFile('@r.pack.umt-page-num');
$this->setTitle('服务列表');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new GoodsList();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.goods-list'); ?>',
			aGoodsStatus: <?php echo json_encode($aGoodsStatus); ?>,
			buildGoodsUrl: '<?php echo Url::to(['goods/show-add-goods']); ?>',
			goodsListUrl: '<?php echo Url::to(['goods/get-goods-list']); ?>',
			opUrl: '<?php echo Url::to(['goods/operate-goods']); ?>',
			editUrl: '<?php echo Url::to(['goods/show-edit-goods', 'goods_id'=>'_goods_id']); ?>'
		});
		oPage.show();
	});
</script>
