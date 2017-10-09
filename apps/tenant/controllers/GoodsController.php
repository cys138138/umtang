<?php
namespace tenant\controllers;

use Yii;
use tenant\lib\Controller;
use umeworld\lib\PhoneValidator;
use umeworld\lib\Response;
use common\model\Goods;
use common\model\GoodsPhoto;
use common\model\form\goods\GoodsForm;
use common\model\GoodsApprove;
use common\model\CommercialTenant;
use common\model\Resource;
use yii\web\UploadedFile;
use common\model\form\ImageUploadForm;
use common\model\CommercialTenantTypeRelation;
use common\model\CommercialTenantType;


/*
 * 服务
 * @author 谭威力
 */
class GoodsController extends Controller{
	private function _getTenantMode(){
		return Yii::$app->commercialTenant->getIdentity();
	}
	
	private function _getActionList($scenario){
		if($scenario == 'photo'){
			return [
				1 => '设为封面',
				2 => '删除',
			];
		}elseif($scenario == 'goods'){
			return [
				1 => '上架',
				2 => '下架',
				3 => '删除',
			];
		}
	}
	
	/*
	 * 首页 tenant/goods/show-home.html
	 */
	public function actionShowHome(){
		return $this->render('home', [
			'aGoodsStatus' => [
				[
					'key' => Goods::HAS_PUT_ON,
					'value' => '已上架服务',
				],
				[
					'key' => Goods::NO_PUT_ON,
					'value' => '未上架服务',
				],
			],
		]);
	}
	
	/*
	 * 获取相关的服务数据 tenant/goods/get-goods-data.json
	 */
	public function actionGetGoodsList(){
		$status = (int)Yii::$app->request->post('status');
		$page = (int)Yii::$app->request->post('page', 1);
		$pageSize = (int)Yii::$app->request->post('pageSize', 10);
		if($page < 1){
			$page = 1;
		}
		if($pageSize < 0){
			$pageSize = 10;
		}
		if(!in_array($status, [Goods::HAS_PUT_ON, Goods::NO_PUT_ON])){
			return new Response('状态错误');
		}
		if($status){
			if($status == Goods::NO_PUT_ON){
				$aWhere['status'] = [Goods::NO_PUT_ON, Goods::LAY_DOWN];
			}
			if($status == Goods::HAS_PUT_ON){
				$aWhere['status'] = [Goods::HAS_PUT_ON, Goods::APPROVE_PUT_ON];
			}
			//$aWhere[] = ['status' => $aCondition['status']];
		}
		$aWhere['tenant_id'] = $this->_getTenantMode()->id;
		$aGoods = Goods::getListForTenant($aWhere, ['page' => $page, 'page_size' => $pageSize, 'order_by' => ['create_time' => SORT_DESC]]);
		return new Response('请求成功', 1, [
			'list' => $aGoods,
			'count' => Goods::getCount($aWhere),
		]);
	}
	
	/*
	 * 新增服务页 tenant/goods/show-add-goods.html
	 */
	public function actionShowAddGoods(){
		return $this->render('add_goods', ['aType' => $this->_getGoodsTypeList()]);
	}
	
	/*
	 * 提交新服务数据 tenant/goods/submit-new-goods.json
	 */
	public function actionAddGoods(){
		$aPost = Yii::$app->request->post();
//		$aPost = [//映射关系
//			'name' => '你想怎么样,我喊了', 
//			'price' => 600, 
//			'retailPrice' => 800,
//			'typeId' => 1, 
//			'validityTme' => 1498752000, 
//			'appointmentDay' => 2, 
//			'suitPeople' => 3, 
//			'maxClassPeople' => 60, 
//			'notice' => '啊啊啊啊啊啊啊啊啊啊啊', 
//			'description' => '啊啊啊啊啊啊寻寻寻寻寻寻', 
//		];
		$mGoodsForm = new GoodsForm();
		$mGoodsForm->scenario = GoodsForm::SCENE_ADD_GOODS_DATA;
		$mGoodsForm->mTenant = $this->_getTenantMode();
		if(!$mGoodsForm->load($aPost, '') || !$mGoodsForm->validate()){
			return new Response(current($mGoodsForm->getErrors())[0]);
		}
		if($mGoodsForm->addData()){
			return new Response('添加成功', 1);
		}
		return new Response('添加失败');
	}
	
	/*
	 * 编辑服务页 tenant/goods/show-edit-goods.html
	 */
	public function actionShowEditGoods(){
		$goodsId = (int)Yii::$app->request->get('goods_id');
		$mGoods = Goods::findOne($goodsId);
		if(!$mGoods || $mGoods->tenant_id != $this->_getTenantMode()->id){ 
			return new Response('参数错误');
		}
		if(!$mGoods->isCanEdit()){
			return new Response('请等待服务审核后再编辑');
		}
		return $this->render('edit_goods', [
			'aType' => $this->_getGoodsTypeList(),
			'aGoodsInfo' => $mGoods->getGoodsInfoWithApprove(['id', 'name', 'price', 'retail_price', 'type_id', 'validity_time', 'appointment_day', 'suit_people', 'max_class_people', 'notice', 'notice', 'description']),
		]);
	}
	
