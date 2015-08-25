<?php

/*
 * File: view_route_debug.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Route view
 */


?><div style="border:1px solid #f00; padding: 10px 10px;">
	<div style="font-family:serif;font-size:18px;text-transform:uppercase;">
		Route debug:
	</div>
	<div style="padding-left: 10px;">
		<code style="font-size: 1.2em;"><?php echo $route ?></code><br />
		<b>Module:</b> <code><?php echo $route->module ?></code><br />
		<b>Controller:</b> <code><?php echo $route->controller ?></code><br />
		<b>Action:</b> <code><?php echo $route->action ?></code><br />
		<b>Params:</b> <code><?php echo implode( Zoombi::SS, $route->getParams()) ?></code><br />
		<b>Query:</b> <code><?php echo $route->queryString() ?></code>
	</div>
</div>
