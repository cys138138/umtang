<?php
namespace manage\assets;

/**
 * UMeditor Mini版编辑器公共引用包
 * @author 福
 * Class UmeditorAsset
 * @package home\assets
 */
Class UmeditorAsset extends \umeworld\lib\AssetBundle
{
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $js = [
		'@r.js.umeditor_config',
		'@r.js.umeditor',
		'@r.js.umeditor_lang_cn',
	];
	public $css = [
		'@r.css.umeditor_css'
	];
	public $depends = [
	];
}