	/*
	 * 提交编辑服务数据 tenant/goods/submit-edit-goods.json
	 */
	public function actionEditGoods(){
		$aPost = Yii::$app->request->post();
//		$aPost = [//映射关系
//			'id' => 1,
//			'name' => '你这样不太好', 
//			'price' => 600, 
//			'retailPrice' => '',
//			'typeId' => 1, 
//			'validityTime' => 1498752000, 
//			'appointmentDay' => 2, 
//			'suitPeople' => 3, 
//			'maxClassPeople' => 60, 
//			'notice' => '啊啊啊啊啊啊啊啊啊啊啊', 
//			'description' => '啊啊啊啊啊啊寻寻寻寻寻寻', 
//		];
		$mGoodsForm = new GoodsForm();
		$mGoodsForm->scenario = GoodsForm::SCENE_EDIT_GOODS_DATA;
		$mGoodsForm->mTenant = $this->_getTenantMode();
		$mGoodsForm->aPost = $aPost;
		if(!$mGoodsForm->load($aPost, '') || !$mGoodsForm->validate()){
			return new Response(current($mGoodsForm->getErrors())[0]);
		}
		
		$aResult = $mGoodsForm->editData();
		//debug($aResult,11);
		if($aResult['isUpate']){
			return new Response('编辑成功', 1);
		}elseif($aResult['isSaveOk']){
			return new Response('编辑成功', 1);
		}
		return new Response('编辑失败');
	}
	
	/*
	 * 服务相册页 tenant/goods/show-goods-photo/<goods_id:\w+>.html
	 */
	public function actionShowGoodsPhoto(){
		$goodsId = (int)Yii::$app->request->get('goods_id');
		$mGoods = Goods::findOne($goodsId);
		if(!$mGoods || $mGoods->tenant_id != $this->_getTenantMode()->id){ 
			return new Response('参数错误');
		}
		return $this->render('goods_photo', ['goodsId' => $goodsId]);
	}
	
	/*
	 * 获取服务相册数据 tenant/goods/get-goods-photo.json
	 */
	public function actionGetGoodsPhotoList(){
		$goodsId = (int)Yii::$app->request->post('goodsId');//7 13;//
		$page = (int)Yii::$app->request->post('page', 1);
		if($page < 1){
			$page = 1;
		}
		$mGoods = Goods::findOne($goodsId);
		if(!$mGoods || $mGoods->tenant_id != $this->_getTenantMode()->id){ 
			return new Response('请选择正确服务');
		}
		$aWhere = [
			'goods_id' => $goodsId,
		];
		$aGoodsPhoto = GoodsPhoto::getListForTenant($aWhere, ['page' => $page, 'page_size' => 15, 'order_by' => ['create_time' => SORT_DESC]]);//'is_cover' => SORT_DESC,
		//debug($aGoodsPhoto,11);
		return new Response('请求成功', 1, [
			'list' => $aGoodsPhoto,
			'count' => GoodsPhoto::getCountForTenant($aWhere),
		]);
	}
	
	/*
	 * 相册操作 tenant/goods/operate-photo.json
	 */
	public function actionOperatePhoto(){
		$action = (int)Yii::$app->request->post('action');2;//
		//$photoId = (int)Yii::$app->request->post('photoId');
		$goodsId = (int)Yii::$app->request->post('goodsId');7;//
		$resourceId = (int)Yii::$app->request->post('resourceId');215;//
		if(!$action || !isset($this->_getActionList('photo')[$action])){
			return new Response('请选择正确操作');
		}
//		$mGoodsPhoto = GoodsPhoto::findOne($photoId);
//		if(!$mGoodsPhoto){
//			return new Response('图片不存在');
//		}
		$mGoods = Goods::findOne($goodsId);
		if(!$mGoods || $mGoods->tenant_id != $this->_getTenantMode()->id){ 
			return new Response('请传递正确的服务');
		}
		if($action == 1){//设置封面
			if($mGoods->goodsPhotoAction(['action' => 2, 'resource_id' => $resourceId])){
				//$mGoods->updateStatus(Goods::NO_PUT_ON);//修改照片也要下架
				return new Response('操作成功', 1);
			}
//			if($mGoods->goodsPhotoAction(['action' => 2, 'resource_id' => $resourceId])){
//				$mGoods->updateStatus(Goods::NO_PUT_ON);//修改照片也要下架
//				return new Response('图片上传成功', 1);
//			}
//			if($mGoodsPhoto->is_cover){
//				return new Response('该图片已经是封面了');
//			}
//			//找出之前是封面的
//			$mGoodsPhotoIsCover = GoodsPhoto::findOne(['goods_id' => $mGoods->id, 'is_cover' => 1]);
//			if($mGoodsPhotoIsCover){
//				$mGoodsPhotoIsCover->set('is_cover', 0);
//				$mGoodsPhotoIsCover->save();
//			}
//			$mGoodsPhoto->set('is_cover', 1);
//			if($mGoodsPhoto->save()){
//				return new Response('操作成功', 1);
//			}
		}elseif($action == 2){//删除  && $mGoodsPhoto->delete()
			if($mGoods->goodsPhotoAction(['action' => 3, 'resource_id' => $resourceId])){
				//$mGoods->updateStatus(Goods::NO_PUT_ON);//修改照片也要下架
				return new Response('删除成功', 1);
			}
		}
		return new Response('操作失败');
	}
	
