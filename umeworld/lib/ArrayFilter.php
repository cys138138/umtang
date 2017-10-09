<?php
namespace umeworld\lib;

/**
 * 数组过滤器
 */
class ArrayFilter extends \yii\base\Object{
	public $aData = [];	//要过滤的数据

	public $aRules = [];	//过滤规则

	/**
	 * 获取过滤数据
	 * @return array 取模的结果集
	 * @throws \yii\base\InvalidParamException
	 */
	public function filter(){
		$aModel = $this->aRules;
		$aData = $this->aData;
		$fFiter = function($aModel, $aData)use(&$fFiter){
			$aResult = [];
			foreach($aModel as $filed => $key){
				if(is_array($key)){
					$aResult[$filed] = $fFiter($key, $aData[$filed]);
				}else{
					if(isset($aData[$key])){
						$aResult[$key] = $aData[$key];
					}
				}
			}
			return $aResult;
		};
		return $fFiter($aModel, $aData);
	}

	/**
	 * 快速过滤数组
	 *
	 * 例如:
	 *
	 * ~~~
	 * $aData = [
	 *		'a' => 1,
	 *		'b' => 2,
	 *		'c' => 3,
	 * ];
	 * $aFilterData = ArrayFilter::fastFilter($aData, 'b,c');	//字符串表达式,结果: ['b' => 2, 'c' => 3]
	 * $aFilterData = ArrayFilter::fastFilter($aData, ['a', 'c']);	//数组表达式也行,结果: ['a' => 1, 'c' => 3]
	 * ~~~
	 * @param array $aArray 要过滤的数组
	 * @param string|array $rules 要过滤出来的键名集合,字符串时用逗号分隔
	 * @return array
	 * @author 黄文非`
	 */
	public static function fastFilter($aArray, $rules){
		$aRules = is_string($rules) ? explode(',', $rules) : $rules;
		$oInstance = new static([
			'aData' => $aArray,
			'aRules' => $aRules,
		]);
		return $oInstance->filter();
	}
}