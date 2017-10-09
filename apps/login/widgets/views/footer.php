<?php
use umeworld\lib\Url;
use login\widgets\Module;
?>
<div id='footer'>
    <div class="mobile-line">
    <?php if($scene == Module::SCENE_BUSINESS){ ?>
	    <a href="<?php echo Url::to(['site/show-about'])?>">关于优满堂</a><a
	    href="<?php echo Url::to(['site/show-intro'])?>">合作介绍</a><a
	    href="<?php echo Url::to(['site/show-process'])?>">加盟流程</a><a target="_blank"
	    href="<?php echo Url::to(['site/about-company'])?>">公司介绍</a><a target="_blank"
	    href="<?php echo Url::to(['site/join'])?>">加入我们</a>
    <?php }else{ ?>
        <a href="<?php echo Url::to(['site/about-terms'])?>">服务条款</a><a
        href="<?php echo Url::to(['site/help-faq'])?>">常见问题</a><a
        href="<?php echo Url::to(['site/join'])?>">加入我们</a><a target="_blank"
        href="<?php echo Url::to(['site/index'])?>">商户中心</a><a target="_blank"
        href="<?php echo Url::to(['site/my-shop'])?>">品牌店</a>
        <span>
            <span class="tel">400-900-9390</span><span>(周一至周五 09:00-18:00)</span>
        </span>
    <?php }?>

    <?php if(!Yii::$app->client->isComputer){ ?>
        <div class="connect">
            <button id="connect-qq">在线客服</button>
            <script type="text/javascript">
                $("#connect-qq").on("click",function () {
                    window.open('http://wpa.qq.com/msgrd?v=3&uin=2025928896&site=qq&menu=yes','_blank');
                });
            </script>
        </div>
    <?php  }?>
    </div>
    <div class="copyright"><span>粤ICP备13000602号-6 Copyright </span>© 优满堂(umtang.com) 2016-2017 All Rights Reserved</div>
</div>
