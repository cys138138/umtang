<?php
use yii\helpers\Html;
$this->registerCssFile('@r.css.error');
$this->beginPage();
?>
<!doctype html>
<html>
<head>
<title>Response - UMFun</title>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="format-detection"content="telephone=no">
<?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>
<div class="error-container">
	<div class="title">
		<h2><?php echo '提示'; ?></h2>
	</div>
	<div class="content">
		<h3><?php echo $msg; ?></h3>
		<?php if(!YII_ENV_PROD){ ?>
		<h4><?php echo is_string($xData) ? $xData : json_encode($xData); ?></h4>
		<?php } ?>
	</div>
	<div class="redirect">
		<?php
			$referer = Yii::$app->request->headers->get('referer');
			if($referer){
				echo Html::a('回上一页', $referer);
			}else{
				echo '<a href="javascript:(history.back())">回上一页</a>';
			}
		?>
		<a href="<?php echo \umeworld\lib\Url::to(['site/index']); ?>">回到优满堂首页</a>
	</div>
</div>

<?php if(!YII_ENV_PROD):
	$_oHandler = Yii::$app->errorHandler;
?>
<div class="dataWrapper">
	<pre>
		<?php
		\umeworld\lib\Debug::$sliceTraceRows = 3;
		echo json_encode($xData);
		?>
	</pre>
	<h4>请求相关数据:</h4>
	<?php echo $_oHandler->renderRequest(); ?>
</div>
<?php endif;
$this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>