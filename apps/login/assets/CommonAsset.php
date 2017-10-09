<?php
namespace login\assets;

/**
 * 公共资源包,一般每个页面都要注册
 */
class CommonAsset extends \umeworld\lib\AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
    ];

    public $js = [
    ];

	public $depends = [
        'common\assets\NoticeAsset',
		'common\assets\CoreAsset'
	];
}
