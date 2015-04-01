<?php 
define( 'CONTENT_DIR', dirname(dirname(SITECAKE_SERVER_BASE_DIR)).DS.'sitecake-content');
define( 'APP_TMP_DIR', CONTENT_DIR.DS.'temp' );

define('LICENSE_PATH', dirname(SITECAKE_SERVER_BASE_DIR).DS.'license.key');
define( 'CREDENTIAL_FILE', SITECAKE_SERVER_BASE_DIR.DS.'credential.php' );
define( 'SITECAKE_EDITOR_CONFIG_FILE', dirname(SITECAKE_SERVER_BASE_DIR).DS.'editor.cfg' );

# define( 'SITECAKE_BASE_RELATIVE_URL', '/' );
if ( !defined('SITECAKE_BASE_RELATIVE_URL') )
{
	$docRootDir = realpath($_SERVER['DOCUMENT_ROOT']); 
	$entryDir = realpath(dirname(dirname(SITECAKE_SERVER_BASE_DIR)));
	if ( substr_compare($entryDir, $docRootDir, 0, strlen($docRootDir)) != 0 ) die('Unable to determine SITECAKE_BASE_RELATIVE_URL');
	define( 'SITECAKE_BASE_RELATIVE_URL', str_replace('\\', '/', substr($entryDir, strlen($docRootDir))));
}

define( 'SERVICE_URL', SITECAKE_BASE_RELATIVE_URL.'/sitecake/server/sitecake_entry.php' );
define( 'SITECAKE_PUBLIC_MANAGER_URL', SITECAKE_BASE_RELATIVE_URL.'/sitecake/client/publicmanager/publicmanager.nocache.js' );
define( 'SITECAKE_CONTENT_MANAGER_URL', SITECAKE_BASE_RELATIVE_URL.'/sitecake/client/contentmanager/contentmanager.nocache.js' );
define( 'CONTENT_BASE_URL', SITECAKE_BASE_RELATIVE_URL.'/sitecake-content');

define( 'DOCUMENT_ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] );

define( 'PHP_TEMPLATE', false );
define( 'TEMPLATE_CACHING', true );

$applicationIncludes = array(
	realpath(APPLICATION_PATH),
	realpath(APPLICATION_PATH.DS.'controllers'),
    realpath(APPLICATION_LIBRARY_PATH),
    realpath(APPLICATION_PATH.DS.'services'.DS.'impl'.DS.'onesite'),
    realpath(APPLICATION_PATH.DS.'services'.DS.'impl'.DS.'basic'),
    realpath(APPLICATION_PATH.DS.'services'),
    get_include_path(),
);

?>