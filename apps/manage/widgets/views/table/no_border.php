<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
?>
<div class="umNoBorderTable">
	<div class="row header">
	<?php foreach($aColumns as $columnId => $aColumn){
		$aOptions = [];
		if(isset($aColumn['class'])){
			$aOptions['class'] = 'column ' . $aColumn['class'];
		}else{
			$aOptions['class'] = 'column col-xs-1';
		}

		echo Html::tag('div', $aColumn['title'], $aOptions);
	?>
	<?php } ?>
	</div>

	<?php
	$fBuildRowId = $this->context->fBuildRowId;
	$customId = is_callable($fBuildRowId);
	foreach($aDataList as $i => $aData){
		$rowId = $customId ? ' id="' . $fBuildRowId($aData) . '"' : '';
	?>
		<div class="row J-row"<?php echo $rowId; ?>>
			<?php foreach($aColumns as $columnId => $aColumn){
				$aItemOptions = [];
				if(isset($aColumn['item'])){
					$aItemOptions = $aColumn['item'];
				}
				if(isset($aItemOptions['class'])){
					$aItemOptions['class'] = 'column ' . $aItemOptions['class'];
				}else{
					$aItemOptions['class'] = 'column col-xs-1';
				}
				$content = $this->context->parseColumnItemContent($aData, $i, $aColumn, $columnId);
				echo Html::tag('div', $content, $aItemOptions);
			} ?>
		</div>
	<?php } ?>
</div>