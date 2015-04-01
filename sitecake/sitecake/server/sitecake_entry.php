<?php

date_default_timezone_set('UTC');

$phpVersion = preg_split("/[:.]/", phpversion());
if ( ($phpVersion[0]*10 + $phpVersion[1]) < 52 ) {
	die("PHP version $phpVersion[0].$phpVersion[1] is found on your webhosting. PHP version 5.2 (or greater) is required.");
}

defined( 'DS' ) || define( 'DS', DIRECTORY_SEPARATOR );
define('APPLICATION_PATH', realpath(dirname(__FILE__).DS.'application'));
define('APPLICATION_LIBRARY_PATH', dirname(APPLICATION_PATH).DS.'library');
define('SITECAKE_SERVER_BASE_DIR', dirname(APPLICATION_PATH));

include_once('config.php');
include_once(CREDENTIAL_FILE);

if ( !file_exists(CONTENT_DIR) ) mkdir(CONTENT_DIR, 0777, true);

if ( !file_exists(APP_TMP_DIR) ) mkdir(APP_TMP_DIR, 0777, true);

if ( !is_writable(CONTENT_DIR) ) {
	die("The sitecake content directory (".CONTENT_DIR.") is not writtable. Please check permissions.");
}

if ( !is_writable(APP_TMP_DIR) ) {
	die("The sitecake temp directory (".APP_TMP_DIR.") is not writtable. Please check permissions.");
}

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, $applicationIncludes));

define( 'APP_CACHE_DIR', APP_TMP_DIR );
define( 'APP_SETTINGS_FILE', dirname(APPLICATION_PATH).DS.'settings.ini' );

/** Zend_Application */
require_once 'Zend/Loader.php';
require_once 'Zend/Loader/Autoloader.php';
require_once 'Zend/Application.php';

$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

// Create application, bootstrap, and run
$application = new Zend_Application('development', APPLICATION_PATH.DS.'configs'.DS.'application.ini');
$bootstrap = $application->bootstrap();
$bootstrap->run();
            
exit();
