<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.js.teacher-edit');
$this->registerAssetBundle('common\assets\FileAsset');
$this->setTitle('教师介绍');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new TeacherEdit();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.teacher-edit'); ?>',
			phpData:<?php echo json_encode($aTeacher); ?>,
			url:{
				uploadUrl:'<?php echo Url::to(['teacher/upload-profile']); ?>',
				saveUrl:'<?php echo Url::to(['teacher/save']); ?>',
				teacherHomeUrl :'<?php echo Url::to(['teacher/index']); ?>',
			}
		});
		oPage.show();
	});
</script>
