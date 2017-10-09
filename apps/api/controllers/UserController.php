<?php
namespace api\controllers;

use Yii;
use api\lib\Controller;
use umeworld\lib\Response;
use common\model\User;
use common\model\UserNotice;
use common\model\UserCollect;
use common\model\OrderCommentIndex;
use common\model\UserAccumulatePointGetRecord;
use common\model\UserAccumulatePointUseRecord;
use common\model\UserAction;
use common\model\UserTask;
use common\model\Goods;
use common\model\Resource;
use common\model\CommercialTenant;
use yii\helpers\ArrayHelper;

class UserController extends Controller{
	public $enableCsrfValidation = false;
	
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}
	
	/*
	public function behaviors(){
		return \yii\helpers\ArrayHelper::merge([
			'access' => [
				'rules' => [
					[
						'allow' => true,
						'actions' => ['show-index'],
					],
				],
			],
		], parent::behaviors());
	}
	*/
	
	private function _getUserId(){
		return Yii::$app->user->id;
	}

	public function actionGetUserInfo(){
		$mUser = Yii::$app->user->getIdentity();
		$aUser = $mUser->toArray(['id', 'name', 'profile_path', 'mobile', 'accumulate_points']);
		
		return new Response('', 1, $aUser);
	}
	
	public function actionCheckUnreadMessageStatus(){
		$mUser = Yii::$app->user->getIdentity();
		
		return new Response('', 1, $this->_getUnreadMessageStatus($mUser));
	}
	
	public function actionUpdateInfo(){
		$mUser = Yii::$app->user->getIdentity();
		$aData = [
			'accumulate_points' => $mUser->accumulate_points,
			'has_unread_message' => $this->_getUnreadMessageStatus($mUser),
		];
		return new Response('', 1, $aData);
	}
	
	private function _getUnreadMessageStatus($mUser){
		$mUserAction = $mUser->getMUserAction();
		$aNote = $mUserAction->note;
		$lastNoticeReadTime = 0;
		if(isset($aNote['last_notice_read_time'])){
			$lastNoticeReadTime = $aNote['last_notice_read_time'];
		}
		$mUserNotice = UserNotice::findOne(['and', ['user_id' => $mUser->id], ['>', 'create_time', $lastNoticeReadTime]]);
		return $mUserNotice ? 1 : 0;
	}
	
	public function actionGetUserNoticeList(){
		$userId = $this->_getUserId();
		$page = (int)Yii::$app->request->post('page');
		$pageSize = (int)Yii::$app->request->post('pageSize');
		if(!$page || $page <= 0){
			$page = 1;
		}
		if(!$pageSize || $pageSize <= 0){
			$pageSize = 10;
		}
		if(!$userId){
			return new Response('', 1, []);
		}
		$aCondition = ['user_id' => $userId];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
			'order_by' => ['id' => SORT_DESC],
		];
		$aList = UserNotice::getList($aCondition, $aControl);
		$mUser = Yii::$app->user->getIdentity();
		$mUserAction = $mUser->getMUserAction();
		$aNote = $mUserAction->note;
		$lastNoticeReadTime = 0;
		if(isset($aNote['last_notice_read_time'])){
			$lastNoticeReadTime = $aNote['last_notice_read_time'];
		}
		foreach($aList as $key => $value){
			if($value['create_time'] > $lastNoticeReadTime){
				$aList[$key]['is_read'] = 0;
			}else{
				$aList[$key]['is_read'] = 1;
			}
		}
		
		$aNote['last_notice_read_time'] = NOW_TIME;
		$mUserAction->set('note', $aNote);
		$mUserAction->save();
		
		return new Response('', 1, $aList);
	}
	
	public function actionGetUserAccumulatePointsGetList(){
		$userId = $this->_getUserId();
		$page = (int)Yii::$app->request->post('page');
		$pageSize = (int)Yii::$app->request->post('pageSize');
		if(!$page || $page <= 0){
			$page = 1;
		}
		if(!$pageSize || $pageSize <= 0){
			$pageSize = 10;
		}
		if(!$userId){
			return new Response('', 1, []);
		}
		$aCondition = ['user_id' => $userId];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
		];
		$aList = UserAccumulatePointGetRecord::getList($aCondition, $aControl);
		
		return new Response('', 1, $aList);
	}
	
	public function actionGetUserAccumulatePointsUseList(){
		$userId = $this->_getUserId();
		$page = (int)Yii::$app->request->post('page');
		$pageSize = (int)Yii::$app->request->post('pageSize');
		if(!$page || $page <= 0){
			$page = 1;
		}
		if(!$pageSize || $pageSize <= 0){
			$pageSize = 10;
		}
		if(!$userId){
			return new Response('', 1, []);
		}
		$aCondition = ['user_id' => $userId];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
		];
		$aList = UserAccumulatePointUseRecord::getList($aCondition, $aControl);
		
		return new Response('', 1, $aList);
	}
	
	public function actionGetUserCollectList(){
		$userId = $this->_getUserId();
		$page = (int)Yii::$app->request->post('page');
		$pageSize = (int)Yii::$app->request->post('pageSize');
		$type = (int)Yii::$app->request->post('type');
		$lng = Yii::$app->request->post('lng', 0);
		$lat = Yii::$app->request->post('lat', 0);
		
		if(!$page || $page <= 0){
			$page = 1;
		}
		if(!$pageSize || $pageSize <= 0){
			$pageSize = 10;
		}
		if(!in_array($type, [UserCollect::TYPE_SHOP, UserCollect::TYPE_GOODS])){
			return new Response('类型错误', 0);
		}
		if(!$userId){
			return new Response('', 1, []);
		}
		$aCondition = [
			'user_id' => $userId,
			'type' => $type,
			'lng' => $lng,
			'lat' => $lat,
		];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
			'with_data_info' => true,
		];
		$aList = UserCollect::getList($aCondition, $aControl);
		
		return new Response('', 1, $aList);
	}
	
	public function actionGetUserCommentList(){
		$userId = $this->_getUserId();
		$page = (int)Yii::$app->request->post('page');
		$pageSize = (int)Yii::$app->request->post('pageSize');
		
		if(!$page || $page <= 0){
			$page = 1;
		}
		if(!$pageSize || $pageSize <= 0){
			$pageSize = 10;
		}
		if(!$userId){
			return new Response('', 1, []);
		}
		$aCondition = [
			'user_id' => $userId,
			'pid' => 0,
		];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
			'with_resource_info' => true,
			'with_user_info' => true,
			'with_reply_list' => true,
			'with_content_info' => true,
			'with_tenant_info' => true,
		];
		$aList = OrderCommentIndex::getList($aCondition, $aControl);
		
		return new Response('', 1, $aList);
	}
	
	public function actionGetUserTaskList(){
		$mUser = Yii::$app->user->getIdentity();
		$mUserTask = $mUser->getMUserTask();
		$mUserTask->checkUserTaskByOrderAndComment();//检查用户支付 和 评价 任务 并设置状态
		return new Response('', 1, $mUserTask->getTaskContentList());
	}
	
	public function actionGetUserTaskPrize(){
		$taskId = (int)Yii::$app->request->post('task_id');
		
		$mUser = Yii::$app->user->getIdentity();
		$mUserTask = $mUser->getMUserTask();
		$aContent = $mUserTask->content;
		if(!isset($aContent[$taskId])){
			return new Response('任务不存在', 0);
		}
		if($aContent[$taskId]['status'] == UserTask::STATUS_UNFINISH){
			return new Response('任务未完成', 0);
		}elseif($aContent[$taskId]['status'] == UserTask::STATUS_RECEIVED_AWARD){
			return new Response('任务已领过奖了', 0);
		}elseif($aContent[$taskId]['status'] == UserTask::STATUS_FINISH){
			$aTaskConfigList = UserTask::getTaskConfigList();
			//$addAccumulatePoint = (int)$aContent[$taskId]['accumulate_point'];
			$addAccumulatePoint = (int)$aTaskConfigList[$taskId]['accumulate_point'];
			$r1 = $mUser->addAccumulatePoint($addAccumulatePoint);
			if(!$r1){
				return new Response('领取失败', 0);
			}
			$r2 = UserAccumulatePointGetRecord::add([
				'user_id' => $mUser->id,
				'type' => $taskId,
				'amount' => $addAccumulatePoint,
				'create_time' => NOW_TIME,
			]);
			if(!$r2){
				return new Response('领取失败', 0);
			}
			$aContent[$taskId]['status'] = UserTask::STATUS_RECEIVED_AWARD;
			$mUserTask->set('content', $aContent);
			$r3 = $mUserTask->save();
			if(!$r3){
				return new Response('领取失败', 0);
			}
			return new Response('领取成功', 1);
		}else{
			return new Response('任务状态不正确', 0);
		}
	}
	
	public function actionUserCollect(){
		$dataId = (int)Yii::$app->request->post('data_id');
		$type = (int)Yii::$app->request->post('type');
		
		$mUser = Yii::$app->user->getIdentity();
		if(!$mUser->mobile){
			return new Response('需要绑定手机才可以收藏哦', 0);
		}
		if(!$dataId){
			return new Response('出错啦', 0);
		}
		
		if($type == UserCollect::TYPE_SHOP){
			$mCommercialTenant = CommercialTenant::findOne($dataId);
			if(!$mCommercialTenant){
				return new Response('收藏的商铺不存在', 0);
			}
		}elseif($type == UserCollect::TYPE_GOODS){
			$mGoods = Goods::findOne($dataId);
			if(!$mGoods){
				return new Response('收藏的服务不存在', 0);
			}
		}else{
			return new Response('收藏类型不正确', 0);
		}
		
		$mUserCollect = UserCollect::findOne([
			'user_id' => $mUser->id,
			'type' => $type,
			'data_id' => $dataId,
		]);
		if($mUserCollect){
			return new Response('已经收藏过了哦', 0);
		}else{
			//检查首次收藏任务
//			$mUserTask = $mUser->getMUserTask();
//			$mUserTask->checkFirstCollectTask($mUser, $type);
			
			$isSuccess = UserCollect::add([
				'user_id' => $mUser->id,
				'type' => $type,
				'data_id' => $dataId,
				'create_time' => NOW_TIME,
			]);
			if(!$isSuccess){
				return new Response('收藏失败', 0);
			}
		}
		return new Response('收藏成功', 1);
	}
	
	public function actionUserCollectCancel(){
		$dataId = (int)Yii::$app->request->post('data_id');
		$type = (int)Yii::$app->request->post('type');
		
		if(!$dataId){
			return new Response('出错啦', 0);
		}
		
		if($type == UserCollect::TYPE_SHOP){
			$mCommercialTenant = CommercialTenant::findOne($dataId);
			if(!$mCommercialTenant){
				return new Response('取消收藏的商铺不存在', 0);
			}
		}elseif($type == UserCollect::TYPE_GOODS){
			$mGoods = Goods::findOne($dataId);
			if(!$mGoods){
				return new Response('取消收藏的服务不存在', 0);
			}
		}else{
			return new Response('取消收藏类型不正确', 0);
		}
		
		$mUser = Yii::$app->user->getIdentity();
		$mUserCollect = UserCollect::findOne([
			'user_id' => $mUser->id,
			'type' => $type,
			'data_id' => $dataId,
		]);
		if(!$mUserCollect){
			return new Response('收藏记录不存在', 0);
		}
		if(!$mUserCollect->delete()){
			return new Response('取消收藏失败', 0);
		}
		return new Response('取消收藏成功', 1);
	}
	
	public function actionBindUserInfo(){
		$name = (string)Yii::$app->request->post('name');
		$profileUrl = (string)Yii::$app->request->post('profileUrl');
		
		if(!$name){
			return new Response('缺少名字', 0);
		}
		if(!$profileUrl){
			return new Response('缺少头像', 0);
		}
		$mUser = Yii::$app->user->getIdentity();
		$savePath = Yii::getAlias('@p.api_comment_upload') . '/' . md5($mUser->id) . '.jpg';
		//file_put_contents(Yii::getAlias('@p.resource') . '/' . $savePath, file_get_contents($profileUrl));
		file_put_contents(Yii::getAlias('@p.resource') . '/' . $savePath, (new \umeworld\lib\Http($profileUrl))->get());
		
		$id = Resource::add([
			'type' => Resource::TYPE_PROFILE,
			'path' => $savePath,
			'create_time' => NOW_TIME,
		]);
		if(!$id){
			return new Response('保存失败', 0);
		}
		$mUser->set('name', $name);
		$mUser->set('profile', $id);
		$mUser->save();
		
		return new Response('保存成功', 1);
	}
}