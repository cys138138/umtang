<?php
use umeworld\lib\Url;
class ApproveAsset extends \umeworld\lib\AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '@r.css.layout-approve',
    ];
    public $js = [
        '@r.js.tools-util',
    ];
    public $depends = [
        'common\assets\NoticeAsset',
        'common\assets\CoreAsset',
    ];
}

$this->registerAssetBundle('ApproveAsset');
$this->beginPage(); 
?>
<!doctype html>
<html lang="zn-CN">
<head>
    <meta charset="UTF-8">
    <?php if(!Yii::$app->client->isComputer){ 
        $this->registerCSSFile('@r.css.layout-mobile'); ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <meta name="format-detection" content="telephone=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <?php }?>
    <title><?php echo $this->title; ?></title>
</head>
<?php $this->head(); ?>
<body class="umt-approve-page">
    <?php $this->beginBody(); ?>
    <div id="header">
        <img class="logo pc-logo" src="/assets/login/account/layout/img/header-title.png">
        <img class="logo mobile-logo" src="/assets/login/account/layout/img/mobile-title.png">
    </div>
    <div id="main"><?php echo $content;?></div>
    <div id="footer" class="bg-white">
            <div class="line">
                <a href="<?php echo Yii::$app->urlManagerLogin->createUrl(['site/show-about'])?>">关于优满堂</a><a
                href="<?php echo Yii::$app->urlManagerLogin->createUrl(['site/show-intro'])?>">合作介绍</a><a
                href="<?php echo Yii::$app->urlManagerLogin->createUrl(['site/show-process'])?>">加盟流程</a><a target="_blank"
                href="<?php echo Yii::$app->urlManagerLogin->createUrl(['site/about-company'])?>">公司介绍</a><a target="_blank"
                href="<?php echo Yii::$app->urlManagerLogin->createUrl(['site/join'])?>">加入我们</a>
            </div>
            <div class="copyright"><span>粤ICP备13000602号-6 Copyright</span> © 优满堂(umtang.com) 2016-2017 All Rights Reserved</div>
    </div>
    <?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage();
