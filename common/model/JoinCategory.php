<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;

class JoinCategory extends \common\lib\DbOrmModel{
	public static function tableName(){
		return Yii::$app->db->parseTable('_@join_category');
	}
	
	public static function add($aData){
		$aData['create_time'] = NOW_TIME;
		return (new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
	}
	
	public static function getListAndCount(){
		return (new Query())->select('c.*, l.id as `join_id`, l.category_id, count(l.id) as `num`')->from(static::tableName() . ' c')->leftJoin(JoinList::tableName() . ' l', 'c.id = l.category_id')->groupBy(['l.category_id'])->having(['>', 'num', 0])->all();//->orderBy(['num' => SORT_DESC])
	}
}

