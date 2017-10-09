<?php
namespace umeworld\lib\PHPExcel;

use Yii;
use PHPExcel_IOFactory;
use common\model\Student;
use common\model\Teacher;
use umeworld\lib\Query;
use common\model\School;
use yii\helpers\ArrayHelper;

class Excel extends \yii\base\Object{
	public $inputPath = '';					//excel文件导入路径，必传
	public $outputPath = '';				//excel文件导出路径，必传
	public $type = '';						//角色类型，必传
	public $aType = '';
	public $schoolId = '';					//学校id，必传
	public $teacherLength = '';				//教师excel表格的长度，必传
	public $studentLength = '';				//学生excel表格的长度，必传
	public $extension = '';					//文件后缀


	public $pageSize = 10;					//从excel表数组中 取 10条数据去 数据库中 校检重复性

	public function __construct(){
		require_once Yii::getAlias('@umeworld/lib/PHPExcel/') . 'PHPExcel.php';
		require_once Yii::getAlias('@umeworld/lib/PHPExcel/') . 'PHPExcel/IOFactory.php';
		require_once Yii::getAlias('@umeworld/lib/PHPExcel/') . 'PHPExcel/Reader/Excel5.php';
        parent::__construct();
    }
	
	/*
	 * excel文件导入，并全部数据放入数组中
	 */
	public function getAllDataInArray($inputPath = ''){
		if(!$inputPath){
			$inputPath = $this->inputPath;
		}
		//$inputPath = 'C:\Users\Administrator\Downloads\teacher.xls';
		if(!$inputPath){
			return [];
		}
		if($this->extension =='xlsx'){
			//$objReader = new \PHPExcel_Reader_Excel2007();
			$className = 'excel2007';
		}else{
			//$objReader = new \PHPExcel_Reader_Excel5();
			$className = 'Excel5';
		}
		$objReader = PHPExcel_IOFactory::createReader($className); // Excel5 => 2003   excel2007 => 2007以上
		$objPHPExcel = $objReader->load($inputPath); 
		$sheet = $objPHPExcel->getSheet(0); 
		$highestRow = $sheet->getHighestRow();           //取得总行数 
		$highestColumn = $sheet->getHighestColumn();	//取得总列数
		$aReturn = [];
		for($j = 1; $j <= $highestRow; $j++){												//从第二行开始读取数据
			$str = '';
			for($k = 'A'; $k <= $highestColumn; $k++){										//从A列读取数据
				$str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue() . '|*|';//读取单元格
			} 
			$str = mb_convert_encoding($str, 'utf8', 'auto');								//根据自己编码修改
			$aOnceData = explode('|*|',  trim($str, '|*|'));
			if($aOnceData[0] != ''){
				$aReturn[] = $aOnceData;
			}
		}
		return $aReturn;
	}
	
	/*
	 * 检查表的格式是否合法
	 */
	public function getCheckExcel($aAllData = []){
		if(!$aAllData){
			$aAllData = $this->getAllDataInArray();
		}
		if(!in_array($this->type, $this->aType)){
			return [
				'status' => 0,
				'msg' => 'type类型错误',
			];
		}
		if(!$aAllData){
			return [
				'status' => 0,
				'msg' => '没有数据,excel表是否没有填？',
			];
		}
		if($this->type == 'teacher' && count($aAllData[0]) != $this->teacherLength){
			return [
				'status' => 0,
				'msg' => 'excel表格错误,请确认是教师版excel表模板?',
			];
		}
		if($this->type == 'student' && count($aAllData[0]) != $this->studentLength){
			return [
				'status' => 0,
				'msg' => 'excel表格错误,请确认是学生版excel表模板?',
			];
		}
		if($this->type == 'teacher'){
			unset($aAllData[0]);
			foreach($aAllData as $key => $aData){
				if(count($aData) < $this->teacherLength - 1){
					return [
						'status' => 0,
						'msg' => 'excel文档的从第 ' . ++$key . ' 行开始,数据不完整',
					];
				}
			}
		}
		if($this->type == 'student'){
			unset($aAllData[0]);
			foreach($aAllData as $key => $aData){
				if(count($aData) < $this->studentLength){
					return [
						'status' => 0,
						'msg' => 'excel文档的从第 ' . ++$key . ' 行开始,数据不完整',
					];
				}
			}
		}
		return ['status' => 1];
	}
	
