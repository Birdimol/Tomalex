<?php
	include("client.class.php");
	
	class ClientManager
	{
		//liste des clients indexés par leur socket
		private $clientList;
		
		public function __construct()
		{
			$this->clientList = array();
		}
		
		public function addClient($ip,$port,&$socket)
		{
			$this->clientList[$socket] = new Client($ip,$port,$socket);
			$this->showNumderOfClient();
		}
		
		public function showNumderOfClient()
		{
			$this->log('Now having '.count($this->clientList).' active client(s)');		
		}
		
		public function deleteClient($socket)
		{
			$this->log('Deleting client '.$this->clientList[$socket]->ip.':'.$this->clientList[$socket]->port);
			$this->clientList[$socket]->delete();
			unset($this->clientList[$socket]);		
			$this->log('Client deleted.');	
			$this->showNumderOfClient();
		}
		
		public function getClient($socket)
		{
			if(isset($this->clientList[$socket]))
			{				
				return $this->clientList[$socket];
			}
			else
			{
				$this->log('CLIENTMANAGER asked for an unexisting client !!!');
				return -1;
			}			
		}
		
		public function doHandshake($socket,$data)
		{
			$this->log('Performing handshake with '.$this->clientList[$socket]->ip.':'.$this->clientList[$socket]->port);
			
			$lines = preg_split("/\r\n/", $data);
			
			// Vérification du http-header:
			if(!preg_match('/\AGET (\S+) HTTP\/1.1\z/', $lines[0], $matches)) 
			{
				$this->log('Invalid request: ' . $lines[0]);
				$this->deleteClient($socket);
				return false;
			}				
			
			// on récupère les données envoyées sous forme de array
			$headers = array();
			foreach($lines as $line) 
			{
				$line = chop($line);
				if(preg_match('/\A(\S+): (.*)\z/', $line, $matches)) 
				{
					$headers[$matches[1]] = $matches[2];
				}
			}	
			
			// On vérifie la version du websocket
			if(!isset($headers['Sec-WebSocket-Version']) || $headers['Sec-WebSocket-Version'] < 6) 
			{
				$this->log('Unsupported websocket version.');
				$this->deleteClient($socket);
				return false;
			}			
			
			// on génère le message de réponse
			$secKey = $headers['Sec-WebSocket-Key'];
			$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
			$response = "HTTP/1.1 101 Switching Protocols\r\n";
			$response.= "Upgrade: websocket\r\n";
			$response.= "Connection: Upgrade\r\n";
			$response.= "Sec-WebSocket-Accept: " . $secAccept . "\r\n";
			$response.= "\r\n";
		
			//on envoie la réponse
			socket_write($socket, $response, strlen($response));		
			$this->clientList[$socket]->handshakedDone();
			$this->log('Handshake sent to '.$this->clientList[$socket]->ip.':'.$this->clientList[$socket]->port);
			
			return true;			
		}

		private function log($msg)
		{
			echo "[".date("Y-m-d H:i:s")."] > ".$msg. PHP_EOL; 
		}		
		
	}
?>