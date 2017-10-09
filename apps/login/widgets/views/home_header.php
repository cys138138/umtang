<?php
use umeworld\lib\Url;
use login\widgets\Module;
use login\widgets\Menu;
?>
<div id='header'>
	<div class="center"> 
        <?php if($scene == Module::SCENE_BUSINESS){ ?>
        	<img class="logo business-logo" src="/assets/login/home/img/business/logo-title.png">
        	<div class="list-tab mobile-tab">
        		<i></i>
        		<div>
	        		<a class ="page-tab" href="<?php echo Url::to(['site/index'])?>">首页</a>
					<a class ="page-tab" href="<?php echo Url::to(['site/show-intro'])?>">合作介绍</a>
					<a class ="page-tab" href="<?php echo Url::to(['site/show-process'])?>">合作流程</a>
					<a class ="page-tab" href="<?php echo Url::to(['site/show-fqa'])?>">常见问题</a>
					<!--<a class="J-head-join" href="<?php echo Url::to(['site/business-join'])?>">立刻加入</a>-->
        		</div>
        	</div>
			<?php echo login\widgets\Account::Widget();?>
        <?php }else{ ?>
			<img class="logo" src="/assets/login/account/layout/img/mobile-title.png">
        	<div class="mobile-tab">
        		<i></i>
        		<div>
        			<a class ="page-tab" href="<?php echo Url::to(['site/home'])?>">首页</a>
		        	<a class ="page-tab  <?php echo $sideType === Menu::DEFAULT_ABOUT ? "active":"";?>" href="<?php echo Url::to(['site/about-company'])?>">关于我们</a>
		        	<a class ="page-tab" href="<?php echo Url::to(['site/join'])?>">加入我们</a>
		        	<a class ="page-tab" href="<?php echo Url::to(['site/help-faq'])?>">帮助中心</a>
		        	<a class ="page-tab" target="_blank" href="<?php echo Url::to(['site/index'])?>">商户中心</a>
        		</div>
        	</div>
        <?php }?>
    </div>
</div>
<script>
	$(function(){
		$('#header').find('a[href="'+location.pathname+'"]').addClass('active').removeAttr('href');
		<?php if(!Yii::$app->client->isComputer){ ?>
			$('#header .mobile-tab').on("click",function(){
				$(this).find('div').toggle();
			});
		<?php }?>
	})
</script> 
