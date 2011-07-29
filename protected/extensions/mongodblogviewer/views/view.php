<?php if (!Yii::app()->request->isAjaxRequest): ?>
<div class="form wide">
<?php echo $searchForm; ?>
</div>

<?php
  $url = $this->controller->createUrl('',array('clearlog'=>1));
  echo CHtml::ajaxButton('Clear log',$url,array('update'=>'#id_mongodblog'),array('confirm' => 'Delete all log entries?'));
?>
<?php endif; ?>
<div id="id_mongodblog">
<!-- start log messages -->
<table class="yiiLog" width="100%" cellpadding="2" style="border-spacing:1px;font:11px Verdana, Arial, Helvetica, sans-serif;background:#EEEEEE;color:#666666; margin-top:0.5em">
	<tr>
		<th style="background:black;color:white;" colspan="5">
			Application Log
		</th>
	</tr>
	<tr style="background-color: #ccc;">
	    <th style="width:120px">Timestamp</th>
		<th>Level</th>
		<th>Category</th>
		<th>Message</th>
	</tr>
<?php
$colors=array(
		CLogger::LEVEL_PROFILE=>'#DFFFE0',
		CLogger::LEVEL_INFO=>'#FFFFDF',
		CLogger::LEVEL_WARNING=>'#FFDFE5',
		CLogger::LEVEL_ERROR=>'#FFC0CB',
);


$this->widget('zii.widgets.CListView', array(
		'dataProvider'=>$dataProvider,
		'itemView'=>'_view',
		'viewData'=>array('colors'=>$colors),
));

?>
</table>
</div>
<!-- end of log messages -->
