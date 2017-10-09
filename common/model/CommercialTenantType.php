<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use common\lib\DbOrmModel;

class CommercialTenantType extends DbOrmModel{
	public static function tableName(){
        return Yii::$app->db->parseTable('_@commercial_tenant_type');
    }
	
	public static function addTenantType($typeName){
		$aData = [
			'name'			=> $typeName,
			'create_time'	=> NOW_TIME,
		];
		$rows = (new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
		if(!$rows){
			throw Yii::$app->buildError('插入商户类型失败', false, $aData);
		}
		return Yii::$app->db->getLastInsertID();
	}
}