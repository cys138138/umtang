<?php
namespace manage\controllers;

use Yii;
use manage\lib\Controller;
use umeworld\lib\Response;
use common\model\CommercialTenantType;
use common\model\CommercialTenantTypeRelation;
use umeworld\lib\Query;

/*
 * 直接运行脚本
 */
class DirectRunController extends Controller{
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
	
	/*
	 * 查询并更新只有一个托管分类的商户数据 direct-run/update-tenant-type
	 */
	public function actionUpdateTenantType(){
		$mCommercialTenantType = CommercialTenantType::findOne(['name' => '其他']);
		if($mCommercialTenantType){
			$typeId = $mCommercialTenantType->id;
		}else{
			//插入 其他 类型
			$typeId = CommercialTenantType::addTenantType('其他');
		}
		$aResult = (new Query())->select('`tenant_id`, count(tenant_id) as `num`')->from(CommercialTenantTypeRelation::tableName())->groupBy(['tenant_id'])->having(['num' => 1])->all();
		if(!$aResult){
			return new Response('没有了');
		}
		$aInsertData = [];
		foreach($aResult as $value){
			$aInsertData[] = [$value['tenant_id'], $typeId, NOW_TIME];
		}
		$aFields = ['tenant_id', 'type_id', 'create_time'];    //定义要插入的字段
		return (new Query())->createCommand()->batchInsert(CommercialTenantTypeRelation::tableName(), $aFields, $aInsertData)->execute();    //返回插入的行数
	}
	
	//计算商户的总销售量 direct-run/count-sale
	public function actionCountSale(){
		$aResult = (new Query())->select('count(tenant_id) as `count`, `tenant_id`')->from(\common\model\Order::tableName())->where(['and', ['!=', 'pay_time', 0]])->groupBy(['tenant_id'])->all();
		if(!$aResult){
			return 'ok';
		}
		foreach($aResult as $aOrder){
			$mCommercialTenant = \common\model\CommercialTenant::findOne($aOrder['tenant_id']);
			if($mCommercialTenant){
				$mCommercialTenant->set('all_sales_count', $aOrder['count']);
				$mCommercialTenant->save();
			}
		}
		return 'oksss';
	}
	
	//计算商户的 总评价数，总评分，评价分   direct-run/count-comment
	public function actionCountComment(){
		$aResult = (new Query())->select('sum(score) as `all_score`, `tenant_id`, count(*) as num')->from(\common\model\OrderCommentIndex::tableName())->where(['and', ['>', 'user_id', 0], ['>', 'score', 0]])->groupBy(['tenant_id'])->all();
		if(!$aResult){
			return 'ok';
		}
		foreach($aResult as $aComment){
			$mCommercialTenant = \common\model\CommercialTenant::findOne($aComment['tenant_id']);
			if($mCommercialTenant){
				$mCommercialTenant->set('all_comment_count', $aComment['num']);
				$mCommercialTenant->set('all_score', $aComment['all_score']);
				$mCommercialTenant->set('avg_score', (int)($mCommercialTenant->all_score / $mCommercialTenant->all_comment_count));
				$mCommercialTenant->save();
			}
		}
		return 'oksss';
	}
}