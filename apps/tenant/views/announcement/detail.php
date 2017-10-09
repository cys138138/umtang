<?php

if(!Yii::$app->client->isComputer){
	$this->registerAssetBundle('tenant\assets\MobileAsset');
}else{
	$this->registerAssetBundle('tenant\assets\CommonAsset');
}
$this->beginPage(); 
?>
<!doctype html>
<html lang="zn-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
   	<meta name="format-detection" content="telephone=no">
   	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title><?php echo $aAnnouncement['title']; ?></title>
</head>
<?php $this->head(); ?>
<body>
<?php $this->beginBody(); ?>
<?php echo tenant\widgets\Header::widget(); ?>
<div class="umt-main umt-content">
	<div class="umt-notice-detail">
		<div class="title"><?php echo $aAnnouncement['title']; ?></div>
		<div class="content"><?php echo $aAnnouncement['content']; ?></div>
		<div class="date">优满堂团队</div>
		<div class="date"><?php echo date('Y-m-d', $aAnnouncement['create_time']); ?></div>
	</div>
</div>
<div id="footer">
     <div class="copyright"><span>© 优满堂(umtang.com) 2016-2017 All Rights Reserved </span>备案号：粤ICP备13000602号-6 Copyright</div>
</div>
<?php $this->endBody(); ?>
</body>
</html>

<?php $this->endPage();
