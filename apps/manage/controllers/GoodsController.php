<?php
namespace manage\controllers;

use Yii;
use manage\lib\Controller;
use umeworld\lib\Response;
use yii\data\Pagination;

class GoodsController extends Controller{
	const APPROVE_PASS = 1;
	const APPROVE_NOT_PASS = 2;
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
	
	public function actionShowGoodsApproveList(){
		$page = (int)Yii::$app->request->get('page', 1);
		if($page < 1){
			$page = 1;
		}
		$pageSize = 10;
		//获取待审核的服务列表
		//$aGoodsList = \common\model\Goods::getListForTenant($aCondition, ['page' => $page, 'page_size' => $pageSize, 'order_by' => ['create_time' => SORT_ASC], 'select' => ['id', 'name', 'tenant_id', 'status', 'type_id', 'validity_time', 'create_time']]);
		$aGoodsList = \manage\model\Goods::getWaitApproveList(['page' => $page, 'page_size' => $pageSize, 'order_by' => ['create_time' => SORT_ASC]]);
		$goodsCount = \manage\model\Goods::getWaitApproveCount();
		$oPage = new Pagination(['totalCount' => $goodsCount, 'pageSize' => $pageSize]);
		//debug($aGoodsList, 11);
		return $this->render('approve_list', [
			'aGoodsList' => $aGoodsList,
			'oPage' => $oPage,
		]);
	}
	
	public function actionShowGoodsApproveDetail(){
		$goodsId = (int)Yii::$app->request->get('goodsId', 0);
		if($goodsId <= 0){
			return new Response('错误的服务id');
		}
		$mGoods = \common\model\Goods::findOne($goodsId);
		if(!$mGoods){
			return new Response('错误的服务');
		}
		if($mGoods->status != \common\model\Goods::APPROVE_PUT_ON && $mGoods->status != \common\model\Goods::HAS_PUT_ON){
			return new Response('错误的服务2');
		}elseif($mGoods->status == \common\model\Goods::HAS_PUT_ON){
			$mGoodsApprove = $mGoods->getMGoodsApprove();
			if($mGoodsApprove->approved_status != \common\model\GoodsApprove::APPROVE_WAIT){
				return new Response('错误的服务3');
			}
		}
		$aGoodsInfo = $mGoods->getGoodsInfoWithApprove();
		
		$mTenant = \manage\model\CommercialTenant::findOne($aGoodsInfo['tenant_id']);
		if(!$mTenant || $mTenant->online_status != \manage\model\CommercialTenant::ONLINE_STATUS_ONLINE){
			return new Response('不是上线的商户');
		}
		$aGoodsInfo['tenant_name'] = $mTenant->name;
		
		$mGoodsType = \common\model\CommercialTenantType::findOne($aGoodsInfo['type_id']);
		if($mGoodsType){
			$aGoodsInfo['type_name'] = $mGoodsType->name;
		}else{
			$aGoodsInfo['type_name'] = '类型已删除';
		}
		
		//debug($aGoodsInfo, 11);
		return $this->render('approve_detail', [
			'aGoodsInfo'	=>	$aGoodsInfo
		]);
	}
	
	public function actionGoodsApprove(){
		$goodsId = (int) Yii::$app->request->post('id');
		$reason = (string) trim(Yii::$app->request->post('reason', ''));
		$action = (int) Yii::$app->request->post('action');
		if($goodsId <= 0){
			return new Response('错误的服务id');
		}
		if(!in_array($action, [static::APPROVE_PASS, static::APPROVE_NOT_PASS])){
			return new Response('错误的操作');
		}
		$mGoods = \common\model\Goods::findOne($goodsId);
		if(!$mGoods){
			return new Response('错误的服务');
		}
		if($mGoods->status != \common\model\Goods::APPROVE_PUT_ON && $mGoods->status != \common\model\Goods::HAS_PUT_ON){
			return new Response('错误的服务2');
		}elseif($mGoods->status == \common\model\Goods::HAS_PUT_ON){
			$mGoodsApprove = $mGoods->getMGoodsApprove();
			if($mGoodsApprove->approved_status != \common\model\GoodsApprove::APPROVE_WAIT){
				return new Response('错误的服务3');
			}
		}
		
		$mGoodsApprove = $mGoods->getMGoodsApprove();
		$aApproveContent = $mGoodsApprove->content;
		$rows = 0;
		$aNotice = [
			'tenant_id'	=>	$mGoods->tenant_id,
			'is_read'	=>	0,
			'content'	=>	'',
			'create_time'	=>	NOW_TIME,
		];
		$goodsName = $mGoods->name;
		if(isset($aApproveContent['name'])){
			$goodsName = $aApproveContent['name'];
		}
		if($action == static::APPROVE_PASS){
			$commercialTenantTypeId = 0;
			if(isset($aApproveContent['type_id'])){
				$commercialTenantTypeId = $aApproveContent['type_id'];
			}else{
				$commercialTenantTypeId = $mGoods->type_id;
			}
			$mGoodsType = \common\model\CommercialTenantType::findOne($commercialTenantTypeId);
			if(!$mGoodsType){
				return new Response('服务类型不存在');
			}
			$aNotice['title'] = '你的服务 ' . $goodsName . ' 已通过审核';
			//如果服务审核通过，则把approve表的内容更新到数据表中，如果服务是未上架的则上架,approve表状态变为审核通过
			foreach($aApproveContent as $field => $xValue){
				if($field == 'photo_list'){
					//插入到相片
					$aPhotoList = [];
					foreach($xValue as $aPhoto){
						unset($aPhoto['id']);
						$aPhotoList[] = $aPhoto;
					}
					if($aPhotoList){
						$rows = \common\model\GoodsPhoto::addBatch($aPhotoList);
					}
				}else{
					$mGoods->set($field, $xValue);
				}
			}
			if($mGoods->status == \common\model\Goods::APPROVE_PUT_ON){
				$mGoods->set('status', \common\model\Goods::HAS_PUT_ON);
			}
			$rows += $mGoods->save();
			if(!$rows){
				return new Response('保存服务信息失败');
			}
			$mGoodsApprove->set('approved_status', \common\model\GoodsApprove::APPROVE_PASS);
			$mGoodsApprove->set('content', []);
			$mGoodsApprove->save();
		}else{
			//如果服务审核不通过
			if(!$reason){
				return new Response('请填写不通过原因');
			}
			$aNotice['title'] = '你的服务 ' . $goodsName . ' 未通过审核';
			$aNotice['content'] = '你的服务 ' . $goodsName . ' 审核未通过，原因：' . $reason;
			if($mGoods->status == \common\model\Goods::APPROVE_PUT_ON){
				//如果是审核中的服务，改变服务状态
				$mGoods->set('status', \common\model\Goods::NO_PUT_ON);
				if(!$mGoods->save()){
					return new Response('改变服务状态失败');
				}
			}else{
				//如果是已上架的服务，改变approve表的状态，并清空content字段
				$mGoodsApprove->set('approved_status', \common\model\GoodsApprove::APPROVE_DEFEATED);
				$mGoodsApprove->set('content', []);
				if(!$mGoodsApprove->save()){
					return new Response('更新审核表失败');
				}
			}
		}
		//给用户发消息
		\common\model\CommercialTenantNotice::add($aNotice);
		return new Response('审核完成', 1);
	}
}