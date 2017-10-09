<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\PhoneValidator;
use yii\validators\EmailValidator;
use umeworld\lib\Response;
use umeworld\lib\StringHelper;
use common\model\form\ImageUploadForm;
use yii\web\UploadedFile;
use common\model\Resource;
use common\model\CommercialTenant;
use common\model\Redis;
use common\filter\TenantAccessControl as Access;
use common\model\CommercialTenantApprove;

class TenantController extends Controller{
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
	
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	public function actionShowFillApprove(){
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aCommercialTenantAuthInfo = $mTenantApprove->getTenantInfoWithPath();
		$aKey = ['leading_official', 'identity_card', 'mobile', 'email', 'bank_account_holder', 'bank_name', 'bank_accout', 'bank_accout_type'];
		foreach($aKey as $key){
			if(!isset($aCommercialTenantAuthInfo[$key])){
				$aCommercialTenantAuthInfo[$key]['value'] = $mCommercialTenant->$key;
			}
		}
		$this->layout = 'approve';
		return $this->render('fill_approve', ['aCommercialTenantAuthInfo' => $aCommercialTenantAuthInfo]);
	}
	
	public function actionSaveFillApprove(){
		$leadingOffical = (string)Yii::$app->request->post('leading_official');
		$identityCard = (string)Yii::$app->request->post('identity_card');
		$email = (string)Yii::$app->request->post('email');
		$bankAccountHolder = (string)Yii::$app->request->post('bank_account_holder');
		$bankName = (string)Yii::$app->request->post('bank_name');
		$bankAccount = (string)Yii::$app->request->post('bank_accout');
		$bankAccoutType = (int)Yii::$app->request->post('bank_accout_type');
		$identityCardFront = (int)Yii::$app->request->post('identity_card_front');
		$identityCardBack = (int)Yii::$app->request->post('identity_card_back');
		$identityCardInHand = (int)Yii::$app->request->post('identity_card_in_hand');
		$bankCardPhoto = (int)Yii::$app->request->post('bank_card_photo');
		$aOtherInfo = (array)Yii::$app->request->post('other_info');
		$isDraft = (int)Yii::$app->request->post('is_draft');
	
		if(!$isDraft && !$leadingOffical){
			return new Response('请输入负责人姓名', -1);
		}
		if(!$isDraft && !$identityCard){
			return new Response('请输入身份证号码', -1);
		}
		$isEmail = (new EmailValidator())->validate($email);
		if(!$isDraft && !$isEmail){
			return new Response('邮箱不正确', -1);
		}
		if(!$isDraft && !$bankAccount){
			return new Response('请输入银行账号', -1);
		}
		if(!$isDraft && !$bankAccountHolder){
			return new Response('请输入开户人', -1);
		}
		if(!$isDraft && !$bankName){
			return new Response('请输入开户银行', -1);
		}
		if(!$isDraft && !Resource::findOne($identityCardFront)){
			return new Response('请上传身份证正面', -1);
		}
		if(!$isDraft && !Resource::findOne($identityCardBack)){
			return new Response('请上传身份证反面', -1);
		}
		if(!$isDraft && !Resource::findOne($identityCardInHand)){
			return new Response('请上传负责人手持身份证照片', -1);
		}
		if(!$isDraft && !Resource::findOne($bankCardPhoto)){
			return new Response('请上传银行卡正面照片', -1);
		}
		
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aOtherInfoList = [];
		if($aOtherInfo){
			foreach($aOtherInfo as $rid){
				if($rid){
					array_push($aOtherInfoList, ['value' => $rid]);
				}
			}
		}
		$aTenantInfo = [
			'leading_official' => ['value' => $leadingOffical],
			'identity_card' => ['value' => $identityCard],
			'email' => ['value' => $email],
			'bank_account_holder' => ['value' => $bankAccountHolder],
			'bank_name' => ['value' => $bankName],
			'bank_accout' => ['value' => $bankAccount],
			'bank_accout_type' => ['value' => $bankAccoutType],
			'identity_card_front' => ['value' => $identityCardFront],
			'identity_card_back' => ['value' => $identityCardBack],
			'identity_card_in_hand' => ['value' => $identityCardInHand],
			'bank_card_photo' => ['value' => $bankCardPhoto],
			'other_info' => $aOtherInfoList,
		];
		$aTenantInfo = array_merge($mTenantApprove->tenant_info, $aTenantInfo);
		$mTenantApprove->set('tenant_info', $aTenantInfo);
		$mTenantApprove->set('last_edit_time', NOW_TIME);
		$mTenantApprove->save();
		if($isDraft){
			$mCommercialTenant->set('data_perfect', CommercialTenant::DATA_PERFECT_TENANT_INFO);
		}else{
			$mCommercialTenant->set('data_perfect', CommercialTenant::DATA_PERFECT_SHOP_INFO);
		}
		$mCommercialTenant->save();
		
		return new Response('保存成功', 1);
	}
	
