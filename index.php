<?php
require_once('common.php');

//Turn on output buffering
ob_start();

//Get list of modules based on dirnames in modules folder
$modules = array_diff(scandir(__DIR__.'/modules'),array('.','..'));

?>
<!doctype html>

<html lang="en">
	<head>
		<title>Tomalex</title>
		<?php
			//Basic css modules loader
			foreach($modules as $module)
			{
				if(is_file(__DIR__.'/modules/'.$module.'/'.$module.'.css'))
					echo '<link rel="stylesheet" type="text/css" href="/modules/'.$module.'/'.$module.'.css" />';
			}
		?>
	</head>

	<body>
		<div id="viewport">
			<div class="map X1 Y1"></div>
		</div>
		
		<script type="text/javascript" src="/libs/jquery/jquery-2.0.3-debug.js"></script>
		<?php
			//Basic js modules loader
			foreach($modules as $module)
			{
				if(is_file(__DIR__.'/modules/'.$module.'/'.$module.'.js'))
					echo '<script type="text/javascript" src="/modules/'.$module.'/'.$module.'.js"></script>';
			}
		?>
		<script>core.init();</script>
	</body>
</html>
<?php

//Flush and end output buffering
ob_end_flush();
?>
