/**
 * @brief Handle the dragging of the map
 * 
 * For each direction, if the map is smaller than the screen, it can't be dragged past the corresponding edges.
 * On the other way if it's bigger, clones are created to create map that loop on herself when exiting screen (this mode will require a minimap).
 * 
 * @author pg17
 * @date 2013-10-19
 */
var map =
{
	container : false, //dom element containing the map
	model : false, //the map (the real one, not the clones)
	
	loopXMode : false, //Is the map looping on X axis
	loopYMode : false, //Is the map looping on Y axis
	
	grabbed : false, //Is the map grabbed and currently dragged
	
	positions : { 
					mouse : {x : 0, y : 0}, //Mouse position when the dragging action start
					map : {x : 0, y : 0} //Map position when the dragging action start
				},
				
	//Initialize map and related events (all events are in a .map namespace @see http://api.jquery.com/on/)
	init : function ()
	{
		map.container = $('#viewport');
		map.model = $('.X1.Y1');
		
		//Launch a first resize event to test if a loop mode is required
		map.handleResize();
		 
		//Bind resize event to the function that (re)initialize loop mode
		$(window).off('resize.map').on('resize.map', function(){map.handleResize()});
		
		//To be dynamically applied the event is applied on the parent's sons and not the sons themselves @see http://api.jquery.com/on/
		map.container.off('mousedown.map', '.map').on('mousedown.map', '.map', function(event){map.dragInit(event)}); 
		
		$(window).off('mousemove.map').on('mousemove.map',function(event){map.dragMove(event)});
		
		$(window).off('mouseup.map').on('mouseup.map',function(){map.dragEnd()});
	},
	
	//Enable/disable and (re)initialize loop mode
	handleResize : function()
	{
		map.loopXMode = map.container.width() - map.model.width() < 0;
		map.loopYMode = map.container.height() - map.model.height() < 0;
		
		//Remove existing clones
		$('.X0, .X2, .Y0, .Y2').remove();
		
		//Create any necessary clones
		if(map.loopXMode)
		{
			map.container.append(map.model.clone().attr('class','map X0 Y1').css('left', (map.model.position().left - map.model.width())+'px'));
			map.container.append(map.model.clone().attr('class','map X2 Y1').css('left', (map.model.position().left + map.model.width())+'px'));
		}
		
		if(map.loopYMode)
		{		
			map.container.append(map.model.clone().attr('class','map X1 Y0').css('top', (map.model.position().top - map.model.height())+'px'));
			map.container.append(map.model.clone().attr('class','map X1 Y2').css('top', (map.model.position().top + map.model.height())+'px'));
		}
		
		if(map.loopXMode && map.loopYMode)
		{
			map.container.append(map.model.clone().attr('class','map X0 Y0').css({'left' : (map.model.position().left - map.model.width())+'px', 'top' : (map.model.position().top - map.model.height())+'px'}));
			map.container.append(map.model.clone().attr('class','map X2 Y0').css({'left' : (map.model.position().left + map.model.width())+'px', 'top' : (map.model.position().top - map.model.height())+'px'}));
			map.container.append(map.model.clone().attr('class','map X0 Y2').css({'left' : (map.model.position().left - map.model.width())+'px', 'top' : (map.model.position().top + map.model.height())+'px'}));
			map.container.append(map.model.clone().attr('class','map X2 Y2').css({'left' : (map.model.position().left + map.model.width())+'px', 'top' : (map.model.position().top + map.model.height())+'px'}));
		}	
		
		//Swap the far right or far left column (the one not visible) to the opposite to ensure continuity
		var left = map.model.position().left;
		if(left > map.model.width()/2 && left <= map.model.width()*1.5)
			$('.X2').each(function(){ $(this).css('left', ($(this).position().left - map.model.width()*3)+'px') });
		else if(left < -map.model.width()/2 && left >= -map.model.width()*1.5)
			$('.X0').each(function(){ $(this).css('left', ($(this).position().left + map.model.width()*3)+'px') });
		
		//Swap the far bottom or far top row (the one not visible) to the opposite to ensure continuity
		var top = map.model.position().top;
		if(top > map.model.height()/2 && top <= map.model.height()*1.5)
			$('.Y2').each(function(){ $(this).css('top', ($(this).position().top - map.model.height()*3)+'px') });
		else if(top < -map.model.height()/2 && top >= -map.model.height()*1.5)
			$('.Y0').each(function(){ $(this).css('top', ($(this).position().top + map.model.height()*3)+'px') });

	},
	
	//Initialize the map dragging
	dragInit : function(event)
	{
		map.grabbed = true;
		
		map.positions.map.x = map.model.position().left;
		map.positions.map.y = map.model.position().top;
		
		map.positions.mouse.x = event.pageX;
		map.positions.mouse.y = event.pageY;
	},
	
	//Drag the map
	dragMove : function(event)
	{
		if(!map.grabbed)
			return;
		
		//Calculate the map positions	
		var left = map.positions.map.x + (event.pageX - map.positions.mouse.x);
		var top = map.positions.map.y + (event.pageY - map.positions.mouse.y);
			
		if(map.loopXMode) //If loop mode on X axis
		{
			//Move accordingly each elements
			$('.X0').each(function(){ $(this).css('left', (left - map.model.width())+'px') });
			$('.X2').each(function(){ $(this).css('left', (left + map.model.width())+'px') });
			$('.X1').each(function(){ $(this).css('left', left+'px') });
			
			//If the loop as made full turn, reduce the coordinates (to not have crazy numbers)
			if(left > map.model.width()*1.5)
				$('.map').each(function(){ $(this).css('left', ($(this).position().left - map.model.width()*3)+'px') });
			else if(left < -map.model.width()*1.5)
				$('.map').each(function(){ $(this).css('left', ($(this).position().left + map.model.width()*3)+'px') });
			
			//Left value of the map after potential coordinates reducing
			var left = map.model.position().left;
			
			//Swap the far right or far left column (the one not visible) to the opposite to ensure continuity
			if(left > map.model.width()/2 && left <= map.model.width()*1.5)
				$('.X2').each(function(){ $(this).css('left', ($(this).position().left - map.model.width()*3)+'px') });
			else if(left < -map.model.width()/2 && left >= -map.model.width()*1.5)
				$('.X0').each(function(){ $(this).css('left', ($(this).position().left + map.model.width()*3)+'px') });
		}
		else
		{
			//Bound the elements in the screen on X axis
			left = Math.max(0, Math.min(left, map.container.width() - map.model.width()));
			$('.X1').each(function(){ $(this).css('left', left+'px') });
		}
		
		//Same as before for Y axis
		if(map.loopYMode) 
		{
			$('.Y0').each(function(){ $(this).css('top', (top - map.model.height())+'px') });
			$('.Y2').each(function(){ $(this).css('top', (top + map.model.height())+'px') });
			$('.Y1').each(function(){ $(this).css('top', top+'px') });
			
			if(top > map.model.height()*1.5)
				$('.map').each(function(){ $(this).css('top', ($(this).position().top - map.model.height()*3)+'px') });
			else if(top < -map.model.height()*1.5)
				$('.map').each(function(){ $(this).css('top', ($(this).position().top + map.model.height()*3)+'px') });
				
			var top = map.model.position().top;
			
			if(top > map.model.height()/2 && top <= map.model.height()*1.5)
				$('.Y2').each(function(){ $(this).css('top', ($(this).position().top - map.model.height()*3)+'px') });
			else if(top < -map.model.height()/2 && top >= -map.model.height()*1.5)
				$('.Y0').each(function(){ $(this).css('top', ($(this).position().top + map.model.height()*3)+'px') });
		}
		else
		{
			top = Math.max(0, Math.min(top, map.container.height() - map.model.height()));
			$('.Y1').each(function(){ $(this).css('top', top+'px') });
		}
	},
	
	//Stop dragging
	dragEnd : function()
	{
		map.grabbed = false;
	}
}
