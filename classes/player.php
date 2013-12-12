<?php
	class Player
	{
		private $pseudo;
		private $xCoord;	
		private $yCoord;
		private $color;
		
		public function __construct($pseudo, $xCoord = 5, $yCoord = 5, $color)
		{
			$this->pseudo = $pseudo;
			$this->xCoord = $xCoord;
			$this->yCoord = $yCoord;
			$this->color  = $color;
		}
		
		public function move($xCoord, $yCoord)
		{
			$this->xCoord = $xCoord;
			$this->yCoord = $yCoord;
		}
		
		public function __get($name)
		{
			return $this->$name;
		}
		
		public function toJSON()
		{
			//les attributs privés posant problème avec json_encode, je fais ceci :
			return json_encode($this->toObject());
		}
		
		public function toObject()
		{
			$object = array();
			//les attributs privés posant problème avec json_encode, je fais ceci :
			foreach($this as $key => $value)
			{
				$object[$key] = $value;
			}
			return $object;
		}
	}
?>