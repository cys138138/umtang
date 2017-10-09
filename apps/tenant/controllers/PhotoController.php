<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\Response;
use common\model\CommercialTenantPhoto;
use common\model\CommercialTenantApprove;
use common\model\Resource;
use common\model\form\ImageUploadForm;
use yii\web\UploadedFile;
use common\filter\TenantAccessControl as Access;

class PhotoController extends Controller{
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	public function behaviors(){
		return [
			'access' => [
				//登陆访问控制过滤
				'class' => Access::className(),
				'ruleConfig' => [
					'class' => 'yii\filters\AccessRule',
					'allow' => true,
				],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['@'],  //'@'
					],
				]
			],
		];
	}
	
	public function actionIndex(){
		return $this->render('index');
	}
	
	public function actionGetList(){
		$page = (int)Yii::$app->request->post('page');
		$pageSize = (int)Yii::$app->request->post('pageSize');
		
		if(!$page || $page <= 0){
			$page = 1;
		}
		if(!$pageSize || $pageSize <= 0){
			$pageSize = 10;
		}
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$aCondition = [
			'tenant_id'	=>	$mCommercialTenant->id,
		];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
			'order_by' => ['id' => SORT_DESC],
		];
		$aList = CommercialTenantPhoto::getListForTenant($aCondition, $aControl);
		$count = CommercialTenantPhoto::getCountForTenant($aCondition);	//不包含审核表的总数
		/*debug($aList, 11);
		$totalPage = $count % $pageSize ? intval($count / $pageSize) + 1 : $count / $pageSize;
		$lastPageFillCount = $pageSize - $count % $pageSize;	//补全最后一页的个数，用作下面从审核表取记录时，从第几个记录开始取，相当于审核表offset
		
		
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aShopInfo = $mTenantApprove->getShopInfoWithPath();
		if(isset($aShopInfo['photo'])){
			$count += count($aShopInfo['photo']);
		}
		//如果相册表的记录取完了，从审核表取
		if($totalPage <= $page){
			if($totalPage == $page){
				$lastPageFillCount = 0;	//如果是最后一页,从审核表第一个记录开始取
			}
			$n = $pageSize - count($aList);	//补全页个数，从审核表取
			for($i = $lastPageFillCount; $i < $n + $lastPageFillCount; $i++){
				if(!isset($aShopInfo['photo'][$i])){
					break;
				}
				array_push($aList, [
					'id' => 0, 
					'resource_id' => $aShopInfo['photo'][$i]['resource_id'],
					'path' => $aShopInfo['photo'][$i]['path'],
					'is_cover' => 0,
				]);
			}
		}*/
		
		return new Response('', 1, [
			'totalCount' => $count,
			'aList' => $aList,
		]);
	}
	
	public function actionUpload(){
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
				'type' => Resource::TYPE_TENANT_PHOTO,
				'path' => $oForm->savedFile,
				'create_time' => NOW_TIME,
			]);
			if(!$id){
				return new Response('上传失败', 0);
			}
			$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
			$mTenantApprove = $mCommercialTenant->getMTenantApprove();
			$aShopInfo = $mTenantApprove->shop_info;
			if(isset($aShopInfo['photo'])){
				array_push($aShopInfo['photo'], ['id' => 0, 'resource_id' => $id]);
			}else{
				$aShopInfo['photo'] = [['id' => 0, 'resource_id' => $id]];
			}
			$mTenantApprove->set('shop_info', $aShopInfo);
			$mTenantApprove->set('shop_approve_status', CommercialTenantApprove::STATUS_WAIT_APPROVE);
			$mTenantApprove->save();
			return new Response('', 1, [
				'resource_id' => $id,
				'path' => $oForm->savedFile,
			]);
		}
	}
		
	public function actionSetCover(){
		$id = (int)Yii::$app->request->post('id');
		$resourceId = (int)Yii::$app->request->post('resourceId');
		
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aShopInfo = $mTenantApprove->shop_info;
		if(isset($aShopInfo['photo'])){
			foreach($aShopInfo['photo'] as $value){
				if($value['resource_id'] == $resourceId){
					return new Response('照片审核中，不能设置为封面', 0);
				}
			}
		}
			
		$mCommercialTenantPhoto = CommercialTenantPhoto::findOne($id);
		if(!$mCommercialTenantPhoto){
			return new Response('找不到照片信息', 0);
		}
		if($mCommercialTenantPhoto->tenant_id != Yii::$app->commercialTenant->id){
			return new Response('不能设置为封面', 0);
		}
		if(!$mCommercialTenantPhoto->setCover()){
			return new Response('设置封面失败', 0);
		}
		return new Response('设置封面成功', 1);
	}
	
	public function actionDelete(){
		$id = (int)Yii::$app->request->post('id');
		$resourceId = (int)Yii::$app->request->post('resourceId');
		
		$mResource = Resource::findOne($resourceId);
		if(!$mResource){
			return new Response('找不到照片信息', 0);
		}
		if($id){
			$mCommercialTenantPhoto = CommercialTenantPhoto::findOne($id);
			if(!$mCommercialTenantPhoto){
				return new Response('找不到照片信息', 0);
			}
			if($mCommercialTenantPhoto->tenant_id != Yii::$app->commercialTenant->id){
				return new Response('不能删除', 0);
			}
			if(!$mCommercialTenantPhoto->delete()){
				return new Response('删除失败', 0);
			}
		}else{
			$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
			$mTenantApprove = $mCommercialTenant->getMTenantApprove();
			$aShopInfo = $mTenantApprove->shop_info;
			if(isset($aShopInfo['photo'])){
				$aData = [];
				foreach($aShopInfo['photo'] as $value){
					if($value['resource_id'] != $resourceId){
						array_push($aData, $value);
					}
				}
				if(count($aData) == count($aShopInfo['photo'])){
					return new Response('找不到照片信息', 0);
				}else{
					if($aData){
						$aShopInfo['photo'] = $aData;
					}else{
						unset($aShopInfo['photo']);
					}
					$mTenantApprove->set('shop_info', $aShopInfo);
					$mTenantApprove->save();
				}
			}else{
				return new Response('找不到照片信息', 0);
			}
		}
		return new Response('删除成功', 1);
	}
	
}
