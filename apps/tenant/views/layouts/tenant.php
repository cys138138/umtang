<?php
use umeworld\lib\Url;

$this->registerAssetBundle('tenant\assets\CommonAsset');
$this->beginPage(); 
?>
<!doctype html>
<html lang="zn-CN">
<head>
    <meta charset="UTF-8">
    <title><?php echo $this->title; ?></title>
</head>
<?php $this->head(); ?>
<body>
    <?php $this->beginBody(); ?>
    <?php echo tenant\widgets\Header::widget(); ?>
    <div class="umt-main umt-content">
        <div class="menu" id="layoutMenu">
            <ul>
                <li><i class="icon_menu icon_menu_center"></i>商户中心</li>
                <li><a href="<?php echo Url::to(['tenant-shop/show-shop-info']); ?>">商户信息</a></li>
                <li><a href="<?php echo Url::to(['photo/index']); ?>">商户相册</a></li>
                <li><a href="<?php echo Url::to(['characteristic/index']); ?>">特色服务</a></li>
                <li><a href="<?php echo Url::to(['teacher/index']); ?>">教师介绍</a></li>
            </ul>
            <ul>
                <li><i class="icon_menu icon_menu_trade"></i>交易中心</li>
                <li><a href="<?php echo Url::to(['order/show-home']); ?>">订单中心</a></li>
                <li><a href="<?php echo Url::to(['goods-volume/show-home']); ?>">服务券激活</a></li>
                <li><a href="<?php echo Url::to(['fund/show-home']); ?>">资金池</a></li>
            </ul>
            <ul>
                <li><i class="icon_menu icon_menu_goods"></i>服务中心</li>
                <li><a href="<?php echo Url::to(['goods/show-home']); ?>">服务列表</a></li>
            </ul>
            <ul>
                <li><i class="icon_menu icon_menu_evaluate"></i>评价中心</li>
                <li><a href="<?php echo Url::to(['comment/show-home']); ?>">我的评价</a></li>
            </ul>
        </div><div class="main">
            <?php echo $content;?>
        </div>
    </div>
    <?php echo tenant\widgets\Footer::widget(); ?>
    <?php $this->endBody(); ?>
    <script>
        //监听页面初始化事件
        window.addEventListener('message', function handler(evnet){
            if(evnet.source == window && event.data == window.PageBase.EVENT_READY){
                var href = $('.umt-tab > a.tab:first-child').attr('href') || location.pathname;
                $('#layoutMenu li a').filter('[href="' + href + '"]').parent('li').addClass('active');
            }
        }, false);
    </script>
</body>
</html>
<?php $this->endPage();