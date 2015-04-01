<?php

class ContentController extends AbstractServiceController
{

    public function saveAction()
    {
		$service = DiManager::get( 'ContentService' );
		$credential = $this->getRequest()->getParam( 'credential' );
		
		$userParams = $this->getRequest()->getParams();
		$params = array();
		
		foreach ( $userParams as $param => $value )
		{
			if ( $param == '__sc_page' || substr($param, 0, 15) == '__sc_container_' )
			{
				$params[$param] = $value;
			}
		}
		
		$this->result = $service->save( $params );
    	$this->renderResponse();
    }
    
    public function publishAction()
    {
		$service = DiManager::get( 'ContentService' );
		
		$userParams = $this->getRequest()->getParams();
		$params = array();
		
		foreach ( $userParams as $param => $value )
		{
			if ( $param == '__sc_page' || substr($param, 0, 15) == '__sc_container_' )
			{
				$params[$param] = $value;
			}
		}
		
		$this->result = $service->publish( $params );
    	$this->renderResponse();
    }
    
}

?>