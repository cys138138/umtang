<?php
namespace common\model;

use Yii;
use umeworld\lib\Query;
use yii\helpers\ArrayHelper;
/**
 * 地区模型类
 */
class Area extends \common\lib\DbOrmModel{

	/**
	 * 获取数据库表名
	 * @author jay
	 * @return string 地区表名
	 */
	public static function tableName(){
		return Yii::$app->db->parseTable('_@area');
	}

	/**
	 * 获取地区列表
	 * @author alvin
	 * ~~~~~
	 * $aCondition = [
	 *		'id'		=>	数字或数组
	 * ]
	 * ~~~~~
	 * @param type $aCondition
	 * @return type
	 * @test \tests\codeception\common\unit\base\AreaTest::testGetAreaList
	 */
	public static function getAreaList($aCondition){
		$aWhere = ['and'];
		if(isset($aCondition['id']) && $aCondition['id']){
			$aWhere[] = ['id' => $aCondition['id']];
		}
		if(isset($aCondition['pid'])){
			$aWhere[] = ['pid' => $aCondition['pid']];
		}
		return (new Query())->from(self::tableName())->where($aWhere)->all();
	}

	/**
	 * 是否区/县
	 * @author alvin
	 * @return boolean 是否区/县
	 * @test \tests\codeception\common\unit\base\AreaTest::testAreaModelIsArea
	 */
	public function isArea(){
		return !(new Query())->from(static::tableName())->where(['pid' => $this->id])->exists();
	}
	
	/**
	 * 是否城市
	 * @author jay
	 * @return boolean 
	 */
	public function isCity(){
		if(!$this->pid){
			return false;
		}
		$mArea = self::findOne($this->pid);
		if(!$mArea->pid){
			return true;
		}
		return false;
	}

	/**
	 * 根据地区id获取地区的城市和省份信息
	 * @author alvin
	 * @param array $aAreaIds 地区id
	 * @return array 地区和地区的城市和省份信息
	 * @test \tests\codeception\common\unit\base\AreaTest::testGetDetailAreaListByAreaIds
	 */
	public static function getDetailAreaListByAreaIds($aAreaIds){
		$aAreaList = self::getAreaList(['id' => $aAreaIds]);
		if(!$aAreaList){
			return [];
		}
		$aCityIds = ArrayHelper::getColumn($aAreaList, 'pid');
		$aCityList = self::getAreaList(['id' => $aCityIds]);
		$aProvinceList = [];
		if($aCityList){
			$aProvinceIds = ArrayHelper::getColumn($aCityList, 'pid');
			$aProvinceList = self::getAreaList(['id' => $aProvinceIds]);
		}
		$aReturnData = [];
		foreach($aAreaList as $aArea){
			$aArea['city_info'] = [];
			$aArea['province_info'] = [];
			foreach($aCityList as $aCity){
				if($aCity['id'] == $aArea['pid']){
					$aArea['city_info'] = $aCity;
					foreach($aProvinceList as $aProvince){
						if($aProvince['id'] == $aCity['pid']){
							$aArea['province_info'] = $aProvince;
							break;
						}
					}
					break;
				}
			}
			$aReturnData[] = $aArea;
		}
		return $aReturnData;
	}


	/**
	 * 获取下属的地区id集合
	 * @author alvin
	 * @return array 下属的地区id集合
	 */
	public function getAreaIdList(){
		$aChildAreaList = (new Query())->from(self::tableName())->where(['pid' => $this->id])->all();
		if(!$aChildAreaList){
			return [$this->id];
		}
		$aChildAreaIds = ArrayHelper::getColumn($aChildAreaList, 'id');
		$aGrandchildAreaList = (new Query())->from(self::tableName())->where(['pid' => $aChildAreaIds])->all();
		if(!$aGrandchildAreaList){
			return $aChildAreaIds;
		}
		return ArrayHelper::getColumn($aGrandchildAreaList, 'id');
	}
}