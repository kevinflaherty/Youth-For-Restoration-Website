<?php

class LicenseController extends AbstractServiceController
{

    public function getAction()
    {
		$service = DiManager::get( 'LicenseService' );
		$this->result = $service->get();
    	$this->renderResponse();
    }
    
}

?>