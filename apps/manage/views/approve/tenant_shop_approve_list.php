<?php 
use umeworld\lib\Url;
use manage\widgets\Table;
use yii\widgets\LinkPager;
$this->setTitle('商户初审');
?>
<br />
<div class="row">
	<div class="table-responsive">
		<?php
			echo Table::widget([
				'aColumns'	=>	[
					'id'	=>	['title' => '商户ID'],
					'name'	=>	[
						'title' => '商铺名',
						'content' => function($aData){
							return $aData['name'];
						}
					],
					'mobile'	=>	[
						'title' => '绑定手机',
						'content' => function($aData){
							return $aData['mobile'];
						},
					],
					'operate' => [
						'title' => '操作',
						'class' => 'col-sm-1',
						'content' => function($aData){
							return '<a href="' . Url::to(['approve/show-tenant-shop-approve-detail', 'id' => $aData['id']]) . '">审核</a>';
						}
					],
				],
				'aDataList'	=>	$aList,
			]);
			echo LinkPager::widget(['pagination' => $oPage]);
		?>
	</div>
</div>
