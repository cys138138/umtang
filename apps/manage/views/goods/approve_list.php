<?php
use umeworld\lib\Url;
use manage\widgets\Table;
use yii\widgets\LinkPager;
use manage\widgets\ModuleNavi;

$this->setTitle('商品审核');
?>
<div class="row">
	<?php echo ModuleNavi::widget([
		'aMenus' => [
			[
				'title' => '商品列表',
				'url' => Url::to(['goods/show-goods-approve-list']),
				'active' => true,
			],
		],
	]); ?>
	<h2>商品审核</h2>
	<div class="table-responsive">
		<?php
			echo Table::widget([
				'aColumns'	=>	[
					'id' => [
						'title'	=>	'商品id',
					],
					'name'	=>	[
						'title' => '商品名',
					],
					'type_name'	=>	[
						'title' => '商品类型',
					],
					'validity_time'	=>	[
						'title' => '过期时间',
						'content' => function($aData, $key){
							return date('Y-m-d', $aData['validity_time']);
						},
					],
					'create_time'	=>	[
						'title' => '创建时间',
						'content' => function($aData, $key){
							return date('Y-m-d H:i:s', $aData['create_time']);
						},
					],
					'option'	=>	[
						'title' => '操作',
						'content' => function($aData, $key){
							return '<a href="' . Url::to(['goods/show-goods-approve-detail', 'goodsId' => $aData['id']])  . '" class="btn btn-xs btn-link">审核</a>';
						}
					],
				],
				'aDataList'	=>	$aGoodsList,
				'style'	=> Table::STYLE_NO_BORDER,
			]);
			echo LinkPager::widget(['pagination' => $oPage]);
		?>
</div>
