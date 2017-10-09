<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\PhoneValidator;
use yii\validators\EmailValidator;
use umeworld\lib\Response;
use common\model\form\ImageUploadForm;
use yii\web\UploadedFile;
use common\model\Resource;
use common\model\CommercialTenantTypeRelation;
use common\model\CommercialTenant;
use common\model\CommercialTenantApprove;
use common\model\CommercialTenantType;
use umeworld\lib\StringHelper;
use yii\helpers\ArrayHelper;

class TenantShopController extends Controller{
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	public function behaviors(){
		return \yii\helpers\ArrayHelper::merge([
			'access' => [
				'rules' => [
					[
						'allow' => true,
						'actions' => ['show-fill-tenant-shop', 'save-fill-tenant-shop'],
						'roles' => ['@'],
					],
				],
			],
		], parent::behaviors());
	}
	
	public function actionShowFillTenantShop(){
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$aCommercialTenantTypeRelation = CommercialTenantTypeRelation::findAll(['tenant_id' => $mCommercialTenant->id]);
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aCommercialTenantShop = $mTenantApprove->getShopInfoWithPath();
		$aCommercialTenantShop['id'] = $mCommercialTenant->id;
		if(!isset($aCommercialTenantShop['commercial_type_info'])){
			$aCommercialTenantShop['commercial_type_info'] = ['value' => ArrayHelper::getColumn($aCommercialTenantTypeRelation, 'type_id')];
		}
		$aKey = ['name', 'profile', 'profile_path', 'address', 'description', 'contact_number', 'lng', 'lat'];
		foreach($aKey as $key){
			if(!isset($aCommercialTenantShop[$key])){
				$aCommercialTenantShop[$key]['value'] = $mCommercialTenant->$key;
				if($key == 'profile'){
					$aCommercialTenantShop[$key]['path'] = $mCommercialTenant->profile_path;
				}
			}
		}
		$adcode = Yii::$app->tencentMap->getAdcodeByLocation($aCommercialTenantShop['lng']['value'], $aCommercialTenantShop['lat']['value']);
		$this->layout = 'approve';
		return $this->render('fill_tenant_shop', [
			'adcode' => $adcode,
			'aCommercialTenantShop' => $aCommercialTenantShop,
			'aCommercialTenantTypeList' => CommercialTenantType::findAll(),
		]);
	}
	
	public function actionSaveFillTenantShop(){
		$name = (string)Yii::$app->request->post('name');
		$description = (string)Yii::$app->request->post('description');
		$contactNumber = (string)Yii::$app->request->post('contact_number');
		$cityId = (int)Yii::$app->request->post('city_id');
		$lng = (string)Yii::$app->request->post('lng');
		$lat = (string)Yii::$app->request->post('lat');
		$address = (string)Yii::$app->request->post('address');
		$profile = (int)Yii::$app->request->post('profile');
		$isDraft = (int)Yii::$app->request->post('is_draft');
		$aCommercialTenantType = (array)Yii::$app->request->post('commercial_tenant_type');
		
		if(!$isDraft && !$name){
			return new Response('请输入商铺名称', -1);
		}
		if(!$isDraft && !$description){
			return new Response('请输入商铺描述', -1);
		}
		if(!$isDraft && !$contactNumber){
			return new Response('请输入商铺电话', -1);
		}
		/*if(!$cityId){
			return new Response('缺少城市id', -1);
		}*/
		if(!$isDraft && !$lng){
			return new Response('缺少城经纬坐标', -1);
		}
		if(!$isDraft && !$lat){
			return new Response('缺少城经纬坐标', -1);
		}
		if(!$isDraft && !$address){
			return new Response('请输入商铺地址', -1);
		}
		if(!$isDraft && !Resource::findOne($profile)){
			return new Response('请上传商铺头像', -1);
		}
		
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		
		$aTenantInfo = [
			'name' => ['value' => $name],
			'description' => ['value' => $description],
			'contact_number' => ['value' => $contactNumber],
			'city_id' => ['value' => $cityId],
			'lng' => ['value' => $lng],
			'lat' => ['value' => $lat],
			'address' => ['value' => $address],
			'profile' => ['value' => $profile],
			'commercial_tenant_type' => ['value' => $aCommercialTenantType],
		];
		$aTenantInfo = array_merge($mTenantApprove->shop_info, $aTenantInfo);
		$mTenantApprove->set('shop_info', $aTenantInfo);
		$mTenantApprove->set('last_edit_time', NOW_TIME);
		$mTenantApprove->save();
		if(!$isDraft){
			$mCommercialTenant->set('data_perfect', CommercialTenant::DATA_PERFECT_FINISH);
			$mCommercialTenant->set('online_status', CommercialTenant::ONLINE_STATUS_IN_APPROVE);
			$mCommercialTenant->save();
		}
		
		return new Response('保存成功', 1);
	}
	
