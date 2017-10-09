<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use common\lib\DbOrmModel;

class UserTask extends DbOrmModel{
	protected $_aEncodeFields = ['content'];
	
	const STATUS_UNFINISH = 0;			//未完成
	const STATUS_FINISH = 1;			//完成
	const STATUS_RECEIVED_AWARD = 2;	//已领奖
	
	public static function tableName(){
        return Yii::$app->db->parseTable('_@user_task');
    }

	public static function getTaskConfigList(){
		return [
			UserAccumulatePointGetRecord::TYPE_NEW_MOBILE_USER_REGISTER_TASK => [
				'name' => '新手机用户注册或绑定',
				'accumulate_point' => 500,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_ORDER_TASK => [
				'name' => '首次下单',
				'accumulate_point' => 500,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_PAY_ORDER_TASK => [
				'name' => '首次买单',
				'accumulate_point' => 500,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_COMMENT_ORDER_TASK => [
				'name' => '首次评价',
				'accumulate_point' => 100,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_SUPERADDITION_COMMENT_ORDER_TASK => [
				'name' => '首次追评',
				'accumulate_point' => 100,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_COLLECT_GOODS_TASK => [
				'name' => '首次收藏商品',
				'accumulate_point' => 50,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_COLLECT_SHOP_TASK => [
				'name' => '首次收藏商店',
				'accumulate_point' => 50,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_COMMENT_ORDER_TEN_COUNT_TASK => [
				'name' => '累计进行10次初评',
				'accumulate_point' => 200,
				'must_complete_count' => 10,
			],
			UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_FIVE_THOUSAND_MONEY_TASK => [
				'name' => '累计实际支付5000元',
				'accumulate_point' => 1000,
				'must_complete_count' => 5000,
			],
			UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_TEN_THOUSAND_MONEY_TASK => [
				'name' => '累计实际支付10000元',
				'accumulate_point' => 2500,
				'must_complete_count' => 25000,
			],
			UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_TWENTY_THOUSAND_MONEY_TASK => [
				'name' => '累计实际支付20000元',
				'accumulate_point' => 6000,
				'must_complete_count' => 60000,
			],
		];
	}
	
	public static function getTaskList(){
		return [
			UserAccumulatePointGetRecord::TYPE_NEW_MOBILE_USER_REGISTER_TASK => [
				'task_id' => UserAccumulatePointGetRecord::TYPE_NEW_MOBILE_USER_REGISTER_TASK,
				'status' => static::STATUS_UNFINISH,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_ORDER_TASK => [
				'task_id' => UserAccumulatePointGetRecord::TYPE_FIRST_ORDER_TASK,
				'status' => static::STATUS_UNFINISH,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_PAY_ORDER_TASK => [
				'task_id' => UserAccumulatePointGetRecord::TYPE_FIRST_PAY_ORDER_TASK,
				'status' => static::STATUS_UNFINISH,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_COMMENT_ORDER_TASK => [
				'task_id' => UserAccumulatePointGetRecord::TYPE_FIRST_COMMENT_ORDER_TASK,
				'status' => static::STATUS_UNFINISH,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_SUPERADDITION_COMMENT_ORDER_TASK => [
				'task_id' => UserAccumulatePointGetRecord::TYPE_FIRST_SUPERADDITION_COMMENT_ORDER_TASK,
				'status' => static::STATUS_UNFINISH,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_COLLECT_GOODS_TASK => [
				'task_id' => UserAccumulatePointGetRecord::TYPE_FIRST_COLLECT_GOODS_TASK,
				'status' => static::STATUS_UNFINISH,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_COLLECT_SHOP_TASK => [
				'task_id' => UserAccumulatePointGetRecord::TYPE_FIRST_COLLECT_SHOP_TASK,
				'status' => static::STATUS_UNFINISH,
			],
			UserAccumulatePointGetRecord::TYPE_FIRST_COMMENT_ORDER_TEN_COUNT_TASK => [
				'task_id' => UserAccumulatePointGetRecord::TYPE_FIRST_COMMENT_ORDER_TEN_COUNT_TASK,
				'first_comment_count' => 0,
				'status' => static::STATUS_UNFINISH,
			],
			UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_FIVE_THOUSAND_MONEY_TASK => [
				'task_id' => UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_FIVE_THOUSAND_MONEY_TASK,
				'pay_money' => 0,
				'status' => static::STATUS_UNFINISH,
			],
			UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_TEN_THOUSAND_MONEY_TASK => [
				'task_id' => UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_TEN_THOUSAND_MONEY_TASK,
				'pay_money' => 0,
				'status' => static::STATUS_UNFINISH,
			],
			UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_TWENTY_THOUSAND_MONEY_TASK => [
				'task_id' => UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_TWENTY_THOUSAND_MONEY_TASK,
				'pay_money' => 0,
				'status' => static::STATUS_UNFINISH,
			],
		];
	}
	
	public function getTaskContentList(){
		$aTaskConfigList = static::getTaskConfigList();
		$aContentList = $this->content;
		foreach($aContentList as $taskId => $aTask){
			$aContentList[$taskId] = array_merge($aTask, $aTaskConfigList[$taskId]);
		}
		return $aContentList;
	}
	
	public function checkFirstCollectTask($mUser, $collectType){
		$mUserCollect = UserCollect::findOne([
			'user_id' => $mUser->id,
			'type' => $collectType,
		]);
		if(!$mUserCollect){
			$aContent = $this->content;
			$taskId = 0;
			if($collectType == UserCollect::TYPE_SHOP){
				$taskId = UserAccumulatePointGetRecord::TYPE_FIRST_COLLECT_SHOP_TASK;
			}elseif($collectType == UserCollect::TYPE_GOODS){
				$taskId = UserAccumulatePointGetRecord::TYPE_FIRST_COLLECT_GOODS_TASK;
			}
			if(isset($aContent[$taskId]) && $aContent[$taskId]['status'] == static::STATUS_UNFINISH){
				$aContent[$taskId]['status'] = static::STATUS_FINISH;
				$this->set('content', $aContent);
				$this->save();
			}
		}
	}
	
	/*
	 * 检查用户支付 和 评价 任务 并设置状态
	 * @param $type  1=》支付任务 2=》评价任务 
	 */
	public function checkUserTaskByOrderAndComment(){
		//$mUserTask = $mUser->getMUserTask();
		$this->_checkContentKey();
		$userId = $this->id;
		$aContent = $this->content;
		$aTaskList = static::getTaskConfigList();
		//检查用户订单交易次数，区分  买单 和 订单
		//判断首次买单
		if($aContent[UserAccumulatePointGetRecord::TYPE_FIRST_PAY_ORDER_TASK]['status'] == static::STATUS_UNFINISH){
			$count = (new Query())->from(Order::tableName())->where(['and', ['type' => Order::DIRECT_PAY], ['user_id' => $userId], ['>', 'status', Order::STATUS_WAIT_PAY]])->count();
			if($count >= 1){
				$task = UserAccumulatePointGetRecord::TYPE_FIRST_PAY_ORDER_TASK;
				$this->setContentAndStatus($task);
			}
		}
		//判断首次下单
		if($aContent[UserAccumulatePointGetRecord::TYPE_FIRST_ORDER_TASK]['status'] == static::STATUS_UNFINISH){
			$count = (new Query())->from(Order::tableName())->where(['and', ['type' => Order::ORDER_PAY], ['user_id' => $userId], ['>', 'status', Order::STATUS_WAIT_PAY]])->count();
			if($count >= 1){
				$task = UserAccumulatePointGetRecord::TYPE_FIRST_ORDER_TASK;
				$this->setContentAndStatus($task);
			}
		}
		//判断消费 累计 是否达到任务
		if($aContent[UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_FIVE_THOUSAND_MONEY_TASK]['status'] == static::STATUS_UNFINISH || $aContent[UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_TEN_THOUSAND_MONEY_TASK]['status'] == static::STATUS_UNFINISH || $aContent[UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_TWENTY_THOUSAND_MONEY_TASK]['status'] == static::STATUS_UNFINISH){
			$aMoneyCount = (new Query())->select('SUM(pay_money) as `count`')->from(Order::tableName())->where(['and', ['user_id' => $userId], ['>', 'pay_money', 0]])->one();
			$sumMoney = (int)$aMoneyCount['count'] / 100;
			//累计实际支付5000元
			$task = UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_FIVE_THOUSAND_MONEY_TASK;
			if($sumMoney >= $aTaskList[$task]['must_complete_count']){
				$this->setContentAndStatus($task, 'pay_money', $aTaskList[$task]['must_complete_count']);
			}elseif($sumMoney < $aTaskList[$task]['must_complete_count']){
				$this->setContentAndStatus($task, 'pay_money', $sumMoney, false);
			}
			
			//累计实际支付10000元	
			$task = UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_TEN_THOUSAND_MONEY_TASK;
			if($sumMoney >= $aTaskList[$task]['must_complete_count']){
				$this->setContentAndStatus($task, 'pay_money', $aTaskList[$task]['must_complete_count']);
			}elseif($sumMoney < $aTaskList[$task]['must_complete_count']){
				$this->setContentAndStatus($task, 'pay_money', $sumMoney, false);
			}
			
			//累计实际支付20000元	
			$task = UserAccumulatePointGetRecord::TYPE_TOTAL_PAY_TWENTY_THOUSAND_MONEY_TASK;
			if($sumMoney >= $aTaskList[$task]['must_complete_count']){
				$this->setContentAndStatus($task, 'pay_money', $aTaskList[$task]['must_complete_count']);
			}elseif($sumMoney < $aTaskList[$task]['must_complete_count']){
				$this->setContentAndStatus($task, 'pay_money', $sumMoney, false);
			}
		}
		//检查用户评价 次数 触发 的积分任务
		//首次评价
		$task = UserAccumulatePointGetRecord::TYPE_FIRST_COMMENT_ORDER_TASK;
		$taskTwo = UserAccumulatePointGetRecord::TYPE_FIRST_COMMENT_ORDER_TEN_COUNT_TASK;
		if($aContent[$task]['status'] == static::STATUS_UNFINISH || $aContent[$taskTwo]['status'] == static::STATUS_UNFINISH){
			$count = (new Query())->from(OrderCommentIndex::tableName())->where(['and', ['user_id' => $userId], ['is_superaddition' => 0], ['>', 'score', 0]])->count();
			if($count >= 1 && $aContent[$task]['status'] == static::STATUS_UNFINISH){
				$this->setContentAndStatus($task);
			}
		//十次初评任务 判断
			if($aContent[$taskTwo]['status'] == static::STATUS_UNFINISH){
				if($count >= $aTaskList[$taskTwo]['must_complete_count']){
					$this->setContentAndStatus($taskTwo, 'first_comment_count', $aTaskList[$taskTwo]['must_complete_count']);
				}else{
					$this->setContentAndStatus($taskTwo, 'first_comment_count', $count, false);
				}
			}
		}
		//首次追评
		$task = UserAccumulatePointGetRecord::TYPE_FIRST_SUPERADDITION_COMMENT_ORDER_TASK;
		if($aContent[$task]['status'] == static::STATUS_UNFINISH){
			$count = (new Query())->from(OrderCommentIndex::tableName())->where(['and', ['user_id' => $userId], ['is_superaddition' => 1], ['>', 'pid', 0]])->count();
			if($count >= 1){
				$this->setContentAndStatus($task);
			}
		}
		//首次收藏商品
		$task = UserAccumulatePointGetRecord::TYPE_FIRST_COLLECT_GOODS_TASK;
		if($aContent[$task]['status'] == static::STATUS_UNFINISH){
		//计算数量
			$count = (new Query())->from(UserCollect::tableName())->where(['user_id' => $userId, 'type' => UserCollect::TYPE_GOODS])->count();
			if($count >= 1){
				$this->setContentAndStatus($task);
			}
		}
		//首次收藏商店
		$task = UserAccumulatePointGetRecord::TYPE_FIRST_COLLECT_SHOP_TASK;
		if($aContent[$task]['status'] == static::STATUS_UNFINISH){
		//计算数量
			$count = (new Query())->from(UserCollect::tableName())->where(['user_id' => $userId, 'type' => UserCollect::TYPE_SHOP])->count();
			if($count >= 1){
				$this->setContentAndStatus($task);
			}
		}
		//新手机用户注册任务
		$task = UserAccumulatePointGetRecord::TYPE_NEW_MOBILE_USER_REGISTER_TASK;
		if($aContent[$task]['status'] == static::STATUS_UNFINISH){
			$mUser = User::findOne($userId);
			if($mUser->mobile){
				$this->setContentAndStatus($task);
			}
		}
		return $this->save();
	}
	
	public function setContentAndStatus($type, $key = '', $value = '', $isFinish = 1){
		$aContent = $this->content;
		if($isFinish && $aContent[$type]['status'] == static::STATUS_UNFINISH){
			$aContent[$type]['status'] = static::STATUS_FINISH;
		}
		if($key && $value){
			$aContent[$type][$key] = $value;
		}
		$this->set('content', $aContent);
	}
	
	private function _checkContentKey(){
		$aTaskList = static::getTaskList();
		$aContent = $this->content;
		foreach($aTaskList as $key => $aValue){
			if(!isset($aContent[$key])){
				$aContent[$key] = $aValue;
			}
		}
		$this->set('content', $aContent);
	}
}