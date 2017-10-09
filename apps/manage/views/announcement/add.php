<?php
use umeworld\lib\Url;
use manage\widgets\ModuleNavi;
$this->registerAssetBundle('manage\assets\UmeditorAsset');
$this->setTitle('发布公告');
?>
<div class="row">
	<?php echo ModuleNavi::widget([
		'aMenus' => [
			[
				'title' => '公告列表',
				'url' => Url::to(['announcement/show-list']),
			],
			[
				'title' => '发布公告',
				'url' => Url::to(['announcement/show-add']),
				'active' => true,
			],
		],
	]); ?>
	<h2>发布公告</h2>
	<div id="page-wrapper">
		<input type="hidden" id="announcementId" value="<?php echo $id; ?>" />
		<div class="row">
			<div class="form-group col-sm-6">
				<input class="J-title form-control" placeholder="请填写公告标题" />
			</div>
		</div>
		<br />
		<div class="row">
			<div class="form-group col-sm-8">
				<script id="editor" name="umContent" type="text/plain" style="height:300px;width:100%;"></script>
			</div>
		</div>
		<br />
		<div class="row">
			<div class="form-group">
				<div class="col-sm-2">
					<button type="button" class="btn btn-primary J-approve-button" onclick="publish(this)">确认发布</button>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
<?php if(isset($aAnnouncement['title'])){ ?>
	$('.J-title').val('<?php echo $aAnnouncement['title']; ?>');
<?php } ?>
$(function(){
	UM.getEditor('editor', {
		toolbar:[
			'emotion image insertvideo | bold forecolor | justifyleft justifycenter justifyright  | removeformat |',
			'link'
		],
		imageUrl : '<?php echo Url::to(['announcement/upload-file']); ?>',
		imagePath : '<?php echo Yii::getAlias('@r.url'); ?>',
		imageFieldName : 'image',
		zIndex: 0
	}).ready(function(){
		<?php if(isset($aAnnouncement['content'])){ ?>
			this.setContent('<?php echo $aAnnouncement['content']; ?>');
		<?php } ?>
	});
});
var clickButton;
function publish(self){
	clickButton = self;
	$(self).attr('disabled', true);
	var aData = {
		'id' : $('#announcementId').val(),
		'title' : $('.J-title').val(),
		'content' : UM.getEditor('editor').getContent()
	};
	ajax({
		url : '<?php echo Url::to(['announcement/add']); ?>',
		data : aData,
		success : function(aResult){
			if(aResult.status != 1){
				UBox.show(aResult.msg, -1);
				$(clickButton).attr('disabled', false);
				return false;
			}
			UBox.show(aResult.msg, 1, function(){
				window.location.reload();
			}, 2);
		}
	});
}
</script>
