<?php
namespace common\assets;

/**
 * jQuery插件资源包
 */
class MapAsset extends \umeworld\lib\AssetBundle{
	public $js = [
		'@r.js.area-selector',
		'@r.pack.umt-map-selector',
	];

	public $depends = [
		'common\assets\JQueryAsset',
	];
}