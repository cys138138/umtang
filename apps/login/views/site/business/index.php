<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.pack.business-home'); 
$this->registerCssFile('@r.css.business-home'); 
$this->setTitle('优满堂-商户中心'); 
?>
<div id="wrapPage"></div>
<script>
    $(function(){
        var oPage = new BusinessHome();
        oPage.config({
            selector: '#wrapPage',
            template: window.pageTemplate.businessHome,
            registerUrl:'<?php echo Url::to(['login/show-register'])?>',
            questionUrl:'<?php echo Url::to(['site/show-fqa'])?>',
        });
        oPage.show();
    });
</script>
