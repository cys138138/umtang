<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use common\lib\DbOrmModel;

class CommercialTenantLimit extends DbOrmModel{
	protected $_aEncodeFields = ['note'];	//需要编码的字段
	
	public static function tableName(){
        return Yii::$app->db->parseTable('_@commercial_tenant_limit');
    }
	
	public function getRemainModifyLimitCount(){
		$aModifyLimitCount = Yii::$app->params['tenant_shop_modify_limit_count'];
		$aRemainModifyCount = [];
		foreach($aModifyLimitCount as $k => $count){
			if(isset($this->note[$k])){
				$aRemainModifyCount[$k] = $count - $this->note[$k];
			}else{
				$aRemainModifyCount[$k] = $count;
			}
		}
		return $aRemainModifyCount;
	}
}