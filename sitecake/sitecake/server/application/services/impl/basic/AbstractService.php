<?php

/**
 * Basic service implementations.
 */
class AbstractService
{
	protected $result;
	
	/**
	 * A helper method that wraps the given service method
	 * with try/catch block and forms the proper response in
	 * case of an execution error.
	 *
	 * @param string $method the method to be executed
	 * @param array $args the method's arguments
	 */
	protected function safeCall($method, $args=array())
	{
		try {
			$this->result = array();
			$this->result['status'] = 0;
			$callResult = call_user_func_array(array($this, $method), $args);
			if ( $callResult === false ) throw new Zend_Exception('Invalid method call '.$method);
		} catch (Exception $e) {
			$this->result = array();
			$this->result['status'] = -1;
			$this->result['errorMessage'] = (string)$e;
		}
		return $this->result;
	}
}