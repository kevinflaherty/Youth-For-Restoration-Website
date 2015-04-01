<?php

class SessionServiceImpl extends AbstractService implements SessionService
{
	public function login($credential)
	{
		return $this->safeCall('_login', array($credential));
	}

	public function change($credential, $newCredential)
	{
		return $this->safeCall('_change', array($credential, $newCredential));
	}

	public function logout()
	{
		return $this->safeCall('_logout');
	}
	
	public function alive()
	{
		return $this->safeCall('_alive');
	}
		
	protected function _login($crdnt)
	{
		global $credential;
				
		if ( $crdnt == $credential )
		{
			$cacheMgr = Zend_Registry::get('cacheManager');
			$cache = $cacheMgr->getCache('genericCache');
			if ( $cache->load('loginLock') )
			{
				$this->result['status'] = 2;
			}
			else
			{
				Zend_Session::start();
				$session = new Zend_Session_Namespace('Default', false);
				$session->loggedin = true;
				$cache->save( true, 'loginLock', array(), 20 );
			}
		}
		else
		{
			$this->result['status'] = 1;
		}
	}

	protected function _change($crdnt, $newCredential)
	{
		global $credential;
		
		if ( $credential == $crdnt )
		{
			$content = '<?php $credential = "'.$newCredential.'"; ?>';
			file_put_contents(CREDENTIAL_FILE, $content);
		}
		else
		{
			$this->result['status'] = 1;
		}
	}
		
	protected function _logout()
	{
		$session = Zend_Registry::get('session');
		
    	if ( $session->loggedin === true )
    	{
			$cacheMgr = Zend_Registry::get('cacheManager');
			$cache = $cacheMgr->getCache('genericCache');
    		$cache->remove('loginLock');
    	}
		Zend_Session::destroy(true);
	}
	
	protected function _alive()
	{
		if ( !Zend_Session::isStarted() ) 
			return;
		
		$session = Zend_Registry::get('session');
		
    	if ( $session->loggedin === true )
    	{
			$cacheMgr = Zend_Registry::get('cacheManager');
			$cache = $cacheMgr->getCache('genericCache');
	   		$cache->save( true, 'loginLock', array(), 20 );
    	}
	}
}

?>