<?php
namespace umeworld\lib;

use yii\log\Logger;

/**
 * 文件日志收集器
 * @author 黄文非
 */
class FileLogTarget extends \yii\log\FileTarget{
	/**
	 * @var int 单个日志最大容量,单位KB
	 */
    public $maxFileSize = 10240;

	/**
	 * @var int 每天的最大日志数量
	 */
    public $maxLogFiles = 50;

	/**
	 * @var array 要过滤的消息分类
	 */
    public $aFilterCategorys = [];

	/**
	 * 格式化日志消息
	 * @param array $aMessage 消息结构体
	 * @return string 格式化之后的日志字符串内容
	 */
    public function formatMessage($aMessage)
    {
        list($text, $level, $category, $timestamp) = $aMessage;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            $text = VarDumper::export($text);
        }

        $prefix = $this->getMessagePrefix($aMessage);
        return "LOG_START=================================\n".
			"[time=" . date('Y-m-d H:i:s', $timestamp) . "] {$prefix}[level=$level][category=$category] \n" .
			$text.
        	"\n=================================LOG_END";
    }

	/**
	 * 导出消息到文件中形成日志
	 */
	public function export(){
		$this->messages = $this->_filterMessages($this->messages);
		if(count($this->messages)){
			parent::export();
		}
	}

	/**
	 * 过滤不需要的消息
	 * @param array $aMessageList 消息列表
	 * @return array 过滤后的消息列表
	 * @see aFilterCategorys
	 */
	private function _filterMessages($aMessageList){
		$aResultList = [];
		$index = 0;
		foreach($aMessageList as $i => $aMessage){
			if(strpos($aMessage[0], '$_SERVER = [') === false){
				$aResultList[$index] = $aMessage;
				$index++;
			}else{
				$traces = [];
		        if (isset($aResultList[$index - 1][4])) {
		            foreach($aResultList[$index - 1][4] as $trace) {
		                $traces[] = "in {$trace['file']}:{$trace['line']}";
		            }
		        }

		        $aMessage[0] = preg_replace('/\$_SERVER = \[(.|\n)*?\]/', '', $aMessage[0]);

				if(empty($aResultList[$index - 1][4])){
					$file = '???';
					$line = '0';
					if(stripos($aResultList[$index - 1][0], 'Stack') == true){
						$matchCount = preg_match('/exception(.+?) in (.+?)[:|php\(](\d+)/i', $aResultList[$index - 1][0], $aErrorFile);
						if($matchCount){
							$file = $aErrorFile[2];
							$line = $aErrorFile[3];
						}
					}
				}else{
					$file = $aResultList[$index - 1][4][0]['file'];
					$line = $aResultList[$index - 1][4][0]['line'];
				}

				$aServerText = '';
				$requestUrl = '';
				if(isset($_SERVER)){
					$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
					$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
					$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
					$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
					$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
					$aServerText = '$_SERVER = [' . "\n\t" .
						"'HTTP_HOST' => '" . $host  . "',\n\t".
						"'REQUEST_URI' => '" . $uri  . "',\n\t".
						"'HTTP_REFERER' => '" . $referer  . "',\n\t".
						"'REQUEST_METHOD' => '" . $method  . "',\n\t".
						"'HTTP_USER_AGENT' => '" . $agent . "',\n" . ']';

					$requestUrl = 'http://' . $host . $uri;
				}
				if(!$file){
					$file = '???';
				}
				if(!is_numeric($line)){
					$line = 0;
				}
				$aResultList[$index - 1][0] = "message_start\n" . $aResultList[$index - 1][0] . "\nmessage_end\n\n" .
				'file: ' . $file . "\n" .
				'line: ' . $line . "\n" .
				'request_url: ' . $requestUrl .
				(empty($traces) ? '' : "\n\ntrace_start:\n    " . implode("\n    ", $traces) . "\ntrace_end") .
				"\n\nenv_vars_start:\n" . $aMessage[0] . PHP_EOL . $aServerText . "\nenv_vars_end";
			}
		}

		if($this->aFilterCategorys){
			$aResultList = $this->filterMessageByCategory($aResultList);
		}
		return $aResultList;
	}

	/**
	 * 按分类过滤日志
	 * @param array $aMessageList 要过滤的消息列表
	 * @return array 过滤后的消息列表
	 * @test \tests\codeception\common\unit\system_lib\FileLogTargetTest::testFilterMessageByCategory
	 */
	public function filterMessageByCategory($aMessageList){
		foreach($aMessageList as $i => $aMessage){
			$messageCategory = $aMessage[2];
			foreach($this->aFilterCategorys as $category){
				if($category == $messageCategory){
					unset($aMessageList[$i]);
					break;
				}elseif(substr($category, -1) == '*'){
					$matchPatten = substr($category, 0, -1);
					if(strstr($messageCategory, $matchPatten)){
						unset($aMessageList[$i]);
						break;
					}
				}
			}
		}
		return $aMessageList;
	}

	/*protected function getContextMessage($aMessage){
		return var_dump($aMessage);
	}*/
}