	/*
	 * 检查表数据是否合法,!!!!!!!! 在执行该方法之前必须先执行 getCheckExcel !!!!!!!!!!
	 * 合法返回
	 * array [
	 *		'is_repeat' => 0,excel表中不存在重复 工号(学号)或手机
	 *		'is_exist'	=> 0,excel表中的工号(学号)或手机 在数据表中不存在重复
	 * ]
	 * 
	 * 教师的不合法返回
	 * array [
	 *		'is_repeat' => 0,excel表中存在重复 工号或手机
	 *		'repeat_number' => [xxxxx,xxxxx] excel表中重复的工号 有xxxx，xxxxx
	 *		'repeat_mobile' => [xxxxx,xxxxx] excel表中重复的手机 有xxxx，xxxxx
	 *		'is_exist'	=> 0,excel表中的工号或手机 在数据表中存在重复
	 *		'mysql_exist_number' => [xxxxx,xxxxx] excel表中的工号在数据表中存在重复 有xxxx，xxxxx
	 *		'mysql_exist_mobile' => [xxxxx,xxxxx] excel表中手机 在数据表中存在重复 有xxxx，xxxxx
	 * ]
	 * 
	 * 学生的不合法返回
	 * array [
	 *		'is_repeat' => 0,excel表中存在重复 学号
	 *		'repeat_number' => [xxxxx,xxxxx] excel表中重复的学号 有xxxx，xxxxx
	 *		'is_exist'	=> 0,excel表中的学号 在数据表中存在重复
	 *		'mysql_exist_number' => [xxxxx,xxxxx] excel表中重复的学号 有xxxx，xxxxx
	 * ]
	 */
	public function getCheckResult($aAllData = []){
		if(!$aAllData){
			$aAllData = $this->getAllDataInArray();
		}
		unset($aAllData[0]);//因为第一行是标题来的，不是数据
		//
		//每次拿10条去数据表查询重复
		if($this->type == 'student'){
			$tableName = Student::tableName();
			return $this->_studentCheck($aAllData, $tableName);
		}elseif($this->type == 'teacher'){
			$tableName = Teacher::tableName();
			return $this->_teacherCheck($aAllData, $tableName);
		}else{
			return false;
		}
		//$aReturnResult = $aRepeat = [];
		//检查excel是否重复
		//检查excel和数据表中是否重复
	}
	
	/*
	 * $aAllData => excel数据
	 * $tableName => 表名
	 */
	private function _teacherCheck($aAllData, $tableName){
		$aJobNumbers = ArrayHelper::getColumn($aAllData, 0);
		$aMobiles = ArrayHelper::getColumn($aAllData, 5);
		$aReturnResult = [];
		//检查excel是否重复
		$aCheckJobNumbers = array_count_values($aJobNumbers);
		$aCheckMobiles = array_count_values($aMobiles);
		$isRepeat = 0;
		$isExist = 0;
		foreach($aCheckJobNumbers as $JobNumber => $count){
			if($count > 1){
				$aReturnResult['repeat_number'][] = $JobNumber;
				$isRepeat = 1;
			}
		}
		foreach($aCheckMobiles as $mobile => $count){
			if($count > 1){
				$isRepeat = 1;
				$aReturnResult['repeat_mobile'][] = $mobile;
			}
		}
		$aReturnResult['is_repeat'] = $isRepeat;
		//检查excel和数据表中是否重复
		$page = 1;
		$pageSize = $this->pageSize;
		$offset = ($page - 1) * $pageSize;
		$aSliceData = array_slice($aAllData, $offset, $pageSize);
		$aReturnResult['mysql_exist_number'] = $aReturnResult['mysql_exist_mobile'] = [];
		while($aSliceData){
			$page++;
			$aJobNumbers = ArrayHelper::getColumn($aSliceData, 0);
			$aMobiles = ArrayHelper::getColumn($aSliceData, 5);
			$aResultJobNumbersBySql = (new Query())->select(['id', 'mobile', 'job_number'])->from($tableName)->where(['and', ['school_id' => $this->schoolId], ['in' ,'job_number', $aJobNumbers], ['is_delete' => 0]])->all();
			foreach($aResultJobNumbersBySql as $aResultJobNumbers){
				$isExist = 1;
				$aReturnResult['mysql_exist_number'][] = $aResultJobNumbers['job_number'];
			}
			$aResultMobilesBySql = (new Query())->from($tableName)->select(['id', 'mobile', 'job_number'])->where(['and', ['school_id' => $this->schoolId], ['in' ,'mobile', $aMobiles], ['is_delete' => 0]])->all();
			foreach($aResultMobilesBySql as $aResultMobiles){
				$isExist = 1;
				$aReturnResult['mysql_exist_mobile'][] = $aResultMobiles['mobile'];
			}
			$offset = ($page - 1) * $pageSize;
			$aSliceData = array_slice($aAllData, $offset, $pageSize);
		}
		$aReturnResult['is_exist'] = $isExist;
		return $aReturnResult;
	}
	
