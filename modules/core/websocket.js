var websocket;
var player;
var players;
var playerManager = new PlayerManager()

function onOpen(evt) 
{ 
	document.getElementById("console").innerHTML += "Connexion reussie<br />";
	
	sendPlayer(player);
} 

function onClose(evt) 
{ 
	//alert("CLOSE");
	document.getElementById("console").innerHTML += "Déconnection.";
} 

function onMessage(evt) 
{
	//On crée un objet a partir de la chaine JSON reçue
	var Message = JSON.parse(evt.data);
	
	//Si il s'agit d'un message pour le chat, on l'affiche.
	if(Message.type == "text")
	{
		if(Message.author != null)
		{
			//message normal
			document.getElementById("console").innerHTML += "<span style='color:blue;'>&lt;"+Message.author+"&gt;</span> "+Message.data+"<br />";
		}
		else
		{
			//message du server
			document.getElementById("console").innerHTML += "<span style='color:red;'>"+Message.data+"</span><br />";
		}
	}
	
	if(Message.type == "playerList")
	{		
		var array_ = JSON.parse(Message.data);
		playerManager.manageList(array_);
		
		
		/*
		 * document.getElementById("playerList").innerHTML ="";	
		for(var a=0; a<array_.length; a++)
		{
			temp = new Player();
			temp.initFromObject(array_[a]);
			document.getElementById("playerList").innerHTML += temp.getStringLine();
		}*/
	}
} 

function onError(evt)
{ 
	//alert("ERROR");
} 

function closeConnexion(websocket)
{
	websocket.close();
}

function sendPacket(packet)
{
	//On crée la chaine JSON
	var jsonPacket = JSON.stringify(packet);
	
	//On l'envoie au serveur
	websocket.send(jsonPacket);
}

function sendMessage(websocket)
{	
	//on place les données a envoyer dans un tableau
	var dataArray = new Array(document.getElementById("message").value)
	
	//On crée l'objet Message en spécifiant qu'il s'agit d'un texte pour le chat
	var packet = new Message("text",dataArray);
	
	//On envoie
	sendPacket(packet);
	
	//On vide le champ message
	document.getElementById("message").value = '';
}

function sendPosition(x,y)
{	
	//on place les données a envoyer dans un tableau
	var dataArray = new Array(x,y);
	
	//On crée l'objet Message en spécifiant qu'il s'agit d'un déplacement du joueur
	var packet = new Message("move",dataArray);
	sendPacket(packet);
}

function sendPlayer(player)
{	
	//on place les données a envoyer dans un tableau
	var dataArray = new Array(player.pseudo,player.color);
	
	//On crée l'objet Message en spécifiant qu'il s'agit d'un déplacement du joueur
	var packet = new Message("initPlayer",dataArray);
	sendPacket(packet);
}

function connect()
{
	player = new Player();
	if("WebSocket" in window) 
	{ 	
		var wsUri = "ws://localhost:8000"; 

		websocket = new WebSocket(wsUri); 
		websocket.onopen = function(evt) { onOpen(evt) }; 
		websocket.onclose = function(evt) { onClose(evt) }; 
		websocket.onmessage = function(evt) { onMessage(evt) }; 
		websocket.onerror = function(evt) { onError(evt) }; 
		player.pseudo = document.getElementById("pseudo").value;
		player.color = document.getElementById("color").value;
		document.getElementById("console").innerHTML = "Tentative de connexion de "+player.pseudo+"...<br />";
	} 
	else 
	{ 
		alert("Votre navigateur ne supporte pas les WebSockets"); 
	}
}
