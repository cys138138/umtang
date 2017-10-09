<?php
use umeworld\lib\Url;
use manage\widgets\ModuleNavi;
use manage\widgets\Table;
use yii\widgets\LinkPager;

$this->setTitle('公告列表');
?>
<div class="row">
	<?php echo ModuleNavi::widget([
		'aMenus' => [
			[
				'title' => '公告列表',
				'url' => Url::to(['announcement/show-list']),
				'active' => true,
			],
			[
				'title' => '发布公告',
				'url' => Url::to(['announcement/show-add']),
			],
		],
	]); ?>
	<h2>公告列表</h2>
	<div class="table-responsive">
		<?php
			echo Table::widget([
				'aColumns'	=>	[
					'id' => [
						'title'	=>	'ID',
					],
					'title'	=>	[
						'title' => '标题',
					],
					'create_time'	=>	[
						'title' => '时间',
						'content' => function($aData, $key){
							return date('Y-m-d H:i:s', $aData['create_time']);
						}
					],
					'option'	=>	[
						'title' => '操作',
						'content' => function($aData, $key){
							return '<a href="' . Url::to(['announcement/show-add', 'id' => $aData['id']]) . '" class="btn btn-xs btn-link">编辑</a><button class="btn btn-xs btn-link" onclick="deleteAnnouncement(' . $aData['id'] . ')">删除</button>';
						}
					],
				],
				'aDataList'	=>	$aAnnouncementList,
			]);
			echo LinkPager::widget(['pagination' => $oPage]);
		?>
	</div>
</div>
<script type="text/javascript">
function deleteAnnouncement(announcementId){
	if(!confirm('请确认是否删除！')){
		return false;
	}
	ajax({
		url : '<?php echo Url::to(['announcement/delete']) ?>',
		data : {
			'id' : announcementId
		},
		success : function(aResult){
			if(aResult.status == 1){
				UBox.show(aResult.msg, aResult.status, function(){
							window.location = '<?php echo Url::to(['announcement/show-list']) ?>';
						}, 2);
			}else{
				UBox.show(aResult.msg, aResult.status);
			}
		}
	});
}
</script>