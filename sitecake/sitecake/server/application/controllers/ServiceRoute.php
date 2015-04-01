<?php

class ServiceRoute extends Zend_Controller_Router_Route_Abstract
{
	protected $_route = null;
	
	/**
	 * Instantiates route based on passed Zend_Config structure
	 *
	 * @param Zend_Config $config Configuration object
	 */
	public static function getInstance(Zend_Config $config)
	{
		return new self($config->route);
	}

	/**
	 * Prepares the route for mapping.
	 *
	 * @param string $route Map used to match with later submitted URL path
	 * @param array $defaults Defaults for map variables with keys as variable names
	 */
	public function __construct($route)
	{
		$this->_route = $route;
	}

    /**
     * Assembles a URL path defined by this route
     *
     * @param array $data An array of variable and value pairs used as parameters
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array(), $reset = false, $encode = false, $partial = false)
    {
        return $this->_route;
    }

	/**
	 * Matches a user submitted path with a previously defined route.
	 * Assigns and returns an array of defaults on a successful match.
	 *
	 * @param string $path Path used to match against this routing map
	 * @return array|false An array of assigned values or a false on a mismatch
	 */
	public function match($request, $partial = false)
	{
		$path = $request->getPathInfo();
		
		if ( !$partial )
		{
			if ( preg_match($this->_route, $path) )
			{
				$params = array();
				$params['controller'] = $request->getParam('controller');
				$params['action'] = $request->getParam('action');

				return $params;
			}
		}
		
		return false;
	}

	/**
	 * Extracts the controller name and action from the request.
	 * 
	 * @param $request the actual http request
	 * @return array An array of assigned values
	 */
	private function getParams($request)
	{
		$params = array();
		$params['controller'] = $request->getParam('controller');
		$params['action'] = $request->getParam('action');
		
		return $params;
	}
}

?>