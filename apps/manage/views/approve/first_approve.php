
<?php
use umeworld\lib\Url;
use manage\widgets\Table;
use manage\widgets\ModuleNavi;
use yii\widgets\LinkPager;

$this->setTitle('商户初审');
?>
<div class="row">
	<h2>初审列表</h2>
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
					'name'	=>	[
						'title' => '商铺名',
						'content' => function($aData, $key){
							return $aData['shop_info']['name']['value'];
						}
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
				'aDataList'	=>	$aTenantList,
				'style'	=> Table::STYLE_NO_BORDER,
			]);
			echo LinkPager::widget(['pagination' => $oPage]);
		?>
	</div>
</div>
<style type="text/css">
	img{
		width: 80%;
	}
	label{
		
	}
</style>
<script type="text/javascript">
	var aTenantList = <?php echo json_encode($aTenantList);?>;
	var resourceURLPrefix ='<?php echo Yii::getAlias('@r.url');?>';
	for(var i in aTenantList){
		var lableId = 'tenant_' + aTenantList[i]['id'];
		var otherInfoHtml = '';
		for(var j in aTenantList[i]['tenant_info']['other_info']){
			otherInfoHtml += '<span><img src="' + resourceURLPrefix + '/' + aTenantList[i]['tenant_info']['other_info'][j]['path'] + '"></span>\
							<div style="display:none;" class="form-group J-reason-box">\
								<textarea name="other_info[' + j + ']" class="col-md-12" style="height: 100px"></textarea>\
							</div>';
		}
		var shopPhotoHtml = '';
		for(var j in aTenantList[i]['shop_info']['photo']){
			shopPhotoHtml += '<span><img src="' + resourceURLPrefix + '/' + aTenantList[i]['shop_info']['photo'][j]['path'] + '"></span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="photo[' + j + ']" class="col-md-12" style="height: 100px"></textarea>\
						</div>';
		}
		var approveHtml = '\
		<div id="detail_info_' + aTenantList[i]['id'] + '" class="J-row" style="background-color:#cccccc; display:none;">\
			<form id ="form_id_' + aTenantList[i]['id'] + '">\
				<input type="hidden" name="_csrf" value="<?php echo Yii::$app->request->csrfToken; ?>" />\
				<input name="id" type="hidden" value="' + aTenantList[i]['id'] + '">\
				<div class="col-md-6">\
					<div class="form-group">\
						<label class="col-md-4">负责人姓名:</label>\
						<span>' + aTenantList[i]['tenant_info']['leading_official']['value'] + '</span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="leading_official" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-4">身份证号码:</label>\
						<span>'+ aTenantList[i]['tenant_info']['identity_card']['value'] +'</span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="identity_card" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-4">邮箱:</label>\
						<span>'+ aTenantList[i]['tenant_info']['email']['value'] +'</span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="email" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-12">身份证正面照片:</label>\
						<span><img src="'+ resourceURLPrefix + '/' + aTenantList[i]['tenant_info']['identity_card_front']['path'] +'"></span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="identity_card_front" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-12">身份证反面照片:</label>\
						<span><img src="'+ resourceURLPrefix + '/' + aTenantList[i]['tenant_info']['identity_card_back']['path'] +'"></span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="identity_card_back" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-12">负责人手持身份证照片:</label>\
						<span><img src="'+ resourceURLPrefix + '/' + aTenantList[i]['tenant_info']['identity_card_in_hand']['path'] +'"></span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="identity_card_in_hand" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-4">银行帐号:</label>\
						<span>'+ aTenantList[i]['tenant_info']['bank_accout']['value'] +'</span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="bank_accout" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-4">开户人:</label>\
						<span>'+ aTenantList[i]['tenant_info']['bank_account_holder']['value'] +'</span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="bank_account_holder" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-4">开户行:</label>\
						<span>'+ aTenantList[i]['tenant_info']['bank_name']['value'] +'</span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="bank_name" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-12">银行卡正面照片:</label>\
						<span><img src="'+ resourceURLPrefix + '/' + aTenantList[i]['tenant_info']['bank_card_photo']['path'] +'"></span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="bank_card_photo" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-12">营业执照照片:</label>\
							'+ otherInfoHtml +'\
					</div>\
				</div>\
				<div class="col-md-6">\
					<div class="form-group">\
						<label class="col-md-4">商铺名称:</label>\
						<span>'+ aTenantList[i]['shop_info']['name']['value'] +'</span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="name" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-12">商铺头像:</label>\
						<span><img src="'+ resourceURLPrefix + '/' + aTenantList[i]['shop_info']['profile']['path'] +'"></span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="profile" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-4">详细位置:</label>\
						<span>'+ aTenantList[i]['shop_info']['address']['value'] +'</span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="address" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-4">商铺电话:</label>\
						<span>'+ aTenantList[i]['shop_info']['contact_number']['value'] +'</span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="contact_number" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-4">商铺描述:</label>\
						<span>'+ aTenantList[i]['shop_info']['description']['value'] +'</span>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea name="description" class="col-md-12" style="height: 100px"></textarea>\
						</div>\
					</div>\
					<div class="form-group">\
						<label class="col-md-12">商铺照片:</label>\
						'+ shopPhotoHtml +'\
					</div>\
				</div>\
				<div style="clear:both;"></div>\
				<p style="margin-top:5px; text-align: center;">\
					<button type="button" class="J-pass-btn btn btn-primary" onclick="approvePass(this)">通过</button>\
					<button style="margin-left:15px;" type="button" class="J-pass-btn btn btn-primary" onclick="approveNot(this)">不通过</button>\
				</p>\
			</form>\
		</div>';		
		$('#' + lableId).parents('.J-row').after(approveHtml);
		$('#detail_info_' + aTenantList[i]['id']).find('span').after('<button type="button" class="btn btn-xs btn-link J-reason-button" onclick="showReason(this)">原因</button>');
	}
			
	function showReason(obj){
		$(obj).next('.J-reason-box').css('display','block');
		$(obj).text('收起');
		$(obj).attr('onclick','hideReason(this)');
	}
	
	function showDetail(obj){
		var buttonText = $(obj).text();
		var tenantId = $(obj).parents('.J-row').find('lable').text();
		var scene = 'firstApprove';
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
	
	function hideReason(obj){
		$(obj).next('.J-reason-box').css('display','none');
		$(obj).text('原因');
		$(obj).attr('onclick','showReason(this)');
	}
	
	function approveNot(obj){
		$(obj).siblings('.J-pass-btn').attr('disabled', true);
		$(obj).attr('disabled', true).text('提交中...');
		var aApproveForm = $(obj).parents('form').serializeArray();
		ajax({
			url : '<?php echo Url::to(['approve/first-approve-not-pass']); ?>',
			data : aApproveForm,
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
			url : '<?php echo Url::to(['approve/first-approve-pass']); ?>',
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