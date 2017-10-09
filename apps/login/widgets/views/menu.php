<?php
use umeworld\lib\Url;
use login\widgets\Menu
?>

<div class="J-menu menu">
    <ul>
	<?php switch ($type) {
    		case Menu::DEFAULT_ABOUT :?>
		<li><a href="<?php echo Url::to(['site/about-company'])?>">关于我们</a></li>
		<li><a href="<?php echo Url::to(['site/about-contact'])?>">联系方式</a></li>
		<li><a href="<?php echo Url::to(['site/about-terms'])?>">用户协议</a></li>
		<li><a href="<?php echo Url::to(['site/about-law'])?>">法律声明</a></li>
		<li><a href="<?php echo Url::to(['site/about-privacy'])?>">隐私保护</a></li>
    	<?php	break;
    		case Menu::DEFAULT_HELP :?>		
		<li><a href="<?php echo Url::to(['site/help-faq'])?>">常见问题</a></li>
		<?php	break;
    		case Menu::BUSINESS_HELP :?>
		<li><a href="<?php echo Url::to(['site/show-about'])?>">关于优满堂</a></li>
		<li><a href="<?php echo Url::to(['site/business-protocol'])?>">商户协议</a></li>
		<li><a href="<?php echo Url::to(['site/show-intro'])?>">合作介绍</a></li>
		<li><a href="<?php echo Url::to(['site/show-process'])?>">加盟流程</a></li>
		<li><a href="<?php echo Url::to(['site/show-fqa'])?>">常见问题</a></li>
		<!--<li><a href="<?php echo Url::to(['site/business-join'])?>">加入我们</a></li>-->
    	<?php	break;    			}?>
    </ul>
</div>



<script>
	//节点选择
	var $umtMenu = $(".J-menu");
	$umtMenu.find('a[href="'+location.pathname+'"]').removeAttr('href').parent().addClass("active");
	<?php if(Yii::$app->client->isComputer){ ?>
	//滚动效果
	var $window = $(window).on("scroll", function (e) {
		var height = $umtMenu.offset().top - 40;
		var top = $window.scrollTop();
		$umtMenu.toggleClass("scroll",top > height);
	});
	<?php }?>
</script>
