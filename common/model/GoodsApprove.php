<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;

/**
 * 商品审核表模型
 * @author 谭威力
 */
class GoodsApprove extends \common\lib\DbOrmModel{
	//需要编译的字段
	protected $_aEncodeFields = ['content'];
	//要保存的字段
	protected $_aExtendFields = [];

	const APPROVE_WAIT = 1;			//未审核
	const APPROVE_PASS = 2;			//审核通过
	const APPROVE_DEFEATED = 3;		//审核不通过

	const PHOTO_KEY_NAME = 'photo_list'; //图片的键值

	/**
     * @inheritdoc
     */
    public static function tableName(){
        return Yii::$app->db->parseTable('_@goods_approve');
    }
	
	public static function add($aData){
		(new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
		return Yii::$app->db->getLastInsertID();
	}
	
	/*
	 * 更改状态
	 */
	public function updateApprove($status){
		$this->set('approved_status', $status);
		return $this->save();
	}
	
	/**
	 *	获取列表
	 *	$aCondition = [
	 *		'id' =>
	 *	]
	 *	$aControl = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'page' =>
	 *		'page_size' =>
	 *		'with_xxx_info' => true/false
	 *	]
	 */
	public static function getList($aCondition = [], $aControl = []){
		$aWhere = static::_parseWhereCondition($aCondition);
		$oQuery = new Query();
		if(isset($aControl['select'])){
			$oQuery->select($aControl['select']);
		}
		$oQuery->from(static::tableName())->where($aWhere);
		if(isset($aControl['order_by'])){
			$oQuery->orderBy($aControl['order_by']);
		}
		if(isset($aControl['page']) && isset($aControl['page_size'])){
			$offset = ($aControl['page'] - 1) * $aControl['page_size'];
			$oQuery->offset($offset)->limit($aControl['page_size']);
		}
		$aList = $oQuery->all();
		if(!$aList){
			return [];
		}
		return $aList;
	}
	
	/**
	 *	获取数量
	 */
	public static function getCount($aCondition = []){
		$aWhere = static::_parseWhereCondition($aCondition);
		return (new Query())->from(static::tableName())->where($aWhere)->count();
	}
	
	private static function _parseWhereCondition($aCondition = []){
		$aWhere = ['and'];
		if(isset($aCondition['id'])){
			$aWhere[] = ['id' => $aCondition['id']];
		}

		return $aWhere;
	}
}

