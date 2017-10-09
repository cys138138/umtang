<?php
namespace manage\lib;

use manage\model\Manager;
use umeworld\lib\Url;


/**
 * 权限验证管理者
 * @author 黄文非
 */
class AuthManager extends \yii\base\Component{
	public $aPermissionList = [];

	public function init() {
		parent::init();
	}

	/**
	 * 检查一个后台用户是否有指定的权限
	 * @param int $managerId 后台用户ID
	 * @param string $permissionName 权限标识名称
	 * @return boolean
	 * @throws \yii\base\InvalidParamException
	 */
//	public function	checkAccess($managerId, $permissionName){
//		if(!$managerId){
//			return false;
//		}
//
//		if(!$mManager = Manager::findOne($managerId)){
//			throw new \yii\base\InvalidParamException('无效的管理者ID');
//		}
//
//		return $mManager->getManagerGroup()->allow($permissionName);
//	}

	/**
	 * 判断一个菜单分组下有没有指定的URL
	 * @param string $url 要查找的URL
	 * @param array $aMenuGroup 菜单组
	 * @return boolean
	 */
	public function getPermissionInfoByUrl($url, $aMenuGroup){
		foreach($aMenuGroup['child'] as $aPermission){
			if(isset($aPermission['url'])){
				if(Url::to($aPermission['url']) == $url){
					return $aPermission;
				}
			}else{
				debug($aPermission,11);
			}
		}
		return [];
	}
}