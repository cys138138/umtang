<?php
/**
 * @var $this manage\model\form\user\LoginForm
 * @var $loginForm manage\model\form\user\LoginForm
 */
use umeworld\lib\Url;
use yii\captcha\Captcha;

\manage\assets\BootstrapAsset::register($this);
\common\assets\UBoxAsset::register($this);
$this->registerJsFile(Yii::getAlias('@r.js.core'), [
	'position' => $this::POS_HEAD,
	'depends' => 'common\assets\JQueryAsset',
]);

$this->setTitle('登陆');
$this->beginPage();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $this->title; ?></title>
<?php $this->head(); ?>
<style type="text/css">
	body{background:#555;}
	.wrapLogin{position:absolute; top:30%; left:50%; margin-top:-125px; margin-left:-140px; width:280px;}
	img{margin-top:-3px;}
</style>
</head>
<body>
<?php $this->beginBody(); ?>
<div class="container">
	<div class="wrapLogin">

		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title">登陆Umantang后台</h3>
			</div>
			<div class="panel-body">
				<form action="<?php echo Url::to(['site/login']); ?>" method="POST" id="loginForm">
					<input type="hidden" name="_csrf" value="<?php echo Yii::$app->request->csrfToken; ?>" />
					<div class="form-group">
						<input class="form-control" type="email" name="aa" placeholder="<?php echo $mLoginForm->attributeLabels()['aa']; ?>" />
					</div>

					<div class="form-group">
						<input class="form-control" name="bb" type="password" placeholder="<?php echo $mLoginForm->attributeLabels()['bb']; ?>" />
					</div>

					<div class="form-group clearfix">
						<?php
						echo Captcha::widget([
							'name' => 'vv',
							'model' => $mLoginForm,
							//'captchaAction'=>'site/captcha',
							'template' => '{input}</div><div class="form-group clearfix">{image}',
							'options' => [
								'class' => 'form-control',
								'placeholder' => $mLoginForm->attributeLabels()['vv'],
							],
							'imageOptions' => [
								'id' => 'verifyImg',
								'class' => 'pull-left',
							],
						]);
						?>

						<button class="btn btn-primary pull-right" type="button" onclick="login()">登陆</button>
					</div>

				</form>
			</div>
		</div>

	</div>
</div>
<script type="text/javascript">
function login(){
	ajax({
		url : '<?php echo Url::to(['site/login']); ?>',
		data : $('#loginForm').serialize(),
		success : function(aResult){
			UBox.show(aResult.msg, aResult.status, aResult.data, 1);
			if(aResult.status != 1){
				$('#w0').val('');	//w0是yii captcha类自动生成的id
				$('#verifyImg').click();
			}
		}
	});
}
document.onkeydown = function(e){
	var currKey=e.keyCode;
	if(currKey == 13){
		login();
	}
}
</script>
<?php $this->endBody(); ?>
</body>
<?php echo \common\widgets\Cnzz::widget();?>
</html>
<?php
$this->endPage();
