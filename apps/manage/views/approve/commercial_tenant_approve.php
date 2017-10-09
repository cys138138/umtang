<?php
use umeworld\lib\Url;
use manage\widgets\Table;
use manage\widgets\ModuleNavi;
use yii\widgets\LinkPager;

$this->setTitle('商户审核');
?>
<div class="row">
	<h2>商户审核列表</h2>
	<div class="table-responsive">
		<?php
			echo Table::widget([
				'aColumns'	=>	[
					'id'	=>	[
						'title' => '商户ID',
						'content' => function($aData){
							return '<lable id="tenant_' . $aData['id'] . '">' . $aData['id'] . '</lable>';
						},
						'class' => 'col-sm-1',
					],
					'mobile'	=>	[
						'title' => '绑定手机',
						'content' => function($aData, $key){
							return $aData['mobile'];
						},
					],
					'leading_official'	=>	[
						'title' => '负责人',
						'content' => function($aData, $key){
							return $aData['tenant_info']['leading_official']['value'];
						},
					],
					'bank_account_holder'	=>	[
						'title' => '开户人',
						'content' => function($aData, $key){
							return $aData['tenant_info']['bank_account_holder']['value'];
						}
					],
					'bank_name'	=>	[
						'title' => '开户银行',
						'content' => function($aData, $key){
							return $aData['tenant_info']['bank_name']['value'];
						}
					],
					'option'	=>	[
						'title' => '操作',
						'content' => function($aData, $key){
							return '<button type="button" class="btn btn-xs btn-link" onclick="showDetail(this)">审核</button>';
						}
					],
				],
				'aDataList'	=>	$aTenantApproveList,
				'style'	=> Table::STYLE_NO_BORDER,
			]);
			echo LinkPager::widget(['pagination' => $oPage]);
		?>
	</div>
</div>
<style type="text/css">
	img{
		width: 270px;
	}
