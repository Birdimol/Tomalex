function Player(pseudo_,x,y,color) 
{ 
    this.pseudo = pseudo_;
    this.xCoord = x;
    this.yCoord = y;
    this.color = color;
    
    this.getStringLine = function() 
    { 
        return "<span style='color:"+this.color+";' >"+this.pseudo+"</span>("+this.xCoord+","+this.yCoord+")<br />"; 
    }
    
    this.initFromObject = function(object) 
    { 
    	this.pseudo = object.pseudo;
        this.xCoord = object.xCoord;
        this.yCoord = object.yCoord;
        this.color = object.color;
    } 
}

