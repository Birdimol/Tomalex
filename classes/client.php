<?php
	class Client
	{
		private $ip;
		private $port;
		private $id;
		private $socket;
		private $handshaked;
		
		function __construct($ip,$port,&$socket)
		{
			$this->ip = $ip;
			$this->port = $port;
			$this->socket = $socket;
			$this->handshaked = false;
			$this->id = uniqid();
			
			$this->log("Client created $this->ip:$this->port");
		}
		
		function delete()
		{
			socket_close($this->socket);
		}
		
		function handshakedDone()
		{
			$this->handshaked = true;
		}
		
		function __get($name)
		{
			return $this->$name;
		}
		
		private function log($msg)
		{
			echo "[".date("Y-m-d H:i:s")."] > ".$msg. PHP_EOL; 
		}
	}
?>