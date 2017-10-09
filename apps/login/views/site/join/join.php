<?php
use umeworld\lib\Url;
$this->registerJsFile('@r.js.tools-date');
$this->registerJsFile('@r.pack.join'); 
$this->setTitle('优满堂-加入我们'); 
?>
<div id="wrapPage"></div>

<script>
    $(function(){
        $("#headerPic").addClass("umt-index-header-join");
        var oPage = new Join();
        oPage.config({
            selector: '#wrapPage',
            template: window.pageTemplate.join,
            listUrl:"<?php echo Url::to(['site/get-join-list'])?>",
            data:<?php echo json_encode($aJoinCategory); ?>
        });
        oPage.show();
    });
</script>
