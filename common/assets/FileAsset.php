<?php
namespace common\assets;

/**
 * 上传文件脚本
 */
class FileAsset extends \umeworld\lib\AssetBundle
{
    public $js = [
		'@r.js.tools-file',
		'@r.js.tools-image'
		// '@r.js.sha1'
    ];

	public $depends = [
		'common\assets\JQueryAsset',
	];

	public $jsOptions = [
		'position' => \yii\web\View::POS_HEAD,
	];
}
