<?php
/**
 * URL配置控制
 * class : 解析器
 * enablePrettyUrl : 是否开启伪静态
 * showScriptName : 生成的URL是否带入口脚本名称
 * enableStrictParsing : 是否开启严格匹配
 * baseUrl 域名
 */
return [
	'class' => 'yii\web\UrlManager',
	'enablePrettyUrl' => true,
	'showScriptName' => false,
	'enableStrictParsing' => false,
	'baseUrl' => Yii::getAlias('@url.manage'),
	'rules' => [
		''											=> 'site/index',
		'login'										=> 'site/login',
		'show-login.html'							=> 'site/show-login',
		'logout'									=> 'site/logout',
		'captcha'									=> 'site/captcha',
		'tools.html' => 'tools/show-home',							//后门工具伪静态
	],
];