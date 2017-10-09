<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use common\lib\DbOrmModel;

class LoginLog extends DbOrmModel{
	const TYPE_TENANT = 1;
	const TYPE_USER = 2;
	
	const LOGIN_RECORD_INTERVAL = 1800; //每隔半小时记录一次日志
	
	public static function tableName(){
        return Yii::$app->db->parseTable('_@login_log');
    }
	
	public static function add($aData){
		(new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
		return Yii::$app->db->getLastInsertID();
	}
}