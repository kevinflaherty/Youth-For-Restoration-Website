<?php

class SessionController extends AbstractServiceController
{
    public function loginAction()
    {
		$service = DiManager::get( 'SessionService' );
		$credential = $this->getRequest()->getParam( 'credential' );
		$this->result = $service->login( $credential );
    	$this->renderResponse();
    }
    
    public function changeAction()
    {
		$service = DiManager::get( 'SessionService' );
		$currentCredential = $this->getRequest()->getParam( 'credential' );
		$newCredential = $this->getRequest()->getParam( 'newCredential' );
		$this->result = $service->change( $currentCredential, $newCredential );
    	$this->renderResponse();
    }
    
    public function logoutAction()
    {
		$service = DiManager::get( 'SessionService' );
		$this->result = $service->logout();
    	$this->renderResponse();
    }

    public function aliveAction()
    {
		$service = DiManager::get( 'SessionService' );
		$this->result = $service->alive();
    	$this->renderResponse();
	}
}

?>