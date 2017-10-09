<?php 
use umeworld\lib\Url;
use manage\widgets\ModuleNavi;

$this->setTitle('商户信息');

$aApproveKeyNameList = [
	'leading_official' => '负责人',
	'identity_card' => '身份证',
	'identity_card_front' => '身份证正面',
	'identity_card_back' => '身份证反面',
	'identity_card_in_hand' => '手持身份证',
	'email' => '邮箱',
	'bank_accout' => '银行帐号',
	'bank_account_holder' => '开户人',
	'bank_name' => '开户银行',
	'bank_accout_type' => '结算类型',
	'bank_card_photo' => '银行卡照片',
	'other_info_path' => '其他资料',
	'name' => '商铺名称',
	'profile' => '商铺头像',
	'city_name' => '城市',
	'address' => '商铺地址',
	'contact_number' => '商铺电话',
	'description' => '商铺描述',
	'characteristic_service_name' => '特色服务',
	'tenant_type_name' => '商户类型'
];
$aTenantKeyList = [
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
	'other_info_path',
];
$aShopKeyList = [
	'name',
	'profile',
	'city_name',
	'address',
	'contact_number',
	'description',
	'characteristic_service_name',
	'tenant_type_name'
];
$aBankAccountTypeName = [0 => '未知', 1 => '个人', 2 => '对公'];
?>
<div class="row">
	<?php echo ModuleNavi::widget([
		'aMenus' => [
			[
				'title' => '商户列表',
				'url' => Url::to(['tenant/show-list']),
				'active' => true,
			],
		],
	]); ?>
	<div class="col-md-4">
		<div class="col-md-12">
			<h3>商户信息</h3>
		</div>
	<?php
		foreach($aTenantKeyList as $field) {
			foreach($aApproveKeyNameList as $key => $value) {
				if($field == $key){
					if($field == 'other_info_path'){
						if(!empty($tenantInfo[$field])){
	?>
						<div class="col-md-12">
							<label class="col-md-12"><?php echo $value; ?></label>
	<?php
							foreach($tenantInfo[$field] as $path){
	?>
								<img class="col-md-12" src="<?php echo Yii::getAlias('@r.url') . '/' . $path; ?>" />
	<?php
							}
	?>
						</div>
	<?php				}
					}elseif(in_array($field, ['identity_card_front', 'identity_card_back', 'identity_card_in_hand', 'bank_card_photo'])){
	?>
						<div class="col-md-12">
							<label class="col-md-12"><?php echo $value; ?></label>
							<img class="col-md-12" src="<?php echo Yii::getAlias('@r.url') . '/' . $tenantInfo[$field . '_path']; ?>" />
						</div>
	<?php
					}elseif($field == 'bank_accout_type'){
	?>
						<div class="col-md-12">
							<label class="col-md-3"><?php echo $value; ?></label>
							<label class="col-md-9"><?php echo $aBankAccountTypeName[$tenantInfo[$field]]; ?></label>
						</div>
	<?php
					}else{
	?>
						<div class="col-md-12">
							<label class="col-md-3"><?php echo $value; ?></label>
							<label class="col-md-9"><?php echo $tenantInfo[$field]; ?></label>
						</div>
	<?php			}
				}
			}
		}
	?>
	</div>
	<div class="col-md-4">
		<div class="col-md-12">
			<h3>商铺信息</h3>
		</div>
	<?php
		foreach($aShopKeyList as $field) {
			foreach($aApproveKeyNameList as $key => $value) {
				if($field == $key){
					if($field == 'profile'){
	?>
						<div class="col-md-12">
							<label class="col-md-12"><?php echo $value; ?></label>
							<img class="col-md-12" src="<?php echo Yii::getAlias('@r.url') . '/' . $tenantInfo[$field . '_path']; ?>" />
						</div>
	<?php
					}elseif(in_array($field, ['characteristic_service_name', 'tenant_type_name'])){
						if(!empty($tenantInfo[$field])){
	?>
							<div class="col-md-12">
								<label class="col-md-12"><?php echo $value; ?></label>
	<?php
								foreach($tenantInfo[$field] as $path){
	?>
									<label class="col-md-3"><?php echo $path; ?></label>
	<?php						}
	?>
							</div>
	<?php
						}
					}elseif($field == 'city_name'){
						if(!empty($tenantInfo[$field])){
	?>					
							<div class="col-md-12">
								<label class="col-md-3"><?php echo $value; ?></label>
								<label class="col-md-9"><?php echo $tenantInfo[$field]; ?></label>
							</div>
	<?php				}
					}else{
	?>
						<div class="col-md-12">
							<label class="col-md-3"><?php echo $value; ?></label>
							<label class="col-md-9"><?php echo $tenantInfo[$field]; ?></label>
						</div>
	<?php			}
				}
			}
		}
	?>
	</div>
	
</div>
<div class="col-md-12">
	<a href="#" onclick="javascript :history.back(-1);" class="btn btn-primary">返回</a>
</div>