<?php

class AbstractServiceController extends Zend_Controller_Action
{
	var $result;
	
	protected function renderResponse()
	{
		$this->sendJson( $this->result );
	}
	
    private function sendJson( $data )
    {
    	$data = $this->_helper->getHelper('json')->encodeJson( $this->result );
        
    	$jsonpCallback = $this->getRequest()->getParam('callback');
    	
    	if ( $jsonpCallback ) {
    		$data = $jsonpCallback . '(' . $data . ')';
    	}
    	
    	$response = $this->getResponse();
        $response->setBody($data);
    }

    /**
     * Implements the authorization check procedure. Only the login and change actions are
     * allowed to be executed without preceeding authentication. 
     */
    public function preDispatch() {
    	$controller = $this->_getParam('controller');
    	$action = $this->_getParam('action');

    	if ( !($controller == 'session' && ($action == 'login' || $action == 'change')) ) {
			$session = Zend_Registry::get( 'session' );
			
			$config = Zend_Registry::get('config');
			$skipAuthorization = $config->skipAuthorization;

			if ( !$skipAuthorization && !$session->loggedin )
			{
				throw new Zend_Exception('Not authorized');
			}
    	}
    	
    }
    
    
}

?>