<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.js.teacher');
$this->setTitle('教师介绍');
?>
<div id="wrapPage"></div>
<script>
	$(function(){
		var oPage = new Teacher();
		oPage.config({
			selector: '#wrapPage',
			templateUrl: '<?php echo Yii::getAlias('@r.tpl.teacher'); ?>',
			phpData:<?php echo json_encode($aTeacherList); ?>,
			url:{
				editUrl:'<?php echo Yii::$app->urlManagerTenant->createUrl(['teacher/show-edit', 'id' => 'tId', 'createTime' => 'time']) ?>',
				delUrl:'<?php echo Url::to(['teacher/delete']); ?>',
				orderUrl:'<?php echo Url::to(['teacher/set-order']); ?>',
			}
		});
		oPage.show();
	});
</script>
