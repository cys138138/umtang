<?php
namespace manage\assets;

/**
 * 异步上传插件
 *
 * 前端用法:
 *
 * ~~~
 * $('你的按钮').AjaxUpload({
 *		uploadUrl : '上传地址',
 *		fileKey : '文件的字段名,后端的$_FILES[这个键]'
 *		callback : function(aResult){
 *		}
 * });

 * ~~~
 * @author 黄文非
 */
class AjaxUploadAsset extends \umeworld\lib\AssetBundle{
	public $js = [
		'@r.js.ajax-upload'
	];

	public $depends = [
		'common\assets\JQueryAsset',
		'common\assets\UBoxAsset',
	];
}