<?php

	$segments = $route->getSegments();
	$params = $route->getParams();
	$query = $route->getQuery()->toArray();

?>

<div style="border: 1px solid #0011BB; padding: 10px 10px;">
	<div style="font-family:serif;font-size:18px;text-transform:uppercase;">Route debugging information</div>
	<br />

	<?php if( count($segments) > 0 ) : ?>
	<span style="font-size: 1.2em; font-weight: bold;">Segments:</span><br />
	<div style="padding-left: 10px; margin-bottom: 10px;">
		<?php foreach( $segments as $k => $v ) : ?>
		<b>#<?php echo $k ?></b>: <?php echo $v ?><br />
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	
	<span style="font-size: 1.2em; font-weight: bold;">Path:</span><br />
	<div style="padding-left: 10px; margin-bottom: 10px;">
		<b>Module</b>: <?php echo $route->getModule() ?><br />
		<b>Controller</b>: <?php echo $route->getController() ?><br />
		<b>Action</b>: <?php echo $route->getAction() ?><br /><br />
	</div>

	<?php if( count($params) > 0 ) : ?>
	<span style="font-size: 1.2em; font-weight: bold;">Params:</span><br />
	<div style="padding-left: 10px; margin-bottom: 10px;">
		<?php foreach( $params as $k => $v ) : ?>
		<b>#<?php echo $k ?></b>: <?php echo $v ?><br />
		<?php endforeach ?>
	</div>
	<?php endif; ?>

	<?php if( count($query) > 0 ) : ?>
	<span style="font-size: 1.2em; font-weight: bold;">Query:</span><br />
	<div style="padding-left: 10px; margin-bottom: 10px;">
		<?php $index = 0; ?>
		<?php foreach( $route->getQuery()->toArray() as $k => $v ) : ?>
		<b>#<?php echo $index++ ?></b>: <?php echo $k ?> = <?php echo $v ?><br />
		<?php endforeach ?>
	</div>
	<?php endif; ?>

	
</div>