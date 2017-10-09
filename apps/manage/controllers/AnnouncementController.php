<?php
namespace manage\controllers;

use Yii;
use manage\lib\Controller;
use common\model\form\ImageUploadForm;
use yii\web\UploadedFile;
use umeworld\lib\Response;
use common\model\CommercialTenantAnnouncement;
use yii\data\Pagination;

class AnnouncementController extends Controller{
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
	
	public function actionShowList(){
		$page = (int)Yii::$app->request->get('page');
		if($page <= 0){
			$page = 1;
		}
		$pageSize = 20;
		$aAnnouncementList = CommercialTenantAnnouncement::findAll([], ['id', 'title', 'create_time'], $page, $pageSize, ['create_time' => SORT_DESC]);
		$allCount = CommercialTenantAnnouncement::getCount();
		$oPage = new Pagination(['totalCount' => $allCount, 'pageSize' => $pageSize]);
		return $this->render('list', [
			'aAnnouncementList' => $aAnnouncementList,
			'oPage' => $oPage,
		]);
	}
	
	public function actionShowAdd(){
		$id = (int)	Yii::$app->request->get('id');
		$aAnnouncement = [];
		if($id){
			$mAnnouncement = \common\model\CommercialTenantAnnouncement::findOne($id);
			if($mAnnouncement){
				$aAnnouncement = $mAnnouncement->toArray();
			}else{
				$id = 0;
			}
		}
		return $this->render('add', [
			'id' => $id,
			'aAnnouncement' => $aAnnouncement,
		]);
	}
	
	public function actionAdd(){
		$id = (int) trim(Yii::$app->request->post('id'));
		$title = (string) trim(Yii::$app->request->post('title'));
		$content = (string) Yii::$app->request->post('content');
		if(!$title || !$content){
			return new Response('标题和内容不能为空！');
		}
		if(mb_strlen($title, 'UTF8') > 50){
			return new Response('标题请控制在50个字以内！');
		}
		if($id > 0){
			$mAnnouncement = \common\model\CommercialTenantAnnouncement::findOne($id);
			if(!$mAnnouncement){
				return new Response('公告不存在！');
			}
			$mAnnouncement->set('title', $title);
			$mAnnouncement->set('content', $content);
			if($mAnnouncement->save()){
				return new Response('修改成功！', 1);
			}
			return new Response('修改失败！');
		}
		$aAnnouncement = [
			'title' => $title,
			'content' => $content,
		];
		if(\common\model\CommercialTenantAnnouncement::add($aAnnouncement)){
			return new Response('发布成功！', 1);
		}
		return new Response('发布失败！');
	}
	
	public function actionUploadFile(){
		$oForm = new ImageUploadForm([
			'aRules' => [
				'base' => [
					'maxSize' => 2048000,
				],
			],
		]);
		$oForm->oImage = UploadedFile::getInstanceByName('image');
		$savePath = Yii::getAlias('@p.announcement_upload') . '/';
		if(!$oForm->upload($savePath)){
			$message = current($oForm->getErrors())[0];
			return "<script>parent.UM.getEditor('". Yii::$app->request->get('editorid') ."').getWidgetCallback('image')('', '" . $message . "')</script>";
		}
		if(Yii::$app->request->isAjax){
			return '/' . $oForm->savedFile;
		}
		return "<script>parent.UM.getEditor('". Yii::$app->request->get('editorid') ."').getWidgetCallback('image')('/" . $oForm->savedFile . "','" . 'SUCCESS' . "')</script>";
	}
	
	public function actionDelete(){
		$id = (int) trim(Yii::$app->request->post('id'));
		$mAnnouncement = CommercialTenantAnnouncement::findOne($id);
		if(!$mAnnouncement){
			return new Response('公告不存在');
		}
		if($mAnnouncement->delete()){
			return new Response('删除成功', 1);
		}
		return new Response('删除失败');
	}
}