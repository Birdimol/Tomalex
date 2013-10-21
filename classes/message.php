<?php
class Message
{
	private $type;
	private $data;
	
	public function __construct($type = null, $data = null)
	{
		$this->type = $type;
		
		//pour que tout les objets messages soient pareils, on converti 
		// les données sous forme de tableau, même d'un seul élément.
		if(!is_array($data))
		{
			$this->data[0] = $data;
		}
		else
		{
			$this->data = $data;
		}
	}
	
	public function LoadFromJSON($JSONObject)
	{
		$object = json_decode($JSONObject);
		$this->type = $object->type;
		$this->data = $object->data;
	}	
	
	public function __get($varName)
	{
		return $this->$varName;
	}
	
	public function toJSON()
	{
		//les attributs privés posant problème avec json_encode, je fais ceci :
		foreach($this as $key => $value) 
		{
			$json[$key] = $value;
		}
		return json_encode($json);
	}
}