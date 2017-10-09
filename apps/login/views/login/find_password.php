<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.pack.account-password'); 
$this->registerJsFile('@r.js.tools-validata'); 
$this->setTitle('优满堂-忘记密码'); 
?>
<div id="wrapPage"></div>
<script>
    $(function(){
        var oPage = new Password();
        oPage.config({
            selector: '#wrapPage',
            template: window.pageTemplate.password,
            url:{
                sendCodeUrl:'<?php echo Url::to(['login/send-find-password-verify-code'])?>',
                checkCodeUrl:'<?php echo Url::to(['login/verify-find-password-verify-code'])?>',
                resetPasswordUrl:'<?php echo Yii::$app->urlManagerTenant->createUrl(['tenant/reset-password'])?>',
                loginUrl:'<?php echo Url::to(['login/show-login'])?>',
            },

        });
        oPage.show();
    });
</script>
