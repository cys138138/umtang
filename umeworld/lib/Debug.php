<?php
namespace umeworld\lib;

use Yii;

/**
 * 调试辅助类,部分代码参照了ThinkPHP
 * @author 黄文非
 */
class Debug{
	const MODE_NORMAL = 1;
	const MODE_EXPORT = 2;
	const MODE_DUMP = 3;
	const MODE_STOP = 10;
	const MODE_TRACE = 100;

	public static $sliceTraceRows = 0;	//要裁剪的trace

	private static $aMarker = array();

	/**
	 * 标记一个调试位置
	 * @param type $name
	 */
	public static function mark($name){
		self::$aMarker['time'][$name] = microtime(true);
		self::$aMarker['mem'][$name] = memory_get_usage();
		self::$aMarker['peak'][$name] = memory_get_peak_usage();
	}

	/**
	 * 取得所用时间
	 * @param type $start
	 * @param type $end
	 * @param type $decimals
	 * @return string
	 */
	public static function getUseTime($start, $end, $decimals = 6){
		if(!isset(self::$aMarker['time'][$start])){
			return 'Error';
		}
		if(!isset(self::$aMarker['time'][$end])){
			self::$aMarker['time'][$end] = microtime(true);
		}
		return number_format(self::$aMarker['time'][$end] - self::$aMarker['time'][$start], $decimals);
	}

	/**
	 * 取得所使用的内存,单位=KB
	 * @param type $start
	 * @param type $end
	 * @return string
	 */
	public static function getUseMemory($start, $end){
		if(!isset(self::$aMarker['mem'][$start])){
			return -1;
		}
		if(!isset(self::$aMarker['mem'][$end])){
			self::$aMarker['mem'][$end] = memory_get_usage();
		}
		return number_format((self::$aMarker['mem'][$end] - self::$aMarker['mem'][$start]) / 1024, 3);
	}

	/**
	 * 取得内存峰值
	 * @param type $start
	 * @param type $end
	 * @return string
	 */
	public static function getPeakMemory($start, $end){
		if(!isset(self::$aMarker['peak'][$start])){
			return 'Error';
		}
		if(!isset(self::$aMarker['peak'][$end])){
			self::$aMarker['peak'][$end] = memory_get_peak_usage();
		}
		return number_format(max(self::$aMarker['peak'][$start], self::$aMarker['peak'][$end]) / 1024, 3);
	}

	/**
	 * 输出调试信息
	 * @param mixed $xData 要输出的调试数据
	 * @param int $mode 调试模式,个位数=输出模式,十位数=是否停止运行,百位数=是否输出回溯
	 * 详解:
	 * 个位数:
	 * 1=正常输出(默认模式)
	 * 2=输出为PHP源代码的书写方式
	 * 3=dump模式,输出详细信息
	 *
	 * 十位数:
	 * 0=不停止运行
	 * 1=停止运行
	 * 但ajax请求情况下无论如何都会停止运行
	 *
	 * 百位数:
	 * 0=不输出回溯
	 * 1=输出圆润
	 *
	 * @example
	 *
	 * ~~~
	 * debug(123, 11);	//十位数1指定停止运行,个位数1指定以正常模式输出
	 * debug(123, 111);	//加上百位数1指定输出回溯,看看怎么跑到这里的
	 * debug([1, 2, 3, 'a' => 'b'], 12);	//个位数=2指定以PHP源代码方式输出,并十位数指定停止运行
	 * ~~~
	 */
	public static function dump($data, $mode = self::MODE_NORMAL, $functionMode = false){
		$rawData = $data;
		if(is_bool($data)){
			$data = $data ? 'true' : 'false';
		}
		static $debugCount = 0;
		$aDebugStruct = array('data' => $data);

		$oException = new \Exception();
		$aTraceList = $oException->getTrace();
		if($functionMode){
			array_shift($aTraceList);
		}
		$aTrace = array_shift($aTraceList);
		$aDebugStruct['file'] = $aTrace['file'];
		$aDebugStruct['line'] = $aTrace['line'];
		unset($aTrace);
		$aDebugStruct['trace'] = $oException->getTraceAsString();

		$aFileCodes = @file($aDebugStruct['file']);
		$aDebugStruct['code'] = '(无法获取脚本内容)';
		if($aFileCodes != ''){
			$aCode = [];
			$lineScript = $aFileCodes[$aDebugStruct['line'] - 1];
			if(preg_match('/debug.*\(.*\)(?= *;)/i', $lineScript, $aCode)){
				$aDebugStruct['code'] = $aCode[0];
			}
		}

		$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST['ajax']) || !empty($_GET['ajax']);