	public function actionShowShopInfo(){
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$aCommercialTenantTypeRelation = CommercialTenantTypeRelation::findAll(['tenant_id' => $mCommercialTenant->id]);
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aCommercialTenantShop = $mTenantApprove->getShopInfoWithPath();
		$aCommercialTenantShop['id'] = $mCommercialTenant->id;
		if(!isset($aCommercialTenantShop['commercial_type_info'])){
			$aCommercialTenantShop['commercial_type_info'] = ['value' => ArrayHelper::getColumn($aCommercialTenantTypeRelation, 'type_id')];
		}
		$aKey = ['name', 'profile', 'profile_path', 'address', 'description', 'contact_number', 'lng', 'lat'];
		foreach($aKey as $key){
			if(!isset($aCommercialTenantShop[$key])){
				$aCommercialTenantShop[$key]['value'] = $mCommercialTenant->$key;
				if($key == 'profile'){
					$aCommercialTenantShop[$key]['path'] = $mCommercialTenant->profile_path;
				}
			}
		}
		$mTenantLimit = $mCommercialTenant->getMTenantLimit();
		$aModifyLimitCount = Yii::$app->params['tenant_shop_modify_limit_count'];
		$aRemainModifyCount = $mTenantLimit->getRemainModifyLimitCount();
		
		$adcode = Yii::$app->tencentMap->getAdcodeByLocation($aCommercialTenantShop['lng']['value'], $aCommercialTenantShop['lat']['value']);
		return $this->render('shop_info', [
			'adcode' => $adcode,
			'aCommercialTenantShop' => $aCommercialTenantShop,
			'aModifyLimitCount' => $aModifyLimitCount,
			'aRemainModifyCount' => $aRemainModifyCount,
			'aCommercialTenantTypeList' => CommercialTenantType::findAll(),
		]);
	}
	
	public function actionSaveShopInfo(){
		$name = (string)Yii::$app->request->post('name');
		$description = (string)Yii::$app->request->post('description');
		$contactNumber = (string)Yii::$app->request->post('contact_number');
		$cityId = (int)Yii::$app->request->post('city_id');
		$lng = (string)Yii::$app->request->post('lng');
		$lat = (string)Yii::$app->request->post('lat');
		$address = (string)Yii::$app->request->post('address');
		$profile = (int)Yii::$app->request->post('profile');
		$isDraft = (int)Yii::$app->request->post('is_draft');
		$aCommercialTenantType = (array)Yii::$app->request->post('commercial_tenant_type');
				
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantLimit = $mCommercialTenant->getMTenantLimit();
		$aLimitNote = $mTenantLimit->note;
		$aModifyLimitCount = Yii::$app->params['tenant_shop_modify_limit_count'];
		
		$aTenantInfo = [];
		if($name){
			if(isset($aLimitNote['name']) && $aLimitNote['name'] >= $aModifyLimitCount['name']){
				return new Response('商铺名称每月只能修改' . $aModifyLimitCount['name'] . '次', -1);
			}
			if(isset($aLimitNote['name'])){
				$aLimitNote['name'] += 1;
			}else{
				$aLimitNote['name'] = 1;
			}
			$aTenantInfo['name'] = ['value' => $name];
		}
		if($aCommercialTenantType){
			$aTenantInfo['commercial_tenant_type'] = ['value' => $aCommercialTenantType];
		}
		if($description){
			$len = StringHelper::getStringLength($description);
			if($len < 1 || $len > 100){
				return new Response('描述1~100个字', -1);
			}
			if(isset($aLimitNote['description']) && $aLimitNote['description'] >= $aModifyLimitCount['description']){
				return new Response('商铺描述每月只能修改' . $aModifyLimitCount['description'] . '次', -1);
			}
			if(isset($aLimitNote['description'])){
				$aLimitNote['description'] += 1;
			}else{
				$aLimitNote['description'] = 1;
			}
			$aTenantInfo['description'] = ['value' => $description];
		}
		if($contactNumber){
			if(isset($aLimitNote['contact_number']) && $aLimitNote['contact_number'] >= $aModifyLimitCount['contact_number']){
				return new Response('商铺电话每月只能修改' . $aModifyLimitCount['contact_number'] . '次', -1);
			}
			if(isset($aLimitNote['contact_number'])){
				$aLimitNote['contact_number'] += 1;
			}else{
				$aLimitNote['contact_number'] = 1;
			}
			$aTenantInfo['contact_number'] = ['value' => $contactNumber];
		}
		if($cityId){
			$aTenantInfo['city_id'] = ['value' => $cityId];
		}
		if($lng){
			$aTenantInfo['lng'] = ['value' => $lng];
		}
		if($lat){
			$aTenantInfo['lat'] = ['value' => $lat];
		}
		if($address){
			if(isset($aLimitNote['address']) && $aLimitNote['address'] >= $aModifyLimitCount['address']){
				return new Response('商铺地址每月只能修改' . $aModifyLimitCount['address'] . '次', -1);
			}
			if(isset($aLimitNote['address'])){
				$aLimitNote['address'] += 1;
			}else{
				$aLimitNote['address'] = 1;
			}
			$aTenantInfo['address'] = ['value' => $address];
		}
		if($profile && Resource::findOne($profile)){
			if(isset($aLimitNote['profile']) && $aLimitNote['profile'] >= $aModifyLimitCount['profile']){
				return new Response('商铺头像每月只能修改' . $aModifyLimitCount['profile'] . '次', -1);
			}
			if(isset($aLimitNote['profile'])){
				$aLimitNote['profile'] += 1;
			}else{
				$aLimitNote['profile'] = 1;
			}
			$aTenantInfo['profile'] = ['value' => $profile];
		}
		if(!$aTenantInfo){
			return new Response('没有任何修改', -1);
		}
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aData = $mTenantApprove->shop_info;
		$aData = array_merge($aData, $aTenantInfo);
		$mTenantApprove->set('shop_info', $aData);
		$mTenantApprove->set('shop_approve_status', CommercialTenantApprove::STATUS_WAIT_APPROVE);
		$mTenantApprove->set('last_edit_time', NOW_TIME);
		$mTenantApprove->save();
		
		if(!$isDraft){
			$mCommercialTenant->set('data_perfect', CommercialTenant::DATA_PERFECT_FINISH);
			$mCommercialTenant->save();
		}
		$mTenantLimit->set('note', $aLimitNote);
		$mTenantLimit->save();
		
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
}
