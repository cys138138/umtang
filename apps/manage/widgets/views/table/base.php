<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
?>
<table class="table table-hover table-striped">
	<thead>
		<tr>
			<?php foreach($aColumns as $columnId => $aColumn){
				$aOptions = [];
				if(isset($aColumn['class'])){
					$aOptions['class'] = $aColumn['class'];
				}else{
					$aOptions['class'] = 'col-xs-';
				}

				echo Html::tag('th', $aColumn['title'], $aOptions);
			} ?>
		</tr>
	</thead>
	<tbody>
		<?php
		$fBuildRowId = $this->context->fBuildRowId;
		$customId = is_callable($fBuildRowId);
		foreach($aDataList as $i => $aData){
			$rowId = $customId ? ' id="' . $fBuildRowId($aData) . '"' : '';
		?>
			<tr class="J-row"<?php echo $rowId; ?>>
				<?php foreach($aColumns as $columnId => $aColumn){
					$aItemOptions = [];
					if(isset($aColumn['item'])){
						$aItemOptions = $aColumn['item'];
					}
					$content = $this->context->parseColumnItemContent($aData, $i, $aColumn, $columnId);
					echo Html::tag('td', $content, $aItemOptions);
				} ?>
			</tr>
		<?php } ?>
	</tbody>
</table>