		$outputString = '<div style="min-width:590px; margin:20px; padding:10px; font-size:14px; border:1px solid #000;">================= 新的调试点： <span style="color:#121E31; font-size:14px;">' . ++$debugCount . '</span> ========================<br />';
		$outputString .= "<font style=\"color:green; font-size:14px;\">$aDebugStruct[file]</font> 第 $aDebugStruct[line] 行<br />\n";
		$outputString .= "<font style=\"color:red; font-size:14px;\">$aDebugStruct[code]</font><br />\n";
		$outputString .= '调试输出内容:<br />';
		$outputString .= '类型:' . gettype($rawData) . '<br />';
		$outputString .= "值:<br /><pre style=\"font-size:14px;\"><p style=\"background:#92E287;\">";

		$modes = self::parseMode($mode);
		$outputData = null;
		if($modes['output'] == self::MODE_DUMP){
			ob_start();
			var_dump($data);
			$outputData = ob_get_clean();
			$outputString .= $outputData;
		} elseif($modes['output'] == self::MODE_EXPORT){
			$outputData = var_export($data, true);
			$outputString .= $outputData;
		} elseif($modes['output'] == self::MODE_NORMAL){
			$outputData = print_r($data, true);
			$outputString .= $outputData;
		}else{
			throw new \Exception('未知的调试模式');
		}

		$outputString .= "</p></pre>\n";

		if(!$isAjax){
			$btnBackReferer = isset($_SERVER['HTTP_REFERER']) ? '<a href="' . $_SERVER['HTTP_REFERER'] . '" style="margin-left:20px;">返回(清空表单)</a>' : '';
			$outputString .= '<br /><br style="clear:both;" /><br /><a href="javascript:history.go(-1)" onclick="">返回(保留表单状态)</a>' . $btnBackReferer . '<a href="javascript:location.reload()" style="margin-left:20px;">刷新</a>';
		}else{
			$aDebugStruct['trace'] = explode("\n", $aDebugStruct['trace']);
		}

		if($modes['trace'] == self::MODE_TRACE){
			$traceHTML = '<div><p>运行轨迹:</p><pre>' . $oException->__toString() . '</pre></div>';
			$outputString .= $traceHTML;
		}

		$outputString .= '</div>';

		$aDebugStruct['string'] = $outputString;

		if(isset(Yii::$app->response->headers) && !Yii::$app->response->headers->has('Content-type')){
			@header('Content-type:text/html;charset=utf8');
		}

