var websocket;
function onOpen(evt) 
{ 
	document.getElementById("console").innerHTML += "Connexion reussie<br />";
} 

function onClose(evt) 
{ 
	//alert("CLOSE");
	document.getElementById("console").innerHTML += "Déconnection";
} 

function onMessage(evt) 
{
	document.getElementById("console").innerHTML += "< "+evt.data+"<br />";
} 

function onError(evt)
{ 
	//alert("ERROR");
} 

function closeConnexion(websocket)
{
	websocket.close();
}

function sendMessage(websocket)
{
	websocket.send(document.getElementById("message").value);
	document.getElementById("console").innerHTML += "> "+document.getElementById("message").value+"<br />";
	document.getElementById("message").value = '';
}

if("WebSocket" in window) 
{ 
	alert("Votre navigateur supporte les WebSockets"); 
	
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