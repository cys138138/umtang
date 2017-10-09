<?php
namespace umeworld\lib;

use Yii;
use common\model\Redis;

class RedisCache extends \yii\base\Component{
	/**
	 * @var \Redis
	 */
    public $redis = null;
	public $serverName = '';
	public $dataPart = [];
	public $loginPart = [];
	public $tempPart = [];
	public $servers = [];

	public function init() {
		parent::init();
		//$this->connect();
	}

	public function switchDb($serverName = ''){
		if($serverName){
			$this->serverName = $serverName;
		}
	}

	public function selectPart($part = 0){
		/*if($part){
			$this->redis->select($part);
		}else{
			$this->redis->select($this->dataPart['index']);
		}*/
	}

	public function connect(){
		/*$host = $this->servers[$this->serverName]['host'];
		$port = $this->servers[$this->serverName]['port'];
		$password =$this->servers[$this->serverName]['password'];
		if(!$this->redis){
			$this->redis = new \Redis();
			$connect = $this->redis->connect($host, $port);
			if(!$connect){
				throw Yii::$app->buildError('连接redis缓存服务器 ' . $host . ' 失败!');
			}else{
				if($password){
					if(!$this->redis->auth($password)){
						Yii::info('redis服务器密码错误或者本身就无须密码');
					}
				}
			}
		}
		$this->selectPart();*/
    }

    public function add($key, $value){
		//线上服务器没有redis
		$aData = [
			'id' => $key,
			'value' => $value,
		];
		return Redis::add($aData);
		//$this->redis->hMset($key, $value);
    }

    public function get($key, $value=array()){
        /*if(empty($value)){
            $this->redis->hGetAll($key);
        }else{
			$value = str_replace('`', '', $value);
			$this->redis->hMGet($key, $value);
		}*/
		//$this->redis->hGetAll($key);
		$mRedis = Redis::findOne(['id' => $key]);
		if(!$mRedis || $mRedis->expiration_time <= NOW_TIME){
			$mRedis ? $this->delete($key): '';//过期的redis清除
			return '';
		}else{
			return $mRedis->value;
		}
    }

    public function update($key, $value){
		//线上服务器没有redis
		$mRedis = Redis::findOne(['id' => $key]);
		if($mRedis){
			$mRedis->set('value', $value);
			$mRedis->save();
		}
		/*
		$keyExist = $this->redis->keys($key);
		if(!empty($keyExist)){
			//如果存在
			$value = str_replace('`', '', $value);
			$this->redis->hMset($key, $value);
		}
		 */
    }

	public function deleteOne($key){
		return $this->redis->delete($key);
    }

    public function delete($keys){
		$mRedis = Redis::findOne(['id' => $keys]);
		if($mRedis){
			return $mRedis->delete();
		}
		return false;
		/*
		if(!empty($keys)){
            $this->redis->delete($keys);
        }
		 */
    }

    public function addToList($key, $value){
		self::select();
        if(!empty($value)){
            return $this->redis->rPush($key, $value);
        }
    }

    public function getList($key, $start=0, $end=-1){
		self::select();
        return $this->redis->lRange($key, $start, $end);
    }

    public function delFromList($key, $value){
		self::select();
        return $this->redis->lRem($key, $value, 0);
    }

	public function getOne($key){
		$mRedis = Redis::findOne(['id' => $key]);
		if(!$mRedis || $mRedis->expiration_time <= NOW_TIME){
			$mRedis ? $this->delete($key): '';//过期的redis清除
			return '';
		}else{
			return $mRedis->value;
		}
		//return $this->redis->hGetAll($key);
    }

	public function expire($key, $seconds){
		//$this->redis->expire($key, $seconds);
		$mRedis = Redis::findOne(['id' => $key]);
		if($mRedis){
			$mRedis->set('expiration_time', NOW_TIME + $seconds);
			return $mRedis->save();
		}else{
			return $mRedis;
		}
	}

	public function expireOne($key, $seconds){
		$mRedis = Redis::findOne(['id' => $key]);
		if($mRedis){
			$mRedis->set('expiration_time', NOW_TIME + $seconds);
			return $mRedis->save();
		}else{
			return $mRedis;
		}
		//return $this->redis->expire($key, $seconds);
	}
}