		if($isAjax){
			(new Response($aDebugStruct['file'] . PHP_EOL . $aDebugStruct['line'] . '行:' . PHP_EOL . $outputData, 0, $aDebugStruct))->send();
			exit;
		}else{
			echo $outputString;
		}
		if($modes['stop'] == self::MODE_STOP){
			exit;
		}
	}

	private static function parseMode($mode){
		$modes = str_split((string)$mode);
		$modeCount = count($modes);

		$outputTrace = 0;
		$stop = 0;
		$outputMode = 0;
		if($modeCount == 3){
			list($outputTrace, $stop, $outputMode) = $modes;
		}
		if($modeCount == 2){
			list($stop, $outputMode) = $modes;
		}
		if($modeCount == 1){
			list($outputMode) = $modes;
		}
		$outputTrace *= 100;
		$stop *= 10;
		return array(
			'output' => $outputMode,
			'stop' => $stop,
			'trace' => $outputTrace
		);
	}

	protected static function filterTraceList($aTraceList){
		foreach($aTraceList as &$aTrace){
			if(!isset($aTrace['class'])){
				$aTrace['class'] = '--';
			}
			//组装参数字符串
			foreach($aTrace['args'] as $j => &$xArgument){
				//file_put_contents($log, '开始' . $i . '的' . $j);
				if(is_array($xArgument) && count($xArgument) > 20){
					$xArgument = array_slice($xArgument, 0, 20);
					$xArgument[] = '...';
				}

				if(is_array($xArgument)){
					foreach($xArgument as &$xArgumentItem){
						if(is_object($xArgumentItem)){
							$xArgumentItem = get_class($xArgumentItem);
						}
					}
				}elseif(is_object($xArgument)){
					$xArgument = get_class($xArgument);
				}
			}

			if(!isset($aTrace['file'])){
				$aTrace['file'] = 'none(自带函数)';
			}

			if(!isset($aTrace['line'])){
				$aTrace['line'] = 'none';
			}
		}
		return $aTraceList;
	}

	/**
	 * 取得回溯的HTML
	 * @param type $aTraceList
	 * @return string
	 */
	public static function getTraceHTML($aTraceList){
		if(static::$sliceTraceRows){
			$aTraceList = array_slice($aTraceList, static::$sliceTraceRows);
			static::$sliceTraceRows = 0;
		}
		$resultHTML = '';
		$traceHTML = '';
		$step = count($aTraceList) + 1;
		//$log = Yii::getAlias('@webroot') . '/db/' . 'ff' . '.php';
		for($i = 0; $i < count($aTraceList); $i++){
			$aTrace = $aTraceList[$i];
			if(!isset($aTrace['class'])){
				$aTrace['class'] = '--';
			}
			//组装参数字符串
			$arumentString = '';
			foreach($aTrace['args'] as $j => $xArgument){
				//file_put_contents($log, '开始' . $i . '的' . $j);
				if(is_array($xArgument) && count($xArgument) > 20){
					$xArgument = array_slice($xArgument, 0, 20);
					$xArgument[] = '...';
				}

				if(is_array($xArgument)){
					foreach($xArgument as &$xArgumentItem){
						if(is_object($xArgumentItem)){
							$xArgumentItem = get_class($xArgumentItem);
						}
					}
				}elseif(is_object($xArgument)){
					$xArgument = get_class($xArgument);
				}

				try{
					$arumentString .= \var_export($xArgument, 1) . ', ';
				}catch(\Exception $e){
					exit($e->getMessage());
				}
			}

			if(!isset($aTrace['file'])){
				$aTrace['file'] = 'none(自带函数)';
			}

			if(!isset($aTrace['line'])){
				$aTrace['line'] = 'none';
			}

			$arumentString = str_replace(PHP_EOL, '', rtrim($arumentString, ', '));
			//组装回溯
			$traceHTML .= '<p>[第' . --$step . '个环节] ' . $aTrace['file'] . ' 第 ' . $aTrace['line'] . ' 行: ' . $aTrace['class'] . '->' . $aTrace['function'] . '(' . $arumentString . ')</p>' . PHP_EOL;
		}

		$resultHTML = '<pre>' . PHP_EOL . $traceHTML . '</pre>';
		return $resultHTML;
	}

	/**
	 * 显示一个类的信息
	 * @param type $class
	 * @param type $mode
	 */
	public static function showClass($class, $mode = self::MODE_NORMAL){
		if(is_string($class) && !class_exists($class)){
			self::dump($class . '这个类不存在', 111);
		}
		$oReflectionClass = new \ReflectionClass($class);
		$filename = $oReflectionClass->getFileName();
		$startLine = $oReflectionClass->getStartLine() - 1;
		$endLine = $oReflectionClass->getEndLine();
		$nameSpace = $oReflectionClass->getNamespaceName();
		$className = $oReflectionClass->getName();
		$docuemt = $oReflectionClass->getDocComment();
		$classCodes = self::getReflectionCode($filename, $startLine - 1, $endLine);
		$infos = <<<UWEOL
文件位置: $filename
第{$startLine}行 到 第{$endLine}行
命名空间: $nameSpace
类名: $className

代码:
$docuemt
$classCodes
UWEOL;
		self::dump($infos, $mode);
	}

	/**
	 * 显示一个类的某个方法信息
	 * @param type $class
	 * @param type $methodName
	 * @param type $mode
	 * @return boolean
	 */
	public static function showMethod($class, $methodName, $mode = self::MODE_NORMAL){
		$oReflectionClass = new \ReflectionClass($class);
		try{
			$oMethod = $oReflectionClass->getMethod($methodName);
		}catch(ReflectionException $oException){
			$errInfo = $oException->getCode() == 0 ? $oReflectionClass->getName() . ' 这个类里面并不存在 ' . $methodName . ' 这个方法' : $oException->getMessage();
			self::dump($errInfo, $mode);
			return false;
		}
		$filename = $oMethod->getFileName();
		$startLine = $oMethod->getStartLine();
		$endLine = $oMethod->getEndLine();
		$docuemt = $oMethod->getDocComment();
		$methodCodes = self::getReflectionCode($filename, $startLine - 1, $endLine);
		$infos = <<<UWEOL
文件位置: $filename
第{$startLine}行 到 第{$endLine}行
代码:
\t$docuemt
$methodCodes
UWEOL;
		self::dump($infos, $mode);
	}

	/**
	 * 得到一个类的代码
	 * @param type $filename
	 * @param type $startLine
	 * @param type $endLine
	 * @return type
	 */
	private static function getReflectionCode($filename, $startLine, $endLine){
		$aReflectionCode = @file($filename);
		return implode(array_slice($aReflectionCode, $startLine, $endLine - $startLine));
	}
}
