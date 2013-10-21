var websocket;
function onOpen(evt) 
{ 
	document.getElementById("console").innerHTML += "Connexion reussie<br />";
} 

function onClose(evt) 
{ 
	//alert("CLOSE");
	document.getElementById("console").innerHTML += "D�connection";
} 

function onMessage(evt) 
{
	//On crée un objet a partir de la chaine JSON reçue
	var Message = JSON.parse(evt.data);
	
	//Si il s'agit d'un message pour le chat, on l'affiche.
	if(Message.type == "text")
	{
		document.getElementById("console").innerHTML += "< "+Message.data+"<br />";		
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
	
	//On affiche dans la page web.
	document.getElementById("console").innerHTML += "> "+document.getElementById("message").value+"<br />";
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

if("WebSocket" in window) 
{ 	
	var wsUri = "ws://localhost:8000"; 

	websocket = new WebSocket(wsUri); 
	websocket.onopen = function(evt) { onOpen(evt) }; 
	websocket.onclose = function(evt) { onClose(evt) }; 
	websocket.onmessage = function(evt) { onMessage(evt) }; 
	websocket.onerror = function(evt) { onError(evt) }; 
} 
else 
{ 
	alert("Votre navigateur ne supporte pas les WebSockets"); 
}