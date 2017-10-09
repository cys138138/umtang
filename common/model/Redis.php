<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;

/*
 * @author twl
 * 当没有redis时保存数据 的表模型
 */
class Redis extends \common\lib\DbOrmModel{
	protected $_aEncodeFields = ['value'];
	
	const EXPIRATION_TIME = 3600;


	public static function tableName() {
		return Yii::$app->db->parseTable('_@redis');
	}
	
	/*
	 * 添加新数据
	 * @param array $aData = [
	 *		id =>	redis的key
	 *		value => redis的redis的value
	 *		expiration_time =>	过期时间
	 * ]
	 * @return model;
	 */
	public static function add($aData){
		if(!isset($aData['expiration_time']) || !$aData['expiration_time']){
			$aData['expiration_time'] = NOW_TIME + static::EXPIRATION_TIME;
		}
		if(isset($aData['value']) && $aData['value']){
			$aData['value'] = json_encode($aData['value']);
		}
		
		if((new Query())->createCommand()->insert(static::tableName(), $aData)->execute()){
			return static::toModel($aData);
		}else{
			return false;
		}
	}
}

