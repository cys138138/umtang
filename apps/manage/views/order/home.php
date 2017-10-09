<?php
use umeworld\lib\Url;
use manage\widgets\Table;
use yii\widgets\LinkPager;
use manage\widgets\ModuleNavi;

$this->setTitle('订单流水');
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
				'title' => '订单流水',
				'url' => Url::to(['order/show-home']),
				'active' => true,
			],
		],
	]); ?>
	<div id="page-wrapper">
		<div class="row">
			<div class="col-lg-12">
				<form role="form" name="searchForm" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label">服务类型</label>
						<div class="col-sm-2">
						<select class="J-style-category col-sm-4 form-control" name="service">
							<option value="0">全部</option>
							<?php foreach($aServiceList as $key => $aServiceType){ ?>
								<option value="<?php echo $key; ?>" name="<?php echo $aServiceType['name']; ?>" <?php echo $key == $service ? 'selected="selected"' : ''; ?>><?php echo $aServiceType['name']; ?></option>
							<?php } ?>
						</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">订单类型</label>
						<div class="col-sm-2">
						<select class="J-style-category col-sm-4 form-control" name="type">
							<option value="0">全部</option>
							<?php foreach($aTypeList as $key => $orderType){ ?>
								<option value="<?php echo $key; ?>" name="<?php echo $orderType; ?>" <?php echo $key == $type ? 'selected="selected"' : ''; ?>><?php echo $orderType; ?></option>
							<?php } ?>
						</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">订单状态</label>
						<div class="col-sm-2">
						<select class="J-style-category col-sm-4 form-control" name="status">
							<option value="0">全部</option>
							<?php foreach($aStatusList as $key => $orderStatus){ ?>
								<option value="<?php echo $key; ?>" name="<?php echo $orderStatus; ?>" <?php echo $key == $status ? 'selected="selected"' : ''; ?>><?php echo $orderStatus; ?></option>
							<?php } ?>
						</select>
						</div>
						<button type="button" class="btn btn-primary" onclick="search();">搜索</button>
						<button type="button" class="btn btn-primary" onclick="generateExcel()">导出</button>
					</div>	
				</form>
			</div>
		</div>
		<div class="table-responsive">
			<?php
				echo Table::widget([
					'aColumns'	=>	[
						'order_num' => ['title'	=> '订单号'],
						'tenant_name' => [
							'title' => '所属商户',
							'content' => function($aData){
								$content = '';
								if(isset($aData['tenant_info']['name'])){
									$content = $aData['tenant_info']['name'];
								}
								return $content;
							}
						],
						'goods_info' => [
							'title' => '购买服务',
							'content' => function($aData){
								$content = '';
								if(isset($aData['goods_info']['name'])){
									$content = $aData['goods_info']['name'];
								}
								return $content;
							}
						],
						'service_name' => [
							'title' => '服务类型',
							'content' => function($aData){
								$content = '';
								if(isset($aData['goods_info']['type_name']) && $aData['goods_info']['type_name']){
									$content = $aData['goods_info']['type_name'];
								}
								return $content;
							}
						],
						'type_name' => ['title' => '订单类型'],
						'status_name' => ['title' => '订单状态'],
						'mobile' =>	['title' => '购买人手机号'],
						'original_price' => [
							'title' => '原价格（元）',
							'content' => function($aData){
								$content = 0;
								if(isset($aData['original_price'])){
									$content = $aData['original_price']/100;
								}
								return $content;
							}
						],
						'pay_discount' => [
							'title' => '折扣减免价格（元）',
							'content' => function($aData){
								$content = 0;
								if($aData['type'] == 1){
									if(isset($aData['price'])){
										$content = ($aData['original_price'] - $aData['price'])/100;
									}
								}else{
									$content = '-';
								}
								return $content;
							}
						],
						'accumulate_points_money' => [
							'title' => '积分抵扣价格（元）',
							'content' => function($aData){
								$content = 0;
								if(isset($aData['accumulate_points_money'])){
									$content = $aData['accumulate_points_money']/100;
								}
								return $content;
							}
						],
						'pay_money'	=> [
							'title' => '实际支付金额（元）',
							'content' => function($aData){
								$content = 0;
								if(isset($aData['pay_money'])){
									$content = $aData['pay_money']/100;
								}
								return $content;
							}
						],
						'pay_time'	=> [
							'title' => '付款时间',
							'content' => function($aData){
								if($aData['pay_time']){
									return date('Y-m-d H:i:s', $aData['pay_time']);
								}
								return;
							}
						],
						'create_time'	=> [
							'title' => '创建时间',
							'content' => function($aData){
								return date('Y-m-d H:i:s', $aData['create_time']);
							}
						],
					],
					'aDataList'	=>	$aOrderList,
				]);
				echo LinkPager::widget(['pagination' => $oPage]);
			?>
		</div>
	</div>
</div>
<script type="text/javascript">
function search(){
	var search = $('form[name="searchForm"]').serialize();
	location.href = '<?php echo Url::to(['order/show-home']);?>?' + search;
}

function generateExcel(){
	var search = $('form[name="searchForm"]').serialize();
	UBox.confirm('导出生成Excel', function(){
			location.href = '<?php echo Url::to(['order/generate-excel']);?>?' + search;
		}	
	);
}
</script>