	public function actionUploadPhoto(){
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
				'type' => Resource::TYPE_BANK_PHOTO,
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
	
	public function actionShowApproveStatus(){
		$this->layout = 'approve';
		return $this->render('approve_status');
	}
	
	public function actionResetPassword(){
		$password = (string)Yii::$app->request->post('password');
		$enPassword = (string)Yii::$app->request->post('enPassword');
		
		if(!$password){
			return new Response('密码不能为空', -1);
		}
		if($password != $enPassword){
			return new Response('输入两次密码不一致', -1);
		}
		if(strlen($password) > 16 || strlen($password) < 6){
			return new Response('密码长度不正确', -1);
		}
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mCommercialTenant->set('password', CommercialTenant::encryptPassword($password));
		$mCommercialTenant->save();
		
		return new Response('重置密码成功', 1);
	}
	
	public function actionShowApproveInfo(){
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aCommercialTenantAuthInfo = $mTenantApprove->getTenantInfoWithPath();
		$aKey = ['leading_official', 'identity_card', 'mobile', 'email', 'bank_account_holder', 'bank_name', 'bank_accout', 'bank_accout_type'];
		foreach($aKey as $key){
			if(!isset($aCommercialTenantAuthInfo[$key])){
				$aCommercialTenantAuthInfo[$key]['value'] = $mCommercialTenant->$key;
			}
		}
		$mTenantLimit = $mCommercialTenant->getMTenantLimit();
		$aModifyLimitCount = Yii::$app->params['tenant_shop_modify_limit_count'];
		$aRemainModifyCount = $mTenantLimit->getRemainModifyLimitCount();
		
		return $this->render('approve_info', [
			'aCommercialTenantAuthInfo' => $aCommercialTenantAuthInfo,
			'aModifyLimitCount' => $aModifyLimitCount,
			'aRemainModifyCount' => $aRemainModifyCount,
		]);
	}
	
	public function actionSaveApproveInfo(){
		$leadingOffical = (string)Yii::$app->request->post('leading_official');
		$identityCard = (string)Yii::$app->request->post('identity_card');
		$email = (string)Yii::$app->request->post('email');
		$bankAccountHolder = (string)Yii::$app->request->post('bank_account_holder');
		$bankName = (string)Yii::$app->request->post('bank_name');
		$bankAccount = (string)Yii::$app->request->post('bank_accout');
		$bankAccoutType = (int)Yii::$app->request->post('bank_accout_type');
		$bankCardPhoto = (int)Yii::$app->request->post('bank_card_photo');
		$oldPassword = (string)Yii::$app->request->post('oldPassword');
		$password = (string)Yii::$app->request->post('password');
		$enpassword = (string)Yii::$app->request->post('enpassword');
	
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantLimit = $mCommercialTenant->getMTenantLimit();
		$aLimitNote = $mTenantLimit->note;
		$aModifyLimitCount = Yii::$app->params['tenant_shop_modify_limit_count'];
		
		$aTenantInfo = [];
		if($leadingOffical){
			if(isset($aLimitNote['leading_official']) && $aLimitNote['leading_official'] >= $aModifyLimitCount['leading_official']){
				return new Response('负责人每月只能修改' . $aModifyLimitCount['leading_official'] . '次', -1);
			}
			if(isset($aLimitNote['leading_official'])){
				$aLimitNote['leading_official'] += 1;
			}else{
				$aLimitNote['leading_official'] = 1;
			}
			$aTenantInfo['leading_official'] = ['value' => $leadingOffical];
		}
		if($identityCard){
			if(isset($aLimitNote['identity_card']) && $aLimitNote['identity_card'] >= $aModifyLimitCount['identity_card']){
				return new Response('身份证每月只能修改' . $aModifyLimitCount['identity_card'] . '次', -1);
			}
			if(isset($aLimitNote['identity_card'])){
				$aLimitNote['identity_card'] += 1;
			}else{
				$aLimitNote['identity_card'] = 1;
			}
			$aTenantInfo['identity_card'] = ['value' => $identityCard];
		}
		if($email){
			$isEmail = (new EmailValidator())->validate($email);
			if(!$isEmail){
				return new Response('邮箱不正确', -1);
			}
			if(isset($aLimitNote['email']) && $aLimitNote['email'] >= $aModifyLimitCount['email']){
				return new Response('邮箱每月只能修改' . $aModifyLimitCount['email'] . '次', -1);
			}
			if(isset($aLimitNote['email'])){
				$aLimitNote['email'] += 1;
			}else{
				$aLimitNote['email'] = 1;
			}
			$aTenantInfo['email'] = ['value' => $email];
		}
		if($bankAccount){
			if(isset($aLimitNote['bank_accout']) && $aLimitNote['bank_accout'] >= $aModifyLimitCount['bank_accout']){
				return new Response('银行帐号每月只能修改' . $aModifyLimitCount['bank_accout'] . '次', -1);
			}
			if(isset($aLimitNote['bank_accout'])){
				$aLimitNote['bank_accout'] += 1;
			}else{
				$aLimitNote['bank_accout'] = 1;
			}
			$aTenantInfo['bank_accout'] = ['value' => $bankAccount];
			$aTenantInfo['bank_accout_type'] = ['value' => $bankAccoutType];
		}
		if($bankAccountHolder){
			if(isset($aLimitNote['bank_account_holder']) && $aLimitNote['bank_account_holder'] >= $aModifyLimitCount['bank_account_holder']){
				return new Response('开户人每月只能修改' . $aModifyLimitCount['bank_account_holder'] . '次', -1);
			}
			if(isset($aLimitNote['bank_account_holder'])){
				$aLimitNote['bank_account_holder'] += 1;
			}else{
				$aLimitNote['bank_account_holder'] = 1;
			}
			$aTenantInfo['bank_account_holder'] = ['value' => $bankAccountHolder];
		}
		if($bankName){
			if(isset($aLimitNote['bank_name']) && $aLimitNote['bank_name'] >= $aModifyLimitCount['bank_name']){
				return new Response('开户银行每月只能修改' . $aModifyLimitCount['bank_name'] . '次', -1);
			}
			if(isset($aLimitNote['bank_name'])){
				$aLimitNote['bank_name'] += 1;
			}else{
				$aLimitNote['bank_name'] = 1;
			}
			$aTenantInfo['bank_name'] = ['value' => $bankName];
		}
		if($bankCardPhoto && Resource::findOne($bankCardPhoto)){
			$aTenantInfo['bank_card_photo'] = ['value' => $bankCardPhoto];
		}
		if($password){
			if($mCommercialTenant->password != CommercialTenant::encryptPassword($oldPassword)){
				return new Response('原密码错误', 0);
			}
			if($password != $enpassword){
				return new Response('两次输入密码不一致', -1);
			}
			$mCommercialTenant->set('password', CommercialTenant::encryptPassword($password));
			$mCommercialTenant->save();
			return new Response('保存成功', 1);
		}
		
		if(!$aTenantInfo){
			return new Response('没有任何修改', -1);
		}
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aData = $mTenantApprove->tenant_info;
		$aData = array_merge($aData, $aTenantInfo);
		$mTenantApprove->set('tenant_info', $aData);
		$mTenantApprove->set('tenant_approve_status', CommercialTenantApprove::STATUS_WAIT_APPROVE);
		$mTenantApprove->set('last_edit_time', NOW_TIME);
		$mTenantApprove->save();
		$mTenantLimit->set('note', $aLimitNote);
		$mTenantLimit->save();
		return new Response('保存成功', 1);
	}
	
	public function actionSendMobileVerifyCode(){
		$mobile = (string)Yii::$app->request->post('mobile');
		
		$isMobile = (new PhoneValidator())->validate($mobile);
		if(!$isMobile){
			return new Response('手机格式不正确', 0);
		}
		
		$id = 'mobile_' . $mobile;
		$mRedis = Redis::findOne(['id' => $id]);
		if($mRedis && $mRedis->expiration_time - NOW_TIME > 840){
			return new Response('请稍后再试', -1);
		}
		$code = mt_rand(100000, 999999);

		//向手机发送短信
		$oSms = Yii::$app->sms;
		$oSms->sendTo = $mobile;
		$oSms->content = '您好，您在优满堂绑定手机，您的验证码是 ' . $code . '此码在十五分钟内有效，请在十五分钟内完成操作。';
		if($oSms->send()){
			if(!$mRedis){
				Redis::add([
					'id' => $id,
					'value' => $code,
					'expiration_time' => NOW_TIME + 900,
				]);
			}else{
				$mRedis->set('value', $code);
				$mRedis->set('expiration_time', NOW_TIME + 900);
				$mRedis->save();
			}
			return new Response('发送验证码成功，请留意手机短信', 1);
		}
		return new Response('发送验证码失败', 0);
	}
	
	public function actionBindMobile(){
		$mobile = (string)Yii::$app->request->post('mobile');
		$verifyCode = (string)Yii::$app->request->post('verifyCode');
		
		$id = 'mobile_' . $mobile;
		$mRedis = Redis::findOne(['id' => $id]);
		if(!$mRedis){
			return new Response('验证失败', 0);
		}
		if($mRedis->expiration_time < NOW_TIME){
			return new Response('验证码过期', -1);
		}
		if($mRedis->value != $verifyCode){
			return new Response('验证码不正确', -1);
		}
		
		$mCommercialTenant = CommercialTenant::findOne(['mobile' => $mobile]);
		if($mCommercialTenant){
			return new Response('账号已被其它用户绑定了', 0);
		}
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mTenantLimit = $mCommercialTenant->getMTenantLimit();
		$aLimitNote = $mTenantLimit->note;
		$aModifyLimitCount = Yii::$app->params['tenant_shop_modify_limit_count'];
		if(isset($aLimitNote['mobile']) && $aLimitNote['mobile'] >= $aModifyLimitCount['mobile']){
			return new Response('手机每月只能修改' . $aModifyLimitCount['mobile'] . '次', -1);
		}
		if(isset($aLimitNote['mobile'])){
			$aLimitNote['mobile'] += 1;
		}else{
			$aLimitNote['mobile'] = 1;
		}
		$mCommercialTenant->set('mobile', $mobile);
		$mCommercialTenant->save();
		$mTenantLimit->set('note', $aLimitNote);
		$mTenantLimit->save();
		
		return new Response('绑定手机成功', 1);
	}
	
	public function actionShowDiscountInfo(){
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$aDiscountInfo = $mCommercialTenant->toArray(['pay_discount', 'preferential_info']);
		return $this->render('discount', ['aDiscountInfo' => $aDiscountInfo]);
	}
	
	public function actionSaveDiscountInfo(){
		$payDiscount = (int)Yii::$app->request->post('pay_discount');
		$preferentialInfo = (string)Yii::$app->request->post('preferential_info');
		
		if($payDiscount || $preferentialInfo){
			if($payDiscount <= 0){
				return new Response('请输入买单折扣', 0);
			}
			if(!$preferentialInfo){
				return new Response('请输入优惠信息', 0);
			}
			if(StringHelper::getStringLength($preferentialInfo) > 25){
				return new Response('优惠信息最多不能超过25个字', 0);
			}
		}
		$mCommercialTenant = Yii::$app->commercialTenant->getIdentity();
		$mCommercialTenant->set('pay_discount', $payDiscount);
		$mCommercialTenant->set('preferential_info', $preferentialInfo);
		$mCommercialTenant->save();
		
		return new Response('保存成功', 1);
	}
	
}
