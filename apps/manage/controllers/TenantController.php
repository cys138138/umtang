<?php
namespace manage\controllers;

use Yii;
use manage\lib\Controller;
use umeworld\lib\Response;
use manage\model\CommercialTenant;
use yii\data\Pagination;

class TenantController extends Controller{
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
		$id = (int)Yii::$app->request->get('id');
		$name = (string)Yii::$app->request->get('name');
		if($page <= 0){
			$page = 1;
		}
		$pageSize = 10;
		$aCondition = [];
		if($id){
			$aCondition['id'] = $id;
		}
		if($name){
			$aCondition['name'] = $name;
		}
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
		];
		$aTenantList = CommercialTenant::getOnlineTenantList($aCondition, $aControl);
		$allCount = CommercialTenant::getOnlineTenantCount($aCondition);
		$oPage = new Pagination(['totalCount' => $allCount, 'pageSize' => $pageSize]);
		return $this->render('list', [
			'id' => $id,
			'name' => $name,
			'aTenantList' => $aTenantList,
			'oPage' => $oPage,
		]);
	}
	
	public function actionSendNotice(){
		$tenantId = (int)Yii::$app->request->post('tenantId');
		$title = trim((string)Yii::$app->request->post('title'));
		$content = trim((string)Yii::$app->request->post('content'));
		if(!$title || mb_strlen($title, 'UTF8') > 20){
			return new Response('标题长度为1-20个字');
		}
		if(!$content || mb_strlen($content, 'UTF8') > 100){
			return new Response('内容长度为1-100个字');
		}
		$mTenant = CommercialTenant::findOne($tenantId);
		if(!$mTenant){
			return new Response('错误的商户');
		}
		$aData = [
			'tenant_id' => $tenantId,
			'title' => $title,
			'content' => $content,
		];
		if(\common\model\CommercialTenantNotice::add($aData)){
			return new Response('发送成功', 1);
		}
		return new Response('发送失败');
	}
	
	public function actionShowTenantInfoDetail(){
		$id = (int)Yii::$app->request->get('id');
		if(!$id){
			return new Response('缺少id');
		}
		$aCondition = [
			'id' => $id,
	 		'online_status'	=> \common\model\CommercialTenant::ONLINE_STATUS_ONLINE
		];
		$aControl = [
	 		'with_tenant_type'	=> true,
	 		'with_type_characteristic_service'	=> true
		];
		$tenantInfo = CommercialTenant::getTenantDetailById($aCondition, $aControl);
		return $this->render('tenant_info_detail', [
			'tenantInfo' => $tenantInfo
		]);
	}
	
	public function actionShowGoodsInfoList(){
		$page = (int)Yii::$app->request->get('page', 1);
		if($page <= 0){
			$page = 1;
		}
		$pageSize = 10;
		$aCondition = [
	 		'status' => \common\model\Goods::HAS_PUT_ON
	 	];
	 	$aControl = [
	 		'order_by' => ['id' =>SORT_ASC],
	 		'page' => $page,
	 		'page_size' => $pageSize,
	 		'with_photo_info' => true
	 	];
		$aGoodsList = \common\model\Goods::getList($aCondition, $aControl);
		$goodsCount = \common\model\Goods::getCount($aCondition);
		$oPage = new Pagination(['totalCount' => $goodsCount, 'pageSize' => $pageSize]);
		return $this->render('goods_info_list', [
			'aGoodsList' => $aGoodsList,
			'oPage' => $oPage
		]);
	}
	
	public function actionOffTheShelf(){
		$id = (int)Yii::$app->request->post('id', 0);
		if(!$id){
			return new Response('缺少必要参数id');
		}
		$reason = (string)trim(Yii::$app->request->post('reason', ''));
		if(!$reason){
			return new Response('请填写下架理由');
		}
		$mGoods = \common\model\Goods::findOne($id);
		if(!$mGoods){
			return new Response('该商品不存在');
		}
		$mGoods->set('status',  \common\model\Goods::LAY_DOWN);
		if($mGoods->save()){
			$aData= [
				'tenant_id' => $mGoods->tenant_id,
				'title' => '你的服务 ' . $mGoods->name . ' 已下架',
				'content' => '你的服务 ' . $mGoods->name . ' 已被管理员下架，原因：' . $reason . '。如有异议请联系优满堂工作人员。',
			];
			\common\model\CommercialTenantNotice::add($aData);
			return new Response('操作成功', 1);
		}
		return new Response('操作失败');
	}
}