<?php
use manage\widgets\ModuleNavi;
use manage\widgets\Table;
use yii\widgets\LinkPager;
use umeworld\lib\Url;

$this->setTitle('商户类型');

class OrderAsset extends \umeworld\lib\AssetBundle
{
	public $css = [];
	public $js = [
		'@r.jquery.bootstrap.teninedialog.v3',
	];
}
OrderAsset::register($this);
?>
<div class="row">
	<?php echo ModuleNavi::widget([
		'aMenus' => [
			[
				'title' => '商户类型',
				'url' => Url::to(['tenant-service/show-commercial-tenant-type-list']),
				'active' => true,
			],
		]
	]); ?>
	<div id="page-wrapper">
		<div class="table-responsive">
			<?php
				echo Table::widget([
					'aColumns' => [
						'id' => ['title' => 'id'],
						'name' => ['title' => '类型'],
						'create_time' => [
							'title' => '时间',
							'content' => function($aData){
								$content = '';
								if(isset($aData['create_time'])){
									$content = date('Y-m-d H:i:s', $aData['create_time']);
								}
								
								return $content;
							}
						],
						'option' => [
							'title' => '操作',
							'content' => function($aData){
								return '<button type="button" class="btn btn-xs btn-link" onclick="edit(this,' . $aData['id'] . ')">编辑</button> | <button type="button" class="btn btn-xs btn-link" onclick="del(this,' . $aData['id'] . ')">删除</button>';
							}
						]
					],
					'aDataList' => $aTenantType
				]);
				echo '<div><button type="button" class="btn btn-primary" onclick="add()">新增</button></div>';
				echo LinkPager::widget(['pagination' => $oPage]);	
			?>
		</div>
	</div>
</div>
<script type="text/javascript">
function edit(o, id){
	var typeName = $(o).parents('.J-row').find('>td:nth-child(2)').text();
	var contentHtml = '<p>id: ' + id + '</p><p>名称: <span style="color:red;">' + typeName + '</span></p><div><input type="text" class="J-refund-money form-control" id="typeName" placeholder="请输入更改的名称" /></div>';
	$.teninedialog({
		title : '编辑',
		content : contentHtml,
		showCloseButton : true,
		otherButtons : ['确定'],
		otherButtonStyles : ['btn-primary'],
		clickButton : function(sender, modal, index){
			if(index == 0){
				var typeName = $('#typeName').val().trim();
				ajax({
					url : '<?php echo Url::to(['tenant-service/tenant-type-edit']) ?>',
					data : {
						'id' : id,
						'typeName' : typeName
					},
					success : function(aResult){
						if(aResult.status == 1){
							$(this).closeDialog(modal);
							UBox.show(aResult.msg, aResult.status);
							window.location.reload();
						}else{
							UBox.show(aResult.msg, aResult.status);
						}
					}
				});
			}
		}
	});
}

function add(){
	var contentHtml = '<div><input type="text" class="J-refund-money form-control" id="typeName" placeholder="请输入新增类型的名称" /></div>';
	$.teninedialog({
		title : '新增',
		content : contentHtml,
		showCloseButton : true,
		otherButtons : ['确定'],
		otherButtonStyles : ['btn-primary'],
		clickButton : function(sender, modal, index){
			if(index == 0){
				var typeName = $('#typeName').val().trim();
				ajax({
					url : '<?php echo Url::to(['tenant-service/add-commercial-tenant-type']) ?>',
					data : {
						'typeName' : typeName
					},
					success : function(aResult){
						if(aResult.status == 1){
							$(this).closeDialog(modal);
							UBox.show(aResult.msg, aResult.status);
							window.location.reload();
						}else{
							UBox.show(aResult.msg, aResult.status);
						}
					}
				});
			}
		}
	});
}

function del(o, id){
	UBox.confirm('执行删除?', function(){
			ajax({
				url : '<?php echo Url::to(['tenant-service/tenant-type-delete']); ?>',
				data : {'id' : id},
				success : function(aResult){
					if(aResult.status == 1){
						UBox.show(aResult.msg, aResult.status);
						$(o).parents('.J-row').remove();
					}else{
						UBox.show(aResult.msg, aResult.status);
					}
				}
			});
		}
	);
}
</script>