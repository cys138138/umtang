<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use umeworld\lib\Response;
use common\model\CommercialTenantType;
use common\model\CommercialTenantTypeRelation;
use common\model\City;
use common\model\CommercialTenant;
use common\model\Goods;
use common\model\Order;
use common\model\User;
use common\model\Teacher;
use common\model\UserCollect;
use yii\helpers\ArrayHelper;

class IndexController extends Controller{
	public $enableCsrfValidation = false;
	
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
		];
	}

	private function _getUserId(){
		return Yii::$app->user->id;
	}
	
	public function actionGetTenantTypeList(){
		$aList = CommercialTenantType::findAll([], ['id', 'name']);
		
		return new Response('', 1, $aList);
	}
	
	public function actionGetNearlyTenantList(){
		$cityId = (int)Yii::$app->request->post('cityId');
		$lng = Yii::$app->request->post('lng', 0);
		$lat = Yii::$app->request->post('lat', 0);
		$page = (int)Yii::$app->request->post('page');
		$pageSize = (int)Yii::$app->request->post('pageSize');
		
		if(!$page || $page <= 0){
			$page = 1;
		}
		if(!$pageSize || $pageSize <= 0){
			$pageSize = 10;
		}
		$cityName = Yii::$app->tencentMap->getCityNameByLocation($lng, $lat);
		$mCity = false;
		if($cityName){
			$mCity = City::findOne(['name' => $cityName]);
		}
		if(!$mCity || $cityId != $mCity->id){
			return new Response('', 1, []);
		}
		$aCondition = [
			'city_id' => $cityId,
			'lng' => $lng,
			'lat' => $lat,
		];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
		];
		$aList = CommercialTenant::getTenantListWithDistance($aCondition, $aControl);
		
		return new Response('', 1, $aList);
	}
	
	public function actionGetGuessYouLikeList(){
		$userId = $this->_getUserId();
		$cityId = (int)Yii::$app->request->post('cityId');
		$lng = Yii::$app->request->post('lng', 0);
		$lat = Yii::$app->request->post('lat', 0);
		$page = (int)Yii::$app->request->post('page');
		$pageSize = (int)Yii::$app->request->post('pageSize');
		
		if(!$page || $page <= 0){
			$page = 1;
		}
		if(!$pageSize || $pageSize <= 0){
			$pageSize = 10;
		}
		$cityName = Yii::$app->tencentMap->getCityNameByLocation($lng, $lat);
		$mCity = false;
		if($cityName){
			$mCity = City::findOne(['name' => $cityName]);
		}
		
		$aReturnId = [];
		//取10个用户买过的商品
		if($userId){
			$aGoodsIdList = Order::getUserBoughtGoodsIdList(['user_id' => $userId], ['page' => 1, 'page_size' => 10, 'order_by' => ['create_time' => SORT_DESC]]);
			if($aGoodsIdList){
				$aReturnId = array_merge($aReturnId, $aGoodsIdList);
			}
		}
		//取10个用户收藏过的商品
		if($userId){
			$aDataIdList = UserCollect::findAll(['user_id' => $userId, 'type' => UserCollect::TYPE_GOODS], ['data_id'], 1, 10, ['create_time' => SORT_DESC]);
			if($aDataIdList){
				$aDataId = ArrayHelper::getColumn($aDataIdList, 'data_id');
				$aReturnId = array_merge($aReturnId, $aDataId);
			}
		}
		//取10个附近10km商家商品
		$aNearlyList = [];
		if(!(!$mCity || $cityId != $mCity->id)){
			$aCondition = [
				'city_id' => $cityId,
				'lng' => $lng,
				'lat' => $lat,
				'in_distince' => 10000,
			];
			$aControl = [
				'page' => $page,
				'page_size' => $pageSize,
			];
			$aNearlyList = CommercialTenant::getTenantListWithDistance($aCondition, $aControl);
			if($aNearlyList){
				$aTenantId = ArrayHelper::getColumn($aNearlyList, 'id');
				$aGoodsIdList = Goods::getGoodsIdListByTenantIds($aTenantId);
				if($aGoodsIdList){
					$aReturnId = array_merge($aReturnId, $aGoodsIdList);
				}
			}
		}
		$aReturnId = array_unique($aReturnId);
		//不足50个取销售靠前的商品
		$aWhere = ['and', ['status' => Goods::HAS_PUT_ON]];
		if($aReturnId){
			$aWhere[] = ['not in', 'id', $aReturnId];
		}
		$aGoodsIdList = Goods::findAll($aWhere, ['id'], 1, 50 - count($aReturnId), ['sales_count' => SORT_DESC]);
		if($aGoodsIdList){
			$aGoodsId = ArrayHelper::getColumn($aGoodsIdList, 'id');
			$aReturnId = array_merge($aReturnId, $aGoodsId);
		}
		shuffle($aReturnId);
		
		return new Response('', 1, $aReturnId);
	}
	
	public function actionGetGoodsListByIds(){
		$lng = Yii::$app->request->post('lng', 0);
		$lat = Yii::$app->request->post('lat', 0);
		$aGoodsId = (array)Yii::$app->request->post('aGoodsId');
		
		if(!$aGoodsId){
			return new Response('缺少服务ID', 0);
		}
		$aGoodsList = Goods::findAll(['id' => $aGoodsId], ['id', 'tenant_id', 'name', 'price', 'retail_price', 'sales_count'], 0, 0);
		if($aGoodsList){
			$aGoodsList = Goods::goodsListWithTenantInfo($aGoodsList, $lng, $lat);
			$aGoodsList = Goods::goodsListWithPhotoPath($aGoodsList);
		}
		return new Response('', 1, $aGoodsList);
	}
	
	public function actionGetTenantList(){
		$lng = Yii::$app->request->post('lng', 0);
		$lat = Yii::$app->request->post('lat', 0);
		$tenantTypeId = (int)Yii::$app->request->post('tenantTypeId');
		$page = (int)Yii::$app->request->post('page');
		$pageSize = (int)Yii::$app->request->post('pageSize');
		
		if(!$page || $page <= 0){
			$page = 1;
		}
		if(!$pageSize || $pageSize <= 0){
			$pageSize = 10;
		}
		
		if(!$tenantTypeId){
			return new Response('缺少类型ID', 0);
		}
		$mCommercialTenantType = CommercialTenantType::findOne($tenantTypeId);
		if(!$mCommercialTenantType){
			return new Response('找不到类型信息', 0);
		}
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
			'order_by' => ['create_time' => SORT_DESC],
		];
		$aList = CommercialTenantTypeRelation::getList(['type_id' => $tenantTypeId], $aControl);
		if($aList){
			$aTenantId = ArrayHelper::getColumn($aList, 'tenant_id');
			$aTenantList = CommercialTenant::getTenantListWithDistance(['id' => $aTenantId, 'lng' => $lng, 'lat' => $lat], ['page' => 1, 'page_size' => $pageSize]);
		}else{
			$aTenantList = [];
		}
		
		return new Response('', 1, $aTenantList);
	}
	
	public function actionGetUserLocateCity(){
		$lng = Yii::$app->request->post('lng', 0);
		$lat = Yii::$app->request->post('lat', 0);
		
		if(!$lng && !$lat){
			$mCity = City::findOne(['name' => '广州市']);
			if(!$mCity){
				return new Response('出错了', 0);
			}
			return new Response('', 1, [
				'id' => $mCity->id,
				'name' => $mCity->name,
			]);
		}
		$cityName = Yii::$app->tencentMap->getCityNameByLocation($lng, $lat);
		$mCity = false;
		if($cityName){
			$mCity = City::findOne(['name' => $cityName]);
		}
		$aCity = [
			'id' => -1,
			'name' => $cityName,
		];
		if($mCity){
			$aCity = [
				'id' => $mCity->id,
				'name' => $mCity->name,
			];
			$userId = $this->_getUserId();
			if($userId){
				$mUser = User::findOne($userId);
				if($mUser){
					$mUser->set('last_city_id', $mCity->id);
					$mUser->set('last_lng', $lng);
					$mUser->set('last_lat', $lat);
					$mUser->save();
				}
			}
		}
		return new Response('', 1, $aCity);
	}
	
	public function actionGetOtherCityList(){
		$aList = City::findAll([], ['id', 'name']);
		
		return new Response('', 1, $aList);
	}
	
	public function actionSearchTenantOrGoods(){
		$searchValue = (string)Yii::$app->request->post('searchValue');
		$lng = Yii::$app->request->post('lng', 0);
		$lat = Yii::$app->request->post('lat', 0);
		$page = (int)Yii::$app->request->post('page');
		$pageSize = (int)Yii::$app->request->post('pageSize');
		
		if(!$page || $page <= 0){
			$page = 1;
		}
		if(!$pageSize || $pageSize <= 0){
			$pageSize = 10;
		}
		if(!$searchValue){
			return new Response('缺少关键字', 0);
		}
		$aCondition = [
			'lng' => $lng,
			'lat' => $lat,
			'search_value' => $searchValue,
		];
		$aControl = [
			'page' => $page,
			'page_size' => $pageSize,
		];
		$aList = CommercialTenant::searchTenantOrGoods($aCondition, $aControl);
		
		return new Response('', 1, $aList);
	}
	
	public function actionGetTeacherList(){
		$tenantId = (int)Yii::$app->request->post('tenantId');
		
		if(!$tenantId){
			return new Response('缺少商户id', 0);
		}
		
		$mCommercialTenant = CommercialTenant::findOne($tenantId);
		if(!$mCommercialTenant){
			return new Response('找不到商户信息', 0);
		}
		
		$aList = Teacher::getList(['tenant_id' => $tenantId], [
			'select' => ['id', 'tenant_id', 'profile', 'name', 'duty', 'seniority'],
		]);
		return new Response('', 1, $aList);
	}
	
	public function actionGetTeacher(){
		$teacherId = (int)Yii::$app->request->post('teacherId');
		
		if(!$teacherId){
			return new Response('缺少教师id', 0);
		}
		
		$mTeacher = Teacher::findOne($teacherId);
		if(!$mTeacher){
			return new Response('找不到教师信息', 0);
		}
		return new Response('', 1, $mTeacher->toArray(['id', 'tenant_id', 'profile', 'profile_path', 'name', 'duty', 'seniority', 'description']));
	}
		
}