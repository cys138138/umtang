<?php
use umeworld\lib\Url;

\manage\assets\CommonAsset::register($this);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="zh-CN">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>UmTang后台管理系统</title>
		<?php $this->head(); ?>
	</head>
	<body>
		<?php $this->beginBody(); ?>
		<style>
			.mainOut{padding:0 15px;}
			.block{margin-bottom:20px; padding:7px; border:1px solid #000;}
			.block.student_test p{margin-bottom:7px;}
		</style>
		<div class="mainOut">
			<form class="form-inline">
				<div class="panel panel-primary">
					<center>
						<div class="panel-heading">
							<h1>后门工具 (你的IP是: <?php echo Yii::$app->request->userIP; ?>)</h1>
						</div>
						<a href="<?php echo Yii::$app->urlManagerLogin->createUrl('site/index'); ?>" target="_blank">站点首页</a>
						<!--a href="##" onclick="oLog.show(); return false;">查看日志</a-->

					</center>
					
					<div class="panel panel-info">
						<div class="panel-heading">
							<h2>商户操作</h2>
						</div>
						<div class="panel-footer">
							<p>
								<input type="text" class="form-control input-sm" placeholder="商户ID" id="tenantId" value="">
								<label>对该商户进行</label>
								<select class="form-control" id="userOperatingType">
									<option value="1">登陆</option>
									<option value="2">注销登陆</option>
								</select>
								<button type="button" class="btn btn-default" onclick="operating()">执行</button>
							</p>
						</div>

						<script>
							function operating() {
								var tenantId = $('#tenantId').val();
								if (!tenantId) {
									UBox.show('请输入学生ID', -1, '', 2);
									return;
								}

								var type = parseInt($('#userOperatingType').val());
								switch (type){
									case 1:
										loginTenant(tenantId);
										break;

									case 2:
										logoutTenant(tenantId);
										break;

									default:
										UBox.show('请选择操作类型', -1);
										return;
								}
							}
							function loginTenant(tenantId) {
								ajax({
									url: '<?php echo Url::to(['tools/login-tenant']); ?>',
									data: {
										type: 1,
										tenantId: tenantId
									},
									success: function (aResult) {
										if (aResult.status == 1) {
											UBox.show(aResult.msg, aResult.status, '', 1);
										} else {
											UBox.show(aResult.msg, aResult.status, '', 2);
										}
									}
								});
							}

							function logoutTenant(tenantId) {
								ajax({
									url: '<?php echo Url::to(['tools/logout-tenant']); ?>',
									data: {
										type: 1,
										tenantId: tenantId
									},
									success: function (aResult) {
										if (aResult.status == 1) {
											UBox.show(aResult.msg, aResult.status, '', 1);
										} else {
											UBox.show(aResult.msg, aResult.status, '', 2);
										}
									}
								});
							}

						</script>
					</div>
				</div>
			</form>
		</div>
		<?php $this->endBody(); ?>
	</body>
</html>
<?php $this->endPage(); ?>