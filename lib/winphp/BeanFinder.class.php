<?php
class BeanFinder
{
    static private $factorys = array();
    static private $interfaceArray = array();
	
    public static function registerFactory($factory)
    {
        self::$factorys[] = $factory;
    }
    public static function register($interface, $object)
    {
    	self::$interfaceArray[$interface] = $object;
    }
    
    public static function clear()
    {
    	self::$factorys = array();
    	self::$interfaceArray = array();
    }
    public static function isClear()
    {
        return empty(self::$factorys) && empty(self::$interfaceArray);
    }
    
    public static function get($typeOfBean)
    {
    	foreach (self::$interfaceArray as $interface => $object)
    	{
    		if ($typeOfBean == $interface)
    			return $object;
    	}
        foreach (self::$factorys as $factory)
        {
            $object = $factory->get($typeOfBean);
            if (isset($object)) return $object;
        }
        throw new SystemException("class $typeOfBean not found!");
    }
}
?>
