<?php
namespace login\controllers;

use Yii;
use login\lib\Controller;
use umeworld\lib\PhoneValidator;
use umeworld\lib\Response;
use common\model\JoinList;
use common\model\JoinCategory;

class SiteController extends Controller{
	public function actions(){
		return [
			'error' => [
				'class' => 'umeworld\lib\ErrorAction',
			],
			'captcha' => [
				'class' => 'yii\captcha\CaptchaAction',
				'offset' => -1,
				//'fixedVerifyCode' => !YII_ENV_PROD ? '121212' : null,
                'maxLength' => 5,
                'minLength' => 5,
			],
		];
	}
	
	public function actionIndex(){
		$this->layout = 'index';
		return $this->render('business/index');
	}
	
	public function actionShowIntro(){
		$this->layout = 'index-menu';
		return $this->render('business/intro');
	}
	
	public function actionShowFqa(){
		$this->layout = 'index-menu';
		return $this->render('business/fqa');
	}
	
	public function actionShowHome(){
		$this->layout = 'index-menu';
		return $this->render('business/home');
	}
	
	public function actionShowProcess(){
		$this->layout = 'index-menu';
		return $this->render('business/process');
	}
	
	public function actionShowAbout(){
		$this->layout = 'index-menu';
		return $this->render('business/umt_about');
	}
	
	/*
	 * 新版首页
	 */
	public function actionHome(){
		$this->layout = 'index';
		return $this->render('home');
	}
	
	/*
	 * 公司介绍(/about/company)
	 */
	public function actionAboutCompany(){
		$this->layout = 'index-menu';
		return $this->render('about/company');
	}
	
	/*
	 * 加入我们(/join)
	 */
	public function actionJoin(){
		$this->layout = 'index';
		$aJoinCategory = JoinCategory::getListAndCount();//JoinCategory::findAll([],['id', 'name'], '', '', ['orders' => SORT_DESC]);
		return $this->render('join/join', ['aJoinCategory' => $aJoinCategory]);
	}
	
	/*
	 * 获取职位列表
	 */
	public function actionGetJoinList(){
		//$page = (int)Yii::$app->request->post('page', 1);
		//$pageSize = (int)Yii::$app->request->post('pageSize', 10);
		$categoryId = (int)Yii::$app->request->post('categoryId');
//		if($page < 1){
//			$page = 1;
//		}
//		if($pageSize < 1){
//			$page = 10;
//		}
		$aWhere = [];
		if($categoryId){
			$aWhere['category_id'] = $categoryId;
		}
		$aJoinList = JoinList::getList($aWhere, [
			//'select' => ['id', 'name', 'category_id', 'number_min', 'number_max', 'city_id', 'create_time'],
			//'page' => $page, 
			//'page_size' => $pageSize,
			'order_by' => [ 'create_time' => SORT_DESC]
		]);
		
		return new Response('请求成功', 1, [
			'list' => $aJoinList,
			'count' => JoinList::getCount(['category_id' => $categoryId]),
		]);
	}
	
	/*
	 * 某个职位
	 */
	public function actionJoinOne(){
		//$this->layout = 'index';
		$joinId = (int)Yii::$app->request->post('id');
		if(!$joinId){
			return new Response('请选择正确的职位查看');
		}
		$aJoinList = JoinList::getList(['id' => $joinId]);
		if(!$aJoinList){
			return new Response('职位不存在');
		}
		return new Response('请求成功', 1, ['list' => $aJoinList[0]]);
//		return $this->render('join-one', [
//			'aJoinList' => $aJoinList[0],
//		]);
	}
	
	/*
	 * 常见问题(/help/faq)
	 */
	public function actionHelpFaq(){
		$this->layout = 'index-menu';
		return $this->render('help/help_faq');
	}
	
	/*
	 * 联系方式(/about/contact)
	 */
	public function actionAboutContact(){
		$this->layout = 'index-menu';
		return $this->render('about/contact');
	}
	
	/*
	 * 用户协议(/about/terms)
	 */
	public function actionAboutTerms(){
		$this->layout = 'index-menu';
		return $this->render('about/terms');
	}
	
	/*
	 * 法律声明(/about/law)
	 */
	public function actionAboutLaw(){
		$this->layout = 'index-menu';
		return $this->render('about/law');
	}
	
	/*
	 * 隐私保护(/about/privacy)
	 */
	public function actionAboutPrivacy(){
		$this->layout = 'index-menu';
		return $this->render('about/privacy');
	}
	
	public function actionMyShop(){
		$this->layout = 'mobile';
		return $this->render('my-home');
	}
	
	/*
	 * 首页(/business/home)
	
	public function actionBusinessHome(){
		$this->layout = 'index';
		return $this->render('business/home');
	} */
	/*
	 * 合作介绍(/business/intro)
	
	public function actionBusinessIntro(){
		$this->layout = 'index-menu';
		return $this->render('business/intro');
	} */
	/*
	 * 合作流程(/business/prcoess)
	 */
	public function actionBusinessPrcoess(){
		$this->layout = 'index-menu';
		return $this->render('business/prcoess');
	}
	/*
	 * 常见问题(/business/faq)
	
	public function actionBusinessFaq(){
		$this->layout = 'index-menu';
		return $this->render('business/faq');
	} */
	
	/*
	 * 商家入驻申请 business-settled-apply.json => site/business-settled-apply
	 */
	public function actionBusinessSettledApply(){
		$aApply = [
			'name' => (string)trim(Yii::$app->request->post('name')),//'东京热',//
			'user_name' => (string)trim(Yii::$app->request->post('userName')),//'小强',//
			'mobile' => (string)trim(Yii::$app->request->post('mobile')),//'13800138000',//
		];
		
		if(!$aApply['name']){
			return new Response('机构名字不能为空');
		}
		if(mb_strlen($aApply['name'], 'utf8') > 50){
			return new Response('机构名字最长不能超出50字');
		}
		
		if(!$aApply['user_name'] || mb_strlen($aApply['user_name'], 'utf8') > 10){
			return new Response('姓名为1-10个字');
		}
		
		if(!(new PhoneValidator())->validate($aApply['mobile'])){
			return new Response('手机格式不正确', 0);
		}
		
		if(\common\model\BusinessSettledApply::add($aApply)){
			return new Response('申请成功', 1);
		}
		return new Response('申请失败');
	}
	
	/*
	 * 商户协议 /business/protocol => site/business-protocol
	 */
	public function actionBusinessProtocol(){
		//debug(1,11);
		$this->layout = 'index-menu';
		return $this->render('business/protocol');
	}
	
	/*
	 * 关于优满堂
	 
	public function actionUmtAbout(){
		$this->layout = 'index-menu';
		return $this->render('business/umt_about');
	}*/
}
