<?php
namespace mobile_parent\assets;

/**
 * 公共资源包,一般每个页面都要注册
 */
class CommonAsset extends \umeworld\lib\AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
		'@r.css.umantang-mobile-parent'
    ];

    public $js = [
    ];

	public $depends = [
		'common\assets\CoreAsset'
	];
}
