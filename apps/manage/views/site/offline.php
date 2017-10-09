<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>停机公告</title>
	<link rel="stylesheet" href="<?php echo Yii::getAlias('@r.url'); ?>/view2/style/style_global.css?v=20140106"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="停机公告" />
	<style>
		h3{padding: 5% 0;}
		p{padding: 1% 0;}
		blockquote{padding: 2% 0;}
	</style>
</head>
<body style="background-color:#FF6600;color:#fff;">
<div style="width:900px; margin: 150px auto;">
	<h3 style="font-size:16px;">服务器暂停公告</h3>
	<div>
		<p><strong style="font-size:30px;"><?php echo $message; ?></strong></p>
		<p><strong style="font-size:30px;"><?php echo $closeTime; ?></strong></p>
	</div>
	<blockquote style="text-align: right;font-size:16px;"><b>UMFun团队 <?php echo $releaseTime; ?></b></blockquote>
<div>
</body>
</html>