<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
//use yii\web\Controller;
use umeworld\lib\Response;
use common\model\CommercialTenant;
use common\model\Order;
use common\model\OrderCommentIndex;
use common\model\Redis;
use common\model\WithdrawCashRecord;
use common\model\OrderComment;

/*
 * 评价
 * @author 谭威力
 */
class CommentController extends Controller{
	private function _getTenantMode(){
		return Yii::$app->commercialTenant->getIdentity();
	}
	
	private function _getCommentStatus(){
		return [
			[
				'key' => 1,
				'value' => '未回复',
			],
			[
				'key' => 2,
				'value' => '已回复',
			],
			[
				'key' => 0,
				'value' => '全部评价',
			],
		];
	}
	
	/*
	 * 首页 tenant/comment/show-home.html
	 */
	public function actionShowHome(){
		$mCommercialTenant = $this->_getTenantMode();
		$mCommercialTenantAction = $mCommercialTenant->getMTenantAction();
		$aNote = $mCommercialTenantAction->note;
		$aNote['last_comment_read_time'] = NOW_TIME;
		$mCommercialTenantAction->set('note', $aNote);
		$mCommercialTenantAction->save();
		return $this->render('home', [
			'aCommentStatus' => $this->_getCommentStatus(),
		]);
	}
	
	/*
	 * 获取相关的评价数据 tenant/comment/get-comment-data.json
	 */
	public function actionGetCommentList(){
		$status = (int)Yii::$app->request->post('status');
		$page = (int)Yii::$app->request->post('page', 1);
		if($page < 1){
			$page = 1;
		}
		$aCommentStatus = \yii\helpers\ArrayHelper::getColumn($this->_getCommentStatus(), 'key');
		if(!in_array($status, $aCommentStatus)){
			return new Response('请选择正确的状态');
		}
		$mCommercialTenant = $this->_getTenantMode();
		$aWhere = [
			'tenant_id' => $mCommercialTenant->id
		];
		if($status){
			$aWhere['is_reply'] = $status - 1;
		}
		$aOrderCommentIndexResult = OrderCommentIndex::getReplyStatusCommentList($aWhere, ['page' => $page, 'page_size' => 10, 'with_all_info' => true]);
		$aOrderCommentIndexs = $aOrderCommentIndexResult['list'];
		$aOrderCommentIndexCount = $aOrderCommentIndexResult['count'];
		//debug($aOrderCommentIndexCount);
		//debug($aOrderCommentIndexs,11);
		return new Response('请求成功', 1, [
			'list' => $aOrderCommentIndexs,
			'count' => $aOrderCommentIndexCount,
		]);
	}
	
	/*
	 * 评价详情页 tenant/comment/show-details/<order_id:\w+>.html
	 */
	public function actionCommentDetails(){
		$orderId = (int)Yii::$app->request->get('order_id');
		if(!$orderId){
			return new Response('请传递订单id');
		}
		$mCommercialTenant = $this->_getTenantMode();
		$aWhere = [
			'tenant_id' => $mCommercialTenant->id,
			'id' => $orderId,
		];
		$aOrders = Order::getList($aWhere);
		if(!$aOrders){
			return new Response('请传递正确的订单id');
		}
		$aOrderInfo = \umeworld\lib\ArrayFilter::fastFilter($aOrders[0], [
				'order_num',
				'type',
				'goods_info',
				'price',
				'quantity',
				'user_id',
				'user_name',
				'activation_time',
		]);
		$aOrderInfo['order_id'] = $orderId;
		$aWhere = [
			'and',
			['order_id' => $orderId],
			['tenant_id' => $mCommercialTenant->id],
			['>' ,'user_id', 0],
			['>' ,'score', 0],
		];
		$mOrderCommentIndex = OrderCommentIndex::findOne($aWhere);
		//$mOrderComment = OrderComment::findOne($mOrderCommentIndex->id);
		$aReturnData = [
			'id' => $mOrderCommentIndex->id,
			'create_time' => $mOrderCommentIndex->create_time,
			'score' => $mOrderCommentIndex->score,
			'content' => $mOrderCommentIndex->content,
			'resource_info' => $mOrderCommentIndex->resource_path,
			'comment_list' => [],
		];
		$aReturnData = array_merge($aOrderInfo, $aReturnData);
		$aOrderComments = OrderCommentIndex::getList(['tenant_id' => $mCommercialTenant->id, 'order_id' => $orderId], ['with_all_info' => true]);//['tenant_id' => $mCommercialTenant->id, 'user_id_no_0' => 1]
		foreach($aOrderComments as $aOrderComment){
			if($mOrderCommentIndex->id == $aOrderComment['id']){
				continue;
			}
			$aReturnData['comment_list'][] = $aOrderComment;
		}
		//debug($mOrderCommentIndex->resource_path);
		//debug($aReturnData,11);
		return $this->render('comment_details', ['aCommentinfo' => $aReturnData]);
	}
	
	/*
	 * 回复评价 tenant/comment/reply-comment.json
	 */
	public function actionReplyComment(){
		$content = trim((string)Yii::$app->request->post('content'));//'卖萌吧';//
		$orderId = (int)Yii::$app->request->post('orderId');// 1001;//
		$id = (int)Yii::$app->request->post('id');//1;//
		if(!$orderId){
			return new Response('请传递订单id', -1);
		}
		if(!$content){
			return new Response('请传递评价内容', -1);
		}
		if(!$id){
			return new Response('评论的id', -1);
		}
		if(mb_strlen($content, 'utf-8') > 100){
			return new Response('评价内容长度是100个字以内', -1);
		}
		$mCommercialTenant = $this->_getTenantMode();
		$aWhere = [
			'tenant_id' => $mCommercialTenant->id,
			'id' => $orderId,
		];
		$aOrders = Order::getList($aWhere);
		if(!$aOrders){
			return new Response('请传递正确的订单id');
		}
		$mOrderCommentIndex = OrderCommentIndex::findOne(['id' => $id, 'tenant_id' => $mCommercialTenant->id, 'order_id' => $orderId]);
		if(!$mOrderCommentIndex || $mOrderCommentIndex->user_id == 0){
			return new Response('评论的id不正确');
		}
		$isFirst = true;//用户的评论初评
		$pId = $mOrderCommentIndex->id;
		if($mOrderCommentIndex->pid){//这是用户追评
			$isFirst = false;
			$pId = $mOrderCommentIndex->pid;
		}
		$aOrderCommentIndexHasReply = OrderCommentIndex::findAll(['pid' => $pId, 'tenant_id' => $mCommercialTenant->id, 'order_id' => $orderId], ['id']);
		if(count($aOrderCommentIndexHasReply) >= 2){
			return new Response('你已经回复过了');
		}
		$aData = [
			'order_id' => $orderId,
			'pid' => $pId,
			'tenant_id' => $mCommercialTenant->id,
			'is_superaddition' => 0,
			'user_id' => 0,
			'score' => 0,
			'content' => $content,
			'resource_ids' => [],
		];
		if(OrderCommentIndex::reply($aData)){
			return new Response('回复评论成功', 1);
		}
		return new Response('回复评论失败');
	}
}

