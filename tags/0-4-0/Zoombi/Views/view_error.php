<div style="border:1px solid #f00; padding: 10px 10px;">
	<div style="font-family:serif;font-size:18px;text-transform:uppercase;">
		Error No:&nbsp;<?php echo $code ?>
	</div>
	<div><?php echo $message ?></div>
	<code>
		<b>At line: </b><?php echo $line ?><br />
		<b>In file: </b><?php echo $file ?><br />
	</code>
	<br />

	<?php if( isset($backtrace) AND is_array($backtrace) ) : ?>
	<div style="font-family:serif;font-size:16px;text-transform:uppercase;">Backtrace:</div>
	<code>
	<?php 
		$last = null;
		foreach( $backtrace as $i => $t )
		{

			if( isset($t['file']) )
			{
				if( $t['file'] != $last )
				{
					$last = $t['file'];
					echo '<i>' . $t['file'] . '</i>' . '<br />';
				}
			}

			if( isset($t['line']) )
			{
				echo '<b>(' . $t['line'] . ')</b> - ';
			}

			if( isset($t['class']) )
			{
				echo $t['class'] . $t['type'] . $t['function'];
			}
			else
			{
				echo $t['function'];
			}
			echo '<br />';
		}
	?>
	</code>
	<?php endif ?>
</div>
