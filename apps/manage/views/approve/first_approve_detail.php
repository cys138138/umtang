<?php 
use umeworld\lib\Url;
use yii\helpers\ArrayHelper;
use manage\widgets\Table;
use common\model\CommercialTenantType;
use yii\widgets\LinkPager;
$this->setTitle('商户初审');

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
$aApproveShopKeyList = [
	'name',
	'commercial_tenant_type',
	'profile',
	'address',
	'contact_number',
	'description',
	//'commercial_tenant_characteristic_service_relation',
	'photo',
];
$aKeyNameList = $aApproveKeyNameList;
$aBankAccountTypeName = [0 => '', 1 => '个人', 2 => '对公'];
$aCommercialTenantTypeList = ArrayHelper::index(CommercialTenantType::findAll(), 'id');
?>
<br />
<div class="row">
	<div class="col-lg-12">
		<h3>商户信息</h3>
		<?php foreach($aApproveTenantKeyList as $key){ ?>
		<div class="form-group">
			<label><?php echo $aKeyNameList[$key]; ?></label>
			<?php if(in_array($key, ['identity_card_front', 'identity_card_back', 'identity_card_in_hand', 'bank_card_photo'])){ ?>
				<p><img src="<?php echo Yii::getAlias('@r.url') . '/' . $aCommercialTenantInfo[$key]['path']; ?>" width="400" /></p>
				<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>"></p>
			<?php }elseif($key == 'other_info' && isset($aCommercialTenantInfo['other_info'])){ ?>
				<?php foreach($aCommercialTenantInfo['other_info'] as $k => $aValue){ ?>
					<p><img src="<?php echo Yii::getAlias('@r.url') . '/' . $aValue['path']; ?>" width="400" /></p>
					<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>" data-index="<?php echo $k; ?>"></p>
				<?php } ?>
			<?php }elseif($key == 'bank_accout_type'){ ?>
				<p><?php echo $aBankAccountTypeName[$aCommercialTenantInfo[$key]['value']]; ?></p>
				<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>"></p>
			<?php }else{ ?>
				<p><?php echo $aCommercialTenantInfo[$key]['value']; ?></p>
				<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>"></p>
			<?php } ?>
		</div>
		<?php } ?>
		<br />
		<h3>商铺信息</h3>
		<?php foreach($aApproveShopKeyList as $key){ ?>
		<div class="form-group">
			<label><?php echo $aKeyNameList[$key]; ?></label>
			<?php if(in_array($key, ['profile'])){ ?>
				<p><img src="<?php echo Yii::getAlias('@r.url') . '/' . $aCommercialTenantShop[$key]['path']; ?>" width="400" /></p>
				<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>"></p>
			<?php }elseif($key == 'photo'){ ?>
				<?php foreach($aCommercialTenantShop['photo'] as $k => $aValue){ ?>
					<p><img src="<?php echo Yii::getAlias('@r.url') . '/' . $aValue['path']; ?>" width="400" /></p>
					<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>" data-index="<?php echo $k; ?>"></p>
				<?php } ?>
			<?php }elseif($key == 'commercial_tenant_characteristic_service_relation'){ ?>
				<?php foreach($aCommercialTenantShop['commercial_tenant_characteristic_service_relation'] as $k => $aValue){ ?>
					<p><?php echo $aValue['name']; ?></p>
					<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>" data-index="<?php echo $k; ?>"></p>
				<?php } ?>
			<?php }elseif($key == 'commercial_tenant_type'){ ?>
				<?php foreach($aCommercialTenantShop['commercial_tenant_type']['value'] as $k => $typeId){ ?>
					<p><?php echo $aCommercialTenantTypeList[$typeId]['name']; ?></p>
					<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>" data-index="<?php echo $k; ?>"></p>
				<?php } ?>
			<?php }else{ ?>
				<p><?php echo $aCommercialTenantShop[$key]['value']; ?></p>
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
		var aCharacteristicServiceRelationReason = [];
		var aPhotoReason = [];
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
			if($(this).attr('data-key') == 'commercial_tenant_characteristic_service_relation'){
				if($(this).val()){
					aCharacteristicServiceRelationReason.push({
						index : $(this).attr('data-index'),
						reason : $(this).val()
					});
				}
				flag = false;
			}
			if($(this).attr('data-key') == 'photo'){
				if($(this).val()){
					aPhotoReason.push({
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
		if(aCharacteristicServiceRelationReason.length != 0){
			aReason['commercial_tenant_characteristic_service_relation'] = aCharacteristicServiceRelationReason;
		}
		if(aPhotoReason.length != 0){
			aReason['photo'] = aPhotoReason;
		}
		ajax({
			url : '<?php echo Url::to(['approve/do-first-approve']); ?>',
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