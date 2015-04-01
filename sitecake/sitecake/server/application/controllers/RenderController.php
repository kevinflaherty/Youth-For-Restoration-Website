<?php

class RenderController extends Zend_Controller_Action
{
    
    public function renderAction()
    {
		$service = DiManager::get( 'RenderService' );
		$requestUrl = $this->getRequest()->getParam('url', $_SERVER['PHP_SELF']);
		$params = $this->getRequest()->getParams();
		$loginAttempt = isset($params['login']);
		
		$page = $service->render($requestUrl, $loginAttempt);
		$page = $this->evalPage($page);
		
    	$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView(new StaticView($page));
    }
    
    private function evalPage($page) {
    	if ( strpos($page, "<?php") === false ) {
    		return $page;
    	}
    	
    	ob_start();
		eval('?>'.$page);
		$result = ob_get_contents();
		ob_end_clean();
	
		return $result;
    }

}

?>