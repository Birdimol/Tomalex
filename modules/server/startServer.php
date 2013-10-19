<?php
	//Fichier à lancer en console pour démarrer le serveur
	//l'instruction à lancer est chez moi : 
	//c:\wamp\bin\php\php5.4.12\php.exe startServer.php 127.0.0.1 8000
	error_reporting(E_ALL ^ E_STRICT);
	include "../../classes/server.class.php";
	$server = new Server();
	$server->runServer();
	
?>