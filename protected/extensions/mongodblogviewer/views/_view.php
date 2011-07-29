<?php
	$color=($index%2)?'#F5F5F5':'#FFFFFF';

	if(isset($colors[$data['level']]))
		$color=$colors[$data['level']];

	$message = '<pre>'.CHtml::encode(wordwrap($data['message'])).'</pre>';
	$time = date('H:i:s.',$data['timestamp']).sprintf('%06d',(int)(($data['timestamp']-(int)$data['timestamp'])*1000000));
	$level = $data['level'];
	$category = $data['category'];

	echo <<<EOD
	<tr style="background:{$color}">
		<td align="center">{$time}</td>
		<td>{$level}</td>
		<td>{$category}</td>
		<td>{$message}</td>
	</tr>
EOD;
