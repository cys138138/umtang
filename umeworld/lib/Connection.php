<?php
namespace umeworld\lib;

use Yii;
use yii\log\Logger;
use umeworld\lib\DbCommand;

/**
 * @inheritdoc
 */
class Connection extends \yii\db\Connection{
	/**
	 * @var array 表别名集合,键名就是别名的名称,键值就是表的真实位置,可以是 数据库.数据表 格式或纯数据表名称
	 */
	public $aTables = [];

	/**
	 * @var array 解析过的表信息
	 */
	protected $_aParsedTableInfos = [];

	/**
	 * 创建SQL命令对象
	 * @author 李亚林
	 * @param string $sql
	 * @param array $params
	 * @return DbCommand
	 */
	public function createCommand($sql = null, $params = [])
    {
        $command = new DbCommand([
            'db' => $this,
            'sql' => $sql,
        ]);

        return $command->bindValues($params);
    }

	/**
	 * 解析表名
	 * @param string $tableName 以 _@ 开头的字母数字组合字符串,比如 _@user
	 * @param bool $isGetTableNameOnly 是否只要返回表名
	 * @return string|array $getTableNameOnly=true就返回解析后的表名,否则返回结构体表达更多的表配置信息
	 * @see $aTables
	 * @test \tests\codeception\common\framework\ConnectionTest::testParseTable
	 * @author 黄文非
	 */
	public function parseTable($tableName, $isGetTableNameOnly = true){
		$aAliasInfo = explode('_@', $tableName);
		$alias = !$aAliasInfo[0] ? $aAliasInfo[1] : $aAliasInfo[0];	//表别名

		if(isset($this->_aParsedTableInfos[$alias])){
			//如果已经解析过
			return $isGetTableNameOnly ? $this->_aParsedTableInfos[$alias]['table'] : $this->_aParsedTableInfos[$alias];
		}

		$aConfig = [
			//默认值
			'table' => $alias,
			'cache' => 1,
		];
		if(isset($this->aTables[$alias])){
			$tableConfig = rtrim($this->aTables[$alias], ';');
			foreach(explode(';', $tableConfig) as $configItem){
				list($attr, $attrValue) = explode(':', trim($configItem));
				$aConfig[$attr] = $attrValue;
			}
		}

		$this->_aParsedTableInfos[$alias] = $aConfig;	//存入缓存的解析结果中
		return $isGetTableNameOnly ? $aConfig['table'] : $aConfig;
	}

	/**
	 * 获取上一条执行的SQL语句
	 * @param int $nums 要获取的条数
	 * @param string $patten 搜索表达式,可以为普通字符串搜索,或者以#或/作定界符的正则表达式搜索
	 * @return array 匹配的SQL语句列表
	 * @test \tests\codeception\common\framework\ConnectionTest::testGetLastSqls
	 * @author 黄文非
	 */
	public function getLastSqls($nums = 1, $patten = ''){
		$aMessageList = &Yii::getLogger()->messages;

		$aSqlList = [];	//匹配结果
		$matchIndex = -1;
		//遍历系统消息
		for($i = count($aMessageList) - 1; $i >=0; $i--){
			$messageLevel = $aMessageList[$i][1];
			$messageCategory = $aMessageList[$i][2];
			if($messageLevel != Logger::LEVEL_INFO
				|| !in_array($messageCategory, [
					'yii\db\Command::query',
					'yii\db\Command::execute',
				])){
				//如果不是执行SQL的消息记录就跳过
				continue;
			}

			$sql = $aMessageList[$i][0];
			if(	strpos($sql, 'SHOW CREATE TABLE') === 0
				|| strpos($sql, 'SHOW FULL COLUMNS FROM') === 0
				){
				continue;
			}

			$matched = false;	//是否匹配
			if(!$patten){
				//无搜索
				$matched = true;
			}else{
				if($patten[0] != '#'){
					//字符搜索
					if(strpos($sql, $patten) !== false){
						$matched = true;
					}
				}elseif($patten[0] == '#' || $patten[0] == '/'){
					//正则搜索
					$aMatchResult = [];
					if(preg_match($patten, $sql, $aMatchResult)){
						$matched = true;
					}
				}
			}

			if($matched){
				$aSqlList[++$matchIndex] = $sql;
				if($matchIndex + 1 == $nums){
					//匹配足够
					break;
				}
			}

		}

		return $aSqlList;
	}
}