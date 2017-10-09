<?php 
use umeworld\lib\Url;
$this->registerCSSFile('@r.css.login-layout'); 
$this->registerAssetBundle('login\assets\CommonAsset');
$this->beginPage(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $this->title; ?></title>
	<?php $this->head(); ?>
</head>
<body>
	<?php $this->beginBody(); ?>
	<div id="header"><img src="/assets/login/account/layout/img/header-title.png"></div>
	<div id ="main"><?php echo $content; ?></div>
	<div id="footer">
	    <div class="line">
			<a href="<?php echo Url::to(['site/show-about'])?>">关于优满堂</a><a
			href="<?php echo Url::to(['site/show-intro'])?>">合作介绍</a><a
			href="<?php echo Url::to(['site/show-process'])?>">加盟流程</a><a target="_blank"
			href="<?php echo Url::to(['site/about-company'])?>">公司介绍</a><a target="_blank"
			href="<?php echo Url::to(['site/join'])?>">加入我们</a>
	    </div>
	    <div class="copyright"><span>粤ICP备13000602号-6 Copyright</span> © 优满堂(umtang.com) 2016-2017 All Rights Reserved</div>
	 </div>
	<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage();
