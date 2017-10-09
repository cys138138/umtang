<?php
require(__DIR__ . '/../../../config-local.php');
$oAppCreater = new \common\lib\AppCreater(['appId' => 'login']);
$oAppCreater->createApp()->run();