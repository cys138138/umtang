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
							if(isset($aData['shop_info']['name']['value'])){
								return $aData['shop_info']['name']['value'];
							}
							return $aData['name'];
						}
					],
					'mobile'	=>	[
						'title' => '绑定手机',
						'content' => function($aData){
							return $aData['mobile'];
						},
					],
					'leading_official'	=>	[
						'title' => '负责人',
						'content' => function($aData){
							return $aData['tenant_info']['leading_official']['value'];
						},
					],
					'bank_account_holder'	=>	[
						'title' => '开户人',
						'content' => function($aData){
							return $aData['tenant_info']['bank_account_holder']['value'];
						}
					],
					'bank_name'	=>	[
						'title' => '开户银行',
						'content' => function($aData){
							return $aData['tenant_info']['bank_name']['value'];
						}
					],
					
					'operate' => [
						'title' => '操作',
						'class' => 'col-sm-1',
						'content' => function($aData){
							return '<a href="' . Url::to(['approve/show-approve-detail', 'id' => $aData['id']]) . '">审核</a>';
						}
					],
				],
				'aDataList'	=>	$aList,
			]);
			echo LinkPager::widget(['pagination' => $oPage]);
		?>
	</div>
</div>
<script type="text/javascript">
	
	$(function(){
		
	});
</script>