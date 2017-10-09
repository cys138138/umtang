<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;


class OrderCommentIndex extends \common\lib\DbOrmModel{
	//需要编译的字段
	protected $_aEncodeFields = ['resource_ids'];
	//要保存的字段
	protected $_aExtendFields = ['resource_ids', 'content'];
	
	public static function tableName() {
		return Yii::$app->db->parseTable('_@order_comment_index');
	}
	
	public static function contentTableName() {
		return Yii::$app->db->parseTable('_@order_comment');
	}
	
	/**
	 * 设定扩展字段
	 * @return array
	 */
	public function fields(){
		$aMyFields = array_keys(Yii::getObjectVars($this));
		return array_merge($aMyFields, $this->_aExtendFields);
	}

	/**
	 * @inheritdoc
	 */
	public function __get($name){
		if($name == 'resource_path' || in_array($name, $this->_aExtendFields)){//
			//如果是复合字段
			$aContentData = (new Query())->from(self::contentTableName())->where(['id' => $this->id])->one();
			/*if(!$aGoodsData){
				(new Query)->createCommand()->insert(static::goodsInfoTableName(), ['id' => $this->id, 'goods_info' => ''])->execute();
				$aGoodsData['goods_info'] = '';
			}*/
			if($name == 'resource_ids'){
				$this->$name = json_decode($aContentData['resource_ids'], true);
			}elseif($name == 'resource_path'){
				$aData = [];
				$aResourceIds = json_decode($aContentData['resource_ids'], true);
				if($aResourceIds){
					$aResources = Resource::findAll(['id' => $aResourceIds]);
					foreach($aResourceIds as $resourceId){
						foreach($aResources as $aResource){
							$mResource = Resource::toModel($aResource);
							if($resourceId == $aResource['id']){
								$aData[$resourceId] = $mResource->getUrl();
							}
						}
					}
				}
				$this->$name = $aData;
			}else{
				$this->$name = $aContentData[$name];
			}
		}
		if(!in_array($name, array_keys(Yii::getObjectVars($this)))){
			return parent::__get($name);
		}else{
			return $this->$name;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function save(){
		$aUpdateIndex = $this->_aSetFields;
		if(!$aUpdateIndex){
			return 0;
		}
		$aUpdateData = [];
		foreach($aUpdateIndex as $field => $xValue){
			if(in_array($field, $this->_aExtendFields)){
				if($field == 'resource_ids'){
					$aUpdateData['resource_ids'] = json_encode($aUpdateIndex['resource_ids']);
				}else{
					$aUpdateData[$field] = $aUpdateIndex[$field];
				}
				unset($aUpdateIndex[$field]);
			}
		}
		$resultIndex = $resultData = 0;
		if($aUpdateIndex){
			$resultIndex = (new Query())->createCommand()->update(self::tableName(), $aUpdateIndex, ['id' => $this->id])->execute();
		}
		if($aUpdateData){
			$resultData = (new Query())->createCommand()->update(self::contentTableName(), $aUpdateData, ['id' => $this->id])->execute();
		}
		$this->_aSetFields = [];
		return $resultIndex + $resultData;
	}

	public static function add($aData){
		$aData['create_time'] = NOW_TIME;
		$aDataNext = [
			'content' => $aData['content'],
			'resource_ids' => json_encode($aData['resource_ids']),
		];
		unset($aData['content'], $aData['resource_ids']);
		(new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
		$id = Yii::$app->db->getLastInsertID();
		if(!$id){
			return $id;
		}
		$aDataNext['id'] = $id;
		(new Query())->createCommand()->insert(static::contentTableName(), $aDataNext)->execute();
		return $id;
	}
	
	/**
	 *	获取列表
	 *	$aCondition = [
	 *		'id' =>
	 *		'pid' =>
	 *		'tenant_id' =>
	 *		'order_id' =>
	 *		'user_id' =>
	 *	]
	 *	$aControl = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'group_by' => 
	 *		'page' =>
	 *		'page_size' =>
	 *		'with_order_info' => true/false
	 *		'with_user_info' => true/false
	 *		'with_goods_info' => true/false
	 *		'with_content_info' => true/false
	 *		'with_resource_info'	=> true/false
	 *		'with_all_info' => 是否获取全部信息
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
		if(isset($aControl['group_by'])){
			$oQuery->groupBy($aControl['group_by']);
		}
		if(isset($aControl['page']) && isset($aControl['page_size'])){
			$offset = ($aControl['page'] - 1) * $aControl['page_size'];
			$oQuery->offset($offset)->limit($aControl['page_size']);
		}
		$aList = $oQuery->all();
		if(!$aList){
			return [];
		}
		return static::_getAllInfo($aList, $aControl);
	}
	
	/**
	 *	获取数量
	 */
	public static function getCount($aCondition = [], $aControl = []){
		$aWhere = static::_parseWhereCondition($aCondition);
		$oQuery =(new Query());
		$oQuery->from(static::tableName())->where($aWhere);
		if(isset($aControl['order_by'])){
			$oQuery->orderBy($aControl['order_by']);
		}
		if(isset($aControl['group_by'])){
			$oQuery->groupBy($aControl['group_by']);
		}
		return $oQuery->count();
	}
	
	private static function _parseWhereCondition($aCondition = []){
		$aWhere = ['and'];
		if(isset($aCondition['id'])){
			$aWhere[] = ['id' => $aCondition['id']];
		}
		if(isset($aCondition['pid'])){
			$aWhere[] = ['pid' => $aCondition['pid']];
		}
		if(isset($aCondition['user_id'])){
			$aWhere[] = ['user_id' => $aCondition['user_id']];
		}
		if(isset($aCondition['order_id'])){
			$aWhere[] = ['order_id' => $aCondition['order_id']];
		}
		if(isset($aCondition['tenant_id'])){
			$aWhere[] = ['tenant_id' => $aCondition['tenant_id']];
		}
		if(isset($aCondition['user_id_no_0'])){
			$aWhere[] = ['>' ,'user_id', 0];
		}
		if(isset($aCondition['start_time'])){
			$aWhere[] = ['>' ,'create_time', $aCondition['start_time']];
		}
		if(isset($aCondition['is_superaddition'])){
			$aWhere[] = ['is_superaddition' => $aCondition['is_superaddition']];
		}
		return $aWhere;
	}
	
	/*
	 * 获取 回复 和 未回复  以及 所有 评论
	 * $aCondition = 【
	 *		tenant_id =》 必传
	 *		is_reply =》 是否回复、不填默认为所有
	 * 】
	 *	$aControl = [
	 *		'select' =>
	 *		'order_by' =>
	 *		'group_by' => 
	 *		'page' =>
	 *		'page_size' =>
	 *		'with_reply_list' => true/false
	 *		'with_order_info' => true/false
	 *		'with_user_info' => true/false
	 *		'with_goods_info' => true/false
	 *		'with_content_info' => true/false
	 *		'with_resource_info'	=> true/false
	 *		'with_all_info' => 是否获取全部信息
	 *	]
	 */
	public static function getReplyStatusCommentList($aCondition, $aControl){
		$string = '';
		if(isset($aCondition['is_reply'])){//是否回复的
			$string = 'is_reply = ' . $aCondition['is_reply'];
		}else{
			$string = 'is_reply in(0,1)';
		}
		$tableName = static::tableName();
		$offset = ($aControl['page'] - 1) * $aControl['page_size'];
		$sql = 'select * from (select max(id) as id, order_id, max(is_reply) as is_reply, max(create_time) as create_time from ' . $tableName . ' where tenant_id = ' . $aCondition['tenant_id'] . ' and user_id > 0 group by order_id) as t1 where ' . $string . ' order by create_time desc limit ' . $offset . ',' . $aControl['page_size'];
		$sqlCount = 'select count(*) as `num` from (select max(id) as id,order_id,max(is_reply) as is_reply, max(create_time) as create_time from ' . $tableName . ' where tenant_id=' . $aCondition['tenant_id'] . ' and user_id > 0 group by order_id) as t1 where ' . $string;
		$aResult = Yii::$app->db->createCommand($sql)->queryAll();
		if(!$aResult){
			return [
				'list' => $aResult,
				'count' => 0,
			];
		}
		$aIds = ArrayHelper::getColumn($aResult, 'id');
		$aExtendFields = (new Query())->select(['id', 'pid', 'tenant_id', 'is_superaddition', 'user_id', 'score'])->from($tableName)->where(['id' => $aIds])->all();
		$aExtendFields = ArrayHelper::index($aExtendFields, 'id');
		foreach($aResult as $key => $value){
			$aResult[$key] = array_merge($value, $aExtendFields[$value['id']]);
		}
		$aCount = Yii::$app->db->createCommand($sqlCount)->queryAll();
		//debug(Yii::$app->db->getLastSqls(1),11);
		//debug($aResult,11);
		return [
			'list' => static::_getAllInfo($aResult, $aControl),
			'count' => (int)$aCount[0]['num'],
		];
	}
	
	/*
	 * 将数据遍历并 获取 更详细信息
	 */
	private static function _getAllInfo($aResult, $aControl){
		$aIds = ArrayHelper::getColumn($aResult, 'id');
		if((isset($aControl['with_resource_info']) && $aControl['with_resource_info']) || (isset($aControl['with_all_info']) && $aControl['with_all_info'])){
			$aOrderComment = (new Query())->from(static::contentTableName())->where(['id' => $aIds])->all();
			foreach($aResult as $key => $value){
			$aResult[$key]['resource_info'] = [];
				foreach($aOrderComment as $aOrderCommentOne){
					if($value['id'] == $aOrderCommentOne['id']){
						$aResourceIds = json_decode($aOrderCommentOne['resource_ids'], true);
						if($aResourceIds){
							$aResources = Resource::findAll(['id' => $aResourceIds]);
							foreach($aResources as $aResourcesOne){
								$aResult[$key]['resource_info'][] = Resource::toModel($aResourcesOne)->getUrl();
							}
						}
					}
				}
			}
		}
		if((isset($aControl['with_user_info']) && $aControl['with_user_info']) || (isset($aControl['with_all_info']) && $aControl['with_all_info'])){
			$aUserIds = ArrayHelper::getColumn($aResult, 'user_id');
			$aOrderIds = ArrayHelper::getColumn($aResult, 'order_id');
			$aUsers = (new Query())->select(['id', 'name', 'profile'])->from(User::tableName())->where(['id' => $aUserIds])->all();
			foreach($aResult as $key => $value){
				$aResult[$key]['user_info'] = [];
				foreach($aUsers as $aUser){
					if($value['user_id'] == $aUser['id']){
						$mUser = User::toModel($aUser);
						$aUser['profile_path'] = $mUser->profile_path;
						$aResult[$key]['user_info'] = $aUser;
					}
				}
			}
		}
		if((isset($aControl['with_order_info']) && $aControl['with_order_info']) || (isset($aControl['with_all_info']) && $aControl['with_all_info'])){
			$aOrders = Order::findAll(['id' => $aOrderIds], ['id', 'quantity', 'price', 'type']);
			$aOrders = ArrayHelper::index($aOrders, 'id');
			foreach($aResult as $key => $value){
				//订单信息
				$aResult[$key]['order_info'] = isset($aOrders[$value['order_id']]) ? $aOrders[$value['order_id']] : [];
			}
		}
		if((isset($aControl['with_goods_info']) && $aControl['with_goods_info']) || (isset($aControl['with_all_info']) && $aControl['with_all_info'])){
			$aOrderGoodsInfos = (new Query())->from(Order::goodsInfoTableName())->where(['id' => $aOrderIds])->all();
			$aOrderGoodsInfos = ArrayHelper::index($aOrderGoodsInfos, 'id');
			foreach($aResult as $key => $value){
				$aResult[$key]['goods_info'] = [];
				if(isset($aOrderGoodsInfos[$value['order_id']])){
					//商品信息
				$aResult[$key]['goods_info'] = json_decode($aOrderGoodsInfos[$value['order_id']]['goods_info'], true);
				}
			}
		}
		if((isset($aControl['with_content_info']) && $aControl['with_content_info']) || (isset($aControl['with_all_info']) && $aControl['with_all_info'])){
			$aOrderComment = (new Query())->from(static::contentTableName())->where(['id' => $aIds])->all();
			foreach($aResult as $key => $value){
				$aResult[$key]['content'] = '';
				foreach($aOrderComment as $aOrderCommentOne){
					if($value['id'] == $aOrderCommentOne['id']){
						$aResult[$key]['content'] = $aOrderCommentOne['content'];
					}
				}
			}
		}
		if(isset($aControl['with_reply_list']) && $aControl['with_reply_list']){
			$aCommentId = ArrayHelper::getColumn($aResult, 'id');
			$aReplyList = static::findAll(['pid' => $aCommentId], null, 0, 0, ['create_time' => SORT_ASC]);
			$aId = ArrayHelper::getColumn($aReplyList, 'id');
			$aOrderComment = (new Query())->from(static::contentTableName())->where(['id' => $aId])->all();
			foreach($aReplyList as $key => $value){
				$aReplyList[$key]['content'] = '';
				foreach($aOrderComment as $aOrderCommentOne){
					if($value['id'] == $aOrderCommentOne['id']){
						$aReplyList[$key]['content'] = $aOrderCommentOne['content'];
					}
				}
			}
			foreach($aResult as $key => $value){
				$aResult[$key]['reply_list'] = [];
				foreach($aReplyList as $aReply){
					if($aReply['pid'] == $value['id']){
						array_push($aResult[$key]['reply_list'], $aReply);
					}
				}
			}
			foreach($aResult as $key => $value){
				$aResult[$key]['has_superaddition'] = 0;
				foreach($value['reply_list'] as $aReplyList){
					if($aReplyList['user_id'] != 0){
						$aResult[$key]['has_superaddition'] = 1;
					}
				}
			}
		}
		if(isset($aControl['with_tenant_info']) && $aControl['with_tenant_info']){
			$aTenantId = array_unique(ArrayHelper::getColumn($aResult, 'tenant_id'));
			//$sql = 'SELECT `t1`.`id`,`t1`.`name`,`t2`.`tenant_id`,COUNT(*) AS `comment_count` FROM ' . CommercialTenant::tableName() . ' AS `t1` LEFT JOIN ' . static::tableName() . ' AS `t2` ON `t1`.`id`=`t2`.`tenant_id` WHERE `t2`.`user_id`>0 AND `t2`.`tenant_id` IN(' . implode(',', $aTenantId) . ') GROUP BY `t2`.`tenant_id`';
			$sql = 'SELECT `t1`.`id`,COUNT(*) AS `comment_count` FROM ' . CommercialTenant::tableName() . ' AS `t1` LEFT JOIN ' . static::tableName() . ' AS `t2` ON `t1`.`id`=`t2`.`tenant_id` WHERE `t2`.`tenant_id` IN(' . implode(',', $aTenantId) . ') GROUP BY `t2`.`tenant_id`';
			$aTenantCommentCountList = Yii::$app->db->createCommand($sql)->queryAll();
			$aTenantCommentCountList = ArrayHelper::index($aTenantCommentCountList, 'id');
			$aTenantList = CommercialTenant::findAll(['id' => $aTenantId], ['id', 'name']);
			foreach($aTenantList as $k => $v){
				$aTenantList[$k]['comment_count'] = 0;
				if(isset($aTenantCommentCountList[$v['id']])){
					$aTenantList[$k]['comment_count'] = $aTenantCommentCountList[$v['id']]['comment_count'];
				}
			}
			foreach($aResult as $key => $value){
				$aResult[$key]['tenant_info'] = [];
				foreach($aTenantList as $aTenant){
					if($aTenant['id'] == $value['tenant_id']){
						$aResult[$key]['tenant_info'] = $aTenant;
					}
				}
			}
		}
		return $aResult;
	}

	/**
	 *	回复
	 */
	public static function reply($aData){
		$id = static::add($aData);
		if(!$id){
			return false;
		}
		$mThis = static::findOne(['and', ['order_id' => $aData['order_id']], ['tenant_id' => $aData['tenant_id']], ['is_superaddition' => 0], ['>', 'user_id', 0]]);
		$mThis->set('is_reply', 1);//将用户初评的未回复状态改成已经回复
		$mThis->save();
		return $id;
	}
}

