<?php
require(__DIR__ . '/../../../../config-local.php');
$aLocal['temp']['by_old_home'] = true;
$oAppCreater = new \common\lib\AppCreater(['appId' => 'home']);
$oAppCreater->createApp()->trigger(\umeworld\lib\Application::EVENT_BEFORE_REQUEST);