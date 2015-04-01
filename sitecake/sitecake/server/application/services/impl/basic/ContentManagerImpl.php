<?php

/**
 * A <code>ContentManager</code> implementation based on 
 * <code>StorageManager</code>.
 */
class ContentManagerImpl implements ContentManager
{
	private $containerIds;
	private $storageManager;
	
	public function __construct()
	{
		$this->containerIds = array();
		$this->storageManager = DiManager::get('StorageManager');	
	}
	
	public function isPublicContentSet($container)
	{
		return $this->objectId($container, false) ? true : false;	
	}
	
	public function getPublicContent($container)
	{
		if ( $id = $this->objectId($container, false) )
		{
			$data = $this->storageManager->loadObject($id);
			return ( $data == null ) ? "" : $data;
		}
		else
			return null;		
	}
	
	public function isDraftContentSet($container)
	{
		return $this->objectId($container, true) ? true : false;	
	}
		
	public function getDraftContent($container)
	{
		if ( $id = $this->objectId($container, true) )
		{
			$data = $this->storageManager->loadObject($id);
			return ( $data == null ) ? "" : $data;
		}
		else
			return null;
	}
	
	public function setPublicContent($container, $content)
	{
		if ( !($id = $this->objectId($container, false)) )
		{
			$id = $this->storageManager->objectId(StorageManager::TYPE_TEXT, 
				'html', 'public-'.$this->containerName($container));
		}

		$this->storageManager->saveObject($id, $content);
	}
	
	public function setDraftContent($container, $content)
	{
		if ( !($id = $this->objectId($container, true)) )
		{
			$id = $this->storageManager->objectId(StorageManager::TYPE_TEXT, 
				'html', 'draft-'.$this->containerName($container));
		}
		
		$this->storageManager->saveObject($id, $content);
	}
	
	public function getAllPublicContainers()
	{
		$containers = array();
		$ids = $this->storageManager->getAllObjectIDs(StorageManager::TYPE_TEXT, 'public-*');
		
		foreach ($ids as $id)
		{
			$idParts = $this->storageManager->explodeObjectId($id);
			$container = substr($idParts['custom'], 7);
			$containers[] = $container;
			$this->containerIds['public-'.$container] = $id;
		}
		
		return $containers;
	}
	
	public function getAllDraftContainers()
	{
		$containers = array();
		$ids = $this->storageManager->getAllObjectIDs(StorageManager::TYPE_TEXT, 'draft-*');
		
		foreach ($ids as $id)
		{
			$idParts = $this->storageManager->explodeObjectId($id);
			$container = substr($idParts['custom'], 6);
			$containers[] = $container;
			$this->containerIds['draft-'.$container] = $id;
		}
		
		return $containers;		
	}
		
	private function objectId($container, $isDraft)
	{
		$idEntry = ( $isDraft ? 'draft-' : 'public-' ).$this->containerName($container);
		
		if ( isset($this->objectIds[$idEntry]) )
			$id = $this->objectIds[$idEntry];
		else
		{
			$ids = $this->storageManager->getAllObjectIDs(StorageManager::TYPE_TEXT, $idEntry); 
		
			if ( isset($ids[0]) )
			{
				$this->containerIds[$idEntry] = $ids[0];
				$id = $ids[0];
			}
			else
				$id = null;
		}
		return $id;
	}
	
	private function containerName($rowName)
	{
		return $rowName;
	}
	
}