	/*
	 * $aAllData => excel数据
	 * $tableName => 表名
	 */
	private function _studentCheck($aAllData, $tableName){
		$aStuNumbers = ArrayHelper::getColumn($aAllData, 0);
		$aReturnResult = [];
		//检查excel是否重复
		$isRepeat = 0;
		$isExist = 0;
		$aCheckStuNumbers = array_count_values($aStuNumbers);
		foreach($aCheckStuNumbers as $StuNumber => $count){
			if($count > 1){
				$aReturnResult['repeat_number'][] = $StuNumber;
				$isRepeat = 1;
			}
		}
		//检查excel和数据表中是否重复
		$page = 1;
		$pageSize = $this->pageSize;
		$offset = ($page - 1) * $pageSize;
		$aSliceData = array_slice($aAllData, $offset, $pageSize);
		$aReturnResult['mysql_exist_number'] = [];
		while($aSliceData){
			$page++;
			$aStuNumbers = ArrayHelper::getColumn($aSliceData, 0);
			$aResultStuNumberBySql = (new Query())->select(['id', 'mobile', 'stu_number'])->from($tableName)->where(['and', ['school_id' => $this->schoolId], ['in' ,'stu_number', $aStuNumbers], ['is_delete' => 0]])->all();
			foreach($aResultStuNumberBySql as $aResultStuNumbers){
				$isExist = 1;
				$aReturnResult['mysql_exist_number'][] = $aResultStuNumbers['stu_number'];
			}
			$offset = ($page - 1) * $pageSize;
			$aSliceData = array_slice($aAllData, $offset, $pageSize);
		}
		$aReturnResult['is_repeat'] = $isRepeat;
		$aReturnResult['is_exist'] = $isExist;
		return $aReturnResult;
	}
	
	/**
	 * 读excel表数据放入数组中
	 * @author jay
	 * @param $inputPath excel文件路径
	 * @param $page 页码
	 * @param $pageSize 页个数
	 * @param $sheetIndex 读取excel文件表格下标
	 */
	public function getSheetDataInArray($inputPath = '', $page = 0, $pageSize = 0, $sheetIndex = 0){
		if(!$inputPath){
			return [];
		}
		$aPathInfo = pathinfo($inputPath);
		if($aPathInfo['extension'] =='xlsx'){
			$className = 'excel2007';
		}else{
			$className = 'Excel5';
		}
		$objReader = PHPExcel_IOFactory::createReader($className);
		$objPHPExcel = $objReader->load($inputPath);
		$sheet = $objPHPExcel->getSheet($sheetIndex);
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$aReturn = [];
		$offset = 0;
		if($page && $pageSize){
			$offset = ($page - 1) * $pageSize;
		}
		$count = 0;
		for($j = 1; $j <= $highestRow; $j++){
			$index = 0;
			$aRow = [];
			for($k = 'A'; $k <= $highestColumn; $k++){
				$aRow[$index++] = mb_convert_encoding($objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue(), 'utf8', 'auto');
			}
			if($page && $pageSize){
				if($count >= $offset){
					array_push($aReturn, $aRow);
				}
				if(count($aReturn) == $pageSize){
					break;
				}
				$count = $count + 1;
			}else{
				array_push($aReturn, $aRow);
			}
		}
		return $aReturn;
	}
	
	/**
	 * 将数组写到excel表中
	 * @author jay
	 * @param $outputPath excel文件路径
	 * @param $sheetIndex 读取excel文件表格下标
	 */
	public function setSheetDataFromArray($outputPath = '', $aData, $isOutPutDirectory = false, $sheetIndex = 0, $startCell = 'A1'){
		if(!$outputPath){
			return false;
		}
		$aPathInfo = pathinfo($outputPath);
		$className = 'Excel5';
		if($aPathInfo['extension'] =='xlsx'){
			$className = 'excel2007';
		}
		$objPHPExcel = PHPExcel_IOFactory::createPHPExcelObject();	
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $className);
		//$objPHPExcel->getSheet($sheetIndex)->fromArray($aData, NULL, $startCell);
		$row = $startCell[1];
		foreach($aData as $value){
			$col = $startCell[0];
			foreach($value as $v){
				$objPHPExcel->getSheet($sheetIndex)->setCellValueExplicit("$col$row", $v, 's');
				$col++;
			}
			$row++;
		}
		
		if($isOutPutDirectory){
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="' . $outputPath . '"');
			header('Cache-Control: max-age=1');
			$objWriter->save('php://output');
		}else{
			$objWriter->save($outputPath);
			return true;
		}
	}
	
	/**
	 * 将Html table写到excel表中
	 * @author jay
	 * @param $outputPath excel文件路径
	 * @param $sheetIndex 读取excel文件表格下标
	 */
	public function setSheetDataFromHtmlTable($outputPath, $htmlTable, $isOutPutDirectory = false){
		if(!$outputPath){
			return false;
		}
		$aPathInfo = pathinfo($outputPath);
		$excelWriterType = 'Excel5';
		if($aPathInfo['extension'] =='xlsx'){
			$excelWriterType = 'Excel2007';
		}
		$oHtmlPHPExcelObject = PHPExcel_IOFactory::createHtmlPHPExcelObject();
		$oHtmlPHPExcelObject->setExcelObject(PHPExcel_IOFactory::createPHPExcelObject());
		$oHtmlPHPExcelObject->setHtmlStringOrFile($htmlTable);
		if($isOutPutDirectory){
			$oHtmlPHPExcelObject->process()->output($outputPath, $excelWriterType);
		}else{
			$oHtmlPHPExcelObject->process()->save($outputPath, $excelWriterType);
			return true;
		}
	}
}
?>