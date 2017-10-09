<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use common\lib\DbOrmModel;

class CommercialTenantAction extends DbOrmModel{
	protected $_aEncodeFields = ['note'];
	
	public static function tableName(){
        return Yii::$app->db->parseTable('_@commercial_tenant_action');
    }
}