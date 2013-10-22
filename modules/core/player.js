function Player(pseudo_,x,y) 
{ 
    this.pseudo = pseudo_;
    this.xCoord = x;
    this.yCoord = y;
    
    this.getStringLine = function() 
    { 
        return this.pseudo+"("+this.xCoord+","+this.yCoord+")<br />"; 
    }
    
    this.initFromObject = function(object) 
    { 
    	this.pseudo = object.pseudo;
        this.xCoord = object.xCoord;
        this.yCoord = object.yCoord;
    } 
}

