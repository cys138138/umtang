<?php
namespace manage\model\form;

use Yii;
use yii\data\Pagination;
use manage\model\CommercialTenant;
use manage\model\CommercialTenantApprove;

class TenantShopApproveListForm extends \yii\base\Model{
	public $page = 1;
	public $pageSize = 15;

	public function rules(){
		return [
			['page', 'compare', 'compareValue' => 0, 'operator' => '>'],
		];
	}
	
	public function getList(){
		$aCondition = $this->getListCondition();
		$aControl = [
			'page' => $this->page,
			'page_size' => $this->pageSize,
			//'with_type_characteristic_service' => true,
		];
		$aList = CommercialTenant::getCommercialTenantList($aCondition, $aControl);

		return $aList;
	}

	public function getListCondition(){
		$aCondition = [
			'online_status' => CommercialTenant::ONLINE_STATUS_ONLINE,
			'shop_approve_status' => [
				CommercialTenantApprove::STATUS_WAIT_APPROVE,
				CommercialTenantApprove::STATUS_IN_APPROVE
			],
		];
		
		return $aCondition;
	}

	public function getPageObject(){
		$aCondition = $this->getListCondition();
		$count = CommercialTenant::getCommercialTenantCount($aCondition);
		return new Pagination(['totalCount' => $count, 'pageSize' => $this->pageSize]);
	}
}