	/*
	 * 服务操作 tenant/goods/operate-goods.json
	 */
	public function actionOperateGoods(){
		$action = (int)Yii::$app->request->post('action');
		$goodsId = (int)Yii::$app->request->post('goodsId');
		if(!$action || !isset($this->_getActionList('goods')[$action])){
			return new Response('请选择正确操作');
		}
		$mGoods = Goods::findOne($goodsId);
		if(!$mGoods || $mGoods->tenant_id != $this->_getTenantMode()->id){ 
			return new Response('请传递正确的服务');
		}
		if($action == 1){//上架
			if($mGoods->status == Goods::HAS_PUT_ON){
				return new Response('服务已经是上架了');
			}
			if($mGoods->status == Goods::APPROVE_PUT_ON){
				return new Response('服务处于审核中');
			}
			//更改服务状态
			if($mGoods->updateStatus(Goods::APPROVE_PUT_ON)){
			//$mGoodsApprove->updateApprove(GoodsApprove::APPROVE_WAIT);
				return new Response('上架成功', 1);
			}
			
		}elseif($action == 2){//下架
			if($mGoods->status != Goods::HAS_PUT_ON){
				return new Response('服务不是上架状态,不能下架');
			}
			if($mGoods->updateStatus(Goods::LAY_DOWN)){
				return new Response('下架成功', 1);
			}
		}elseif($action == 3){//删除
			if($mGoods->deleteGoods()){
				return new Response('删除成功', 1);
			}
		}
		return new Response('操作失败');
	}
	
	public function actionAddPhoto(){
		$resourceId = (int)Yii::$app->request->post('resourceId');
		$goodsId = (int)Yii::$app->request->post('goodsId');
		$mGoods = Goods::findOne($goodsId);
		if(!$mGoods || $mGoods->tenant_id != $this->_getTenantMode()->id){ 
			return new Response('请传递正确的服务');
		}
		$mResource = Resource::findOne($resourceId);
		if(!$mResource){ 
			return new Response('图片错误,请确认');
		}
		//先判断服务状态
		if($mGoods->status == Goods::APPROVE_PUT_ON){
			return new Response('服务处于审核中');
		}
		//if(GoodsPhoto::add(['goods_id' => $goodsId, 'resource_id' => $resourceId])){
		if($mGoods->goodsPhotoAction(['action' => 1, 'resource_id' => $resourceId, 'goods_id' => $goodsId])){
			//$mGoods->updateStatus(Goods::NO_PUT_ON);//修改照片也要下架
			return new Response('图片上传成功', 1);
		}
		return new Response('图片上传失败');
	}
	
	/*
	 * 图片传输接口 tenant/goods/upload-file.json
	 */
	public function actionUploadFile(){
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
		$savePath = Yii::getAlias('@p.tenant_goods_photo');

		$oForm->oImage = UploadedFile::getInstanceByName('filecontent');
		$aSize = getimagesize($oForm->oImage->tempName);
		$oForm->toWidth = $aSize[0];
		$oForm->toHeight = $aSize[1];
		//$oForm->toWidth = 300;
		//$oForm->toHeight = 300;
		if(!$oForm->upload($savePath)){
			$message = current($oForm->getErrors())[0];
			return new Response($message, 0);
		}else{
			$id = Resource::add([
				'type' => Resource::TYPE_GOODS_PHOTO,
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
	
	private function _getGoodsTypeList(){
		$aTypeId = CommercialTenantTypeRelation::findAll([
			'tenant_id' => $this->_getTenantMode()->id,
		], ['type_id']);
		if(!$aTypeId){
			return $aTypeId;
		}
		$aReturn = [];
		$aTypeIds = \yii\helpers\ArrayHelper::getColumn($aTypeId, ['type_id']);
		$aTypeInfo = CommercialTenantType::findAll(['id' => $aTypeIds], ['id', 'name']);
		foreach($aTypeInfo as $key => $value){
			$aReturn[$value['id']] = $value['name'];
		}
		return $aReturn;
	}
}

