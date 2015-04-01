<?php

// TODO: in case of an error, implement safe rollback of affected resources (files)
class ContentServiceImpl extends AbstractService implements ContentService
{
	private $storageManager;
	private $contentManager;
	private $cache;
	
	protected function init()
	{
		$this->storageManager = DiManager::get('StorageManager');
		$this->contentManager = DiManager::get('ContentManager');
		//$this->logger = Zend_Registry::get( 'defaultLogger' );
		$cacheMgr = Zend_Registry::get('cacheManager');
		$this->cache = $cacheMgr->getCache('genericCache');
	}
	
	public function save( $params )
	{
		return $this->safeCall('_save', array($params));
	}
	
	public function publish( $params )
	{
		return $this->safeCall('_publish', array($params));
	}
	
	protected function _save( $params )
	{
		$this->init();

		// determine the page name
		$page = $this->pageName($params['__sc_page']);
		unset($params['__sc_page']);
		
		// because the content is changed
		$this->cache->save(false, 'draftPublished');

		// for every received container
		foreach ( $params as $param => $containerHtml )
		{
			// remove slashes
			if ( get_magic_quotes_gpc() ) 
				$containerHtml = stripcslashes($containerHtml);
			
			$containerHtml = base64_decode($containerHtml);
			
			// stripping '__sc_content_' prefix
			$containerName = substr( $param, 15 );
			
			$outHtml = '';
			if ( strlen( $containerHtml ) > 0 )
			{
				// process/transform the new content
				$oldContent = $this->contentManager->getDraftContent($containerName);
				if ( $oldContent == null ) $oldContent = '';
				$oldContentDom = SimpleHtmlDom_Parser::str_get_html( $oldContent );
				$newContentDom = SimpleHtmlDom_Parser::str_get_html( $containerHtml );
				$this->processSaveContent($newContentDom, $oldContentDom);
				$outHtml = $newContentDom->__toString();
			}
			// save the new content
			$this->contentManager->setDraftContent($containerName, $outHtml);
		}
	}
	
	protected function _publish( $params )
	{
		$this->init();

		$page = $this->pageName($params['__sc_page']);
		unset($params['__sc_page']);
		
		// remember that the draft is published
		$this->cache->save(true, 'draftPublished');
		// and save the publishing time
		$this->cache->save(time(), 'lastPublished');
		// and finally publish the draft content			
		$this->publishDraftContent();
	}
	
	private function transformImage( $item, $src, $data )
	{
		$datas = explode(':', $data);
		$srcWidth = $datas[0];
		$srcHeight = $datas[1];
		$srcX = $datas[2];
		$srcY = $datas[3];
		$dstWidth = $datas[4];
		$dstHeight = $datas[5];
		
		$item->data = (string)$data;
		
		$objectId = $this->storageManager->url2objectId($src);
		$objectIdParts = $this->storageManager->explodeObjectId($objectId);
		
		$origObjectId = $this->storageManager->objectId(StorageManager::TYPE_IMAGE, 
			$objectIdParts['ext'], 'orig', $objectIdParts['uuid']);

		$newObjectId = $this->storageManager->objectId(StorageManager::TYPE_IMAGE, 
			$objectIdParts['ext'], 'draft', $objectIdParts['uuid']);

		$item->src = $this->storageManager->objectId2url($newObjectId);
		
		$imageTransform = new ImageTransform();
		
		$imageTransform->load($this->storageManager->getObjectStreamUrl($origObjectId));
		
		$origWidth = $imageTransform->getWidth();
		$origHeight = $imageTransform->getHeight();
		
		$xRatio = $origWidth / $srcWidth;
		$yRatio = $origHeight / $srcHeight;
		
		$srcWidth = $dstWidth * $xRatio;
		$srcHeight= $dstHeight * $yRatio;
		$srcX = $srcX * $xRatio;
		$srcY = $srcY * $yRatio;
		
		$imageTransform->transform( $srcX, $srcY, $srcWidth, $srcHeight, $dstWidth, $dstHeight );
		$imageTransform->save( $this->storageManager->getObjectStreamUrl($newObjectId) );		
	}
	
	private function pageName($rowName)
	{
		if ( $rowName == '' || $rowName == 'index.html' || $rowName == 'index.htm' )
			return 'index';
		else
			return $rowName;
	}
	
	/**
	 * Goes though the new content and transform it and its resources
	 * if necessary (e.g. image files).
	 * Checks if the images are modified and resizes them accordingly.
	 * 
	 * @param SimpleHtmlDom_Node $newContentDom new content for saving
	 * @param SimpleHtmlDom_Node $oldContentDom the previously saved content
	 */
	private function processSaveContent($newContentDom, $oldContentDom)
	{
		foreach ( $newContentDom->childNodes() as $item )
		{
			if ( $item->tag != "img" ) continue;
			
			$src = $item->src;
			$data = $item->data;
			$oldImg = $oldContentDom->find( "img[@src='$src']");

			// check if the previous image exists 
			if ( $oldImg && isset($oldImg[0]) )
			{
				// check if the new data differs from the previous data
				if ( (string)($oldImg[0]->data) != (string)$data )
				{
					// if different, resize the image file
					$this->transformImage( $item, $src, $data );
				}
			}
			else
			{
				// if the previous image does not exists, resize the image file
				$this->transformImage( $item, $src, $data );
			}
		}		
	}
	
	/**
	 * Publishes the draft content (all containers).
	 * Also modifies the draft content in order to clean the public HTML code
	 * and to replace references to the draft resource files.
	 * Copies all draft files to their public equivalents.
	 */
	private function publishDraftContent()
	{
		// publish all containers, one by one
		$allContainers = $this->contentManager->getAllDraftContainers();
		foreach ( $allContainers as $container )
		{
			// get the draft content
			$content = $this->contentManager->getDraftContent($container);
			
			// processing needed only if the content is not empty
			if ( $content )
			{
				// check if the content contains refs to external resources (images)
				preg_match_all( '/[^"]+\-'.StorageManager::TYPE_IMAGE.'-draft\.[a-zA-Z0-9]{1,4}/', $content, $matches );
				if ( isset( $matches[0] ) && count( $matches[0] ) > 0 )
				{
					// replace refs to draft files with refs to public files
					$content = preg_replace( '/("[^"]+\-'.StorageManager::TYPE_IMAGE.')-draft\.(.{1,4}")/', '\1.\2', $content );
					// remove helper tag attributes to make public HTML valid (e.g. data attribute for img tags)
					$content = preg_replace( '/(<img[^<]*)data="[^"]*"([^>]*\/>)/', '\1\2', $content );
					// copy reffered draft files to public ones
					foreach ( $matches[0] as $src )
					{
						$draftOid = $this->storageManager->url2objectId($src);
						$draftOidParts = $this->storageManager->explodeObjectId($draftOid);
						$publicOid = $this->storageManager->objectId(StorageManager::TYPE_IMAGE, 
							$draftOidParts['ext'], null, $draftOidParts['uuid']);
						$this->storageManager->copyObject($draftOid, $publicOid);
					}
				}
			}
			
			// save processed draft content as the public content
			$this->contentManager->setPublicContent($container, $content);
		}

	}

}

?>