<?php
namespace umeworld\lib;

use Yii;

class DbCommand extends \yii\db\Command{
	public $table = '';
	public $aWhere = [];
	public $action = '';
	public $isCacheData = true;

	private	$_aRedisIds = [];
	private $_redisDbName = '';
	private $_redisTableName = '';

	public function execute() {
		$oRedis = Yii::$app->redis;
		if(($this->action != 'update' && $this->action != 'delete') || !$oRedis->servers[$oRedis->serverName]['is_active']){
			return parent::execute();
		}
		if(!$this->aWhere){
			throw Yii::$app->buildError('更新或删除数据必须带条件');
		}
		if($this->isCacheData){
			$this->_beforExecute();
		}
		$result = parent::execute();
		if($result && $this->isCacheData){
			$this->_afterExecute();
		}
		return $result;
	}

	public function update($table, $columns, $condition = '', $params = []){
		$table = $this->_parseTable($table);
		$this->table = $table;
		$this->aWhere = $condition;
		$this->action = 'update';
		$columns = $this->_parseColumns($columns);
        $sql = $this->db->getQueryBuilder()->update($table, $columns, $condition, $params);

        return $this->setSql($sql)->bindValues($params);
    }

	public function delete($table, $condition = '', $params = []) {
		$table = $this->_parseTable($table);
		$this->table = $table;
		$this->aWhere = $condition;
		$this->action = 'delete';
		$sql = $this->db->getQueryBuilder()->delete($table, $condition, $params);
        return $this->setSql($sql)->bindValues($params);
	}

	private function _parseColumns($columns){
		foreach($columns as $key => $val){
			if(is_array($val)){
				if($val[0] == 'add'){
					$oExpression = new \yii\db\Expression($key . ' + :' . $key, [':' . $key => $val[1]]);
				}elseif($val[0] == 'sub'){
					$oExpression = new \yii\db\Expression($key . ' - :' . $key, [':' . $key => $val[1]]);
				}else{
					throw Yii::$app->buildError('错误的字段更新操作');
				}
				$columns[$key] = $oExpression;
			}
		}
		return $columns;
	}

	private function _parseTable($xTableInfo){
		$tableName = '';
		if(is_string($xTableInfo)){
			$aTableInfo = Yii::$app->db->parseTable($xTableInfo, false);
			$this->isCacheData = $aTableInfo['cache'];
			$tableName = $aTableInfo['table'];
		}elseif(is_array($xTableInfo)){
			$this->isCacheData = $xTableInfo['cache'];
			$tableName = $xTableInfo['table'];
		}
		return $tableName;
	}

	private function _beforExecute(){
		$aWhere = $this->aWhere;
		$aIds = [];
		if(is_array($aWhere) && count($aWhere) == 1 && isset($aWhere['id'])){
			if(is_numeric($aWhere['id'])){
				$aIds = [$aWhere['id']];
			}elseif(is_array($aWhere['id'])){
				$aIds = $aWhere['id'];
			}
		}
		if(!$aIds){
			$aRecordList = (new \umeworld\lib\Query())->select('`id`')->from($this->table)->where($aWhere)->all();
			if($aRecordList){
				foreach($aRecordList as $aRecord){
					$aIds[] = $aRecord['id'];
				}
			}
		}
		$aTable = explode('.', $this->table);
		if(count($aTable) == 1){
			$tableName = $aTable[0];
			$aDsn = explode('=', Yii::$app->db->slaves[0]['dsn']);
			$dbName = $aDsn[2];
		}else{
			$dbName = $aTable[0];
			$tableName = $aTable[1];
		}
		$this->_aRedisIds = $aIds;
		$this->_redisDbName = $dbName;
		$this->_redisTableName = $tableName;
	}

	private function _afterExecute(){
		$aIds = $this->_aRedisIds;
		$dbName = $this->_redisDbName;
		$tableName = $this->_redisTableName;
		if(!$aIds){
			throw Yii::$app->buildError('查找记录失败,不能同步redis了');
		}
		$oRedis = Yii::$app->redis;
		$oRedis->connect();
		$oRedis->redis->multi(\Redis::PIPELINE);
		foreach($aIds as $id){
			$key = $dbName . ':' . $tableName . ':' . $id;
			$oRedis->delete($key);
		}
		$oRedis->redis->exec();
	}
}