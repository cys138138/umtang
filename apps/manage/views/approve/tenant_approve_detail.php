<?php 
use umeworld\lib\Url;
use manage\widgets\Table;
use yii\widgets\LinkPager;
$this->setTitle('商户审核');

$aApproveTenantKeyList = [
	'leading_official',
	'email',
	'identity_card',
	'identity_card_front',
	'identity_card_back',
	'identity_card_in_hand',
	'bank_accout',
	'bank_account_holder',
	'bank_name',
	'bank_accout_type',
	'bank_card_photo',
	'other_info',
];
$aKeyNameList = $aApproveKeyNameList;
$aBankAccountTypeName = [0 => '', 1 => '个人', 2 => '对公'];
?>
<style type="text/css">
.J-approve-table td{width:400px;}
</style>
<br />
<div class="row">
	<div class="col-lg-12">
		<h3>商户信息</h3>
		<?php foreach($aApproveTenantKeyList as $key){
			if(!isset($aCommercialTenantInfo[$key]) || !$aCommercialTenantInfo[$key]){
				continue;
			}
		?>
		<div class="form-group">
			<label><?php echo $aKeyNameList[$key]; ?></label>
			<?php if(in_array($key, ['identity_card_front', 'identity_card_back', 'identity_card_in_hand', 'bank_card_photo'])){$keyPath = $key . '_path'; ?>
				<p><table class="J-approve-table"><tr><td>原来的</td><td>待审核的</td></tr><tr><td><img src="<?php echo Yii::getAlias('@r.url') . '/' . $mCommercialTenant->$keyPath; ?>" width="400" /></td><td><img src="<?php echo Yii::getAlias('@r.url') . '/' . $aCommercialTenantInfo[$key]['path']; ?>" width="400" /></td></tr></table></p>
				<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>"></p>
			<?php }elseif($key == 'other_info' && isset($aCommercialTenantInfo['other_info'])){
					$aOtherInfoPathList = $mCommercialTenant->other_info_path;
					$aOtherInfoPath = array_values($aOtherInfoPathList);
			?>
				<?php foreach($aCommercialTenantInfo['other_info'] as $k => $aValue){ ?>
					<p><table class="J-approve-table"><tr><td>原来的</td><td>待审核的</td></tr><tr><td><?php if(isset($aOtherInfoPath[$k])){ ?><img src="<?php echo Yii::getAlias('@r.url') . '/' . $aOtherInfoPath[$k]; ?>" width="400" /><?php } ?></td><td><img src="<?php echo Yii::getAlias('@r.url') . '/' . $aValue['path']; ?>" width="400" /></td></tr></table></p>
					<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>" data-index="<?php echo $k; ?>"></p>
				<?php } ?>
			<?php }elseif($key == 'bank_accout_type'){ ?>
				<p><table class="J-approve-table"><tr><td>原来的</td><td>待审核的</td></tr><tr><td><?php echo $aBankAccountTypeName[$mCommercialTenant->$key]; ?></td><td><?php echo $aBankAccountTypeName[$aCommercialTenantInfo[$key]['value']];?></td></tr></table></p>
				<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>"></p>
			<?php }else{ ?>
				<p><table class="J-approve-table"><tr><td>原来的</td><td>待审核的</td></tr><tr><td><?php echo $mCommercialTenant->$key; ?></td><td><?php echo $aCommercialTenantInfo[$key]['value']; ?></td></tr></table></p>
				<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>"></p>
			<?php } ?>
		</div>
		<?php } ?>
		<div class="form-group">
			<button type="button" class="J-save-btn btn btn-primary" onclick="submitApprove(this, <?php echo $id; ?>);">提交审核</button>
		</div>
	</div>
</div>
<script type="text/javascript">
	function submitApprove(o, id){
		var aReason = {};
		var aOtherInfoReason = [];
		$('.J-reason').each(function(){
			var flag = true;
			if($(this).attr('data-key') == 'other_info'){
				if($(this).val()){
					aOtherInfoReason.push({
						index : $(this).attr('data-index'),
						reason : $(this).val()
					});
				}
				flag = false;
			}
			
			if($(this).val() && flag){
				aReason[$(this).attr('data-key')] = $(this).val();
			}
		});
		if(aOtherInfoReason.length != 0){
			aReason['other_info'] = aOtherInfoReason;
		}
		
		ajax({
			url : '<?php echo Url::to(['approve/do-tenant-approve']); ?>',
			data : {
				id : id,
				aReason : aReason
			},
			beforeSend : function(){
				$(o).attr('disabled', 'disabled');
			},
			complete : function(){
				$(o).attr('disabled', false);
			},
			success : function(aResult){
				UBox.show(aResult.msg, aResult.status);
			}
		});
	}
</script>