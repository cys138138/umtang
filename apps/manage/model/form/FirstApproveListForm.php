<?php
namespace manage\model\form;

use Yii;
use yii\data\Pagination;
use manage\model\CommercialTenant;

class FirstApproveListForm extends \yii\base\Model{
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
		];
		$aList = CommercialTenant::getCommercialTenantList($aCondition, $aControl);

		return $aList;
	}

	public function getListCondition(){
		$aCondition = ['online_status' => CommercialTenant::ONLINE_STATUS_IN_APPROVE];
		
		return $aCondition;
	}

	public function getPageObject(){
		$aCondition = $this->getListCondition();
		$count = CommercialTenant::getCommercialTenantCount($aCondition);
		return new Pagination(['totalCount' => $count, 'pageSize' => $this->pageSize]);
	}
}