<?php
	//include("client.class.php");
	
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
			$this->clientList[intval($socket)] = new Client($ip,$port,$socket);
			$this->showNumderOfClient();
		}
		
		public function showNumderOfClient()
		{
			$this->log('Now having '.count($this->clientList).' active client(s)');		
		}
		
		public function deleteClient($socket)
		{
			$this->log('Deleting client '.$this->clientList[intval($socket)]->ip.':'.$this->clientList[intval($socket)]->port);
			$this->clientList[intval($socket)]->delete();
			unset($this->clientList[intval($socket)]);		
			$this->log('Client deleted.');	
			$this->showNumderOfClient();
		}
		
		public function getClient($socket)
		{
			if(isset($this->clientList[intval($socket)]))
			{				
				return $this->clientList[intval($socket)];
			}
			else
			{
				$this->log('CLIENTMANAGER asked for an unexisting client !!!');
				return -1;
			}			
		}
		
		public function getPlayerList()
		{
			$playerList = array();
			foreach($this->clientList as $client)
			{
				if($client->player !== null)
				{
					$playerList[] = $client->player->toObject();
				}
			}
			/*
			$JSONString = "[";
			foreach($playerList as $player)
			{
				if($JSONString == "[")
				{
					$JSONString .= $player;
				}
				else
				{
					$JSONString .= ",".$player;
				}
			}
			$JSONString .= "]";
			*/
			return json_encode($playerList);
		}
		
		public function doHandshake($socket,$data)
		{
			$this->log('Performing handshake with '.$this->clientList[intval($socket)]->ip.':'.$this->clientList[intval($socket)]->port);
			
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
			$this->clientList[intval($socket)]->handshakedDone();
			$this->log('Handshake sent to '.$this->clientList[intval($socket)]->ip.':'.$this->clientList[intval($socket)]->port);
			
			return true;			
		}

		private function log($msg)
		{
			echo "[".date("Y-m-d H:i:s")."] > ".$msg. PHP_EOL; 
		}		
		
	}
?>