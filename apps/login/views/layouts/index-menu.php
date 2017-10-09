<?php 
use umeworld\lib\Url;
use login\widgets\Module;
use login\widgets\Menu;
$this->registerCSSFile('@r.css.umt-index'); 
$this->registerAssetBundle('common\assets\CoreAsset');
$this->beginPage(); 

$path = $_SERVER['REDIRECT_URL'];

if(preg_match('/business/',$path) || $path == Url::to(['site/umt-about'])){
	$scene = Module::SCENE_BUSINESS;
}else{
	$scene = Module::SCENE_DEFAULT;
}
switch ($path) {
	case Url::to(['site/about-company']):
	case Url::to(['site/about-contact']):
	case Url::to(['site/about-terms']):
	case Url::to(['site/about-law']):
	case Url::to(['site/about-privacy']):
		$sideType = Menu::DEFAULT_ABOUT;
		break;
	case Url::to(['site/help-faq']):
		$sideType = Menu::DEFAULT_HELP;
		break;				
	case Url::to(['site/show-about']):
	case Url::to(['site/show-process']):
	case Url::to(['site/show-fqa']):
	case Url::to(['site/show-intro']):
	case Url::to(['site/show-join']):
	case Url::to(['site/business-protocol']):
		$sideType = Menu::BUSINESS_HELP;
		break;
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
	<?php echo Module::Widget(['type' => Module::TYPE_HEADER,'scene' => $scene,'sideType'=>$sideType]); ?>

	<?php if($sideType == Menu::DEFAULT_ABOUT){ ?>
		<div class="header-pic umt-index-header-about"></div>
	<?php } ?>



	<div id="main" class="main two-col">
		<?php echo Menu::widget(['type' => $sideType]); ?>
		<?php echo $content; ?>
	</div>
	<?php echo Module::Widget(['type' => Module::TYPE_FOOTER,'scene' => $scene]); ?>
	<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage();?>
