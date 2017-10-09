<?php
/**
 * @var $this umeworld\lib\View
 */
\manage\assets\CommonAsset::register($this);

$this->beginPage(); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $this->title; ?></title>
<?php $this->head(); ?>
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
<style type="text/css">
.page-Wrapper{min-height:850px;}
</style>
</head>
<body>
<?php $this->beginBody(); ?>
<div id="wrapper">
	<div id="page-wrapper" class="page-Wrapper">
		<div class="container-fluid">
		<?php
			echo \manage\widgets\Navi::widget();
			echo $content;
		?>
		</div>
	</div>
</div>
<?php $this->endBody(); ?>
</body>
<?php echo \common\widgets\Cnzz::widget();?>
</html>
<?php $this->endPage();