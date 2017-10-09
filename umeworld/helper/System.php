<?php
namespace umeworld\helper;

/**
 * 服务端系统助手类
 * @author 黄文非
 */
class System extends \yii\base\Object{
	/**
	 * 判断当前系统是否windows
	 * @return bool
	 */
	public function getIsWindows(){
		return strpos(PHP_OS, 'winnt') !== false;
	}
}