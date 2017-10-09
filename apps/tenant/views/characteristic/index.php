<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.js.server');
$this->setTitle('特色服务');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new Server();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.server'); ?>',
			phpData:{
				listData :<?php echo json_encode($aCharacteristicList); ?>,
				infoData: <?php echo json_encode($aCommercialTenantCharacteristicServiceRelation); ?>
			},
			url:{
				addUrl:'<?php echo Url::to(['characteristic/add']); ?>',
				saveUrl:'<?php echo Url::to(['characteristic/save-setting']); ?>',
			}
		});
		oPage.show();
	});
</script>
