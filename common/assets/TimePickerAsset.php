<?php
namespace common\assets;

/**
 * 提示信息组件
 */
class TimePickerAsset extends \umeworld\lib\AssetBundle
{
    public $js = [
		'@r.js.jquery-ui',
		// '@r.js.jquery-ui-slider-access',
		'@r.js.jquery-ui-date-timepicker',
		'@r.js.jquery-ui-datepicker-lang',
    ];

    public $css = [
    	'@r.css.jquery-ui',
    	'@r.css.jquery-ui-date-timepicker',
    ];

	public $depends = [
		'common\assets\JQueryAsset',
	];

	public $jsOptions = [
		'position' => \yii\web\View::POS_HEAD,
	];
}
