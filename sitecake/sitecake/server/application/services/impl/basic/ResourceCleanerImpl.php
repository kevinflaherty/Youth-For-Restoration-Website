<?php

class ResourceCleanerImpl implements ResourceCleaner
{
	private $contentManager;
	private $storageManager;
	private $cache;
	private $session;
	
	public function __construct()
	{
		$this->storageManager = DiManager::get('StorageManager');
		$this->session = Zend_Registry::get('session');
		$this->contentManager = DiManager::get('ContentManager');
		//$this->logger = Zend_Registry::get( 'defaultLogger' );
		$cacheMgr = Zend_Registry::get('cacheManager');
		$this->cache = $cacheMgr->getCache('genericCache');
	}
	
	public function cleanup()
	{
		// check if the cleanup has already been executed within
		// the current session
		if ( isset($this->session->resourceCleanerStarted) ) return;
		
		// if we are green to go
		// remember that the cleanup started
		$this->session->resourceCleanerStarted = true;

		// check if there is a list of files to be removed
		// from the last time
		if ( $deleteList = $this->cache->load('resourceCleanerList') )
			$this->deleteResources($deleteList);
		else
			$this->cleanupStart();
	}
	
	private function deleteResources($list)
	{
		if ( count($list) == 0 ) return;
		
		$startTime = time();
		
		for ( $i=0; $i<count($list); $i++ )
		{
			try {
				$this->storageManager->removeObject($list[$i]);
			} catch (Exception $e) {
				// silently skip the problem
			}
			// if the operation is taking too much time, break it
			if ( (time() - $startTime) > 5 ) break;
		}
		
		if ( $i < (count($list)-1) )
		{
			// update the saved delete list
			$newDeleteList = array_slice($list, $i+1);
			$this->cache->save($newDeleteList, 'resourceCleanerList');
		}
		else
		{
			// remove the saved delete list
			$this->cache->remove('resourceCleanerList');
		}
	}
	
	// TODO: remove cleaner dependency of the object ID's structure (UUID)
	private function cleanupStart()
	{
		$usedResources = array();
		
		// extract resource URLs in both draft and public content
		$allContainers = $this->contentManager->getAllDraftContainers();
		foreach ( $allContainers as $container )
		{
			$content = $this->contentManager->getDraftContent($container);
			$usedResources = array_merge($usedResources, $this->extractResources($content));
		}

		$allContainers = $this->contentManager->getAllPublicContainers();
		foreach ( $allContainers as $container )
		{
			$content = $this->contentManager->getPublicContent($container);
			$usedResources = array_merge($usedResources, $this->extractResources($content));
		}
		// $usedResources now contains all used files (an array of UUIDs)
		
		//$this->logger->log('$usedResources: '.print_r($usedResources, true), Zend_Log::INFO);
		
		// next, obtain a list of all (but not text) existing resources (an array of resource IDs)
		$existingResourceIds = $this->storageManager->getAllObjectIds(StorageManager::TYPE_BINARY);
		$existingResourceIds = array_merge($existingResourceIds, 
			$this->storageManager->getAllObjectIds(StorageManager::TYPE_IMAGE));

		//$this->logger->log('existing: '.print_r($existingResourceIds, true), Zend_Log::INFO);

		// determine the list of objects that should be deleted
		$forDeletion = array();
		foreach ( $existingResourceIds as $existingResourceId )
		{
			// extract the resource UUID
			if ( preg_match('/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/', $existingResourceId, $matches) == 0) continue;

			$uuid = $matches[0];
			
			// try to find the occurance of the UUID in $resourceURLs
			if ( array_search($uuid, $usedResources) === false )
			{
				// not find, so the appropraite file should be deleted
				$forDeletion[] = $existingResourceId;
			} 
		}
		//$this->logger->log('$forDeletion: '.print_r($forDeletion, true), Zend_Log::INFO);		
		$this->deleteResources($forDeletion);
	}
	
	private function extractResources($text)
	{
		preg_match_all('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/s', $text, $matches);
		//$this->logger->log('extractResources: '.$text."\n".print_r($matches[0], true), Zend_Log::INFO);		
		return $matches[0];
	}
}