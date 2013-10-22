<?php
	//include("clientManager.class.php");

	class Server
	{
		private $clients = array();
		private $activeSockets = array();
		private $host = '0.0.0.0';
		private $port = 8000;
		private $masterSocket;
		private $clientManager;
		
		function __construct()
		{
			$this->clientManager = new ClientManager;
			
			//création du socket d'écoute
			if(!($this->masterSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)))
			{
				die("socket_create() failed, reason: " . socket_strerror(socket_last_error()));
			}
			
			//définition des options du socket
			// 
			// SO_REUSEADDR : Reporte si les adresses locales peuvent être réutilisées ou pas.
			//
			if (!socket_set_option($this->masterSocket, SOL_SOCKET, SO_REUSEADDR, 1))
			{
				echo 'Impossible de définir l\'option du socket : '. socket_strerror(socket_last_error()) . PHP_EOL;
			}
			
			if(!socket_bind($this->masterSocket, $this->host, $this->port))
			{
				die("socket_bind() failed, reason: " . socket_strerror(socket_last_error($this->masterSocket)));
			}		
			
			//On active l'écoute du socket avec max 5 clients
			if(!socket_listen($this->masterSocket, 5))
			{
				die("socket_listen() failed, reason: " . socket_strerror(socket_last_error($this->masterSocket)));
			}		
			
			//on place le socket principal dans la table des sockets actifs dans l'appli
			$this->activeSockets[] = $this->masterSocket;
			
			//socket_getpeername($this->masterSocket, $ip, $port);
			$this->log("Activation du socket d'ecoute sur le port ".$this->port);
		}
		
		public function runServer()
		{
			$this->log("Lancement du serveur.");
			while (1) 
			{				
				//On crée une copie du tableau de sockets actifs car le tableau envoyé à socket_select sera modifié par référence.
				$changedSockets;
				$changedSockets = $this->activeSockets;
				
				//On attend un signal d'un des sockets actifs.
				@socket_select($changedSockets, $write = NULL, $except = NULL, null);
				foreach ($changedSockets  as $changedSocket)
				{
					//Si il s'agit du socket  principal, c'est une demande de connexion.
					if ($changedSocket == $this->masterSocket) 
					{
						//on accepte la connexion.
						if (($newSocket = socket_accept($this->masterSocket)) < 0) 
						{
							//en cas d'erreur
							$this->log('Socket error: ' . socket_strerror(socket_last_error($ressource)));
							continue;
						} 
						else 
						{
							//si tout roule, on enregistre le socket actif.
							$ip;
							$port;
							socket_getpeername($newSocket, $ip, $port);
							$this->activeSockets[] = $newSocket;
							
							$this->clientManager->addClient($ip,$port,$newSocket);	
						}
					} 
					else 
					//S'il s'agit d'un socket client
					{
						//On récupère le client concerné
						$this->log("Socket ".$changedSocket." activated .");
						
						$changedClient = $this->clientManager->getClient($changedSocket);
						
						//si le client est valide
						if($changedClient !== -1)
						{
							//On récupère les données envoyées
							$data = "";
							$bytes = @socket_recv($changedSocket, $data, 4096, 0);
							$this->log($bytes." received from ".$changedSocket.".");
							if ($bytes === 0) 
							{
								//deconnexion, socket à jarter de la liste
								$msg = new Message("text",$changedClient->player->pseudo." a quitté la partie.");
								$this->clientManager->deleteClient($changedSocket);
								$index = array_search($changedSocket, $this->activeSockets);
								unset($this->activeSockets[$index]);
								
								$this->SendPlayerList();								
								$this->SendMsgToAllClients($msg);
							} 
							else 
							{
								//si il a déjà fait son handshake
								if($changedClient->handshaked)
								{
									//On affiche le message ligne par ligne pour avoir une belle console
									$this->log("Message received from ".$changedClient->ip.":".$changedClient->port.".");
									$this->handleData($data,$changedClient);
								}
								else
								{
									//Sinon, il va envoyer tout d'abord son handshake, on doit le gérer
									$this->log("Handshaking with ".$changedClient->ip.":".$changedClient->port.".");
									$this->clientManager->doHandShake($changedSocket,$data);
								}							
							}						
						}
					}
				}
			}
		}
		
		
			
		private function handleData($data, $client)
		{
			//on converti pour pouvoir lire en PHP
			$decodedData = $this->hybi10Decode($data);
		
			//De quel message venu du navigateur s'agit-il ?
			switch($decodedData['type'])
			{
				//Du texte (tous les dialogues se font via le type text)
				case 'text':
					//On converti l'objet reçu.
					$message = new Message();
					$message->LoadFromJSON($decodedData["payload"]);				
					
					//On check le type de l'objet reçu 
					switch($message->type)
					{
						//un message pour le chat
						case "text" :							
							$message->setAuthor($client->player->pseudo);
													
							//On affiche le message reçu sur la console du serveur
							$this->log ("-------------------------------------");
							$dataLines = explode("\n",$message->data[0]);
							foreach($dataLines as $dataLine)
							{
								$this->log($dataLine);
							}
							$this->log("-------------------------------------");
							
							//On envoie le message reçu à tout le monde
							$this->sendMsgToAllClients($message);
							
							//Si on reçoit "hello" dans le message on répond poliment juste à celui qui a envoyé ce message.
							if(strpos($decodedData["payload"],"hello")!==false)
							{
								$msg = new Message("text","Hi browser !");									
								$this->SendMsgToOneClient($client, $msg);
							}
						break;
						//un mouvement du joueur
						case "move" :
							//On bouge le joueur
							$client->player->move($message->data[0],$message->data[1]);
							
							//On affiche le mouvement reçu sur la console du serveur
							$this->log ("-------------------------------------");
							$this->log("Client ".$client->ip.":".$client->port." moves in ".$message->data[0].",".$message->data[1]);
							$this->log("-------------------------------------");	

							//On signale le mouvement à tout le monde
							$msg = new Message("text",$client->player->pseudo." moves in ".$message->data[0].",".$message->data[1]);
							$this->SendMsgToAllClients($msg);
							$this->SendPlayerList();
							break;
						
						//Un client initialise son joueur
						case "initPlayer" :
							$client->initPlayer($message->data[0]);
							//On signale le nouveau joueur à tout le monde.
							$this->SendPlayerList();
							$msg = new Message("text",$message->data[0]." a rejoint la partie.");
							$this->SendMsgToAllClients($msg);
							break;
					}										
				break;	
			}
		}
		
		private function SendPlayerList()
		{
			$msg = new Message("playerList",$this->clientManager->getPlayerList());
			$this->SendMsgToAllClients($msg);
		}
		
		private function SendMsgToAllClients($msg)
		{
			//On converti l'objet Message reçu en chaine JSON, et on l'encode pour qu'il puisse être compris par le Javascript
			$this->log("sending '".$msg->toJSON()."' to all.");	
			$msg = $this->hybi10Encode($msg->toJSON());
			//pour chaque client actif
			foreach($this->activeSockets as $socket)
			{
				if($socket != $this->masterSocket)
				{
					//on envoie la chaine JSON
					socket_write($socket, $msg, strlen($msg));
				}
			}
		}
		
		private function SendMsgToOneClient($client, $msg)
		{
			//On converti l'objet Message reçu en chaine JSON, et on l'encode pour qu'il puisse être compris par le Javascript
			$msg = $this->hybi10Encode($msg->toJSON());
			
			//on envoie la chaine JSON au client 
			socket_write($client->socket, $msg, strlen($msg));
		}
			
		private function log($msg)
		{
			echo "[".date("Y-m-d H:i:s")."] > ".$msg. PHP_EOL; 
		}
		
		/**
		 * Hanshake made with protocol hybi10
		 */
		private function hybi10Encode($payload, $type = 'text', $masked = false)
		{
			$frameHead = array();
			$frame = '';
			$payloadLength = strlen($payload);
		
			switch($type)
			{
				case 'text':
					// first byte indicates FIN, Text-Frame (10000001):
					$frameHead[0] = 129;
					break;
		
				case 'close':
					// first byte indicates FIN, Close Frame(10001000):
					$frameHead[0] = 136;
					break;
		
				case 'ping':
					// first byte indicates FIN, Ping frame (10001001):
					$frameHead[0] = 137;
					break;
		
				case 'pong':
					// first byte indicates FIN, Pong frame (10001010):
					$frameHead[0] = 138;
					break;
			}
		
			// set mask and payload length (using 1, 3 or 9 bytes)
			if($payloadLength > 65535)
			{
				$payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
				$frameHead[1] = ($masked === true) ? 255 : 127;
				for($i = 0; $i < 8; $i++)
				{
				$frameHead[$i+2] = bindec($payloadLengthBin[$i]);
				}
				// most significant bit MUST be 0 (close connection if frame too big)
				if($frameHead[2] > 127)
				{
					$this->close(1004);
					return false;
				}
			}
			elseif($payloadLength > 125)
			{
				$payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
				$frameHead[1] = ($masked === true) ? 254 : 126;
				$frameHead[2] = bindec($payloadLengthBin[0]);
				$frameHead[3] = bindec($payloadLengthBin[1]);
			}
			else
			{
				$frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
			}
		
			// convert frame-head to string:
			foreach(array_keys($frameHead) as $i)
			{
				$frameHead[$i] = chr($frameHead[$i]);
			}
			
			if($masked === true)
			{
				// generate a random mask:
				$mask = array();
				for($i = 0; $i < 4; $i++)
				{
					$mask[$i] = chr(rand(0, 255));
				}		
				$frameHead = array_merge($frameHead, $mask);
			}
			$frame = implode('', $frameHead);
			
			// append payload to frame:
			$framePayload = array();
			for($i = 0; $i < $payloadLength; $i++)
			{
				$frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
			}			
			return $frame;
		}
		
		/**
		* Decode received message with protocol hybi10
		*/
		private function hybi10Decode($data)
		{
			$payloadLength = '';
			$mask = '';
			$unmaskedPayload = '';
			$decodedData = array();
				
			// estimate frame type:
			$firstByteBinary = sprintf('%08b', ord($data[0]));
			$secondByteBinary = sprintf('%08b', ord($data[1]));
			$opcode = bindec(substr($firstByteBinary, 4, 4));
			$isMasked = ($secondByteBinary[0] == '1') ? true : false;
			$payloadLength = ord($data[1]) & 127;
				
			// close connection if unmasked frame is received:
			if($isMasked === false)
			{
				//$this->close(1002);
			}
			
			switch($opcode)
			{
				// text frame:
				case 1:
				$decodedData['type'] = 'text';
				break;
				
				// connection close frame:
				case 8:
				$decodedData['type'] = 'close';
				break;
				
				// ping frame:
				case 9:
				$decodedData['type'] = 'ping';
					break;
				
				// pong frame:
				case 10:
				$decodedData['type'] = 'pong';
				break;
				
				default:
				// Close connection on unknown opcode:
				//$this->close(1003);
				break;
			 }
					 	
			 if($payloadLength === 126)
			 {
			 	$mask = substr($data, 4, 4);
			 	$payloadOffset = 8;
			 }
			 elseif($payloadLength === 127)
			 {
				$mask = substr($data, 10, 4);
				$payloadOffset = 14;
			 }
			 else
			 {
				$mask = substr($data, 2, 4);
				$payloadOffset = 6;
			 }
		 	
		 	$dataLength = strlen($data);
				
			if($isMasked === true)
			{
				for($i = $payloadOffset; $i < $dataLength; $i++)
				{
					$j = $i - $payloadOffset;
					$unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
				}
				$decodedData['payload'] = $unmaskedPayload;
			}
			else
			{
				$payloadOffset = $payloadOffset - 4;
				$decodedData['payload'] = substr($data, $payloadOffset);
			}

		return $decodedData;
		}
	}