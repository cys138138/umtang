<?php 
use umeworld\lib\Url;
use common\model\CommercialTenantType;
use yii\helpers\ArrayHelper;
use manage\widgets\Table;
use yii\widgets\LinkPager;
$this->setTitle('商铺审核');

$aApproveShopKeyList = [
	'name',
	'commercial_tenant_type',
	'profile',
	'address',
	'contact_number',
	'description',
	'commercial_tenant_characteristic_service_relation',
	'photo',
	'teacher',
];
$aKeyNameList = $aApproveKeyNameList;
$aCommercialTenantTypeList = ArrayHelper::index(CommercialTenantType::findAll(), 'id');
?>
<style type="text/css">
.J-approve-table td{width:400px;}
</style>
<br />
<div class="row">
	<div class="col-lg-12">
		<h3>商铺信息</h3>
		<?php foreach($aApproveShopKeyList as $key){ 
			if(!isset($aCommercialTenantShop[$key]) || !$aCommercialTenantShop[$key]){
				continue;
			}
		?>
		<div class="form-group">
			<label><?php echo $aKeyNameList[$key]; ?></label>
			<?php if(in_array($key, ['profile'])){$keyPath = $key . '_path';  ?>
				<p><table class="J-approve-table"><tr><td>原来的</td><td>待审核的</td></tr><tr><td><img src="<?php echo Yii::getAlias('@r.url') . '/' . $mCommercialTenant->$keyPath; ?>" width="400" /></td><td><img src="<?php echo Yii::getAlias('@r.url') . '/' . $aCommercialTenantShop[$key]['path']; ?>" width="400" /></td></tr></table></p>
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
			<?php }elseif($key == 'teacher'){ ?>
				<?php foreach($aCommercialTenantShop['teacher'] as $k => $aValue){ ?>
					<p><img src="<?php echo Yii::getAlias('@r.url') . '/' . $aValue['profile_path']; ?>" width="50" height="50" />&nbsp;&nbsp;&nbsp;&nbsp;姓名:<?php echo $aValue['name']; ?>&nbsp;&nbsp;&nbsp;&nbsp;职务:<?php echo $aValue['duty']; ?>&nbsp;&nbsp;&nbsp;&nbsp;教龄:<?php echo $aValue['seniority']; ?>&nbsp;&nbsp;&nbsp;&nbsp;简介:<?php echo $aValue['description']; ?>&nbsp;&nbsp;&nbsp;&nbsp;</p>
					<p><input class="J-reason form-control" type="text" placeholder="审核不通过请填写原因" data-key="<?php echo $key; ?>" data-index="<?php echo $k; ?>"></p>
				<?php } ?>
			<?php }else{ ?>
				<p><table class="J-approve-table"><tr><td>原来的</td><td>待审核的</td></tr><tr><td><?php echo $mCommercialTenant->$key; ?></td><td><?php echo $aCommercialTenantShop[$key]['value']; ?></td></tr></table></p>
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
		var aCharacteristicServiceRelationReason = [];
		var aPhotoReason = [];
		var aTeacherReason = [];
		$('.J-reason').each(function(){
			var flag = true;
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
			if($(this).attr('data-key') == 'teacher'){
				if($(this).val()){
					aTeacherReason.push({
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
		if(aCharacteristicServiceRelationReason.length != 0){
			aReason['commercial_tenant_characteristic_service_relation'] = aCharacteristicServiceRelationReason;
		}
		if(aPhotoReason.length != 0){
			aReason['photo'] = aPhotoReason;
		}
		if(aTeacherReason.length != 0){
			aReason['teacher'] = aTeacherReason;
		}
		ajax({
			url : '<?php echo Url::to(['approve/do-tenant-shop-approve']); ?>',
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