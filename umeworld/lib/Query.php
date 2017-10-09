<?php
namespace umeworld\lib;

use Yii;

class Query extends \yii\db\Query{
	/**
	 * @var bool 是否缓存本次查询的数据
	 */
	public $isCacheData = true;		//是否要从缓存中查，通过表配置得到
	private $_select = null;		//保存最开始要查询的字段信息
	public $isSelectFromCache = true;	//本次query是否查缓存，如果是false则不查缓存。是true则有可能查缓存，看表配置

	/**
	 * 设定查询的表名
	 * @param type $tableName 需要查询的表名,如果在主库则直接填表名,如果在附加数据库则加 _@ 进行别名解析,具体别名映射表在表配置里
	 * @return $this
	 */
	public function from($xTableInfo){
		$tableName = '';
		if(is_string($xTableInfo)){
			$aTableInfo = Yii::$app->db->parseTable($xTableInfo, false);
			$this->isCacheData = $aTableInfo['cache'];
			$tableName = $aTableInfo['table'];
		}elseif(is_array($xTableInfo)){
			$this->isCacheData = $xTableInfo['cache'];
			$tableName = $xTableInfo['table'];
		}

		return parent::from($tableName);
	}

	/**
	 * 执行查询
	 * @return array 查询的结果数据
	 */
	public function run(){
		return $this->createCommand()->queryAll();
	}

	public function one($db = null){
		$isSelectRedis = $this->_isSelectFromRedis();
		if(!$isSelectRedis){
			$aOne = parent::one($db);
			if(!$aOne){
				return [];
			}
			return $aOne;
		}
		$aRedisResult = $this->_berforeSelect();
		if($aRedisResult['is_redis']){
			if($aRedisResult['data']){
				return $aRedisResult['data'][0];
			}else{
				return false;
			}
		}
		$this->select = ['id'];
		$aRecord = parent::one($db);
		if(!$aRecord){
			return [];
		}
		$aResult = $this->_afterSelect([$aRecord]);
		if($aResult){
			$aResult = $aResult[0];
		}
		return $aResult;
	}

	public function all($db = null){
		$isSelectRedis = $this->_isSelectFromRedis();
		if(!$isSelectRedis){
			$aAll = parent::all($db);
			if(!$aAll){
				return [];
			}
			return $aAll;
		}
		$aRedisResult = $this->_berforeSelect();
		if($aRedisResult['is_redis']){
			return $aRedisResult['data'];
		}
		$this->select = ['id'];
		$aRecordList = parent::all($db);
		if(!$aRecordList){
			return [];
		}
		return $this->_afterSelect($aRecordList);
	}

	private function _isSelectFromRedis(){
		$this->_select = $this->select;
		$oRedis = Yii::$app->redis;
		$specialFlag = false;
		if($this->select){
			foreach($this->select as $field){
				$specialFlag = preg_match('/avg\(|AVG\(|count\(|COUNT\(|max\(|MAX\(|min\(|MIN\(|sum\(|SUM\(|distinct|DISTINCT|as|AS/', $field);
				if($specialFlag){
					break;
				}
			}
		}
		$isSelectRedis = true;
		if(!$oRedis->servers[$oRedis->serverName]['is_active'] || $this->groupBy || $this->orderBy || $specialFlag || !$this->isCacheData || $this->join || $this->union || !$this->isSelectFromCache){
			$isSelectRedis = false;
		}
		return $isSelectRedis;
	}

	private function _afterSelect($aRecordList){
		if(!$aRecordList){
			return $aRecordList;
		}
		$aIds = [];
		foreach($aRecordList as $aRecord){
			$aIds[] = $aRecord['id'];
		}
		$this->where = ['id' => $aIds];
		$this->orderBy = null;
		$this->offset = null;
		$this->limit = null;
		$aRedisResult = $this->_berforeSelect();
		return $aRedisResult['data'];
	}

