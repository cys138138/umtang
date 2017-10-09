<?php 
use umeworld\lib\Url;
?>
<div class="umt-account-status">
	<?php if($shopName == ""){  ?>
		<a class="account-name">我是商家</span>
    	<a class="account-status blue login" href="<?php echo Url::to(['login/show-login']); ?>">登录</a>
	<?php }else{  ?>
		<a class="account-name blue" 
		href="<?php echo Yii::$app->urlManagerTenant->createUrl(['site/show-index']); ?>"><?php echo $shopName?></span>
    	<a class="J-logout account-status">退出</a>
    	<script>
    		$(".J-logout").on("click",function(){
    			ajax({
    				url:"<?php echo Yii::$app->urlManagerLogin->createUrl('login/logout-asynchronous'); ?>"
    			}).done(function(res){
    				if(res.status == 1){
    					location.reload()
    				}
    			});
    		})
    	</script>
	<?php } ?>
</div>

