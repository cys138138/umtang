<?php
use umeworld\lib\Url;
$this->registerAssetBundle('tenant\assets\MobileAsset');
$this->beginPage(); 

$aNotice = common\model\CommercialTenantNotice::findOne(['tenant_id' => Yii::$app->commercialTenant->id, 'is_read' => 0]);
$hasUnreadMessage = $aNotice ? 1 : 0;
?>
<!doctype html>
<html lang="zn-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<meta name="format-detection" content="telephone=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title><?php echo $this->title; ?></title>
</head>
<?php $this->head(); ?>
<body>
    <?php $this->beginBody(); ?>
    <?php echo tenant\widgets\Header::widget(); ?>
    <div id="main">
        <div id="menu">
            <span class="title"><?php echo $this->title; ?></span>
            <label class="toggle">
                <a href="<?php echo Url::to(['notice/index']); ?>" class="mail icon_menu icon_menu_mail <?php echo $hasUnreadMessage == 1 ? 'notice': ''; ?>" title="通知"></a>
                <input type="checkbox">
                <span class="block"></span>
                <div class="menu">
                    <ul>
                        <li><i class="icon_menu icon_menu_center"></i>商户中心</li>
                        <li><a href="<?php echo Url::to(['tenant-shop/show-shop-info']); ?>">商户信息</a></li>
                        <li><a href="<?php echo Url::to(['photo/index']); ?>">商户相册</a></li>
                        <li><a href="<?php echo Url::to(['characteristic/index']); ?>">特色服务</a></li>
                        <li><a href="<?php echo Url::to(['teacher/index']); ?>">教师介绍</a></li>
                    </ul><ul>
                        <li><i class="icon_menu icon_menu_trade"></i>交易中心</li>
                        <li><a href="<?php echo Url::to(['order/show-home']); ?>">订单中心</a></li>
                        <li><a href="<?php echo Url::to(['goods-volume/show-home']); ?>">服务券激活</a></li>
                        <li><a href="<?php echo Url::to(['fund/show-home']); ?>">资金池</a></li>
                    </ul><ul>
                        <li><i class="icon_menu icon_menu_goods"></i>服务中心</li>
                        <li><a href="<?php echo Url::to(['goods/show-home']); ?>">服务列表</a></li>
                    </ul><ul>
                        <li><i class="icon_menu icon_menu_evaluate"></i>评价中心</li>
                        <li><a href="<?php echo Url::to(['comment/show-home']); ?>">我的评价</a></li>
                    </ul>
                </div>
            </span>
        </div>
        <?php echo $content;?>
    </div>
    <div id="footer">
         <div class="copyright"><span>© 优满堂(umtang.com) 2016-2017 All Rights Reserved </span>备案号：粤ICP备13000602号-6 Copyright</div>
    </div>
    <?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage();
