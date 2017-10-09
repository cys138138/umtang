<?php
namespace manage\controllers;

use Yii;
use manage\lib\Controller;
use manage\model\WithdrawCashRecord;
use yii\data\Pagination;
use umeworld\lib\Response;

class ManagerController extends Controller{
	public function actions() {
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction'
			],
		];
	}
	
	public function behaviors() {
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
	
	public function actionShowProfile(){
		$mManager = Yii::$app->manager->identity;
		return $this->render('profile', ['mManager' => $mManager]);
	}
	
	public function actionShowPassword(){
		return $this->render('password');
	}
	
	public function actionUpdatePassword(){
		$oldPassword = trim((string)Yii::$app->request->post('oldPassword'));
		$newPassword = trim((string)Yii::$app->request->post('newPassword'));
		if(!$oldPassword || !$newPassword){
			return new Response('请输入原密码和新密码');
		}
		if(mb_strlen($newPassword, 'utf8') < 6){
			return new Response('新密码长度必须大于6位');
		}
		$mManager = Yii::$app->manager->identity;
		$oldPassword = md5($oldPassword);
		$newPassword = md5($newPassword);
		if($mManager->password != $oldPassword){
			return new Response('原密码错误');
		}
		$mManager->set('password', $newPassword);
		if($mManager->save()){
			return new Response('修改成功', 1);
		}
		return new Response('修改失败');
	}
}