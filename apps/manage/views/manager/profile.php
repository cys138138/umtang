<?php
use manage\widgets\ModuleNavi;
use umeworld\lib\Url;
$this->setTitle('个人信息');	//设置网页标题
class TeacherStatisticsAddAsset extends \umeworld\lib\AssetBundle{
	public $js = [
		'@r.js.wdate-picker'
	];

	public $css = [

	];
}
TeacherStatisticsAddAsset::register($this);
?>

<div class="row">
	<?php echo ModuleNavi::widget([
		'aMenus' => [
			[
				'title' => '个人信息',
				'url' => Url::to(['manager/show-profile']),
				'active' => true,
			],
			[
				'title' => '更改密码',
				'url' => Url::to(['manager/show-password']),
				'active' => false,
			],
		],
	]);?>
	<div id="page-wrapper">
		<h2>管理员信息</h2>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-2">账号:</label><span><?php echo $mManager->email;?></span>
			</div>
			<div class="form-group">
				<label class="col-sm-2">昵称:</label><span><?php echo $mManager->name;?></span>
			</div>
			<div class="form-group">
				<label class="col-sm-2">创建时间:</label><span><?php echo date('Y-m-d', $mManager->create_time);?></span>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
function searchTeacherStatistics(){
	var condition = $('form[name=J-search-form]').serialize();
	location.href = '<?php echo Url::to(['student-manage/show-login-statistics']); ?>?' + condition;
}
</script>