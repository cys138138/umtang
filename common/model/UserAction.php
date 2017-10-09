<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use common\lib\DbOrmModel;

class UserAction extends DbOrmModel{
	protected $_aEncodeFields = ['note'];
	
	public static function tableName(){
        return Yii::$app->db->parseTable('_@user_action');
    }

}