	private function _berforeSelect(){
		$aNoRedisData = [
			'is_redis'	=>	false,
			'data'		=>	false,
		];
		$aWhere = $this->where;
		if($this->orderBy || !is_null($this->limit) || !is_null($this->offset)){
			return $aNoRedisData;
		}
		if(!is_array($aWhere) || !((count($aWhere) == 1 && isset($aWhere['id'])) || (count($aWhere) == 2 && isset($aWhere[0]) && $aWhere[0] = 'and' && isset($aWhere[1]['id'])))){
			return $aNoRedisData;
		}
		if(isset($aWhere[1]['id'])){
			$aWhere = ['id' => $aWhere[1]['id']];
		}
		if(is_numeric($aWhere['id']) || is_array($aWhere['id'])){
			$aRedisData = [
				'is_redis'	=>	true,
				'data'		=>	[],
			];
			if(is_numeric($aWhere['id'])){
				$aWhere['id'] = [$aWhere['id']];
			}
			$aWhere['id'] = array_unique($aWhere['id']);
			$recordCount = count($aWhere['id'] = array_filter($aWhere['id']));
			if(!$recordCount){
				return $aRedisData;
			}
			$aTable = explode('.', $this->from[0]);
			if(count($aTable) == 1){
				$tableName = $aTable[0];
				$aDsn = explode('=', Yii::$app->db->slaves[0]['dsn']);
				$dbName = $aDsn[2];
			}else{
				$dbName = $aTable[0];
				$tableName = $aTable[1];
			}
			$oRedis = Yii::$app->redis;
			$oRedis->connect();
			$oRedis->redis->multi(\Redis::PIPELINE);
			foreach($aWhere['id'] as $id){
				$key = $dbName . ':' . $tableName . ':' . $id;
				$oRedis->get($key);
			}
			$aDataList = $oRedis->redis->exec();
			$aDataList = array_filter($aDataList);
			if(count($aDataList) != $recordCount){
				$this->select = null;
				$aMysqlDataList = parent::all();
				if(count($aMysqlDataList) > count($aDataList)){	//如果mysql查出来的记录数大于redis记录数
					$oRedis->redis->multi(\Redis::PIPELINE);
					foreach($aMysqlDataList as $aMysqlData){
						$key = $dbName . ':' . $tableName . ':' . $aMysqlData['id'];
						$oRedis->add($key, $aMysqlData);
					}
					$oRedis->redis->exec();
				}elseif(count($aMysqlDataList) < count($aDataList)){	//如果redis记录数大于mysql
					$oRedis->redis->multi(\Redis::PIPELINE);
					foreach($aMysqlDataList as $aMysqlData){
						$key = $dbName . ':' . $tableName . ':' . $aMysqlData['id'];
						$oRedis->delete($key);
					}
					$oRedis->redis->exec();
				}
				$aDataList = $aMysqlDataList;
			}
			$aDataList = $this->_filterDataList($aDataList);
			$aRedisData['data'] = $aDataList;
			return $aRedisData;
		}else{
			return $aNoRedisData;
		}
	}

	private function _filterDataList($aDataList){
		$xSelect = $this->_select;
		if(!$xSelect || (is_string($xSelect) && !trim($xSelect)) || (isset($xSelect[0]) && $xSelect[0] == '*')){
			return $aDataList;
		}
		$aFields = [];
		if(is_string($xSelect)){
			$xSelect = str_replace('`', '', $xSelect);
			$aFields = explode(',', $xSelect);
			foreach($aFields as $key => $field){
				$aFields[$key] = trim($field);
			}
		}elseif(is_array($xSelect)){
			$aFields = $xSelect;
		}
		foreach($aFields as $key => $field){
			$aFields[$key] = str_replace('`', '', $field);
		}
		if($aFields){
			foreach($aDataList as $key => $aData){
				$aRecord = array();
				foreach($aFields as $field){
					$aRecord[$field] = $aData[$field];
				}
				$aDataList[$key] = $aRecord;
			}
		}
		return $aDataList;
	}
}