<?php
namespace common\assets;

/**
 * 自定义富文本编辑器
 */
class EditorAsset extends \umeworld\lib\AssetBundle
{
    public $js = [
		'@r.pack.umt-editor',
		'@r.js.tools-image',
		'@r.js.ui'
    ];

    public $css = [
    	'@r.css.umt-editor',
    ];

	public $depends = [
		'common\assets\JQueryAsset',
		'common\assets\FileAsset',
	];

	public $jsOptions = [
		'position' => \yii\web\View::POS_HEAD,
	];
}
