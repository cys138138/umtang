<?php
namespace umeworld\lib;

/**
 * 视图类
 * @author 黄文非
 */
class View extends \yii\web\View{
	/**
	 * @var string 公共标题,每个页面都有的标题
	 */
	protected $_commonTitle = '';

	/**
	 * @var string 基础标题,这个概念有点含糊,应该找推广部统一一下标题文案先
	 */
	protected $_baseTitle = '';

	/**
	 * 设置页面标题,会自动在该标题后面拼接上 $_commonTitle 属性
	 * @param string $title
	 * @param bool $onlyPageName 是否只显示标题
	 */
	public function setTitle($title, $onlyPageName = false){
		if(!$onlyPageName){
			if($title){
				$this->title = $title . ($this->_commonTitle ? ' - ' . $this->_commonTitle : '');
			}else{
				$this->title = $this->_commonTitle;
			}
		}else{
			$this->title = $title;
		}
	}

	/**
	 * 设置公共标题
	 * @param string $title
	 */
	public function setCommonTitle($title){
		$this->_commonTitle = $title;
	}

	/**
	 * 获取公共标题
	 * @return string
	 */
	public function getCommonTitle(){
		return $this->_commonTitle;
	}

	/**
	 * 设置基础标题
	 * @param string $title
	 */
	public function setBaseTitle($title){
		$this->_baseTitle = $title;
	}

	/**
	 * 获取基础标题
	 * @return string
	 */
	public function getBaseTitle(){
		return $this->_baseTitle;
	}
}