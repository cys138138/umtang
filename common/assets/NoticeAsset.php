<?php
namespace common\assets;

/**
 * 提示信息组件
 */
class NoticeAsset extends \umeworld\lib\AssetBundle
{
    public $js = [
		'@r.pack.u-notice',
    ];

    public $css = [
    	'@r.css.u-notice',
    ];

	public $depends = [
		'common\assets\JQueryAsset',
	];

	public $jsOptions = [
		'position' => \yii\web\View::POS_HEAD,
	];
}
