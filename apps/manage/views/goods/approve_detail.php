<?php
use umeworld\lib\Url;
use manage\widgets\ModuleNavi;

$this->setTitle('商品审核');

class GoodsApproveAsset extends \umeworld\lib\AssetBundle
{
	public $css = [];
	public $js = [
		'@r.jquery.bootstrap.teninedialog.v3',
	];
}
GoodsApproveAsset::register($this);
?>
<style type="text/css">
img{max-width: 500px; margin: 5px;}
</style>
<div class="row">
	<?php echo ModuleNavi::widget([
		'aMenus' => [
			[
				'title' => '商品列表',
				'url' => Url::to(['goods/show-goods-approve-list']),
			],
			[
				'title' => '商品详情',
				'url' => Url::to(['goods/show-goods-approve-list']),
				'active' => true,
			],
		],
	]); ?>
	<h2>商品详情</h2>
	<div id="page-wrapper">
		<input type="hidden" id="goodsId" value="<?php echo $aGoodsInfo['id']; ?>" />
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">商品ID:</label><?php echo $aGoodsInfo['id']; ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">商品名:</label><?php echo $aGoodsInfo['name']; ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">商品类型:</label><?php echo $aGoodsInfo['type_name']; ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">商户名:</label><?php echo $aGoodsInfo['tenant_name']; ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">商品价格:</label><?php echo $aGoodsInfo['price'] / 100 . ' 元'; ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">门市价:</label><?php echo $aGoodsInfo['retail_price'] ? $aGoodsInfo['retail_price'] / 100 . ' 元' : '无'; ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">提前预约:</label><?php echo $aGoodsInfo['appointment_day'] . ' 天'; ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">适用人数:</label><?php echo $aGoodsInfo['suit_people']; ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">班级最多人数:</label><?php echo $aGoodsInfo['max_class_people']; ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">温馨提示:</label><?php echo $aGoodsInfo['notice']; ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">过期时间:</label><?php echo date('Y-m-d', $aGoodsInfo['validity_time']); ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">创建时间:</label><?php echo date('Y-m-d', $aGoodsInfo['create_time']); ?>
			</div>
		</div>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">图文介绍:</label>
				<div class="c-item col-md-6">
				<?php 
					foreach($aGoodsInfo['description'] as $aDescription){
						if(isset($aDescription['text'])){
							echo '<p>' . $aDescription['text'] . '</p>';
						}elseif(isset($aDescription['resource'])){
							echo '<img style="display:block;" src="' . Yii::getAlias('@r.url') . '/' . $aDescription['resource']['path'] . '" />';
						}
					} 
				?>
				</div>
			</div>
		</div>
		<?php if(isset($aGoodsInfo['photo_list']) && $aGoodsInfo['photo_list']){ ?>
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1">商品相册:</label>
				<div class="col-md-6">
				<?php foreach($aGoodsInfo['photo_list'] as $aPhoto){ ?>
					<img src="<?php echo Yii::getAlias('@r.url') . '/' . $aPhoto['path']; ?>" width="400" />
				<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
		<br />
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1"></label>
				<div class="col-sm-2">
					<button type="button" class="btn btn-primary J-approve-button" onclick="passApprove()">审核通过</button>
				</div>
				<button type="button" class="btn btn-primary J-approve-button" onclick="fillNotPassReason()">审核不通过</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
function fillNotPassReason(){
	var contentHtml = '<div><textarea id="notPassReason" rows="3" style="width:500px"></textarea></div>';
	$.teninedialog({
		title : '请填写不通过原因',
		content : contentHtml,
		showCloseButton : true,
		otherButtons : ['确定'],
		otherButtonStyles : ['btn-primary'],
		clickButton : function(sender, modal, index){
			if(index == 0){
				var reason = $('#notPassReason').val().trim();
				if(reason.length == 0){
					alert('请填写原因!');
					return false;
				}
				$('.J-approve-button').attr('disabled', true);
				ajax({
					url : '<?php echo Url::to(['goods/goods-approve']) ?>',
					data : {
						'id' : $('#goodsId').val(),
						'reason' : reason,
						'action' : <?php echo \manage\controllers\GoodsController::APPROVE_NOT_PASS ?>
					},
					success : function(aResult){
						if(aResult.status == 1){
							$(this).closeDialog(modal);
							UBox.show(aResult.msg, aResult.status, function(){
								window.location = '<?php echo Url::to(['goods/show-goods-approve-list']) ?>';
							}, 2);
						}else{
							UBox.show(aResult.msg, aResult.status);
							$('.J-approve-button').attr('disabled', false);
						}
					}
				});
			}
		}
	});
}

function passApprove(){
	if(confirm('请确认通过审核了！')){
		$('.J-approve-button').attr('disabled', true);
		ajax({
			url : '<?php echo Url::to(['goods/goods-approve']) ?>',
			data : {
				'id' : $('#goodsId').val(),
				'reason' : '',
				'action' : <?php echo \manage\controllers\GoodsController::APPROVE_PASS ?>
			},
			success : function(aResult){
				if(aResult.status == 1){
					UBox.show(aResult.msg, aResult.status, function(){
								window.location = '<?php echo Url::to(['goods/show-goods-approve-list']) ?>';
							}, 2);
				}else{
					UBox.show(aResult.msg, aResult.status);
					$('.J-approve-button').attr('disabled', false);
				}
			}
		});
	}
}
</script>