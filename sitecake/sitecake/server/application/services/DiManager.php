<?php

/**
 * Dependenci injection container (well, sort of :) ).
 * Maintains a pool of singleton instances that are created
 * upon the first request.
 */
class DiManager
{
	private static $singletons = array();
	
	/**
	 * Returns an instance of the given type(interface).
	 * The instace is created instantiating the respective
	 * implementation class. The implementation class name
	 * is constructed from the type's name appending the postfix
	 * 'Impl'.
	 * 
	 * @param string $type the name of the interface
	 * @param bool $singleton selects if a singleton instance should be returned
	 */
	public static function get($type, $singleton=true)
	{
		if ( !$singleton )
		{
			$className = $type."Impl";
			return new $className;
		}
		elseif ( !isset(self::$singletons[$type]) )
		{
			$className = $type."Impl";
			self::$singletons[$type] = new $className;
		}
		return self::$singletons[$type];
	}
}