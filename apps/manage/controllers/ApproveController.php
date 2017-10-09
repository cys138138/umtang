<?php
namespace manage\controllers;

use Yii;
use manage\lib\Controller;
use common\model\CommercialTenant;
use common\model\CharacteristicServiceType;
use common\model\CommercialTenantCharacteristicServiceRelation;
use common\model\CommercialTenantPhoto;
use common\model\CommercialTenantApprove;
use common\model\CommercialTenantNotice;
use common\model\CommercialTenantTypeRelation;
use common\model\City;
use common\model\Teacher;
use yii\data\Pagination;
use umeworld\lib\Response;
use manage\model\form\FirstApproveListForm;
use manage\model\form\TenantApproveListForm;
use manage\model\form\TenantShopApproveListForm;
use yii\helpers\ArrayHelper;
use umeworld\lib\PhoneValidator;

class ApproveController extends Controller{
	public function actions() {
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
						'roles' => ['@'],
					],
				],
			],
		], parent::behaviors());
	}
	/*
	public function actionGetFirstApproveList(){
		$page = (int)Yii::$app->request->get('page', 1);
		$pageSize = 2;
		$aCondition = [
			'online_status' => \manage\model\CommercialTenant::ONLINE_STATUS_IN_APPROVE,
		];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
		];
		$aTenantList = \manage\model\CommercialTenant::getCommercialTenantList($aCondition, $aControl);
		$matchCount = \manage\model\CommercialTenant::getCommercialTenantCount($aCondition);
		$oPage = new Pagination(['totalCount' => $matchCount, 'pageSize' => $pageSize]);
		//debug($aTenantList, 11);
		return $this->render('first_approve', [
			'aTenantList' => $aTenantList,
			'oPage' => $oPage
		]);
	}

	public function actionCommercialTenantApprove() {
		$page = (int)Yii::$app->request->get('page', 1);
		$pageSize = 5;
		$aCondition = [
			'online_status' => \manage\model\CommercialTenant::ONLINE_STATUS_ONLINE,
			'tenant_approve_status' => [
				\manage\model\CommercialTenantApprove::STATUS_WAIT_APPROVE,
				\manage\model\CommercialTenantApprove::STATUS_IN_APPROVE
			]
		];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
		];
		$aTenantApproveList = \manage\model\CommercialTenant::getCommercialTenantList($aCondition, $aControl);
		$matchCount = \manage\model\CommercialTenant::getCommercialTenantCount($aCondition);
		$oPage = new Pagination(['totalCount' => $matchCount, 'pageSize' => $pageSize]);
		//debug($aTenantApproveList, 11);
		return $this->render('commercial_tenant_approve', [
			'aTenantApproveList' => $aTenantApproveList,
			'oPage' => $oPage
		]);
	}
	
	public function actionCommercialTenantShopApprove() {
		$page = (int)Yii::$app->request->get('page', 1);
		$pageSize = 5;
		$aCondition = [
			'online_status' => \manage\model\CommercialTenant::ONLINE_STATUS_ONLINE,
			'shop_approve_status' => [
				\manage\model\CommercialTenantApprove::STATUS_WAIT_APPROVE,
				\manage\model\CommercialTenantApprove::STATUS_IN_APPROVE
			]
		];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
			'with_type_characteristic_service' => true,
		];
		$aTenantShopApproveList = \manage\model\CommercialTenant::getCommercialTenantList($aCondition, $aControl);
		debug($aTenantShopApproveList, 11);
		$matchCount = \manage\model\CommercialTenant::getCommercialTenantCount($aCondition);
		$oPage = new Pagination(['totalCount' => $matchCount, 'pageSize' => $pageSize]);
		return $this->render('commercial_tenant_shop_approve', [
			'aTenantShopApproveList' => $aTenantShopApproveList,
			'oPage' => $oPage
		]);
	}
	
	public function actionGoodsApprove() {
		debug(json_encode([
	'leading_official' => ['value' => '测试负责人222'],
	'identity_card' => ['value' => '2222222'],
	'email' => ['value' => '1233211234567@um.com'],
	'identity_card_front' => ['value' => 2],
	'identity_card_back' => ['value' => 3],
	'identity_card_in_hand' => ['value' => 4],
	'bank_accout' => ['value' => '1233211234567'],
	'bank_account_holder' => ['value' => '测试开户人222'],
	'bank_name' => ['value' => '测试银行111'],
	'bank_card_photo' => ['value' => 4],
	'other_info' => [
						['value' => 2],
						['value' => 4],
					],
 ]),11);
		debug('编写中', 11);
		return $this->render('index', [
			'test' => '商品审核'
		]);
	}
	//初审
	public function actionFirstApproveNotPass(){
		$aPost = (array) Yii::$app->request->post();
		if(!isset($aPost['id']) || $aPost['id'] <= 0){
			return new Response('缺少必要参数');
		}
		$aPostBak = $aPost;
		unset($aPostBak['_csrf'], $aPostBak['id']);
		$hasFillReason = false;
		foreach($aPostBak as $field => $value){
			if($field == '_csrf'){
				continue;
			}elseif(is_array($value)){
				foreach($value as $key => $value2){
					if($value2){
						$hasFillReason = true;
					}
				}
			}elseif($value){
				$hasFillReason = true;
			}
		}
		if(!$hasFillReason){
			return new Response('必须填写原因!', 0, $aPost['id']);
		}
		$mCommercialTenant = \manage\model\CommercialTenant::findOne(['id' => $aPost['id'], 'online_status' => \manage\model\CommercialTenant::ONLINE_STATUS_IN_APPROVE]);
		if(!$mCommercialTenant){
			return new Response('商户不存在');
		}
		$mCommercialTenantApprove = $mCommercialTenant->getMTenantApprove();
		//填写商户审核原因
		$mCommercialTenantApprove->fillTenantNotPassReason($aPost);
		//填写商铺审核原因
		$mCommercialTenantApprove->fillShopNotPassReaSon($aPost);
		$mCommercialTenant->set('online_status', \manage\model\CommercialTenant::ONLINE_STATUS_PERFECT_INFOR);
		if(!$mCommercialTenant->save() || !$mCommercialTenantApprove->save()){
			return new Response('提交失败', 0, $aPost['id']);
		}
		return new Response('提交成功', 1, $aPost['id']);
	}
	
	public function actionFirstApprovePass(){
		$tenantId = (array) Yii::$app->request->post('id', 0);
		if($tenantId <= 0){
			return new Response('缺少必要参数');
		}
		$mTenant = \manage\model\CommercialTenant::findOne(['id' => $tenantId, 'online_status' => \manage\model\CommercialTenant::ONLINE_STATUS_IN_APPROVE]);
		if(!$mTenant){
			return new Response('商户不存在');
		}
		if(!$mTenant->updateTenantInfo() || !$mTenant->updateShopInfo()){
			return new Response('操作失败！');
		}
		$mTenant->set('online_status', \manage\model\CommercialTenant::ONLINE_STATUS_ONLINE);
		$mTenant->save();
		return new Response('操作成功', 1, $tenantId);
	}
	//商户审核
	public function actionTenantApproveNotPass(){
		$aPost = (array) Yii::$app->request->post();
		if(!isset($aPost['id']) || $aPost['id'] <= 0){
			return new Response('缺少必要参数');
		}
		if(!isset($aPost['reason']) || !$aPost['reason']){
			return new Response('必须填写原因!', 0, $aPost['id']);
		}
		$mCommercialTenant = \manage\model\CommercialTenant::findOne(['id' => $aPost['id'], 'online_status' => \manage\model\CommercialTenant::ONLINE_STATUS_ONLINE]);
		if(!$mCommercialTenant){
			return new Response('商户不存在');
		}
		//发私信给商户
		$aData = [
			'tenant_id' => $aPost['id'],
			'title' => '商户审核不通过',
			'content' => $aPost['reason'],
			'is_read' => 0,
			'create_time' => NOW_TIME
		];
		$mCommercialTenantApprove = $mCommercialTenant->getMTenantApprove();
		$mCommercialTenantApprove->set('tenant_info', []);
		$mCommercialTenantApprove->set('tenant_approve_status', \manage\model\CommercialTenantApprove::STATUS_ONT_PASS_APPROVE);
		if(\common\model\CommercialTenantNotice::add($aData) && !$mCommercialTenantApprove->save()){
			return new Response('操作失败', 0, $aPost['id']);
		}
		return new Response('操作成功', 1, $aPost['id']);
	}
	
	public function actionTenantApprovePass(){
		$tenantId = (array) Yii::$app->request->post('id', 0);
		if($tenantId <= 0){
			return new Response('缺少必要参数');
		}
		$mTenant = \manage\model\CommercialTenant::findOne(['id' => $tenantId, 'online_status' => \manage\model\CommercialTenant::ONLINE_STATUS_ONLINE]);
		if(!$mTenant){
			return new Response('商户不存在');
		}
		$mTenantApprove = $mTenant->getMTenantApprove();
		if($mTenantApprove->tenant_approve_status != CommercialTenantApprove::STATUS_IN_APPROVE){
			return new Response('商户不在审核中');
		}
		if(!$mTenant->updateTenantInfo()){
			return new Response('操作失败！');
		}
		return new Response('操作成功', 1, $tenantId);
	}
	//商铺审核
	public function actionShopApproveNotPass(){
		$aPost = (array) Yii::$app->request->post();
		if(!isset($aPost['id']) || $aPost['id'] <= 0){
			return new Response('缺少必要参数');
		}
		if(!isset($aPost['reason']) || !$aPost['reason']){
			return new Response('必须填写原因!', 0, $aPost['id']);
		}
		$mCommercialTenant = \manage\model\CommercialTenant::findOne(['id' => $aPost['id'], 'online_status' => \manage\model\CommercialTenant::ONLINE_STATUS_ONLINE]);
		if(!$mCommercialTenant){
			return new Response('商铺不存在');
		}
		//发私信给商户
		$aData = [
			'tenant_id' => $aPost['id'],
			'title' => '商铺审核不通过',
			'content' => $aPost['reason'],
			'is_read' => 0,
			'create_time' => NOW_TIME
		];
		$mCommercialTenantApprove = $mCommercialTenant->getMTenantApprove();
		$mCommercialTenantApprove->set('shop_info', []);
		$mCommercialTenantApprove->set('shop_approve_status', \manage\model\CommercialTenantApprove::STATUS_ONT_PASS_APPROVE);
		if(\common\model\CommercialTenantNotice::add($aData) && !$mCommercialTenantApprove->save()){
			return new Response('操作失败', 0, $aPost['id']);
		}
		return new Response('操作成功', 1, $aPost['id']);
	}
	
	public function actionShopApprovePass(){
		$tenantId = (array) Yii::$app->request->post('id', 0);
		if($tenantId <= 0){
			return new Response('缺少必要参数');
		}
		$mTenant = \manage\model\CommercialTenant::findOne(['id' => $tenantId, 'online_status' => \manage\model\CommercialTenant::ONLINE_STATUS_ONLINE]);
		if(!$mTenant){
			return new Response('商户不存在');
		}
		$mTenantApprove = $mTenant->getMTenantApprove();
		if($mTenantApprove->shop_approve_status != CommercialTenantApprove::STATUS_IN_APPROVE){
			return new Response('商铺不在审核中');
		}
		if(!$mTenant->updateShopInfo()){
			return new Response('操作失败！');
		}
		return new Response('操作成功', 1, $tenantId);
	}
	
	public function actionChangeApproveStatus(){
		$approveInfo = (array) Yii::$app->request->post();
		if(!isset($approveInfo['id']) || !$approveInfo['id']){
			return new Response('缺少必要参数');
		}
		$mTenant = \manage\model\CommercialTenant::findOne(['id' => $approveInfo['id'], 'online_status' => \manage\model\CommercialTenant::ONLINE_STATUS_ONLINE]);
		$mApprove = $mTenant->getMTenantApprove();
		$mApprove->changeApproveStatus($approveInfo);
		if($mApprove->save()){
			return new Response('修改成功', 1);
		}
		return new Response('修改失败', 0);
	}
	*/
	
	//////////////////////////////////////////////////////////////////////Jay codeing
	
	public $aApproveKeyNameList = [
		'leading_official' => '负责人',
		'identity_card' => '身份证',
		'identity_card_front' => '身份证正面',
		'identity_card_back' => '身份证反面',
		'identity_card_in_hand' => '手持身份证',
		'email' => '邮箱',
		'bank_accout' => '银行帐号',
		'bank_account_holder' => '开户人',
		'bank_name' => '开户银行',
		'bank_accout_type' => '结算类型',
		'bank_card_photo' => '银行卡照片',
		'other_info' => '其他资料',
		'name' => '商铺名称',
		'commercial_tenant_type' => '商铺类型',
		'profile' => '商铺头像',
		'address' => '商铺地址',
		'contact_number' => '商铺电话',
		'description' => '商铺描述',
		'photo' => '商铺相册',
		'commercial_tenant_characteristic_service_relation' => '特色服务',
		'teacher' => '教师',
	];

	public function actionShowFirstApproveList(){
		$oListForm = new FirstApproveListForm();
		$aParams = Yii::$app->request->get();
		if($aParams && (!$oListForm->load($aParams, '') || !$oListForm->validate())){
			return new Response(current($oListForm->getErrors())[0]);
		}
		$aList = $oListForm->getList();
		$oPage = $oListForm->getPageObject();
		
		return $this->render('first_approve_list', [
			'aList' => $aList,
			'oPage' => $oPage,
		]);
	}
	
	public function actionShowTenantApproveList(){
		$oListForm = new TenantApproveListForm();
		$aParams = Yii::$app->request->get();
		if($aParams && (!$oListForm->load($aParams, '') || !$oListForm->validate())){
			return new Response(current($oListForm->getErrors())[0]);
		}
		$aList = $oListForm->getList();
		$oPage = $oListForm->getPageObject();
		
		return $this->render('tenant_approve_list', [
			'aList' => $aList,
			'oPage' => $oPage,
		]);
	}
	
	public function actionShowTenantShopApproveList(){
		$oListForm = new TenantShopApproveListForm();
		$aParams = Yii::$app->request->get();
		if($aParams && (!$oListForm->load($aParams, '') || !$oListForm->validate())){
			return new Response(current($oListForm->getErrors())[0]);
		}
		$aList = $oListForm->getList();
		$oPage = $oListForm->getPageObject();
		
		return $this->render('tenant_shop_approve_list', [
			'aList' => $aList,
			'oPage' => $oPage,
		]);
	}
	
	public function actionShowApproveDetail(){
		$id = (int)Yii::$app->request->get('id');
		
		if(!$id){
			return new Response('缺少id', 0);
		}
		$mCommercialTenant = CommercialTenant::findOne($id);
		if(!$mCommercialTenant){
			return new Response('找不到商户信息', 0);
		}
		if($mCommercialTenant->online_status != CommercialTenant::ONLINE_STATUS_IN_APPROVE){
			return new Response('商户状态不在上线审核中', 0);
		}
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aCommercialTenantShop = $mTenantApprove->getShopInfoWithPath();
		$aCommercialTenantInfo = $mTenantApprove->getTenantInfoWithPath();
		if(isset($aCommercialTenantShop['commercial_tenant_characteristic_service_relation'])){
			$aCharacterId = ArrayHelper::getColumn($aCommercialTenantShop['commercial_tenant_characteristic_service_relation'], 'value');
			if($aCharacterId){
				$aCharacterList = CharacteristicServiceType::findAll(['id' => $aCharacterId]);
				foreach($aCommercialTenantShop['commercial_tenant_characteristic_service_relation'] as $k => $v){
					foreach($aCharacterList as $vv){
						if($vv['id'] == $v['value']){
							$aCommercialTenantShop['commercial_tenant_characteristic_service_relation'][$k]['name'] = $vv['name'];
						}
					}
				}
			}
		}
		return $this->render('first_approve_detail', [
			'id' => $id,
			'aApproveKeyNameList' => $this->aApproveKeyNameList,
			'aCommercialTenantShop' => $aCommercialTenantShop,
			'aCommercialTenantInfo' => $aCommercialTenantInfo,
		]);
	}
	
	public function actionShowTenantApproveDetail(){
		$id = (int)Yii::$app->request->get('id');
		
		if(!$id){
			return new Response('缺少id', 0);
		}
		$mCommercialTenant = CommercialTenant::findOne($id);
		if(!$mCommercialTenant){
			return new Response('找不到商户信息', 0);
		}
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		if(!in_array($mTenantApprove->tenant_approve_status, [CommercialTenantApprove::STATUS_WAIT_APPROVE, CommercialTenantApprove::STATUS_IN_APPROVE])){
			return new Response('商户状态不在审核中', 0);
		}
		$aCommercialTenantInfo = $mTenantApprove->getTenantInfoWithPath();
		return $this->render('tenant_approve_detail', [
			'id' => $id,
			'mCommercialTenant' => $mCommercialTenant,
			'aApproveKeyNameList' => $this->aApproveKeyNameList,
			'aCommercialTenantInfo' => $aCommercialTenantInfo,
		]);
	}
	
	public function actionShowTenantShopApproveDetail(){
		$id = (int)Yii::$app->request->get('id');
		
		if(!$id){
			return new Response('缺少id', 0);
		}
		$mCommercialTenant = CommercialTenant::findOne($id);
		if(!$mCommercialTenant){
			return new Response('找不到商户信息', 0);
		}
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		if(!in_array($mTenantApprove->shop_approve_status, [CommercialTenantApprove::STATUS_WAIT_APPROVE, CommercialTenantApprove::STATUS_IN_APPROVE])){
			return new Response('商铺状态不在审核中', 0);
		}
		$aCommercialTenantShop = $mTenantApprove->getShopInfoWithPath();
		if(isset($aCommercialTenantShop['commercial_tenant_characteristic_service_relation'])){
			$aCharacterId = ArrayHelper::getColumn($aCommercialTenantShop['commercial_tenant_characteristic_service_relation'], 'value');
			if($aCharacterId){
				$aCharacterList = CharacteristicServiceType::findAll(['id' => $aCharacterId]);
				foreach($aCommercialTenantShop['commercial_tenant_characteristic_service_relation'] as $k => $v){
					foreach($aCharacterList as $vv){
						if($vv['id'] == $v['value']){
							$aCommercialTenantShop['commercial_tenant_characteristic_service_relation'][$k]['name'] = $vv['name'];
						}
					}
					if(!isset($aCommercialTenantShop['commercial_tenant_characteristic_service_relation'][$k]['name'])){
						unset($aCommercialTenantShop['commercial_tenant_characteristic_service_relation'][$k]);
					}
				}
			}
		}elseif(isset ($aCommercialTenantShop['teacher'])){
			$aTeacherIds = ArrayHelper::getColumn($aCommercialTenantShop['teacher'], 'id');
			$aTeacherList = Teacher::getList(['id' => $aTeacherIds]);
			foreach($aCommercialTenantShop['teacher'] as $teacherKey => $aApproveTeacher ){
				foreach($aTeacherList as $aTeacher){
					if($aTeacher['id'] == $aApproveTeacher['id']){
						$aCommercialTenantShop['teacher'][$teacherKey] = array_merge($aTeacher, $aApproveTeacher);
					}
				}
			}
		}
		return $this->render('tenant_shop_approve_detail', [
			'id' => $id,
			'mCommercialTenant' => $mCommercialTenant,
			'aApproveKeyNameList' => $this->aApproveKeyNameList,
			'aCommercialTenantShop' => $aCommercialTenantShop,
		]);
	}
	
	public function actionDoFirstApprove(){
		$id = (int)Yii::$app->request->post('id');
		$aReason = (array)Yii::$app->request->post('aReason');
		
		if(!$id){
			return new Response('缺少id', 0);
		}
		$mCommercialTenant = CommercialTenant::findOne($id);
		if(!$mCommercialTenant){
			return new Response('找不到商户信息', 0);
		}
		if($mCommercialTenant->online_status != CommercialTenant::ONLINE_STATUS_IN_APPROVE){
			return new Response('商户状态不在上线审核中', 0);
		}
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$isSuccess = $this->_doApproveShopInfo($mCommercialTenant, $aReason, true);
		if(!$isSuccess){
			return new Response('审核失败', 0);
		}
		$isSuccess = $this->_doApproveTenantInfo($mCommercialTenant, $aReason, true);
		if(!$isSuccess){
			return new Response('审核失败', 0);
		}
		$content = '';
		if(!$aReason){
			$content = '您的优满堂商户审核已通过，请登录优满堂完善商品信息。';
			$mCommercialTenant->set('online_status', CommercialTenant::ONLINE_STATUS_ONLINE);
		}else{
			$content = '您的优满堂商户审核未通过，请重新登录并按提示修改未通过信息。';
			$mCommercialTenant->set('online_status', CommercialTenant::ONLINE_STATUS_PERFECT_INFOR);
		}
		$mCommercialTenant->save();
		if((new PhoneValidator())->validate($mCommercialTenant->mobile)){
			$oSms = Yii::$app->sms;
			$oSms->sendTo = $mCommercialTenant->mobile;
			$oSms->content = $content;
			$oSms->send();
		}
		
		return new Response('审核成功', 1);
	}
	
	public function actionDoTenantApprove(){
		$id = (int)Yii::$app->request->post('id');
		$aReason = (array)Yii::$app->request->post('aReason');
		
		if(!$id){
			return new Response('缺少id', 0);
		}
		$mCommercialTenant = CommercialTenant::findOne($id);
		if(!$mCommercialTenant){
			return new Response('找不到商户信息', 0);
		}
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		if(!in_array($mTenantApprove->tenant_approve_status, [CommercialTenantApprove::STATUS_WAIT_APPROVE, CommercialTenantApprove::STATUS_IN_APPROVE])){
			return new Response('商户状态审核中', 0);
		}
		$isSuccess = $this->_doApproveTenantInfo($mCommercialTenant, $aReason);
		if(!$isSuccess){
			return new Response('审核失败', 0);
		}
		
		return new Response('审核成功', 1);
	}
	
	public function actionDoTenantShopApprove(){
		$id = (int)Yii::$app->request->post('id');
		$aReason = (array)Yii::$app->request->post('aReason');
		
		if(!$id){
			return new Response('缺少id', 0);
		}
		$mCommercialTenant = CommercialTenant::findOne($id);
		if(!$mCommercialTenant){
			return new Response('找不到商户信息', 0);
		}
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		if(!in_array($mTenantApprove->shop_approve_status, [CommercialTenantApprove::STATUS_WAIT_APPROVE, CommercialTenantApprove::STATUS_IN_APPROVE])){
			return new Response('商铺状态审核中', 0);
		}
		$isSuccess = $this->_doApproveShopInfo($mCommercialTenant, $aReason);
		if(!$isSuccess){
			return new Response('审核失败', 0);
		}
		
		return new Response('审核成功', 1);
	}
	
	private function _doApproveTenantInfo($mCommercialTenant, $aReason, $isFirstApprove = false){
		$aInsertTenantNoticeList = [];
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aCommercialTenantInfo = $mTenantApprove->tenant_info;
		foreach($aCommercialTenantInfo as $key => $value){
			if(!isset($this->aApproveKeyNameList[$key])){
				continue;
			}
			//通知start
			if(!$isFirstApprove){
				$aTenantNotice = ['tenant_id' => $mCommercialTenant->id];
				if(isset($aReason[$key])){
					$aTenantNotice['title'] = '你的' . $this->aApproveKeyNameList[$key] . '更改未通过审核';
					$aTenantNotice['content'] = '你的' . $this->aApproveKeyNameList[$key] . '审核未通过，原因：' . $aReason[$key];
				}else{
					$aTenantNotice['title'] = '你的' . $this->aApproveKeyNameList[$key] . '更改已通过审核';
					$aTenantNotice['content'] = '你的' . $this->aApproveKeyNameList[$key] . '审核通过';
				}
				$aTenantNotice['is_read'] = 0;
				$aTenantNotice['create_time'] = NOW_TIME;
				if(!isset($aReason[$key])){
					$aTenantNotice['content'] = '';
				}
				array_push($aInsertTenantNoticeList, $aTenantNotice);
			}
			//通知end
			if(in_array($key, ['leading_official', 'email', 'identity_card', 'identity_card_front', 'identity_card_back', 'identity_card_in_hand', 'bank_accout', 'bank_account_holder', 'bank_name', 'bank_accout_type', 'bank_card_photo'])){
				if(isset($aReason[$key])){
					$aCommercialTenantInfo[$key]['reason'] = $aReason[$key];
				}else{
					$mCommercialTenant->set($key, $value['value']);
					if(!$isFirstApprove){
						unset($aCommercialTenantInfo[$key]);
					}
				}
			}elseif($key == 'other_info'){
				$aOtherInfo = [];
				foreach($value as $index => $v){
					$isPass = true;
					if(isset($aReason[$key])){
						foreach($aReason[$key] as $aValue){
							if($aValue['index'] == $index){
								$aCommercialTenantInfo[$key][$index]['reason'] = $aValue['reason'];
								$isPass = false;
								break;
							}
						}
					}
					if($isPass){
						array_push($aOtherInfo, $v['value']);
						if(!$isFirstApprove){
							unset($aCommercialTenantInfo[$key][$index]);
						}
					}
				}
				$mCommercialTenant->set($key, $aOtherInfo);
			}
		}
		
		if($aReason){
			$mTenantApprove->set('tenant_info', $aCommercialTenantInfo);
			$mTenantApprove->set('tenant_approve_status', CommercialTenantApprove::STATUS_ONT_PASS_APPROVE);
		}else{
			$mTenantApprove->set('tenant_info', []);
			$mTenantApprove->set('tenant_approve_status', CommercialTenantApprove::STATUS_PASS_APPROVE);
			$mCommercialTenant->save();
		}
		if(!$isFirstApprove){
			$mTenantApprove->set('tenant_info', []);
		}
		$mTenantApprove->save();
		
		//发通知
		if(!$isFirstApprove && $aInsertTenantNoticeList){
			CommercialTenantNotice::batchInsertData($aInsertTenantNoticeList);
		}
		
		return true;
	}
	
	private function _doApproveShopInfo($mCommercialTenant, $aReason, $isFirstApprove = false){
		$aInsertTenantNoticeList = [];
		$mTenantApprove = $mCommercialTenant->getMTenantApprove();
		$aCommercialTenantShop = $mTenantApprove->shop_info;
		foreach($aCommercialTenantShop as $key => $value){
			if(!isset($this->aApproveKeyNameList[$key])){
				continue;
			}
			//通知start
			if(!$isFirstApprove && !in_array($key, ['commercial_tenant_characteristic_service_relation', 'teacher', 'photo', 'commercial_tenant_type'])){
				$aTenantNotice = ['tenant_id' => $mCommercialTenant->id];
				if(isset($aReason[$key])){
					$aTenantNotice['title'] = '你的' . $this->aApproveKeyNameList[$key] . '更改未通过审核';
					$aTenantNotice['content'] = '你的' . $this->aApproveKeyNameList[$key] . '审核未通过，原因：' . $aReason[$key];
				}else{
					$aTenantNotice['title'] = '你的' . $this->aApproveKeyNameList[$key] . '更改已通过审核';
					$aTenantNotice['content'] = '你的' . $this->aApproveKeyNameList[$key] . '审核通过';
				}
				$aTenantNotice['is_read'] = 0;
				$aTenantNotice['create_time'] = NOW_TIME;
				if(!isset($aReason[$key])){
					$aTenantNotice['content'] = '';
				}
				array_push($aInsertTenantNoticeList, $aTenantNotice);
			}
			//通知end
			if(in_array($key, ['name', 'profile', 'address', 'contact_number', 'description'])){
				if(isset($aReason[$key])){
					$aCommercialTenantShop[$key]['reason'] = $aReason[$key];
				}else{
					$mCommercialTenant->set($key, $value['value']);
					if(!$isFirstApprove){
						unset($aCommercialTenantShop[$key]);
					}
					if((!$aReason || !$isFirstApprove) && $key == 'address'){
						$cityName = Yii::$app->tencentMap->getCityNameByLocation($aCommercialTenantShop['lng']['value'], $aCommercialTenantShop['lat']['value']);
						if(!$cityName){
							return false;
						}
						$mCity = City::findOne(['name' => $cityName]);
						$id = 0;
						if(!$mCity){
							$id = City::add([
								'name' => $cityName,
								'create_time' => NOW_TIME,
							]);
						}else{
							$id = $mCity->id;
						}
						$street = Yii::$app->tencentMap->getStreetNameByLocation($aCommercialTenantShop['lng']['value'], $aCommercialTenantShop['lat']['value']);
						if(!$street){
							return false;
						}
						$mCommercialTenant->set('city_id', $id);
						$mCommercialTenant->set('street', $street);
						$mCommercialTenant->set('lng', $aCommercialTenantShop['lng']['value']);
						$mCommercialTenant->set('lat', $aCommercialTenantShop['lat']['value']);
					}
				}
			}elseif($key == 'commercial_tenant_characteristic_service_relation'){
				$aInsertCharacterRelationList = [];
				foreach($value as $index => $v){
					$isPass = true;
					$aTenantNotice = ['tenant_id' => $mCommercialTenant->id];
					$mCharacteristicServiceType = CharacteristicServiceType::findOne($v['value']);
					if(!$mCharacteristicServiceType){
						continue;
					}
					if(isset($aReason[$key])){
						foreach($aReason[$key] as $aValue){
							if($aValue['index'] == $index){
								$aCommercialTenantShop[$key][$index]['reason'] = $aValue['reason'];
								$isPass = false;
								$aTenantNotice['title'] = '你的' . $this->aApproveKeyNameList[$key] . ' ' . $mCharacteristicServiceType->name . ' 更改未通过审核';
								$aTenantNotice['content'] = '你的' . $this->aApproveKeyNameList[$key] . ' ' . $mCharacteristicServiceType->name . ' 审核未通过，原因：' . $aValue['reason'];
								$aTenantNotice['is_read'] = 0;
								$aTenantNotice['create_time'] = NOW_TIME;
								array_push($aInsertTenantNoticeList, $aTenantNotice);
								break;
							}
						}
					}
					if($isPass){
						$aTenantNotice['title'] = '你的' . $this->aApproveKeyNameList[$key] . ' ' . $mCharacteristicServiceType->name . ' 更改已通过审核';
						//$aTenantNotice['content'] = '你的' . $this->aApproveKeyNameList[$key] . ' ' . $mCharacteristicServiceType->name . ' 审核通过';
						$aTenantNotice['content'] = '';
						$aTenantNotice['is_read'] = 0;
						$aTenantNotice['create_time'] = NOW_TIME;
						array_push($aInsertTenantNoticeList, $aTenantNotice);
						array_push($aInsertCharacterRelationList, [
							'tenant_id' => $mCommercialTenant->id,
							'service_type_id' => $v['value'],
							'create_time' => NOW_TIME,
						]);
						if(!$isFirstApprove){
							unset($aCommercialTenantShop[$key][$index]);
						}
					}
				}
				if($aInsertCharacterRelationList){
					CommercialTenantCharacteristicServiceRelation::batchInsertData($aInsertCharacterRelationList);
				}
			}elseif($key == 'photo'){
				$aInsertPhotoList = [];
				$passCount = count($value);
				$unPassCount = 0;
				if(isset($aReason[$key])){
					$unPassCount = count($aReason[$key]);
					$passCount = count($value) - $unPassCount;
				}
				if($passCount){
					$aTenantNotice = [
						'tenant_id' => $mCommercialTenant->id,
						'title' => '你有' . $passCount . '照片已通过审核',
						'content' => '',
						'is_read' => 0,
						'create_time' => NOW_TIME,
					];
					array_push($aInsertTenantNoticeList, $aTenantNotice);
				}
				if($unPassCount){
					$aTenantNotice = [
						'tenant_id' => $mCommercialTenant->id,
						'title' => '你有' . $unPassCount . '照片未通过审核',
						'content' => '',
						'is_read' => 0,
						'create_time' => NOW_TIME,
					];
					array_push($aInsertTenantNoticeList, $aTenantNotice);
				}
				foreach($value as $index => $v){
					$isPass = true;
					if(isset($aReason[$key])){
						foreach($aReason[$key] as $aValue){
							if($aValue['index'] == $index){
								$aCommercialTenantShop[$key][$index]['reason'] = $aValue['reason'];
								$isPass = false;
								break;
							}
						}
					}
					if($isPass){
						array_push($aInsertPhotoList, [
							'tenant_id' => $mCommercialTenant->id,
							'resource_id' => $v['resource_id'],
							'is_cover' => 0,
							'create_time' => NOW_TIME,
						]);
						if(!$isFirstApprove){
							unset($aCommercialTenantShop[$key][$index]);
						}
					}
				}
				if((!$aReason || !$isFirstApprove) && $aInsertPhotoList){
					CommercialTenantPhoto::batchInsertData($aInsertPhotoList);
				}
				if(!$isFirstApprove){
					unset($aCommercialTenantShop[$key]);
				}
			}elseif($key == 'teacher'){
				$aInsertTeacherList = [];
				foreach($value as $index => $v){
					$isPass = true;
					$aTenantNotice = ['tenant_id' => $mCommercialTenant->id];
					if(isset($aReason[$key])){
						foreach($aReason[$key] as $aValue){
							if($aValue['index'] == $index){
								$aCommercialTenantShop[$key][$index]['reason'] = $aValue['reason'];
								$isPass = false;
								$aTenantNotice['title'] = '你的' . $this->aApproveKeyNameList[$key] . ($v['id'] ? '编辑' : '新增') . '未通过审核';
								$aTenantNotice['content'] = '你的' . $this->aApproveKeyNameList[$key] . ($v['id'] ? '编辑' : '新增') . '审核未通过，原因：' . $aValue['reason'];
								$aTenantNotice['is_read'] = 0;
								$aTenantNotice['create_time'] = NOW_TIME;
								array_push($aInsertTenantNoticeList, $aTenantNotice);
								break;
							}
						}
					}
					if($isPass){
						$aTenantNotice['title'] = '你的' . $this->aApproveKeyNameList[$key] . ($v['id'] ? '编辑' : '新增') . '已通过审核';
						//$aTenantNotice['content'] = '你的' . $this->aApproveKeyNameList[$key] . ($v['id'] ? '编辑' : '新增') . '审核已通过';
						$aTenantNotice['content'] = '';
						$aTenantNotice['is_read'] = 0;
						$aTenantNotice['create_time'] = NOW_TIME;
						array_push($aInsertTenantNoticeList, $aTenantNotice);
						if(!$v['id']){
							array_push($aInsertTeacherList, [
								'tenant_id' => $mCommercialTenant->id,
								'profile' => $v['profile'],
								'name' => $v['name'],
								'duty' => $v['duty'],
								'seniority' => $v['seniority'],
								'description' => $v['description'],
								'order' => $v['order'],
								'create_time' => $v['create_time'],
							]);
						}else{
							$mTeacher = Teacher::findOne($v['id']);
							if(isset($v['profile'])){
								$mTeacher->set('profile', $v['profile']);
							}
							if(isset($v['name'])){
								$mTeacher->set('name', $v['name']);
							}
							if(isset($v['duty'])){
								$mTeacher->set('duty', $v['duty']);
							}
							if(isset($v['seniority'])){
								$mTeacher->set('seniority', $v['seniority']);
							}
							if(isset($v['description'])){
								$mTeacher->set('description', $v['description']);
							}
							if(isset($v['order'])){
								$mTeacher->set('order', $v['order']);
							}
							$mTeacher->save();
						}
						if(!$isFirstApprove){
							unset($aCommercialTenantShop[$key][$index]);
						}
					}
				}
				if($aInsertTeacherList){
					Teacher::batchInsertData($aInsertTeacherList);
				}
			}elseif($key == 'commercial_tenant_type'){
				if(!$aReason || (!$isFirstApprove && !isset($aReason[$key]))){
					$aCommercialTenantTypeRelationList = CommercialTenantTypeRelation::findAll(['tenant_id' => $mCommercialTenant->id]);
					$aCommercialTenantTypeId = ArrayHelper::getColumn($aCommercialTenantTypeRelationList, 'type_id');
					
					$aDeleteIds = [];
					foreach($aCommercialTenantTypeRelationList as $rValue){
						if(!in_array($rValue['type_id'], $value['value'])){
							array_push($aDeleteIds, $rValue['id']);
						}
					}
					$aInsertList = [];
					foreach($value['value'] as $saveId){
						if(!in_array($saveId, $aCommercialTenantTypeId)){
							array_push($aInsertList, [
								'tenant_id' => $mCommercialTenant->id,
								'type_id' => $saveId,
								'create_time' => NOW_TIME,
							]);
						}
					}
					if($aDeleteIds){
						CommercialTenantTypeRelation::deleteByIds($aDeleteIds);
					}
					if($aInsertList){
						CommercialTenantTypeRelation::batchInsertRecord($aInsertList);
					}
					if(!$isFirstApprove){
						unset($aCommercialTenantShop[$key]);
					}
				}
			}
		}
		if($aReason){
			$mTenantApprove->set('shop_info', $aCommercialTenantShop);
			$mTenantApprove->set('shop_approve_status', CommercialTenantApprove::STATUS_ONT_PASS_APPROVE);
		}else{
			$mTenantApprove->set('shop_info', []);
			$mTenantApprove->set('shop_approve_status', CommercialTenantApprove::STATUS_PASS_APPROVE);
			$mCommercialTenant->save();
		}
		if(!$isFirstApprove){
			$mTenantApprove->set('shop_info', []);
		}
		$mTenantApprove->save();
		
		//发通知
		if(!$isFirstApprove && $aInsertTenantNoticeList){
			CommercialTenantNotice::batchInsertData($aInsertTenantNoticeList);
		}
		
		return true;
	}
	//////////////////////////////////////////////////////////////////////Jay codeing
}