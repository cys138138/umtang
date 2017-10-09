/**
 * AjaxUpload 基于jQuery的微型异步上传插件
 * @author 黄文非
 */
(function($){
	$.fn.extend({
		AjaxUpload : function(aOptions){
			aOptions = $.extend({
				uploadUrl : '',
				callback : $.noop,
				fileKey : 'ume_upload_file',
				beforeSend : $.noop
			}, aOptions);

			$(this).click(function(){
				var $file = $('<input type="file" name="' + aOptions.fileKey + '" />');
				$file.change(function(){
					var $this = $(this);
					var oCloneFile = $this.clone();
					oCloneFile[0].files = $this[0].files;	//兼容谷歌浏览器
					$('<iframe src="javascript:void(0);" style="display:none;" name="__tmpUploadIframe"></iframe>').appendTo('body').load(function(){
						var oPre = this.contentDocument.body.firstChild;
						if(!oPre){
							UBox.show('上传出错！相关信息：' + this.contentDocument.body.innerHTML);
						}

						var url = null,		//文件在浏览器内的URL
						file = oCloneFile[0].files[0];
						if (window.createObjectURL != undefined) {
							url = window.createObjectURL(file);
						} else if (window.URL != undefined) {
							url = window.URL.createObjectURL(file);
						} else if (window.webkitURL != undefined) {
							url = window.webkitURL.createObjectURL(file);
						}

						var aResult = $.parseJSON(oPre.innerHTML);
						aOptions.callback(aResult, file, url);

						$oForm.remove();
						$(this).remove();
					});

					var $oForm = $('<form style="display:none;" action="' + aOptions.uploadUrl + '" method="POST" enctype="multipart/form-data" target="__tmpUploadIframe">\
						<input type="hidden" name="_csrf" value="' + $('meta[name="csrf-token"]').attr('content') + '">\
						<input type="hidden" name="_is_ajax" value="1" />\
					</form>').append(oCloneFile).appendTo('body');

					aOptions.beforeSend($oForm);
					$oForm.submit();
				});
				$file.click();
			});
		}
	});
})(jQuery);