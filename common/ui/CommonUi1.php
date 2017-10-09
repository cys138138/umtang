<?php
namespace common\ui;

use common\model\Area;
use common\model\Grade;
use common\model\School;
use common\model\Student;
use umeworld\lib\BaseUi;
use Yii;

/**
 * 公共第1版UI组件
 * @author 黄文非
 */
class CommonUi1 extends BaseUi{
	const ROOT_PATH = '/view3/pc/plugin/ui1';
	
	const PAGE_MANAGER_NAME = 'stand-alone-page';

	/**
	 * @var bool 是否通过页面管理器发起当前请求
	 */
	private $_isRequestByPageManager = null;

	public function getIsRequestByPageManager(){
		if($this->_isRequestByPageManager === null){
			$this->_isRequestByPageManager = (string)Yii::$app->request->get('requestBy') == static::PAGE_MANAGER_NAME;
		}
		return $this->_isRequestByPageManager;
	}

	public $aDefaultProfile = [];
	
	/**
	 * 构建按VIP级别区分颜色的用户名字标签
	 * @param array $aStudent 学生信息数组,至少要带id,name,vip三个键
	 * @param bool $isJump 是否带链接跳转
	 * @return string 名字标签的HTML
	 * @author 黄文非
	 */
	public function buildVipName($aStudent, $isJump = true){
		$aAttrList = [
			'class' => 'vip' . $aStudent['vip'] . '-name vname'
		];
		if($isJump){
			$aAttrList['href'] = Yii::$app->urlManagerHome->createUrl(['student/show-home', 'student_id' => $aStudent['id']]);
			$aAttrList['target'] = '_blank';
		}
		return static::tag('a', $aStudent['name'], $aAttrList);
	}

	/**
	 * 构建学生头像标签
	 * @param array $aStudent 学生信息数组,至少要带id,name,profile三个键
	 * @param bool $isJump 是否带链接跳转
	 * @param array $aOptions 附加HTML属性,键值对,键名是属性名称,值就是属性值了
	 * @return string 头像标签的HTML
	 * @author 黄文非
	 */
	public function buildProfile($aStudent, $isJump = true, $aOptions = []){
		$profilePath = $aStudent['profile'];
		if(empty($profilePath)){
			$profilePath = Yii::getAlias('@r.url') . static::ROOT_PATH . '/head_error.png';
		}else{
			if($profilePath[0] != 'h'){
				$profilePath = Yii::getAlias('@r.url') . '/' . $profilePath;
			}
		}

		$aOptions['class'] = 'avatar';
		if(isset($aOptions['addClass'])){
			$aOptions['class'] .= ' ' . $aOptions['addClass'];
			unset($aOptions['addClass']);
		}
		$aAttrList = $aOptions;
		if($isJump){
			$aAttrList['href'] = Yii::$app->urlManagerHome->createUrl(['student/show-home', 'student_id' => $aStudent['id']]);
			$aAttrList['target'] = '_blank';
		}
		return static::tag('a', $this->buildImage($profilePath), $aAttrList);
	}

	/**
	 * 构建带默认图片的图像标签
	 * @param string $src 图像地址
	 * @return string 图像标签的HTML
	 * @author 黄文非
	 */
	public function buildImage($src, $aOptions = []){
		$aAttrList = array_merge([
			'src' => Yii::getAlias('@r.img.default_img'),
			'real' => $src,
			'onload' => 'Ui1.loadImage(this)',
		], $aOptions);
		return static::tag('img', '', $aAttrList);
	}

	public function buildVipIcon($vipLevel) {
		$icon = static::tag('i', '', ['class' => 'icon']);
		return static::a($icon, Yii::$app->urlManagerVip->createUrl(['site/index']), [
			'class' => 'vip' . (int)$vipLevel . '-icon vicon',
			'target' => '_blank',
		]);
	}

	/**
	 * 获取完整地区名字
	 * @param Area $mArea 地区模型实例,必须是区域的实例
	 * @param string $format 格式化字符串,就像date函数的Y-m-d一样,$province=省份名称,$city=城市名称,$area=区域名称
	 * @see Area::isArea 判断一个地区模型是否为一个区域以确认能否作为地区参数传进来
	 * @author 黄文非
	 * @test \tests\codeception\common\unit\base\UiTest::testGetFullAddress
	 * @return string
	 */
	public function getFullAddress(Area $mArea, $format = '$province $city $area'){
		if(!$mCity = Area::findOne($mArea->pid)){
			throw Yii::$app->buildError('获取区域的所在城市失败');
		}

		if(!$mProvince = Area::findOne($mCity->pid)){
			throw Yii::$app->buildError('获取城市的所在省份失败');
		}

		return str_replace([
			'$province',
			'$city',
			'$area',
		], [
			$mProvince->name,
			$mCity->name,
			$mArea->name,
		], $format);
	}

	/**
	 * 获取完整学校信息
	 * @param Student $mStudent 学生模型
	 * @param string $format  格式化字符串,就像date函数的Y-m-d一样,$school=学校名称,$grade=年级名称,$class=班级名称
	 * @return string
	 * @test \tests\codeception\common\unit\base\UiTest::testGetFullSchoolInfo
	 * @author 黄文非
	 */
	public function getFullSchoolInfo(Student $mStudent, $format = '$school $grade $class'){
		$mSchool = School::findOne($mStudent->school_id);
		$mGrade = Grade::findOne($mStudent->grade);
		return str_replace([
			'$school',
			'$grade',
			'$class',
		], [
			$mSchool->name,
			$mGrade->name,
			$mStudent->class,
		], $format);
	}

	/**
	 * @var string 其实这是个数组，如果后期有声明文件资源都可以追加到这数组里
	 * @author 钟长青
	 */
	const STATEMENT_FILE_URL = 'return array(
		\'topic\' => \'/view3/legal_papers/topic_protocol.txt\'
	);';

	/**
	 * 获取本站官方 对应法律声明的文件内容
	 * @param string $category 传入对应模块或栏目参数
	 * @return string 返回声明文件的内容
	 * @author 钟长青
	 */
	public function getStatementFile($category){
		$aUrl = eval(self::STATEMENT_FILE_URL);
		$fileUrl = $aUrl[$category];
		$statementContent = file_get_contents(Yii::getAlias('@p.resource') . '/' . $fileUrl);
		return $statementContent;
	}
	
	/*
	 * 通过UI组件来获取系统经典头像
	 * @param int $defaultProfileId 图片id		common/config/main.php ui组件里的aDefaultProfile下标
	 * @return string img url 相对于 resource目录
	 * @author 谭威力
	 */
	public function getDefaultProfile($defaultProfileId){
		$aConfigData = $this->aDefaultProfile;
		return $aConfigData[$defaultProfileId];
	}
	
	/*
	 * 通过UI组件来随机获取系统经典头像
	 * @param int $isBoy 是不是一个男人 1/0 是/否
	 * return int ui组件里的aDefaultProfile下标
	 * @author 谭威力
	 */
	public function getRandDefaultProfileId($isBoy = true){
		$aProfileBoyIds = $this->aProfileBoyIds;
		$aProfileGirlIds = $this->aProfileGirlIds;
		if($isBoy){
			return $aProfileBoyIds[array_rand($aProfileBoyIds)];
		}else{
			return $aProfileGirlIds[array_rand($aProfileGirlIds)];
		}
	}
}