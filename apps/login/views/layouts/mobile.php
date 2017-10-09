<?php 
use umeworld\lib\Url;
$this->registerCSSFile('@r.css.layout-mobile'); 
$this->registerAssetBundle('login\assets\CommonAsset');
$this->beginPage(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<meta name="format-detection" content="telephone=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title><?php echo $this->title; ?></title>
	<?php $this->head(); ?>
</head>
<body class="umt-mobile-layout-login">
	<?php $this->beginBody(); ?>
	   	<div id="header"><img class="logo" src="/assets/login/account/layout/img/mobile-title.png"></div>
	  	<div id ="main"><?php echo $content; ?></div>
		<div id="footer" class="bg-white">
	       <div class="copyright"><span>粤ICP备13000602号-6 Copyright</span> © 优满堂(umtang.com) 2016-2017 All Rights Reserved</div>
	   	</div>
	<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage();
