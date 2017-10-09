<?php
use umeworld\lib\Url;
use manage\widgets\ModuleNavi;

$this->setTitle('修改密码');
//manage\assets\EsCategorySelectorAsset::register($this);
?>
<style>
	.f-left {float:left;}
	.saForm {height: 100px;}
	.isNumLa{ padding: 0px;}
	.choice-es {width: 50px; height:36px; float: right;  background: #0000ff; line-height: 36px; font-weight: bold; text-align: center;
		color: #fff;}
	#editBut {margin-top:50px;}
</style>
<div class="row">
	<?php echo ModuleNavi::widget([
		'aMenus' => [
			[
				'title' => '个人信息',
				'url' => Url::to(['manager/show-profile']),
				'active' => false,
			],
			[
				'title' => '更改密码',
				'url' => Url::to(['manager/show-password']),
				'active' => true,
			],
		],
	]);?>

	<div class="row">
		<div class="col-md-12">
			<form role="form" name="J-search-form" class="col-md-6">
				
				<div class="form-group f-left col-md-12">
					<label class="col-md-2">原密码：</label>

					<div class="c-item col-md-3">
						<input type="password" value="" class="J-group-name form-control" id="oldPassword" name="oldPassword" />
					</div>
				</div>

				<div class="form-group f-left col-md-12">
					<label class="col-md-2">新密码：</label>

					<div class="c-item col-md-3">
						<input type="password" value="" class="J-group-name form-control" id="newPassword" name="newPassword" />
					</div>
				</div>
				
				<div class="col-md-3"></div>
				<div class="col-md-2">
					<button type="button" class="btn btn-primary" id="submitData" >提交</button>
				</div>
			</form>

		</div>
	</div>

</div>
<script>
	function submitData(){
		var oldPassword = $("#oldPassword").val();
		var newPassword = $("#newPassword").val();
		if(!oldPassword){
			UBox.show('原密码不能为空', 3);
			return;
		}
		if(!newPassword){
			UBox.show('新密码不能为空', 3);
			return;
		}
		ajax({
			url:'<?php echo Url::to(['manager/update-password']); ?>',
			data:{
				oldPassword : oldPassword,
				newPassword : newPassword
			},
			success : function(aResult){
				if(aResult.status == 1){
					UBox.show(aResult.msg, aResult.status);
					window.location.reload();
				}else{
					UBox.show(aResult.msg, aResult.status);
				}
			}
		});
	}
	document.getElementById('submitData').onclick = submitData;
</script>