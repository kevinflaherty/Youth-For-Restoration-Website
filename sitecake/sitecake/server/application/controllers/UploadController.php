<?php

class UploadController extends AbstractServiceController
{
    public function uploadAction()
    {
		$service = DiManager::get( 'UploadService' );
		$this->result = $service->upload();
    	$this->renderResponse();
    }
}

?>