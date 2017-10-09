<?php
namespace manage\assets;

class CommonAsset extends \umeworld\lib\AssetBundle{
	public $css = [
		'@r.css.sb-admin',
		'@r.css.sb-morris',
		'@r.css.sb-font-awesome',
		'@r.css.common',
	];

	public $js = [
		//'@r.js.log',
		'@r.js.core',
	];

	public $depends = [
		'manage\assets\BootstrapAsset',
		'common\assets\UBoxAsset',
	];
}