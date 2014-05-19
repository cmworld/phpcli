<?php
class Config
{
	private static $configCache;
	
	public static function load($filetype){
		if (!isset($configCache[$filetype]) && empty(self::$configCache[$filetype]))
		{
			$config = require (ROOT_PRO_PATH."/config/".$filetype.".properties.php");
			self::$configCache[$filetype] = $config;
		}
	}
	
	public static function getConfig($type,$key =null)
	{
		if(!isset(self::$configCache[$type])){
			self::load($type);
		}
		
		if(!$key){
			return isset(self::$configCache[$type]) ? self::$configCache[$type]: null;
		}else{
			return isset(self::$configCache[$type][$key]) ? self::$configCache[$type][$key]: null; 
		}
	}
}
?>
