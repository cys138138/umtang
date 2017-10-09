<?php 
use umeworld\lib\Url;
use login\widgets\Module;
$this->registerCSSFile('@r.css.umt-index'); 
$this->registerAssetBundle('common\assets\CoreAsset');
$this->beginPage(); 

$path = $_SERVER['REQUEST_URI'];
if(preg_match('/business/',$path)){
	$scene = Module::SCENE_BUSINESS;
}else{
	$scene = Module::SCENE_DEFAULT;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php if(!Yii::$app->client->isComputer){ 
		$this->registerCSSFile('@r.css.layout-mobile'); ?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<meta name="format-detection" content="telephone=no">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<?php }?>

	<title><?php echo $this->title; ?></title>
	<?php $this->head(); ?>
</head>

<body>
	<?php $this->beginBody(); ?>
	<?php echo Module::Widget(['type' => Module::TYPE_HEADER,'scene' => $scene]); ?>
	<div id="main"><?php echo $content; ?></div>
	<?php echo Module::Widget(['type' => Module::TYPE_FOOTER,'scene' => $scene]); ?>
	<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage();
