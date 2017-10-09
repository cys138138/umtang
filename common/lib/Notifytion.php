<?php
namespace common\lib;

use Yii;

/**
 * 通知组件
 * @property-read array $aData 全部通知数据
 */
class Notifytion extends \yii\base\Component{
	/**
	 * 没有VIP权限的通知
	 */
	const NOTICE_TIPS_VIP_ACTIVATION = 'tips_vip_activation';

	/**
	 * 金币发生了变化
	 */
	const NOTICE_STUDENT_INFO_CHANGED = 'student_info_changed';

	/**
	 * 通知数据追加模式:覆盖模式
	 */
	const DATA_ADD_MODE_OVERWRITE = 1;

	/**
	 * 通知数据追加模式:当此前不存在此通知时才写入通知数据
	 */
	const DATA_ADD_MODE_ON_NOT_EXISTS = 2;

	/**
	 * 通知数据追加模式:追加模式
	 */
	const DATA_ADD_MODE_APPEND = 3;

	/**
	 * @var array 通知数据
	 */
	private $_aData = [];

	/**
	 * 添加通知
	 * @param string $noticeKey 通知的标识符
	 * @param mixed $xData 通知信息
	 * @param int $dataAddMode 数据追加模式
	 *
	 *  - [[DATA_ADD_MODE_OVERWRITE]]
	 *  - [[DATA_ADD_MODE_ON_NOT_EXISTS]]
	 *  - [[DATA_ADD_MODE_APPEND]]
	 *
	 * @author 黄文非
	 * @return bool 是否已经追加了数据
	 * @test \tests\common\base\NotifytionTest::testAdd
	 */
	public function add($noticeKey, $xData, $dataAddMode = self::DATA_ADD_MODE_OVERWRITE){
		$noticeExists = isset($this->_aData[$noticeKey]);
		if($dataAddMode == static::DATA_ADD_MODE_ON_NOT_EXISTS && $noticeExists){
			//已存在则不写入通知数据
			return false;
		}

		$xWriteData = $xData;
		if($dataAddMode == static::DATA_ADD_MODE_APPEND){
			if($noticeExists){
			//追加模式
				$aLastData = $this->_aData[$noticeKey];
				if(!is_array($aLastData)){
					throw Yii::$app->buildError('上一条相同通知的数据不是数组,无法追加数据');
				}
				$aLastData[] = $xData;
				$xWriteData = $aLastData;
			}else{
				$xWriteData = [$xData];
			}
		}

		$this->_aData[$noticeKey] = $xWriteData;
		return true;
	}

	/**
	 * 获取通知数据
	 * @param string $noticeKey 通知的标识,没有的时候返回全部通知数据
	 * @return mixed
	 * @author 黄文非
	 * @test \tests\common\base\NotifytionTest::testGet
	 */
	public function get($noticeKey = ''){
		if(!$noticeKey){
			return $this->_aData;
		}

		if(!isset($this->_aData[$noticeKey])){
			return null;
		}
		return $this->_aData[$noticeKey];
	}

	/**
	 * 移除通知
	 * @param string $noticeKey 要移除的通知标识
	 * @author 黄文非
	 * @test \tests\common\base\NotifytionTest::testRemove
	 */
	public function remove($noticeKey){
		unset($this->_aData[$noticeKey]);
	}

	/**
	 * 获取通知数据
	 * @return array
	 * @author 黄文非
	 * @test \tests\common\base\NotifytionTest::testGet
	 */
	public function getAData(){
		return $this->_aData;
	}
}