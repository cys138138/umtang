<?php
use umeworld\lib\Url;
use manage\widgets\Table;
use yii\widgets\LinkPager;
use manage\widgets\ModuleNavi;

$this->setTitle('商户提现');
class AccountAsset extends \manage\assets\CommonAsset
{
	public $css = [
		'@r.css.bootstrap',
		'@r.css.sb-admin',
		'@r.css.sb-morris',
		'@r.css.sb-font-awesome',
	];
	public $js = [
		'@r.js.area-selector',
		'@r.js.wdate-picker',
	];
}
AccountAsset::register($this);
?>
<div class="row">
	<?php echo ModuleNavi::widget([
		'aMenus' => [
			[
				'title' => '商户提现',
				'url' => Url::to(['account/show-withdraw-cash-list']),
				'active' => true
			]
		]
	]);?>
	<div id="page-wrapper">
		<div class="row">
			<div class="col-lg-12">
				<form role="form" name="searchForm" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label">发起商户</label>
						<div class="col-sm-2">
							<input class="J-style-name form-control" name="tenantName" type="text" value="<?php echo $tenantName;?>" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">发起时间</label>
						<div class="col-sm-2">
							<input name="startTime" type="text" class="J-form-rush-time form-control col-md-2" onclick="WdatePicker({dateFmt:'yyyy-M-d'})" value="<?php echo $startTime;?>" />
						</div>
						<div class="col-sm-2">
							<input name="endTime" type="text" class="J-form-rush-time form-control col-md-2" onclick="WdatePicker({dateFmt:'yyyy-M-d'})" value="<?php echo $endTime;?>" />
						</div>
						<button type="button" class="btn btn-primary" onclick="search()">搜索</button>
						<button type="button" class="btn btn-primary" onclick="generateExcel()">导出</button>
					</div>	
				</form>
			</div>
		</div>
		<div class="table-responsive">
			<?php 
				echo Table::widget([
					'aColumns' => [
						'tenant' => [
							'title' => '商户',
							'content' => function($aData){
								$content = '';
								if(isset($aData['tenant_info']['name'])){
									$content = $aData['tenant_info']['name'];
								}
								return $content;
							}
						],
						'bank_name' => [
							'title' => '开户银行',
							'content' => function($aData){
								$content = '';
								if(isset($aData['tenant_info']['bank_name'])){
									$content = $aData['tenant_info']['bank_name'];
								}
								return $content;
							}
						],
						'bank_accout_type' => [
							'title' => '帐号类型',
							'content' => function($aData){
								$content = '';
								if(isset($aData['tenant_info']['bank_accout_type']) && $aData['tenant_info']['bank_accout_type'] == \manage\model\CommercialTenant::BANK_ACCOUNT_TYPE_PERSONAL){
									$content = '个人';
								}elseif(isset($aData['tenant_info']['bank_accout_type']) && $aData['tenant_info']['bank_accout_type'] == \manage\model\CommercialTenant::BANK_ACCOUNT_TYPE_COMMUNAL){
									$content = '对公';
								}
								return $content;
							}
						],
						'bank_accout' => [
							'title' => '银行帐号',
							'content' => function($aData){
								$content = '';
								if(isset($aData['tenant_info']['bank_accout'])){
									$content = $aData['tenant_info']['bank_accout'];
								}
								return $content;
							}
						],
						'bank_account_holder' => [
							'title' => '开户人',
							'content' => function($aData){
								$content = '';
								if(isset($aData['tenant_info']['bank_account_holder'])){
									$content = $aData['tenant_info']['bank_account_holder'];
								}
								return $content;
							}
						],
						'balance' => [
							'title' => '提现后余额',
							'content' => function($aData){
								$content = 0;
								if(isset($aData['balance'])){
									$content = $aData['balance']/100;
								}
								return $content;
							}
						],
						'amount' => [
							'title' => '提现金额',
							'content' => function($aData){
								$content = 0;
								if(isset($aData['amount'])){
									$content = $aData['amount']/100;
								}
								return $content;
							}
						],
						'create_time' => [
							'title' => '发起时间',
							'content' => function($aData){
								return date('Y-m-d H:i:s', $aData['create_time']);
							}
						],
						'option' => [
							'title' => '操作',
							'content' => function($aData){
								return '<button class="btn btn-xs btn-link" onclick="withdraw(this,' . $aData['id'] . ')">已提现</button>';
							}
						],
					],
					'aDataList' => $aWithdrawCashList,
				]);
				echo LinkPager::widget(['pagination' => $oPage]);
			?>
		</div>
	</div>
</div>
<script type="text/javascript">
function search(){
	var search = $('form[name="searchForm"]').serialize();
	location.href = '<?php echo Url::to(['account/show-withdraw-cash-list']);?>?' + search;
}
function generateExcel(){
	var search = $('form[name="searchForm"]').serialize();
	UBox.confirm('导出生成Excel', function(){
			location.href = '<?php echo Url::to(['account/generate-excel']);?>?' + search;
		}	
	);
}
function withdraw(o, id){
	UBox.confirm('提现成功?', function(){
		ajax({
			url : '<?php echo Url::to(['account/withdraw-cash-success']); ?>',
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
	});
}
</script>