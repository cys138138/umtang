<?php 
use umeworld\lib\Url;

$this->registerJsFile('@r.pack.index'); 
$this->setTitle('优满堂'); 
?>
<link rel="stylesheet" media="screen and (max-width: 640px)" href="<?php echo Yii::getAlias('@r.css.index-app'); ?>">
<link rel="stylesheet" media="screen and (min-width: 641px)" href="<?php echo Yii::getAlias('@r.css.index-pc'); ?>">
<div id="wrapPage"></div>
<div id="enter"><?php include 'join_details.php'; ?></div>
<script>
    $(function(){
        var oPage = new Index();
        oPage.config({
            selector: '#wrapPage',
            template: window.pageTemplate.index
        });
        oPage.show();

        $('#enter').insertBefore('#about');
    });
</script>
