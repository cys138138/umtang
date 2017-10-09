<?php
namespace manage\widgets;

class Table extends \yii\base\Widget{
	const STYLE_BASE = 1;
	const STYLE_NO_BORDER = 2;

	public $aColumns = [];
	public $aDataList = [];
	public $style = 0;
	public $fBuildRowId = null;

	public function run() {
		if(!$this->style){
			$this->style = static::STYLE_BASE;
		}

		$aTemplateList = [
			static::STYLE_BASE => 'base',
			static::STYLE_NO_BORDER => 'no_border',
		];
		return $this->render('table/' . $aTemplateList[$this->style], [
			'aColumns' => $this->aColumns,
			'aDataList' => $this->aDataList,
		]);
	}

	/**
	 * 解析一个单元格的内容
	 * @param array $aData 数据单元
	 * @param int $dataIndex 数据的序列号
	 * @param array $aColumnInfo 列信息
	 * @param string $columnId 列的ID
	 * @return string
	 * @author 黄文非
	 */
	public function parseColumnItemContent($aData, $dataIndex, $aColumnInfo, $columnId) {
		$content = '';
		if(isset($aColumnInfo['content'])){
			if(is_callable($aColumnInfo['content'])){
				echo ob_get_clean();
				ob_start();
				$content = $aColumnInfo['content']($aData, $dataIndex);
				$outputContent = ob_get_clean();
				if(is_null($content)){
					$content = $outputContent;
				}
				ob_start();
			}elseif(is_scalar($aColumnInfo['content'])){
				$content = $aColumnInfo['content'];
			}
		}elseif(isset($aData[$columnId])){
			$data = $aData[$columnId];
			if(isset($aColumnInfo['use_index']) && isset($aColumnInfo['use_index'][$data])){
				$content = (string)$aColumnInfo['use_index'][$data];
			}elseif(is_scalar($data)){
				$content = $data;
			}
		}
		return $content;
	}
}