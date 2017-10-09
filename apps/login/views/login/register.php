<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.pack.account-register'); 
$this->registerJsFile('@r.js.tools-validata'); 
$this->registerAssetBundle('login\assets\CommonAsset');
$this->setTitle('优满堂-注册'); 
?>
<div id="wrapPage"></div>
<script>
    $(function(){
        var oPage = new Register();
        oPage.config({
            selector: '#wrapPage',
            template: window.pageTemplate.register,
            url:{
                loginUrl:'<?php echo Url::to(['login/show-login'])?>',
                sendCodeUrl:'<?php echo Url::to(['login/send-login-verify-code'])?>',
                registerUrl:'<?php echo Url::to(['login/register'])?>',

                protocolUrl:'<?php echo Url::to(['site/business-protocol'])?>',
                indexUrl:'<?php echo Yii::$app->urlManagerTenant->createUrl(['site/show-index'])?>'
            },
        });
        oPage.show();
    });
</script>
