<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use umeworld\lib\Xxtea;
use common\lib\DbOrmModel;
use yii\web\IdentityInterface;

class User extends DbOrmModel implements IdentityInterface{
	private $_authKey = 'umtang20170506fdsafeee@###!';	//身份验证密钥
	public static function tableName(){
        return Yii::$app->db->parseTable('_@user');
    }
	
	public static function findOne($xCondition){
		$mUser = parent::findOne($xCondition);
		if($mUser){
			$mUser->checkAccumulatePointsAndReset();
		}
		return $mUser;
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function getAuthKey(){
        return $this->_authKey;
    }
	
	public function validateAuthKey($authKey){
        return $this->getAuthKey() === $authKey;
    }
	
	public static function findIdentityByAccessToken($token, $type = null){
        throw new NotSupportedException('根据令牌找用户 的方法未实现');
    }
	
	public static function findIdentity($id){
        return static::findOne($id);
    }
	
	public function fields(){
		return array_merge(parent::fields(), ['profile_path']);
	}
	
	public function __get($name){
		if($name == 'profile_path'){
			$this->$name = '';
			if($this->profile){
				$mResource = Resource::findOne($this->profile);
				if($mResource){
					$this->$name = $mResource->path;
				}
			}
		}
		return $this->$name;
	}
	
	/*
	 * 积分减少操作
	 */
	public function subAccumulatePoints($subAccumulatePoint){
		if(!$subAccumulatePoint){
			return false;
		}
		$this->set('accumulate_points', ['sub', $subAccumulatePoint]);
		return $this->save();
	}
	
	public static function registerByOpenId($openId){
		$aData = [
			'openid' => $openId,
			'profile' => 0,
			'mobile' => '',
			'name' => '',
			'accumulate_points' => 0,
			'last_city_id' => 0,
			'last_lng' => 0,
			'last_lat' => 0,
			'create_time' => NOW_TIME,
		];
		(new Query())->createCommand()->insert(static::tableName(), $aData)->execute();
		$id = Yii::$app->db->getLastInsertID();
		$aData['id'] = $id;
		return static::toModel($aData);
	}
	
	public function createUserToken(){
		$lastLoginTime = static::_getUserLastLoginRecordTime($this->id);
		if(NOW_TIME - $lastLoginTime < LoginLog::LOGIN_RECORD_INTERVAL){
			return Xxtea::encrypt($this->id . ':' . $lastLoginTime);
		}
		return Xxtea::encrypt($this->id . ':' . NOW_TIME);
	}
	
	public static function getUserByToken($token){
		$string = Xxtea::decrypt($token);
		$aResult = explode(':', $string);
		if(is_array($aResult) && isset($aResult[0]) && $aResult[0]){
			$mUser = static::findOne((int)$aResult[0]);
			if($mUser){
				$lastLoginTime = static::_getUserLastLoginRecordTime($mUser->id);
				if($lastLoginTime != $aResult[1]){
					return false;
				}
				return $mUser;
			}
		}
		return false;
	}
	
	public static function _getUserLastLoginRecordTime($userId){
		return (new Query())->from(LoginLog::tableName())->where([
			'type' => LoginLog::TYPE_USER,
			'user_id' => $userId,
		])->max('`create_time`');
	}
	
	public function getMUserAction(){
		$mUserAction = UserAction::findOne($this->id);
		if(!$mUserAction){
			$aData = [
				'id'	=> $this->id,
				'note'	=> json_encode([]),
			];
			(new Query())->createCommand()->insert(UserAction::tableName(), $aData)->execute();
			$mUserAction = UserAction::toModel($aData);
		}
		return $mUserAction;
	}
	
	public function getMUserTask(){
		$mUserTask = UserTask::findOne($this->id);
		if(!$mUserTask){
			$aData = [
				'id'	=> $this->id,
				'content'	=> json_encode(UserTask::getTaskList()),
			];
			(new Query())->createCommand()->insert(UserTask::tableName(), $aData)->execute();
			$mUserTask = UserTask::toModel($aData);
		}
		return $mUserTask;
	}
	
	public function addAccumulatePoint($addAccumulatePoint){
		if(!$addAccumulatePoint){
			return false;
		}
		$this->set('accumulate_points', ['add', $addAccumulatePoint]);
		return $this->save('', $addAccumulatePoint);
	}
	
	/*
	 * 检查积分并重置
	 */
	public function checkAccumulatePointsAndReset(){
		//查出用户所有未支付的订单，收集被扣去的积分
		$aOrders = Order::findAll(['and', ['user_id' => $this->id], ['status' => Order::STATUS_WAIT_PAY]]);//, ['>', 'accumulate_points_money', 0]
		//将积分归还到用户中
		$countAccumulatePoints = 0;
		foreach($aOrders as $aOrder){
			if($aOrder['create_time'] < NOW_TIME){
				$mOrder = Order::toModel($aOrder);
				$countAccumulatePoints += $mOrder->accumulate_points_money;
				//删除失效订单
				$mOrder->delete();
				(new Query())->createCommand()->delete(Order::goodsInfoTableName(), ['id' => $mOrder->id])->execute();
			}
		}
		//debug($aOrders);
		//debug($countAccumulatePoints,11);
		if($countAccumulatePoints){
			$this->addAccumulatePoint($countAccumulatePoints);
		}
	}
}