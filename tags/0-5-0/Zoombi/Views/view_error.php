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
				echo '&nbsp;&nbsp;<b>#' . $t['line'] . ':&nbsp;</b> ';

			if( isset($t['class']) )
				echo $t['class'] . $t['type'];

			$args = array();
			foreach( $t['args'] as $a )
			{
				switch( gettype($a) )
				{
					default:
						$args[]= $a;
						break;

					case null:
						$args[]= 'NULL';
						break;

					case 'string':
						$args[]= strlen($a) > 40 ? ZString::truncate($a, 40) : $a;
						break;

					case 'array':
						$args[]= 'Array('.count($a).')';
						break;

					case 'object':
						$args[]= get_class( $a ) . '(' . count( get_class_vars($a) ) . ')';
						break;
				}
			}
			echo $t['function'] . '( ' . implode(', ', $args) . ' )';
			echo '<br />';
		}
	?>
	</code>
	<?php endif ?>
</div>
