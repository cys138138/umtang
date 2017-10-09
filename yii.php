#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require(__DIR__ . '/config-local.php');

defined('YII_DEBUG') or define('YII_DEBUG', $aLocal['is_debug']);
defined('YII_ENV') or define('YII_ENV', $aLocal['env']);

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

require(FRAMEWORK_PATH . '/autoload.php');
require(FRAMEWORK_PATH . '/yiisoft/yii2/Yii.php');
require(__DIR__ . '/common/config/bootstrap.php');
require(Yii::getAlias('@console/config/bootstrap.php'));

$aConfig = yii\helpers\ArrayHelper::merge(
    require(Yii::getAlias('@console/config/main.php')),
    require(Yii::getAlias('@console/config/main-local.php'))
);

$application = new yii\console\Application($aConfig);
$exitCode = $application->run();
exit($exitCode);
