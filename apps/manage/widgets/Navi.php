<?php
namespace manage\widgets;

use Yii;

/**
 * 后台导航部件
 * @author 黄文非
 */
class Navi extends \yii\base\Widget{
	public function run(){
		return $this->render('navi', [
			'mManager' => Yii::$app->manager->getIdentity(),
		]);
	}

	/**
	 * 判断当前用户是否拥有一个菜单分组中任意一个子菜单的使用权
	 * @param array $aMenuGroup
	 * @return boolean
	 * @author 黄文非
	 */
	public function hasPermissionInSubMenu($aMenuGroup){
		if(Yii::$app->manager->identity->isRootAdmin()){
			return true;
		}
		
		$mManagerGroup = Yii::$app->manager->getIdentity()->getManagerGroup();
		foreach($aMenuGroup['child'] as $aMenu){
			if($mManagerGroup->allow($aMenu['permission'])){
				return true;
			}
		}

		return false;
	}
}