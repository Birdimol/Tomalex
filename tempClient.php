<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Chat</title>
    <script type="text/javascript" src="modules/core/player.js"></script>
    <script type="text/javascript" src="modules/core/playerManager.js"></script>
    <script type="text/javascript" src="modules/core/message.js"></script>
    <script type="text/javascript" src="modules/core/websocket.js"></script>
	<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.10.3/themes/dark-hive/jquery-ui.min.css" />
	<script type="text/javascript" src='http://code.jquery.com/jquery-1.10.2.min.js'></script>
	<script type="text/javascript" src='http://code.jquery.com/ui/1.10.3/jquery-ui.min.js'></script>
    <style>
    	table
    	{
    		border-collapse:collapse;
    	}
    	
    	.case
    	{
    		width:20px;
    		height:20px;
    		border:1px grey solid;
    	}
    </style>
  </head>
  <body id='body'>
    <div id="playerList" style="border:1px black solid;width:200px;float:left;height:300px;padding:10px;"></div>
    <div id="map" style="border:1px black solid;padding:10px;margin-left:220px;min-height:300px;">
    	<table>
    		<?php for($a = 0; $a <10; $a++): ?>
    			<tr>
    				<?php for($b = 0; $b <10; $b++): ?>
    					<td class='case' onclick='sendPosition(<?php echo $b; ?>,<?php echo $a; ?>)' id='<?php echo $a; ?>-<?php echo $b; ?>' ></td>
    				<?php endfor; ?>
    			</tr>
    		<?php endfor; ?>
    	</table>
    </div>
    <div id="console" style="border:1px black solid;height:300px;padding:10px;">
    	<label for="pseudo">pseudo : </label>
    	<input type="text" name="pseudo" id="pseudo" />
    	<label for="color">couleur : </label>
    	<input type="color" name="color" id="color" />
    	<input type='button' value='se connecter'  onclick='connect()' />
    </div>
	<label for="message">message : </label>
	<input type='text' id='message' name='message' />
	<input type='button' value='envoyer message'  onclick='sendMessage(websocket)' />
	<input type='button' value='envoie une position'  onclick='sendPosition(1,3)' />
  </body>
</html>