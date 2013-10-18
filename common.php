<?php
header('Content-Type: text/html; charset=UTF-8');

//Basic autoloader
function autoload($class)
{
	require_once(__DIR__.'/classes/'.strtolower($class).'.php');
}

spl_autoload_register('autoload');
?>
