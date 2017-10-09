<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\Response;
use common\model\Teacher;
use common\model\CommercialTenantApprove;
use common\model\Resource;
use common\model\form\ImageUploadForm;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;

class TeacherController extends Controller{
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	public function actionIndex(){
		$aTeacherList = Teacher::getTeacherListForTenant(['tenant_id' => Yii::$app->commercialTenant->id], ['order_by' => ['order' => SORT_ASC]]);
		return $this->render('index', [
			'aTeacherList' => $aTeacherList,
		]);
	}

	public function actionShowEdit(){
		$id = (int)Yii::$app->request->get('id');
		$createTime = (int)Yii::$app->request->get('createTime');
		
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$aTeacher = Teacher::getTeacherInfoForTenant($mCommercialTenant, $id, $createTime);
		return $this->render('edit', [
			'aTeacher' => $aTeacher,
		]);
	}

	public function actionSave(){
		$id = (int)Yii::$app->request->post('id');
		$profile = (int)Yii::$app->request->post('profile');
		$name = (string)Yii::$app->request->post('name');
		$duty = (string)Yii::$app->request->post('duty');
		$seniority = (int)Yii::$app->request->post('seniority');
		$description = (string)Yii::$app->request->post('description');
		$createTime = (string)Yii::$app->request->post('createTime');
		
		if(!$profile){
			return new Response('请上传教师头像', -1);
		}
		if(!$name){
			return new Response('请填写教师姓名', -1);
		}
		if(!$duty){
			return new Response('请填写教师职务', -1);
		}
		if(!$seniority){
			return new Response('请填写教龄', -1);
		}
		if(!$description){
			return new Response('请填写教师介绍', -1);
		}
		
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$aTeacher = Teacher::getTeacherInfoForTenant($mCommercialTenant, $id, $createTime);
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aShopInfo = $mTenantApprove->shop_info;
		if(!isset($aShopInfo['teacher'])){
			$aShopInfo['teacher'] = [];
		}
		$aData = [];
		if(!$aTeacher && $id != 0){
			return new Response('找不到教师信息', 0);
		}elseif($aTeacher){
			//如果存在就修改
			if($profile != $aTeacher['profile']){
				$aData['profile'] = $profile;
			}
			if($name != $aTeacher['name']){
				$aData['name'] = $name;
			}
			if($duty != $aTeacher['duty']){
				$aData['duty'] = $duty;
			}
			if($seniority != $aTeacher['seniority']){
				$aData['seniority'] = $seniority;
			}
			if($description != $aTeacher['description']){
				$aData['description'] = $description;
			}
			if(!$aData){
				return new Response('操作成功', 1);
			}
			
			$isUpdate = false;
			foreach($aShopInfo['teacher'] as $key => $aWaitApproveTeacher){
				if($aWaitApproveTeacher['id'] == $aTeacher['id'] || $aWaitApproveTeacher['create_time'] == $aTeacher['create_time']){
					$aShopInfo[$key] = array_merge($aWaitApproveTeacher, $aData);
					$isUpdate = true;
					break;
				}
			}
			if(!$isUpdate){
				$aData['id'] = $aTeacher['id'];
				$aShopInfo['teacher'][] = $aData;
			}
		}else{
			//不存在就添加
			$aData = [
				'id' => 0,
				'profile' => $profile,
				'name' => $name,
				'duty' => $duty,
				'seniority' => $seniority,
				'description' => $description,
				'order' => Teacher::getMaxOrder($mCommercialTenant) + 1,
				'create_time' => NOW_TIME,
			];
			$aShopInfo['teacher'][] = $aData;
		}
		$mTenantApprove->set('shop_info', $aShopInfo);
		$mTenantApprove->set('shop_approve_status', CommercialTenantApprove::STATUS_WAIT_APPROVE);
		$mTenantApprove->save();
		
		return new Response('保存成功', 1);
	}