</style>
<script type="text/javascript">
	var aTenantApproveList = <?php echo json_encode($aTenantApproveList);?>;
	var resourceURLPrefix ='<?php echo Yii::getAlias('@r.url');?>';
	for(var i in aTenantApproveList){
		var lableId = 'tenant_' + aTenantApproveList[i]['id'];
		var otherInfoHtml = '';
		for(var j in aTenantApproveList[i]['tenant_info']['other_info']){
			otherInfoHtml += '<div class="col-md-4"><span><img src="' + resourceURLPrefix + '/' + aTenantApproveList[i]['tenant_info']['other_info'][j]['path'] + '"></span></div>';
		}
		var approveHtml = '\
		<div id="detail_info_' + aTenantApproveList[i]['id'] + '" class="J-row" style="background-color:#cccccc; display:none;">\
			<form id ="form_id_' + aTenantApproveList[i]['id'] + '">\
				<input type="hidden" name="_csrf" value="<?php echo Yii::$app->request->csrfToken; ?>" />\
				<input name="id" type="hidden" value="' + aTenantApproveList[i]['id'] + '">\
				<div class="col-md-12">\
					<div class="form-group col-md-12">\
						<label class="col-md-2">负责人姓名:</label>\
						<span>' + aTenantApproveList[i]['tenant_info']['leading_official']['value'] + '</span>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-2">身份证号码:</label>\
						<span>'+ aTenantApproveList[i]['tenant_info']['identity_card']['value'] +'</span>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-2">邮箱:</label>\
						<span>'+ aTenantApproveList[i]['tenant_info']['email']['value'] +'</span>\
					</div>\
					<div class="form-group col-md-12">\
						<div class="col-md-4">\
							<label>身份证正面照片:</label>\
							<span><img src="'+ resourceURLPrefix + '/' + aTenantApproveList[i]['tenant_info']['identity_card_front']['path'] +'"></span>\
						</div>\
						<div class="col-md-4">\
							<label>身份证反面照片:</label>\
							<span><img src="'+ resourceURLPrefix + '/' + aTenantApproveList[i]['tenant_info']['identity_card_back']['path'] +'"></span>\
						</div>\
						<div class="col-md-4">\
							<label>负责人手持身份证照片:</label>\
							<span><img src="'+ resourceURLPrefix + '/' + aTenantApproveList[i]['tenant_info']['identity_card_in_hand']['path'] +'"></span>\
						</div>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-2">银行帐号:</label>\
						<span>'+ aTenantApproveList[i]['tenant_info']['bank_accout']['value'] +'</span>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-2">开户人:</label>\
						<span>'+ aTenantApproveList[i]['tenant_info']['bank_account_holder']['value'] +'</span>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-2">开户行:</label>\
						<span>'+ aTenantApproveList[i]['tenant_info']['bank_name']['value'] +'</span>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-12">银行卡正面照片:</label>\
						<div class="col-md-4">\
							<span><img src="'+ resourceURLPrefix + '/' + aTenantApproveList[i]['tenant_info']['bank_card_photo']['path'] +'"></span>\
						</div>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-12">营业执照照片:</label>\
							'+ otherInfoHtml +'\
					</div>\
					<div class="form-group col-md-12">\
						<button type="button" class="btn btn-xs btn-link J-reason-button" onclick="showReason(this)">原因</button>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea class="col-md-12 J-reason" style="height: 100px"></textarea>\
						</div>\
					</div>\
				</div>\
				<p style="margin-top: 5px; text-align: center;">\
					<button type="button" class="J-pass-btn btn btn-primary" onclick="approvePass(this)">通过</button>\
					<button style="margin-left:15px;" type="button" class="J-pass-btn btn btn-primary" onclick="approveNot(this)">不通过</button>\
				</p>\
			</form>\
		</div>';		
		$('#' + lableId).parents('.J-row').after(approveHtml);
	}
			
	function showReason(obj){
		$(obj).next('.J-reason-box').css('display','block');
		$(obj).text('收起');
		$(obj).attr('onclick','hideReason(this)');
	}
	
	function hideReason(obj){
		$(obj).next('.J-reason-box').css('display','none');
		$(obj).text('原因');
		$(obj).attr('onclick','showReason(this)');
	}
	
	function showDetail(obj){
		var buttonText = $(obj).text();
		var tenantId = $(obj).parents('.J-row').find('lable').text();
		var scene = 'tenantApprove';
		if(buttonText == '审核'){
			$(obj).parents('.J-row').next('.J-row').css('display', 'block');
			$(obj).text('取消');
			ajax({
				url : '<?php echo Url::to(['approve/change-approve-status']); ?>',
				data : {
					id : tenantId,
					button : buttonText,
					scene : scene
				}
			});
		}else{
			$(obj).text('审核');
			$(obj).parents('.J-row').next('.J-row').css('display', 'none');
			ajax({
				url : '<?php echo Url::to(['approve/change-approve-status']); ?>',
				data : {
					id : tenantId,
					button : buttonText,
					scene : scene
				}
			});
		}
	}
	
	function approveNot(obj){
		$(obj).siblings('.J-pass-btn').attr('disabled', true);
		$(obj).attr('disabled', true).text('提交中...');
		var approveReason = $(obj).parents('form').find('.J-reason').val();
		var tenantId = $(obj).parents('form').attr('id').substr(-1);
		ajax({
			url : '<?php echo Url::to(['approve/tenant-approve-not-pass']); ?>',
			data : {
				id : tenantId,
				reason : approveReason
			},
			success : function(aResult){
				UBox.show(aResult.msg, aResult.status);
				if(aResult.status == 1){
					$('#tenant_' + aResult.data).parents('.J-row').remove();
					$('#detail_info_' + aResult.data).remove();
				}else{
					$('#detail_info_' + aResult.data).find('.J-pass-btn').each(function(){
						$(this).attr('disabled', false);
						if($(this).text() == '提交中...'){
							$(this).text('不通过');
						}
					});
				}
			}
		});
	}
	
	function approvePass(obj){
		$(obj).siblings('.J-pass-btn').attr('disabled', true);
		$(obj).attr('disabled', true).text('提交中...');
		var TenantId = $(obj).parents('form').attr('id').substr(-1);
		ajax({
			url : '<?php echo Url::to(['approve/tenant-approve-pass']); ?>',
			data : {
				id : TenantId
			},
			success : function(aResult){
				UBox.show(aResult.msg, aResult.status);
				$('#tenant_' + aResult.data).parents('.J-row').remove();
				$('#detail_info_' + aResult.data).remove();
			}
		});
	}
</script>