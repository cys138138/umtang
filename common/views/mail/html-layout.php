<?php
$contractUrl = Yii::$app->urlManagerAbout->createUrl(['site/contract']);
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo $this->title; ?></title>
</head>
<body style="margin:0;padding:0;">
<div style="color:#f30;border-bottom:2px solid #f30;padding:15px 30px;margin:0;">
</div>
<div style="font-size:16px;color:#000;font-family:Microsoft Yahei;margin:0;padding:30px;border-bottom:2px solid #f30;">
	<?php echo $content; ?>
	<div style="font-size:16px;color:#444;margin-bottom:30px;">UMFun团队<br /><?php echo date('Y年n月j日 H:i:s', NOW_TIME); ?></div>
	<div style="color:#aaa;font-size:12px;">
		出于安全考虑，以上内容如果非您本人，请不要理会此邮件，我们对此为你带来的不便表示歉意！<br />这是一封由机器人自动发送的邮件，请勿回复该邮件。<br />若有任何疑问，请与我们取得联系：<a href="<?php echo $contractUrl; ?>">联系我们</a><br />
	</div>
</div>
<div style="height:50px;padding:20px 30px;font-size:12px;color:#666;">
	<div>
		<a href="<?php echo Yii::$app->urlManagerLogin->createUrl(['site/index']); ?>" style="color:#666;text-decoration:none;">UMFun</a> |
		<a href="<?php echo Yii::$app->urlManagerService->createUrl(['site/index']); ?>" style="color:#666;text-decoration:none;">用户反馈</a> |
		<a href="<?php echo Yii::$app->urlManagerAbout->createUrl(['site/index']); ?>" style="color:#666;text-decoration:none;">关于优满分</a> |
		<a href="<?php echo $contractUrl; ?>" style="color:#666;text-decoration:none;">联系我们</a>
	</div>
	<div style="color:#666;margin-bottom:10px;">Copyright © 优满分（<?php echo Yii::$app->urlManagerLogin->createUrl(['site/index']); ?>）2013 All Rights Reserved.</div>
</div>
</body>
</html>