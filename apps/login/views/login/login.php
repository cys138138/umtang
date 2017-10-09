<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.pack.account-login'); 
$this->registerJsFile('@r.js.tools-validata'); 
$this->setTitle('优满堂-登录'); 
?>
<div id="wrapPage"></div>
<script>
    $(function(){
        var oPage = new Login();
        oPage.config({
            selector: '#wrapPage',
            template: window.pageTemplate.login,
            url:{
            	nameLoginUrl:'<?php echo Url::to(['login/login'])?>',
            	phoneLoginUrl:'<?php echo Url::to(['login/mobile-login'])?>',
            	captchaUrl:'<?php echo Url::to(['site/captcha'])?>'+'?refresh=1&_='+Math.random(),
            	codePhoneUrl:'<?php echo Url::to(['login/send-login-verify-code'])?>',
            	forgetPasswordUrl:'<?php echo Url::to(['login/show-find-password'])?>',
            	registerUrl:'<?php echo Url::to(['login/show-register'])?>',
                indexUrl:'<?php echo Yii::$app->urlManagerTenant->createUrl(['site/show-index'])?>'
            },
        });
        oPage.show();
    });
</script>


