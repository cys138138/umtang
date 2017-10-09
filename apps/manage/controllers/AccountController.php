<?php
namespace manage\controllers;

use Yii;
use manage\lib\Controller;
use manage\model\WithdrawCashRecord;
use yii\data\Pagination;
use umeworld\lib\Response;

class AccountController extends Controller{
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
	
	public function actionShowWithdrawCashList(){
		$tenantName = (string)trim(Yii::$app->request->get('tenantName', ''));
		$startTime = (string)trim(Yii::$app->request->get('startTime', ''));
		$endTime = (string)trim(Yii::$app->request->get('endTime', ''));
		$page = (int)Yii::$app->request->get('page', 1);
		if($page < 1){
			$page = 1;
		}
		$pageSize = 10;
		
		$aCondition = [
			'is_finish' => 0,
		];
		if($startTime){
			$aCondition['start_time'] = strtotime($startTime);
		}
		if($endTime){
			$aCondition['end_time'] = strtotime('+1day', strtotime($endTime));
		}
		if($tenantName){
			$mtenant = \common\model\CommercialTenant::findOne(['name' => $tenantName]);
			if($mtenant){
				$aCondition['tenant_id'] = $mtenant->id;
			}else{
				$aCondition['tenant_id'] = 0;
			}
		}
		
		$aControl = [
			'order_by' => ['create_time' => SORT_ASC],
			'with_tenant_info' => true,
			'page' => $page,
			'page_size' => $pageSize
		];
		
		$aWithdrawCashList = WithdrawCashRecord::getList($aCondition, $aControl);
		$withdrawCashCount = WithdrawCashRecord::getCount($aCondition);
		$oPage = new Pagination(['totalCount' => $withdrawCashCount, 'pageSize' => $pageSize]);
		
		return $this->render('list', [
			'tenantName' => $tenantName,
			'startTime' => $startTime,
			'endTime' => $endTime,
			'aWithdrawCashList' => $aWithdrawCashList,
			'oPage' => $oPage
		]);
	}
	
	public function actionWithdrawCashSuccess(){
		$withdrawId = (int)Yii::$app->request->post('id', 0);
		if(!$withdrawId){
			return new Response('缺少必要参数id');
		}
		
		$mWithdrawCashRecord = WithdrawCashRecord::findOne(['id' => $withdrawId, 'is_finish' => 0]);
		if(!$mWithdrawCashRecord){
			return new Response('提现记录不存在');
		}
		
		$mWithdrawCashRecord->set('is_finish', 1);
		$mWithdrawCashRecord->set('finish_time', NOW_TIME);
		
		$aData = [
	 		'tenant_id' => $mWithdrawCashRecord->tenant_id,
	 		'title' => '提现成功',
	 		'content' => '提现金额' . ($mWithdrawCashRecord->amount)/100 . '(元)操作成功,账户余额' . ($mWithdrawCashRecord->balance)/100 . '(元)',
	 		'is_read' => 0,
	 		'create_time' => NOW_TIME
	 	];
		
		if($mWithdrawCashRecord->save()){
			\common\model\CommercialTenantNotice::add($aData);
			return new Response('操作成功', 1);
		}
		
		return new Response('操作失败');
	}
	
	public function actionGenerateExcel(){
		$tenantName = (string)trim(Yii::$app->request->get('tenantName', ''));
		$startTime = (string)trim(Yii::$app->request->get('startTime', ''));
		$endTime = (string)trim(Yii::$app->request->get('endTime', ''));
		
		$aCondition = [
			'is_finish' => 0,
		];
		if($startTime){
			$aCondition['start_time'] = strtotime($startTime);
		}
		if($endTime){
			$aCondition['end_time'] = strtotime('+1day', strtotime($endTime));
		}
		if($tenantName){
			$mtenant = \common\model\CommercialTenant::findOne(['name' => $tenantName]);
			if($mtenant){
				$aCondition['tenant_id'] = $mtenant->id;
			}else{
				$aCondition['tenant_id'] = 0;
			}
		}
		
		$aControl = [
			'order_by' => ['create_time' => SORT_ASC],
			'with_tenant_info' => true
		];
		
		$aWithdrawCashList = WithdrawCashRecord::getList($aCondition, $aControl);

		$aField = ['name', 'bank_name', 'bank_accout', 'bank_account_holder', 'balance', 'amount', 'create_time'];
		$aFieldName = ['商户', '开户银行', '银行帐号', '开户人', '提现后余额', '提现金额', '发起时间'];
		
		$aList = [];
		foreach($aWithdrawCashList as $key => $withdrawCash){
			foreach($aField as $value){
				switch($value){
					case 'name':
						$aList[$key][$value] = $withdrawCash['tenant_info']['name'];
						break;
					case 'bank_name':
						$aList[$key][$value] = $withdrawCash['tenant_info']['bank_name'];
						break;
					case 'bank_accout':
						$aList[$key][$value] = $withdrawCash['tenant_info']['bank_accout'];
						break;
					case 'bank_account_holder':
						$aList[$key][$value] = $withdrawCash['tenant_info']['bank_account_holder'];
						break;
					case 'balance':
						$aList[$key][$value] = $withdrawCash['balance']/100;
						break;
					case 'amount':
						$aList[$key][$value] = $withdrawCash['amount']/100;
						break;
					case 'create_time':
						if($withdrawCash['create_time']){
							$aList[$key][$value] = date('Y-m-d H:i:s', $withdrawCash['create_time']);
						}else{
							$aList[$key][$value] = '';
						}
						break;
				}
			}
		}
		array_unshift($aList, $aFieldName);
		
		$time = date('Ymd');
		return Yii::$app->excel->setSheetDataFromArray('提现列表' . $time . '.xls', $aList, true);
	}
}