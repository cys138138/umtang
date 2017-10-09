<?php
namespace manage\assets;

class BootstrapAsset extends \umeworld\lib\AssetBundle{
	public $css = [
		'@r.css.bootstrap',
	];

	public $js = [
		'@r.js.bootstrap',
	];

	public $depends = [
		'common\assets\JQueryAsset'
	];
}