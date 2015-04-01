<?php

define( 'PUBLIC_MODE', 0 );
define( 'ADMIN_MODE', 1 );

class RenderServiceImpl implements RenderService
{
	private $requestUri;
	private $renderMode;
	private $loginAttempt;
	private $cacheMgr;
	private $pageTemplate;
	
	public function __construct() {
		$this->logger = Zend_Registry::get( 'defaultLogger' );
	}
	
	public function render($requestPath, $loginAttempt)
	{
		$this->requestUri = $requestPath;
		$this->determineRenderMode();
		$this->loginAttempt = $loginAttempt;
		return $this->renderResponse();
	}
	
	/**
	 * Decides if either the public or admin mode should be displayed.
	 */
	private function determineRenderMode()
	{
		$this->renderMode = PUBLIC_MODE;
		if ( Zend_Session::sessionExists() )
		{
			$session = Zend_Registry::get('session');
    		if ( isset( $session->loggedin ) && $session->loggedin === true )
    			$this->renderMode = ADMIN_MODE;
    	}
	}
	
	/**
	 * Render the output page.
	 * The page is re-rendered is any of the following conditions is satisfied:
	 * - the rendered mode is <code>ADMIN_MODE</code>
	 * - the request is an login attempt
	 * - the page output has not yet been cached
	 * - there is no information about the last publishing time
	 * - the publishing time is more recent than the cached page output
	 * - the page template has been changed
	 */
	private function renderResponse()
	{
		$pageId = md5($this->requestUri);
		$this->pageTemplate = DiManager::get('PageTemplate', false);
		$this->pageTemplate->setPageRequest($this->requestUri);
		
		$this->cacheMgr = Zend_Registry::get('cacheManager');

		// cache for page output caching
		$pageCache = $this->cacheMgr->getCache('pageCache');
		
		// cache for page publishing time
		$this->genericCache = $this->cacheMgr->getCache('genericCache');
		
		if ( $this->renderMode == ADMIN_MODE ||
			$this->loginAttempt || 
			($pageMeta = $pageCache->getMetadatas($pageId)) === false ||
			($publishMtime = $this->genericCache->load('lastPublished')) === false ||
			$publishMtime > $pageMeta['mtime'] ||
			$this->pageTemplate->isUpdated() )
		{
			$page = $this->assemblePage();
			
			// it makes sense to cache page only in PUBLIC_MODE
			// and when it is not a login attempt
			if ( $this->renderMode == PUBLIC_MODE && !$this->loginAttempt )
			{
				$pageCache->save($page, $pageId);
			}
			
			// if the mode is ADMIN_MODE and trigger resource
			// cleanup process
			if ( $this->renderMode == ADMIN_MODE )
			{
				DiManager::get('ResourceCleaner')->cleanup();
			}			
		}
		else
		{
			$page = $pageCache->load($pageId);
		}
		
		return $page;
	}
	
	/**
	 * Renders the output page using the actual page template
	 * and the containers' content. If the content of a container
	 * is not yet saved, the template's content will be used instead.
	 */
	private function assemblePage()
	{
		if ( $this->pageTemplate->isEditable() )
		{
			$contentManager = DiManager::get('ContentManager');
			$pageContainers = $this->pageTemplate->getPageContainers();
			$header = $this->getHeadContent();
			$this->pageTemplate->setHeader($header);

			foreach ($pageContainers as $pageContainer) {
				if ( $this->renderMode == ADMIN_MODE )
				{
					if ( $contentManager->isDraftContentSet($pageContainer) )
						$this->pageTemplate->setPageContainer($pageContainer, 
							$contentManager->getDraftContent($pageContainer));
				}
				else
				{ 
					if ( $contentManager->isPublicContentSet($pageContainer) )
						$this->pageTemplate->setPageContainer($pageContainer, 
							$contentManager->getPublicContent($pageContainer));					
				}
			}
		}
		return $this->pageTemplate->renderPage();			
	}
	
	private function getHeadContent()
	{
		$draftPublished = $this->genericCache->load('draftPublished');
		
		$sitecakeGlobals = "var sitecakeGlobals = {".
			"editMode:" . ( $this->renderMode == ADMIN_MODE ? "true," : "false," ) .
			"sessionId:" . "'<session id>'," .
			"serverVersionId:" . "'" . 'SiteCake server PHP 1.0.21' . "'," .
			"sessionServiceUrl:'" . SERVICE_URL.'?controller=session' . "',";
		
		if ( $this->renderMode == ADMIN_MODE )
		{
			$sitecakeGlobals .=
				"uploadServiceUrl:'" . SERVICE_URL.'?controller=upload' . "'," .
				"contentServiceUrl:'" . SERVICE_URL.'?controller=content' . "'," .
				"licenseServiceUrl:'" . SERVICE_URL.'?controller=license' . "'," .
				"draftPublished:" . ( $draftPublished ? "true," : "false," ) .
				$this->getEditorConfig() . "," .
				"styles:".$this->pageTemplate->getPageContainerStyles().
				"};";

			$header = '<meta http-equiv="X-UA-Compatible" content="chrome=1">' . "\n" .
				'<script type="text/javascript">' . $sitecakeGlobals . '</script>' . "\n" .
				'<script type="text/javascript" language="javascript" src="' . SITECAKE_CONTENT_MANAGER_URL . '"></script>' . "\n";
		}
		else
		{
			$sitecakeGlobals .=
				"forceLoginDialog:" . ( $this->loginAttempt ? "true," : "false," ) .
				$this->getEditorConfig() .
			"};";
			
			$header = '<script type="text/javascript">' . $sitecakeGlobals . '</script>' . "\n" .
				'<script type="text/javascript" language="javascript" src="' . SITECAKE_PUBLIC_MANAGER_URL . '"></script>' . "\n";
		}
		
		return $header;
	}

	/**
	 * Returns the sitecake editor's config object (<code>sitecakeGlobal.config</code>)
	 * created from the content of the config file <code>SITECAKE_EDITOR_CONFIG_FILE</code>
	 * 
	 * @return string
	 */
	private function getEditorConfig()
	{
		$config = "config:{";
		if ( file_exists(SITECAKE_EDITOR_CONFIG_FILE) )
		{
			$configFile = fopen(SITECAKE_EDITOR_CONFIG_FILE, 'r');
			$first = true;
			while ( !feof($configFile) )
			{
				$line = trim(fgets($configFile));
				if ( !($line == '' || substr($line, 0, 1) == '#') )
				{
					$pair = explode('=', $line, 2);
					$key = trim($pair[0]);
					$val = trim($pair[1]);
					$config .= ( $first ? '' : ',')."'$key':'$val'";
					$first = false; 
				}
			}
		}
		$config .= "}";
		
		return $config;
	}
}

?>