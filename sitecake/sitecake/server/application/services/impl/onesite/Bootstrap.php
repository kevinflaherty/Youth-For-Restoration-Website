<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	
	protected function _initErrorHandling()
	{
		set_error_handler('Bootstrap::globalErrorHandler', E_ALL);
	}

	protected function _initConfig()
	{
		$config = new Zend_Config_Ini( APP_SETTINGS_FILE );	
		Zend_Registry::set('config', $config);
	}
	
	protected function _initSession()
	{
		if ( Zend_Session::sessionExists() )
		{
			$session = new Zend_Session_Namespace('Default', false);
			Zend_Registry::set('session', $session);
		}
	}
	
	protected function _initClassLoading()
	{
		$loader = Zend_Loader_Autoloader::getInstance();
		//$loader->registerNamespace('Application_');
		$loader->setFallbackAutoloader(true);
	}
	
	protected function _initCaching()
	{
		$manager = new Zend_Cache_Manager();
		
		$manager->setCacheTemplate('genericCache', 
			array(
				'frontend' => array(
					'name' => 'Core',
					'options' => array( 
						'automatic_serialization' => true
            		)
            	),
            	'backend' => array(
            		'name' => 'File',
            		'options' => array(
						'cache_dir' => APP_CACHE_DIR,
            			'file_name_prefix' => 'cache_generic'
					)
				)
			)
		);
        
		$manager->setCacheTemplate('tplCache', 
			array(
				'frontend' => array(
					'name' => 'File',
					'options' => array( 
						'automatic_serialization' => false,
						'master_files_mode' => 'OR'
            		)
            	),
            	'backend' => array(
            		'name' => 'File',
            		'options' => array(
						'cache_dir' => APP_CACHE_DIR,
            			'file_name_prefix' => 'cache_tpl'
					)
				)
			)
		);

		$manager->setCacheTemplate('pageCache', 
			array(
				'frontend' => array(
					'name' => 'Core',
					'options' => array( 
						'automatic_serialization' => false,
						'master_files_mode' => 'OR'
            		)
            	),
            	'backend' => array(
            		'name' => 'File',
            		'options' => array(
						'cache_dir' => APP_CACHE_DIR,
            			'file_name_prefix' => 'cache_page'
					)
				)
			)
		);
		
		Zend_Registry::set('cacheManager', $manager);
		
		$defaultCache = $manager->getCache('genericCache');
		Zend_Registry::set('defaultCache', $defaultCache);
	}
	
	protected function _initRouting()
	{
		$frontController = Zend_Controller_Front::getInstance();
		$router = $frontController->getRouter();
		
		$router->addRoute( 'renderer', new Zend_Controller_Router_Route_Regex(
			'.*',
			array(
				'controller' => 'render', 
				'action' => 'render'
			)
		));
		$router->addRoute( 'services', new ServiceRoute( "/^.*\/sitecake_entry\.php$/i" ) );
	}
	
	protected function _initLogging()
	{
		$logger = new Zend_Log(new Zend_Log_Writer_Stream(APP_TMP_DIR.DS.'log.txt'));
		Zend_Registry::set('defaultLogger', $logger);
	}
	
	public static function globalErrorHandler($errno, $errstr, $errfile = '', $errline = '')
	{
		throw new Zend_Exception($errstr, $errno);
	}	
}

