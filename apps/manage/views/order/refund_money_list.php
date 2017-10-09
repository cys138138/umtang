<?php
use umeworld\lib\Url;
use manage\widgets\Table;
use yii\widgets\LinkPager;
use manage\widgets\ModuleNavi;

$this->setTitle('订单退款');
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
				'title' => '订单退款',
				'url' => Url::to(['order/show-refund-money-list']),
				'active' => true,
			],
		],
	]); ?>
	<div id="page-wrapper">
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
						'type_name' => ['title' => '订单类型'],
						'status_name' => ['title' => '订单状态'],
						'mobile' =>	['title' => '购买人手机号',],
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
									return date('Y-m-d h:i:s', $aData['pay_time']);
								}
								return;
							}
						],
						'option' =>	[
							'title' => '操作',
							'content' => function($aData){
								return $content = '<button class="btn btn-xs btn-link" onclick="showRefund(this,' . $aData['id'] . ',' . $aData['pay_money']/100 . ')">退款</button>';
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
function filterInput(obj, payMoney){  
	obj.value = obj.value.replace(/[^\d.]/g,""); 
	obj.value = obj.value.replace(/\.{2,}/g,".");   
	obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");  
	obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');
	if(obj.value.indexOf(".")< 0 && obj.value !=""){
		obj.value = parseFloat(obj.value);
	}
	if(obj.value > payMoney){
		obj.value = payMoney;
	}
}
function showRefund(o, id, payMoney){
	var contentHtml = '<div><p>说明：此操作向支付平台提交退款申请，支付平台收到退款请求并且验证成功之后，按照退款规则将支付款按原路退到买家帐号上。</p></div><p style="color:red;">实际支付金额: ' + payMoney + '（元）</p><div><input type="text" class="J-refund-money form-control" onkeyup="filterInput(this, ' + payMoney + ')" id="refund" placeholder="请输入退款金额（不应大于实际支付金额），单位为元，支持两位小数" /></div>';
	$.teninedialog({
		title : '退款',
		content : contentHtml,
		showCloseButton : true,
		otherButtons : ['确定'],
		otherButtonStyles : ['btn-primary'],
		clickButton : function(sender, modal, index){
			if(index == 0){
				var refundMoney = $('#refund').val().trim();
				ajax({
					url : '<?php echo Url::to(['order/refund-money']) ?>',
					data : {
						'id' : id,
						'refundMoney' : refundMoney,
						'payMoney' : payMoney
					},
					success : function(aResult){
						if(aResult.status == 1){
							$(this).closeDialog(modal);
							UBox.show(aResult.msg, aResult.status);
							$(o).parents('.J-row').remove();
						}else{
							UBox.show(aResult.msg, aResult.status);
						}
					}
				});
			}
		}
	});
}
</script>