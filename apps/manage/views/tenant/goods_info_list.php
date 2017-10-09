<?php
use umeworld\lib\Url;
use yii\widgets\LinkPager;
use manage\widgets\Table;
use manage\widgets\ModuleNavi;
use common\model\Goods;

$this->setTitle('商品列表');
$this->registerJsFile('@r.jquery.bootstrap.teninedialog.v3');
?>
<div class="row">
	<div class="page-Wrapper">
		<?php echo ModuleNavi::widget([
			'aMenus' => [
				[
					'title' => '商品列表',
					'url' => Url::to(['tenant/show-goods-info-list']),
					'active' => true,
				],
			],
		]); ?>
		<h2>商品列表</h2>
		<div class="table-responsive">
			<?php
				echo Table::widget([
					'aColumns'	=>	[
						'id' => ['title' => 'id'],
						'name' => ['title' => '商户服务'],
						'tenant' => [
							'title' => '所属商户',
							'content' => function($aData){
								$content = '';
								if($aData['tenant_id']){
									$mtenant = common\model\CommercialTenant::findOne($aData['tenant_id']);
									if($mtenant){
										$content = $mtenant->name;
									}
								}
								return $content;
							}
						],
						'type_name' => ['title' => '商品类型'],
						'validity_time' => [
							'title' => '有效期',
							'content' => function($aData){
								$content = '';
								if($aData['validity_time']){
									$content = date('Y-m-d H:i:s', $aData['validity_time']);
								}
								return $content;
							}
						],
						'retail_price' => [
							'title' => '门市价',
							'content' => function($aData){
								$content = '';
								if($aData['retail_price']){
									$content = $aData['retail_price']/100;
								}
								return $content;
							}
						],
						'price' => [
							'title' => '价格',
							'content' => function($aData){
								$content = '';
								if($aData['price']){
									$content = $aData['price']/100;
								}
								return $content;
							}
						],
						'status' => [
							'title' => '状态',
							'content' => function($aData){
								$content = '';
								if($aData['status'] == Goods::HAS_PUT_ON){
									$content = '已上架';
								}
								return $content;
							}
						],
						'sales_count' => ['title' => '售出数量'],
						'create_time' => [
							'title' => '创建时间',
							'content' => function($aData){
								$content = '';
								if($aData['create_time']){
									$content = date('Y-m-d H:i:s', $aData['create_time']);
								}
								return $content;
							}
						],
						'option' => [
							'title' => '操作',
							'content' => function($aData){
								return '<button type="button" class="btn btn-link btn-xs" onclick="off(this,' . $aData['id'] . ')">下架</button>';
							}
						],
					],
					'aDataList'	=>	$aGoodsList,
				]);
				echo LinkPager::widget(['pagination' => $oPage]);
			?>
		</div>
	</div>
</div>
<script type="text/javascript">
function off(obj, id){
	var contentHtml = '<div><textarea rows="3" class="form-control J-reason" placeholder="该操作将强制下架商品，请输入下架理由"></textarea></div>';
	$.teninedialog({
		title : '商品下架',
		content : contentHtml,
		showCloseButton : true,
		otherButtons : ['确定'],
		otherButtonStyles : ['btn-primary'],
		clickButton : function(sender, modal, index){
			if(index == 0){
				var reason = $('.J-reason').val().trim();
				ajax({
					url : '<?php echo Url::to(['tenant/off-the-shelf']) ?>',
					data : {
						'id' : id,
						'reason' : reason
					},
					success : function(aResult){
						if(aResult.status == 1){
							$(this).closeDialog(modal);
							UBox.show(aResult.msg, aResult.status);
							$(obj).parents('.J-row').remove();
						}
						UBox.show(aResult.msg, aResult.status);
					}
				});
			}
		}
	});
}
</script>