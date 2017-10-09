<?php
use umeworld\lib\Url;
use manage\assets\EsAsset;

EsAsset::register($this, EsAsset::SCENE_EDIT);
?>
<script type="text/javascript">
$(function(){
	var aConfig = {
		imageBaseUrl : '<?php echo Yii::getAlias('@r.url'); ?>',
		shuffleChoise : false,

		showAllComplexItem : true
	};
	<?php if($editSubject){ ?>
	$.extend(aConfig, {
		imageUploadUrl : '<?php echo Url::to(['common/upload-es-image', 'subject' => $editSubject]); ?>'
	});
	<?php } ?>
	Esp.config(aConfig);
});
</script>