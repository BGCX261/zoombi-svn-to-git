<?php

/*
 * File: view_error.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Error view
 */

	if( isset($backtrace) )
	{
		foreach ( $backtrace as $i => $v )
		{
			if( isset($v['class']) && isset($v['function']) )
			{
				if( $v['class'] == 'Zoombi_Component' && $v['function'] == 'triggerError' )
				{
					unset($backtrace[ $i ]);
				}
			}

			if( isset($v['class']) && isset($v['function']) )
			{
				if( $v['class'] == 'Zoombi_Application' && $v['function'] == '_error_handler' )
				{
					list($code,$message,$file,$line) = $v['args'];
					unset($backtrace[$i]);
				}
			}
		}
	}

	$err_titles = array(
		E_ERROR => 'Error',
		E_WARNING => 'Warning',
		E_NOTICE => 'Notice',
		//E_DEPRECATED => 'Deprecated notice',
		E_USER_ERROR => 'User error',
		E_USER_WARNING => 'User warning',
		E_USER_NOTICE => 'User notice',
		//E_USER_DEPRECATED => 'User deprecated notice'
	);

	$err_colors = array(
		E_ERROR => '#f00',
		E_WARNING => '#0ff',
		E_NOTICE => '#00f',
		//E_DEPRECATED => '#ff0',
		E_USER_ERROR => '#f00',
		E_USER_WARNING => '#0ff',
		E_USER_NOTICE => '#00f',
		//E_USER_DEPRECATED => '#ff0'
	);

	$_title = isset( $err_titles[ $code ] ) ? $err_titles[ $code ] : 'Other error No: '. $code;
	$_color = isset( $err_colors[ $code ] ) ? $err_colors[ $code ] : '#000';
?>

<div style="border:2px solid <?php echo $_color ?>; padding: 10px 10px; margin: 10px 10px;">
	<div style="font-family:serif;font-size:18px;text-transform:uppercase;">
		<?php echo $_title /*?> No:&nbsp;<?php echo $code*/ ?>
	</div>
	<div><?php echo $message ?></div>
	<code>
		<b>At line: </b><?php echo $line ?><br />
		<b>In file: </b><?php echo str_replace( realpath($_SERVER['DOCUMENT_ROOT']), '', $file) ?><br />
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
				if( $t['file'] != $last['file'] )
				{
					$last = $t;
					echo '<br /><i>'. str_replace( realpath($_SERVER['DOCUMENT_ROOT']), '', $t['file']).'</i><br />';
				}
			}

			if( empty($t['line']) )
				$t['line'] = $last['line'];

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
						if( file_exists($a) && is_file($a) )
							$args[]= '"' . str_replace( realpath($_SERVER['DOCUMENT_ROOT']), '', $a).'"';
						else
							$args[]= '"' . (strlen($a) > 40 ? Zoombi_String::truncate($a, 40) : $a) . '"';
						break;

					case 'array':
						if( is_callable($a) )
						{
							list($obj,$act) = $a;
							if( is_object($obj) )
								$args[]= get_class($obj) . '->' . $act;
							else
								$args[]= $obj . '->' . $act;
						}	
						else
							$args[]= 'Array('.count($a).')';
						break;

					case 'object':
						$args[]= get_class( $a ) . '(' . count( get_class_vars(get_class($a)) ) . ')';
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
