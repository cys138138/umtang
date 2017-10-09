<?php
use umeworld\lib\Url;
use yii\helpers\Html;

/*@var manage\lib\AuthManager $_oAuthManager*/
$_aCurrentPermission = [];
?>
<!-- Navigation -->
<nav id="navi" class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<!-- Brand and toggle get grouped for better mobile display -->
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<?php echo Html::a('Umantang后台', Url::to(['site/index']), ['class' => 'navbar-brand']); ?>
	</div>
	<!-- Top Menu Items -->
	<ul class="nav navbar-right top-nav">
		<li>

			<?php echo Html::a('<i class="fa fa-fw fa-home"></i>站点首页', Yii::$app->urlManagerManage->createUrl(['site/index']), ['target' => '_blank']); ?>
		</li>
		<li class="dropdown">
			<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
				<i class="fa fa-user"></i>
				<?php echo $mManager->name; ?>
				<b class="caret"></b>
			</a>
			<ul class="dropdown-menu">
				<li>
					<?php echo Html::a('<i class="fa fa-fw fa-user"></i>个人信息', Url::to(['manager/show-profile'])); ?>
				</li>
				<li>
					<?php echo Html::a('<i class="fa fa-fw fa-cog"></i>更改密码', Url::to(['manager/show-password'])); ?>
				</li>
				<li class="divider"></li>
				<li>
					<?php echo Html::a('<i class="fa fa-fw fa-sign-out"></i>退出后台', Url::to(['site/logout'])); ?>
				</li>
			</ul>
		</li>
	</ul>

	<div class="collapse navbar-collapse navbar-ex1-collapse">
		<ul class="nav navbar-nav side-nav">
			<?php
			$_oNavi = $this->context;
			$_pathInfo = '/' . Yii::$app->request->pathInfo;
			$_oAuthManager = Yii::$app->authManager;
			//debug($_oAuthManager,11);
			//$_mManagerGroup = Yii::$app->manager->getIdentity()->getManagerGroup();
			foreach($_oAuthManager->aPermissionList as $i => $aPermissionGroup):
				//if($i === 'hidden' || !$_oNavi->hasPermissionInSubMenu($aPermissionGroup)){
					//continue;
				//}
			?>
				<li class="J-menuGroup">
					<a href="javascript:;" data-toggle="collapse" data-target="#leftNavi<?php echo $i; ?>" class="J-btnMenuToggle">
						<i class="fa fa-fw fa-<?php echo $aPermissionGroup['icon_class']; ?>"></i>
						<?php echo $aPermissionGroup['title']; ?>
						<i class="fa fa-fw fa-caret-down"></i>
					</a>
					<ul id="leftNavi<?php echo $i; ?>" class="collapse<?php
						if(!$_aCurrentPermission && ($_aCurrentPermission = $_oAuthManager->getPermissionInfoByUrl($_pathInfo, $aPermissionGroup))){
							echo ' collapsed in';
						} ?>">
						<?php  foreach($aPermissionGroup['child'] as $aPermissionItem):
							//if(!$_mManagerGroup->allow($aPermissionItem['permission'])){
								//continue;
							//}
						?>
						<li>
							<?php
							$aOptions = [];
							//debug($aPermissionGroup,11);
							if (!empty($_aCurrentPermission) && $_aCurrentPermission['url'] == $aPermissionItem['url']) {
								$aOptions['class'] = 'active';
							}
							echo Html::a('<i class="fa fa-fw fa-' . $aPermissionItem['icon_class'] . '"></i> ' . $aPermissionItem['title'], Url::to($aPermissionItem['url']), $aOptions); ?>
						</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>