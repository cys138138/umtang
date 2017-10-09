<?php
use umeworld\lib\Url;
use manage\widgets\ModuleNavi;
use manage\widgets\Table;
use yii\widgets\LinkPager;

$this->setTitle('商户列表');
class NoticeAsset extends \umeworld\lib\AssetBundle
{
	public $css = [];
	public $js = [
		'@r.jquery.bootstrap.teninedialog.v3',
	];
}
NoticeAsset::register($this);
?>
<div class="row">
	<?php echo ModuleNavi::widget([
		'aMenus' => [
			[
				'title' => '商户列表',
				'url' => Url::to(['tenant/show-list']),
				'active' => true,
			],
		],
	]); ?>
	
	<h2>商户列表</h2>
	<div id="page-wrapper">
		<div class="row">
			<div class="col-lg-12">
				<form role="form" name="J-search-form" class="col-lg-6 saForm">
					<div class="col-lg-12 f-left saFormDiv">
						<label class="col-md-1 control-label f-left">商户ID</label>
						<div class="col-md-2"><input name="id" style="width: 80%" value='<?php echo $id ? $id : ''; ?>' /></div>
						<label class="col-md-1 control-label f-left">商户名</label>
						<div class="col-md-2"><input name="name" style="width: 100%" value='<?php echo $name; ?>' /></div>
						<div class="col-md-2">
							<button type="button" class="btn btn-primary" onclick="search();">搜索</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="table-responsive">
			<?php
				echo Table::widget([
					'aColumns'	=>	[
						'id' => [
							'title'	=>	'ID',
						],
						'name'	=>	[
							'title' => '商户名',
						],
						'online_status'	=>	[
							'title' => '上线状态',
							'content' => function($aData){
								$content = '';
								if($aData['online_status'] == \common\model\CommercialTenant::ONLINE_STATUS_PERFECT_INFOR){
									$content = '完善资料中';
								}elseif($aData['online_status'] == common\model\CommercialTenant::ONLINE_STATUS_IN_APPROVE){
									$content = '上线审核中';
								}elseif($aData['online_status'] == common\model\CommercialTenant::ONLINE_STATUS_ONLINE){
									$content = '已上线';
								}elseif($aData['online_status'] == common\model\CommercialTenant::ONLINE_STATUS_OFFLINE){
									$content = '已下架';
								}
								return $content;
							}
						],
						'mobile'	=>	[
							'title' => '注册手机',
						],
						'leading_official'	=>	[
							'title' => '负责人',
						],
						'contact_number'	=>	[
							'title' => '联系电话',
						],
						'email'	=>	[
							'title' => '邮箱',
						],
						'address'	=>	[
							'title' => '地址',
						],
						'create_time'	=>	[
							'title' => '注册时间',
							'content' => function($aData){
								return date('Y-m-d', $aData['create_time']);
							}
						],
						'option'	=>	[
							'title' => '操作',
							'content' => function($aData, $key){
								$content = '';
								if($aData['online_status'] == \common\model\CommercialTenant::ONLINE_STATUS_ONLINE){
									$content = '<button class="btn btn-xs btn-link" onclick="sendNotice(' . $aData['id'] . ')">发通知</button>|<button class="btn btn-xs btn-link" onclick="detail(' . $aData['id'] . ')">详情</button>';
								}
								return $content;
							}
						],
					],
					'aDataList'	=>	$aTenantList,
				]);
				echo LinkPager::widget(['pagination' => $oPage]);
			?>
		</div>
	</div>
</div>

<script type="text/javascript">
function search(){
	var condition = $('form[name=J-search-form]').serialize();
	location.href = '<?php echo Url::to(['tenant/show-list']); ?>?' + condition;
}
function detail(id){
	location.href = '<?php echo Url::to(['tenant/show-tenant-info-detail']); ?>?id=' + id;
}
var selectTenantId;
function sendNotice(tenantId){
	selectTenantId = tenantId;
	var contentHtml = '\
		<div class="row">\
			<div class="form-group">\
				<label class="col-sm-1"></label>\
				<input class="J-notice-title col-sm-7" placeholder="请填写标题" />\
			</div>\
		</div>\
		<br />\
		<div class="row">\
			<div class="form-group">\
				<label class="col-sm-1"></label>\
				<textarea class="J-notice-content col-sm-7" rows="3" placeholder="请填写内容"></textarea>\
			</div>\
		</div>';
	$.teninedialog({
		title : '发通知',
		content : contentHtml,
		showCloseButton : true,
		otherButtons : ['确定'],
		otherButtonStyles : ['btn-primary'],
		clickButton : function(sender, modal, index){
			if(index == 0){
				var title = $('.J-notice-title').val().trim();
				var content = $('.J-notice-content').val().trim();
				if(title.length == 0 || content.length == 0){
					alert('标题和内容都不可以为空!');
					return false;
				}
				ajax({
					url : '<?php echo Url::to(['tenant/send-notice']) ?>',
					data : {
						'tenantId' : selectTenantId,
						'title' : title,
						'content' : content
					},
					success : function(aResult){
						UBox.show(aResult.msg, aResult.status);
						if(aResult.status == 1){
							$(this).closeDialog(modal);
						}
					}
				});
			}
		}
	});
}
</script>