	public function actionUploadProfile(){
		$oForm = new ImageUploadForm();
		$oForm->fCustomValidator = function($oForm){
			/*list($width, $height) = getimagesize($oForm->oImage->tempName);
			if($width != $height){
				$oForm->addError('oImage', '图片宽高比例应为1:1');
				return false;
			}
			return true;*/
		};
		
		$isUploadFromUEditor = false;
		$savePath = Yii::getAlias('@p.tenant_upload') . '/' . mt_rand(10, 99);

		$oForm->oImage = UploadedFile::getInstanceByName('filecontent');
		$aSize = getimagesize($oForm->oImage->tempName);
		$oForm->toWidth = $aSize[0];
		$oForm->toHeight = $aSize[1];
		if(!$oForm->upload($savePath)){
			$message = current($oForm->getErrors())[0];
			return new Response($message, 0);
		}else{
			$id = Resource::add([
				'type' => Resource::TYPE_PROFILE,
				'path' => $oForm->savedFile,
				'create_time' => NOW_TIME,
			]);
			if(!$id){
				return new Response('上传失败', 0);
			}
			
			return new Response('', 1, [
				'resource_id' => $id,
				'path' => $oForm->savedFile,
			]);
		}
	}
	
	public function actionSetOrder(){
		$aOrder = (array)Yii::$app->request->post('aOrder');
		$needCount = 2;
		if(!$aOrder || count($aOrder) != $needCount){
			return new Response('出错了', 0);
		}
		$aTeacherId = ArrayHelper::getColumn($aOrder, 'id');
		if(!$aTeacherId){
			return new Response('出错了', 0);
		}
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$aOrderIndex = ArrayHelper::index($aOrder, 'id');
		$aTeacherList = Teacher::findAll(['id' => $aTeacherId, 'tenant_id' => $mCommercialTenant->id]);
		$approvedCount = count($aTeacherList);
		if($approvedCount < $needCount){
			$aFindTeacherIds = ArrayHelper::getColumn($aTeacherList, 'id');
			$mTenantApprove = $mCommercialTenant->getMTenantApprove();
			$aShop = $mTenantApprove->shop_info;
			$findWaitCount = 0;
			foreach($aShop['teacher'] as $key => $aWaitApproveTeacher){
				if(in_array($aWaitApproveTeacher['id'], $aFindTeacherIds)){
					continue;
				}
				foreach($aOrder as $aOneOrder){
					if($aWaitApproveTeacher['create_time'] == $aOneOrder['createTime']){
						$aShop['teacher'][$key]['order'] = $aOneOrder['order'];
						$findWaitCount++;
						break;
					}
				}
			}
			if(($findWaitCount + $approvedCount) != $needCount){
				return new Response('数量出错了', 0);
			}
			$mTenantApprove->set('shop_info', $aShop);
			$mTenantApprove->save();
		}
		foreach($aTeacherList as $aTeacher){
			$mTeacher = Teacher::toModel($aTeacher);
			$mTeacher->set('order', (int)$aOrderIndex[$mTeacher->id]['order']);
			$mTeacher->save();
		}
		return new Response('设置成功', 1);
	}
	
	public function actionDelete(){
		$id = (int)Yii::$app->request->post('id');
		$createTime = (string)Yii::$app->request->post('createTime');
		
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aShopInfo = $mTenantApprove->shop_info;
		if($id){
			$mTeacher = Teacher::findOne($id);
			if(!$mTeacher){
				return new Response('找不到教师信息', 0);
			}
			if($mTeacher->tenant_id != Yii::$app->commercialTenant->id){
				return new Response('出错了', 0);
			}
			if(!$mTeacher->delete()){
				return new Response('删除失败', 0);
			}
			if(isset($aShopInfo['teacher'])){
				foreach($aShopInfo['teacher'] as $key => $aWaitApproveTeacher){
					if($aWaitApproveTeacher['id'] == $id){
						unset($aShopInfo['teacher'][$key]);
						if(!$aShopInfo['teacher']){
							unset($aShopInfo['teacher']);
						}
						$mTenantApprove->set('shop_info', $aShopInfo);
						$mTenantApprove->save();
					}
				}
			}
		}else{
			if(isset($aShopInfo['teacher'])){
				$aData = [];
				foreach($aShopInfo['teacher'] as $value){
					if($value['create_time'] != $createTime){
						array_push($aData, $value);
					}
				}
				if(count($aData) == count($aShopInfo['teacher'])){
					return new Response('找不到教师信息', 0);
				}else{
					if($aData){
						$aShopInfo['teacher'] = $aData;
					}else{
						unset($aShopInfo['teacher']);
					}
					$mTenantApprove->set('shop_info', $aShopInfo);
					$mTenantApprove->save();
				}
			}else{
				return new Response('找不到教师信息', 0);
			}
		}
		
		return new Response('删除成功', 1);
	}
	
}