<?php

class PageTemplateImpl implements PageTemplate
{
	private $_pageRequest;
	private $_htmlTemplate;
	private $_pageTpl;
	private $_cacheMgr;
	private $_tplCache;
	private $_pageContainers;
	private $_editable;
	private $_modified;
	private $styles;
	private static $supportedTags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'a', 'img', 'ul', 'div' );
	private static $contentTypeCodes = array( 
		'h1'=>'HEADING1', 'h2'=>'HEADING2', 'h3'=>'HEADING3', 
		'h4'=>'HEADING4', 'h5'=>'HEADING5', 'h6'=>'HEADING6', 'p'=>'TEXT', 'ul'=>'LIST', 
		'img'=>'IMAGE', 'div.sc-slideshow'=>'SLIDESHOW', 'div.sc-flash'=>'FLASH', 'div.sc-video'=>'VIDEO', 
		'div.sc-map'=>'MAP', 'div.sc-html'=>'HTML', 'div.sc-contact'=>'CONTACT' );
	
	public function setPageRequest($request)
	{
		$this->_pageRequest = $request;
		
		// a file that contains the page template
		$this->_htmlTemplate = DOCUMENT_ROOT_DIR . DS . $request;
		if ( defined('OVERRIDE_HTACCESS') ) {
			$this->_htmlTemplate = $_SERVER['SCRIPT_FILENAME'];	
		}
		if ( !file_exists($this->_htmlTemplate) ) {
			throw new Zend_Exception('Could not open template file ' . $this->_htmlTemplate);			
		}
		
		$this->initTemplateEngine();
		$this->_editable = $this->checkEditable();
		
		$this->_pageContainers = array();			
		if ( $this->_editable )
		{
			$this->_pageContainers = $this->getTplContainers();
		}
	}
	
	public function isEditable()
	{
		return $this->_editable;	
	}
	
	public function isUpdated()
	{
		return $this->_modified;	
	}
	
	public function getPageContainers()
	{
		return $this->_pageContainers;		
	}
	
	public function setHeader($content)
	{
		$this->setTemplatePart("#sc_header", $content);
	}
	
	public function getPageContainer($container)
	{
		return $this->getTemplatePart($container);
	}
	
	public function getPageContainerStyles()
	{
		preg_match('/@@@#styles\[\{\{(.*?)\}\}\]/s', $this->_pageTpl, $matches);
		if ( $matches )
		{
			return $matches[1];
		}
		else
		{
			return "{}";
		}		
	}

	public function setPageContainer($container, $content)
	{
		$this->setTemplatePart($container, $content);
	}
	
	public function renderPage()
	{
		// remove from the template container placeholders ( $$$placeholder[{{.*}}] )
		$out = preg_replace('/\$\$\$[^\[]+\[\{\{(.*?)\}\}\]/s', "$1", $this->_pageTpl);
		// remove styles section
		$out = preg_replace('/@@@#styles\[\{\{(.*?)\}\}\]/s', '', $out);
		return $out;
	}

	private function initTemplateEngine()
	{
		// init the render template cache
		
		$this->_cacheMgr = Zend_Registry::get('cacheManager');
		$this->_cacheMgr->setTemplateOptions('tplCache', array(
			'frontend' => array(
				'options' => array(
					'master_files' => array( $this->_htmlTemplate )
				)
			)
		));
		$this->_tplCache = $this->_cacheMgr->getCache('tplCache');
		
		$pageId = md5($this->_pageRequest);

		$this->_modified = false;
		if ( TEMPLATE_CACHING === false || ($this->_pageTpl = $this->_tplCache->load($pageId)) === false )
		{
			// cache miss or the template should be recreate each time
			$this->_modified = true;
			$this->_pageTpl = $this->createTemplate();
			if ( TEMPLATE_CACHING ) {
				$this->_tplCache->save($this->_pageTpl, $pageId);	
			}
		}
	}
	
	/**
	 * Creates the page template from the raw html file.
	 * It filters out all PHP code blocks. All template parts
	 * will be marked as $$$<part name>[{{<part content>}}].
	 * Template parts are header part (named as '#sc_header'), located at the
	 * end of the HTML head tag
	 * and content containers (named as '<container name>').
	 * 
	 * @return the created page template
	 */
	private function createTemplate()
	{
		$html = $this->loadHtml();
		$this->styles = array();
		$dom = SimpleHtmlDom_Parser::str_get_html($html);
		$contentManager = DiManager::get('ContentManager');
		
		$head = $dom->find('head');
		$head[0]->innertext .= '$$$#sc_header[{{}}]';
		
		$containers = $dom->find('*[class*=sc-content]');
		foreach ($containers as $container) {
			$class = $container->class;
			if (isset($class) && preg_match('/(^|.* )sc\-content\-([^ ]+)( |$)/', $class, $matches)) {
				$containerName = $matches[2];			
				$containerContent = $this->parseContainerContent($containerName, $container);
				$container->innertext = '$$$'.$containerName.'[{{'. $containerContent .'}}]';
			}
		}
		
		$head[0]->innertext .= '@@@#styles[{{'.Zend_Json_Encoder::encode($this->styles).'}}]';
		
		return $dom->__toString();
	}
	
	/**
	 * Loads and cleans the html template file.
	 * 
	 */
	private function loadHtml()
	{
		// load the html file
		$html = file_get_contents($this->_htmlTemplate);

		// TODO: tidy html code
		
		// remove the SC include PHP block
		$html = preg_replace('/<\?.*?sitecake_entry.php.*?\?>/s', '', $html);
		
		if ( !(PHP_TEMPLATE && strpos($html, "<?php") !== false) ) {
			return $html;
		}
		
    	ob_start();
		eval('?>'.$html);
		$result = ob_get_contents();
		ob_end_clean();
	
		return $result;		
	}
	
	/**
	 * Gets the content of the specified template part.
	 * 
	 * @param string $part template part name
	 * @return the template part's content
	 */
	private function getTemplatePart($part)
	{
		preg_match('/\$\$\$'.$part.'\[\{\{(.*?)\}\}\]/s', $this->_pageTpl, $matches);
		if ( $matches )
		{
			return $matches[1];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Stuffs the specified template part with the given content.
	 * 
	 * @param string $part template part name
	 * @param string $content template part content
	 */	
	private function setTemplatePart($part, $content)
	{
		// escape $n patterns
		$cnt = str_replace('$', "\\$", $content);
		
		$this->_pageTpl = preg_replace('/\$\$\$'.$part.'\[\{\{.*?\}\}\]/s', '\$\$\$'.$part.'[{{'.$cnt.'}}]', $this->_pageTpl);
	}
	
	/**
	 * Check if the html page template is editable, that is,
	 * if it contains the HTML meta tag <code>sitecake</code> with content 
	 * <code>exclude</code>.
	 * 
	 * <code>&lt;meta name="sitecake" content="exclude"&gt;</code>
	 * 
	 * @return <code>true</code> if the page is editable, <code>false</code> otherwise
	 */	
	private function checkEditable()
	{
		return preg_match('/<\s*meta[^>]+(name\s*=\s*\"sitecake\"[^>]*content\s*=\s*\"exclude\"|content\s*=\s*\"exclude\"[^>]+name\s*=\s*\"sitecake\")/', 
			$this->_pageTpl) == 0;
	}
	
	/**
	 * Returns content containers names present in the template.
	 * 
	 * @return an array of container names
	 */
	private function getTplContainers()
	{
		preg_match_all('/\$\$\$([^\[]+)\[\{\{/s', $this->_pageTpl, $matches);

		if ( $matches && isset($matches[1]) )
		$len = count($matches[1]);
		for ( $i = 0; $i < $len; $i++ )
		{
			if ( $matches[1][$i] != '#sc_header' )
				$this->_pageContainers[] = $matches[1][$i];
		}

		return $this->_pageContainers;
	}
	
	/**
	 * Returns the normalized container content as HTML text
	 * by removing unsupported content types. As a side effect,
	 * it collects the container styles. 
	 *
	 * @param string $containerName 
	 * @param SimpleHtmlDom_Node container node
	 * @param boolean $extractHtml if the template's html extraction should be executed
	 * @return string container inner HTML
	 */
	private function parseContainerContent($containerName, $containerNode)
	{
		$contentManager = DiManager::get('ContentManager');
		$outHtml = '';
		// avoid parsing content and duplicating images if public content already exists
		$extractHtml = !$contentManager->isPublicContentSet($containerName);
				
		// iterates over all container child nodes
		$children = $containerNode->children();
		foreach ( $children as $child )
		{
			// only supported tags are processed further
			if ( ($child->nodetype != HDOM_TYPE_ELEMENT) ||
					!in_array($child->tag, self::$supportedTags) ) continue;
					
			if ( ($contentType = $this->checkContentElement($child)) !== false )
			{
				if ( $extractHtml )
					$outHtml .= $this->extractHtml($contentType, $child);	
				$this->extractStyle($containerName, $contentType, $child);
			}
		}
		
		if ( $outHtml != '' )
		{
			if ( !$contentManager->isPublicContentSet($containerName) )
				$contentManager->setPublicContent($containerName, $outHtml);
				
			if ( !$contentManager->isDraftContentSet($containerName) )	
				$contentManager->setDraftContent($containerName, $outHtml);
		}
		return $outHtml;
	}

	// TODO: ensure that node's innerHTML does not contain unsupported HTML constructs 
	// (e.g. <span> tags inside a <p> tag)
	private function extractHtml($contentType, $node)
	{
		if ( $contentType == 'IMAGE' )
		{
			$html = $this->extractImageHtml($node);
		}
		else
		{
			$html = $node->outertext;
		}
		
		// strip newline characters from the output html
		return (string)str_replace(array("\r", "\r\n", "\n"), '', $html);
	}
	
	private function extractStyle($container, $contentType, $contentNode)
	{
			
		if ( $contentNode->hasAttribute('class') )
		{
			$style = trim(preg_replace('/^((([^\s]+\s)+)?)sc\-[^\s]+(.*)/', '$1$4', $contentNode->class));
			if ( $style == '' ) return;
			
			if ( !isset($this->styles[$container]) )
				$this->styles[$container] = array();
				
			if ( !isset($this->styles[$container][$contentType]) )
				$this->styles[$container][$contentType] = array();
				
			if ( !in_array($style, $this->styles[$container][$contentType]) )
				$this->styles[$container][$contentType][] = $style;
		} 
	}
	
	private function checkContentElement($node)
	{
		$typeKey = $node->tag;
		if ( $node->tag == 'div' )
		{
			if ( !isset($node->class) ) return false;
			if ( preg_match('/^(([^\s]+\s)+)?(sc\-[^\s]+)/', $node->class, $matches) == 0 ) return false;
			$typeKey .= '.'.$matches[3];			
		}
		else if ( $node->tag == 'a' )
		{
			if ( count($node->children) != 1 || 
					$node->children(0)->tag != 'img' )
				return false;
			$typeKey = 'img';
		}

		if ( !array_key_exists($typeKey, self::$contentTypeCodes) )
			return false;
		//TODO: check the content item inner text
		
		return self::$contentTypeCodes[$typeKey];
	}
	
	private function extractImageHtml($element)
	{
		$imgElement = $element;
		if ( $element->tag == 'a' )
		{
			$imgElement = $element->children(0);
		}
		
		$imgUrl = $imgElement->src;
		
		if ( substr($imgUrl, 0, 4) == 'http' )
		{
			// image url is an absolute URL - not supported
			return '';
		}
		
		// determine the path to the image file
		if ( substr($imgUrl, 0, 1) == '/' )
		{
			// document root relative path
			$imageFilePath = $_SERVER['DOCUMENT_ROOT'] . $imgUrl;
		}
		else
		{
			// html page relative path
			$imageFilePath = dirname($this->_htmlTemplate) . DS . $imgUrl;
		}
		
		// if the path is not correct, just skip the element
		if ( !file_exists($imageFilePath) )
		{
			return '';
		}
		
		$this->storageManager = DiManager::get('StorageManager');
		
		$comps = explode(".", $imageFilePath);
		$fileExt = $comps[count($comps)-1];
		$objectId = $this->storageManager->objectId(StorageManager::TYPE_IMAGE, $fileExt, 'orig');
		$objectIdParts = $this->storageManager->explodeObjectId($objectId);
		$resizedObjectId = $this->storageManager->objectId(StorageManager::TYPE_IMAGE, $fileExt, 'draft', $objectIdParts['uuid']);
		$imgElement->src = $this->storageManager->objectId2url($resizedObjectId);
		
		copy($imageFilePath, $this->storageManager->getObjectStreamUrl($objectId));
		copy($imageFilePath, $this->storageManager->getObjectStreamUrl($resizedObjectId));
		
		return $element->outertext;
	}
	
}