<?php
use umeworld\lib\Url;
use manage\widgets\Table;
use manage\widgets\ModuleNavi;
use yii\widgets\LinkPager;

$this->setTitle('商铺审核');
?>
<div class="row">
	<h2>商铺审核列表</h2>
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
					'option'	=>	[
						'title' => '操作',
						'content' => function($aData, $key){
							return '<button type="button" class="btn btn-xs btn-link" onclick="showDetail(this)">审核</button>';
						}
					],
				],
				'aDataList'	=>	$aTenantShopApproveList,
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
	var aTenantShopApproveList = <?php echo json_encode($aTenantShopApproveList);?>;
	var resourceURLPrefix ='<?php echo Yii::getAlias('@r.url');?>';
	for(var i in aTenantShopApproveList){
		var lableId = 'tenant_' + aTenantShopApproveList[i]['id'];
		var shopPhotoHtml = '';
		for(var j in aTenantShopApproveList[i]['shop_info']['photo']){
			shopPhotoHtml += '<div class="col-md-4"><span><img src="' + resourceURLPrefix + '/' + aTenantShopApproveList[i]['shop_info']['photo'][j]['path'] + '"></span></div>';
		}
		var shopServiceHtml = '';
		for(var j in aTenantShopApproveList[i]['shop_info']['characteristic_service_type']){
			shopServiceHtml += '<div class="col-md-2"><span>' + aTenantShopApproveList[i]['shop_info']['characteristic_service_type'][j]['name'] + '</span></div>';
		}
		var shopTypeHtml = '';
		for(var j in aTenantShopApproveList[i]['shop_info']['commercial_tenant_type']){
			shopTypeHtml += '<div class="col-md-2"><span>' + aTenantShopApproveList[i]['shop_info']['commercial_tenant_type'][j]['name'] + '</span></div>';
		}
		var approveHtml = '\
		<div id="detail_info_' + aTenantShopApproveList[i]['id'] + '" class="J-row" style="background-color:#cccccc; display:none;">\
			<form id ="form_id_' + aTenantShopApproveList[i]['id'] + '">\
				<input type="hidden" name="_csrf" value="<?php echo Yii::$app->request->csrfToken; ?>" />\
				<input name="id" type="hidden" value="' + aTenantShopApproveList[i]['id'] + '">\
				<div class="col-md-12">\
					<div class="form-group col-md-12">\
						<label class="col-md-2">商铺名称:</label>\
						<span>'+ aTenantShopApproveList[i]['shop_info']['name']['value'] +'</span>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-12">商铺头像:</label>\
						<div class="col-md-4">\
							<span><img src="'+ resourceURLPrefix + '/' + aTenantShopApproveList[i]['shop_info']['profile']['path'] +'"></span>\
						</div>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-2">详细地址:</label>\
						<span>'+ aTenantShopApproveList[i]['shop_info']['address']['value'] +'</span>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-2">商铺电话:</label>\
						<span>'+ aTenantShopApproveList[i]['shop_info']['contact_number']['value'] +'</span>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-2">商铺描述:</label>\
						<span>'+ aTenantShopApproveList[i]['shop_info']['description']['value'] +'</span>\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-12">商铺照片:</label>\
						'+ shopPhotoHtml +'\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-12">特色服务:</label>\
						'+ shopServiceHtml +'\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-12">商户分类:</label>\
						'+ shopTypeHtml +'\
					</div>\
					<div class="form-group col-md-12">\
						<label class="col-md-2">优惠信息:</label>\
						<span>'+ aTenantShopApproveList[i]['shop_info']['preferential_info']['value'] +'</span>\
					</div>\
					<div class="form-group col-md-12">\
						<button type="button" class="btn btn-xs btn-link J-reason-button" onclick="showReason(this)">原因</button>\
						<div style="display:none;" class="form-group J-reason-box">\
							<textarea class="col-md-12 J-reason" style="height: 100px"></textarea>\
						</div>\
					</div>\
				</div>\
				<div style="margin-top: 5px; text-align: center;">\
					<button type="button" class="J-pass-btn btn btn-primary" onclick="approvePass(this)">通过</button>\
					<button style="margin-left:15px;" type="button" class="J-pass-btn btn btn-primary" onclick="approveNot(this)">不通过</button>\
				</div>\
			</form>\
		</div>';
		$('#' + lableId).parents('.J-row').after(approveHtml);
	}
			
	function showReason(obj){
		$(obj).next('.J-reason-box').css('display','block');
		$(obj).text('收起');
		$(obj).attr('onclick','hideReason(this)');
	}
	
	function showDetail(obj){
		var buttonText = $(obj).text();
		var tenantId = $(obj).parents('.J-row').find('lable').text();
		var scene = 'shopApprove';
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
		var approveReason = $(obj).parents('form').find('.J-reason').val();
		var TenantId = $(obj).parents('form').attr('id').substr(-1);
		ajax({
			url : '<?php echo Url::to(['approve/shop-approve-not-pass']); ?>',
			data : {
				id : TenantId,
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
			url : '<?php echo Url::to(['approve/shop-approve-pass']); ?>',
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