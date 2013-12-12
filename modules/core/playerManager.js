function PlayerManager() 
{ 
    this.playerList = Array();
    
    this.addOrUpdatePlayer = function(player) 
    { 
    	if(!this.playerExisteInList(player,this.playerList))
    	{
    		//add
    		this.playerList.push(player);    		
    	}
    	else
    	{
    		//Update
    		this.playerList[this.getPlayerIndexInList(player)] = player;
    	}
    }
    
    this.getPlayerIndexInList = function(player) 
    { 
    	for(var a=0; a<this.playerList.length; a++)
		{    		
    		if(this.playerList[a].pseudo == player.pseudo)
        	{
    			return a;	            	
        	}	
		}
    	return false;
    }
    
    this.playerExisteInList = function(player,list)
    {
    	for(var a=0; a<list.length; a++)
		{
    		console.log(list[a].pseudo+" == "+player.pseudo);
    		if(list[a].pseudo == player.pseudo)
        	{
    			return true;	            	
        	}	
		}
    	return false;
    }
    
    this.deletePlayer = function(player) 
    { 
    	if(this.playerExisteInList(player,this.playerList))
    	{
    		this.playerList.splice(this.playerList.indexOf(player),1);  
    		$("#"+player.pseudo).fadeTo( 400, 0,function(){$("#"+player.pseudo).remove();});
    	}
    }
    
    this.manageList = function(list) 
    {     	
    	//On ajoute ceux qui manquent
    	for(var a=0; a<list.length; a++)
		{
    		temp = new Player();
			temp.initFromObject(list[a]);
    		this.addOrUpdatePlayer(temp);    		
		}
    	
    	//On deplace ceux qui ont bougé
    	
    	//on retire ceux qui ne sont plus sur le serveur
    	for(var a=0; a<this.playerList.length; a++)
		{
    		if(!this.playerExisteInList(this.playerList[a],list))
        	{
            	this.deletePlayer(this.playerList[a]);    		
        	}
		}
    	
    	//On affiche
    	document.getElementById("playerList").innerHTML ="";	
    	for(var a=0; a<this.playerList.length; a++)
		{
    		document.getElementById("playerList").innerHTML += this.playerList[a].getStringLine();
    		var tableOffset = $('#0-0').offset();
    		if($("#"+this.playerList[a].pseudo).length < 1)
    		{
    			$('#body').append("<span id='"+this.playerList[a].pseudo+"' style='opacity:0;width:19px;height:19px;border:2px "+this.playerList[a].color+" solid;background-color:"+this.playerList[a].color+";position:absolute;top:"+(tableOffset.top+(this.playerList[a].yCoord*23))+"px;left:"+(tableOffset.left+(this.playerList[a].xCoord*23)) +"px;'></span>");	
    			$("#"+this.playerList[a].pseudo).fadeTo( 400, 1 );
    		}
    		else
    		{
    			/*
    			$("#"+this.playerList[a].pseudo).css({
    				"top" : (tableOffset.top+(this.playerList[a].yCoord*23))+"px",
    				"left":(tableOffset.left+(this.playerList[a].xCoord*23)) +"px"
    			});
    			*/
    			$("#"+this.playerList[a].pseudo).animate({
				    left: (tableOffset.left+(this.playerList[a].xCoord*23)) +"px",
				    top:  (tableOffset.top +(this.playerList[a].yCoord*23)) +"px"
			 	},
			 	{
					duration : 1200
				});
    		}
    	}
    }
    
    this.initFromObject = function(object) 
    { 
    	this.pseudo = object.pseudo;
        this.xCoord = object.xCoord;
        this.yCoord = object.yCoord;
        this.color = object.color;
    } 
}