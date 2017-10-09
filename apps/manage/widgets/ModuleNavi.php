<?php
namespace manage\widgets;

use Yii;

class ModuleNavi extends \yii\base\Widget{
	public $aMenus = [];

	public function run(){
		$menuItemsHtml = '';
		foreach($this->aMenus as $aMenu){
			$activeClass = isset($aMenu['active']) && $aMenu['active'] ? ' class="active"' : '';
			$menuItemsHtml .= '<li' . $activeClass . '><a href="' . $aMenu['url'] . '">' . $aMenu['title'] . '</a></li>';
		}
		return '<div class="umNavbar navbar navbar-default">
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">' . $menuItemsHtml . '</ul>
			</div>
		</div>';
	}
}