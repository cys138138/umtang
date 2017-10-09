<?php 
use umeworld\lib\Url;
?>

<?php if(!Yii::$app->client->isComputer){ ?>
<div id="header">
    <a href="<?php echo Url::to(['site/show-index']); ?>" title="优满堂-商户中心">
        <img class="logo mobile-logo" src="/assets/tenant/img/logo-title.png">
    </a>
</div>
<div id="shopInfo">
    <span class="shop-name"><?php echo $shopName; ?></span>
    <a  href="<?php echo Yii::$app->urlManagerLogin->createUrl('login/logout'); ?>" title="退出"><button>退出</button></a>
</div>
<?php }else{ ?>
<div id="header">
	<div class="umt-main">
        <a href="<?php echo Url::to(['site/show-index']); ?>" title="优满堂-商户中心">
        	<i class="icon_logo"></i>
        </a>
        <span class="info">
            <a title="" href="<?php echo Url::to(['site/show-index']); ?>"><?php echo $shopName; ?>
            </a><a href="<?php echo Url::to(['notice/index']); ?>" class="mail icon_menu icon_menu_mail <?php echo $hasUnreadMessage == 1 ? 'notice': ''; ?>" title="通知">
            </a><a href="<?php echo Yii::$app->urlManagerLogin->createUrl('login/logout'); ?>" title="退出">退出</a>
        </span>
    </div>
</div>
<?php } ?>