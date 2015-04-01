<?php

/**
 * An abstract <code>StorageManager</code> file-based implementation.
 * All objects are kept under the local file system's directory.
 */
abstract class AbstractFileStorageManager implements StorageManager
{
	
	public function __construct()
	{
		stream_wrapper_register('storageManager', 'StorageManagerStream');
		$this->logger = Zend_Registry::get( 'defaultLogger' );
	}
	
	private function ensureContentDir()
	{
		if ( !file_exists($this->fileContentDir()) )
			mkdir($this->fileContentDir(), 0777, true);
	}
	
	public function objectId($type, $ext, $custom=null, $uuid=null)
	{
		if ( $uuid == null )
			$uuid = Zend_Utility_Uuid::generate(Zend_Utility_Uuid::UUID_TIME, Zend_Utility_Uuid::FMT_STRING);
			
		if ( $ext == null ) $ext = 'bin';
		
		if ( $custom == null ) 
			$custom = '';
		else
			$custom = '-'.$custom;
			
		return $uuid.'-'.$type.$custom.'.'.$ext;
	}
	
	public function explodeObjectId($id)
	{
		return $this->idParts($id);
	}
	
	public function objectExists($id)
	{
		return file_exists($this->filePath($id));
	}
	
	public function getObjectStreamUrl($id)
	{
		$this->ensureContentDir();
		return $this->filePath($id);
	}
	
	public function getObjectStream($id, $mode)
	{
		$this->ensureContentDir();
		$path = $this->filePath($id);
		return fopen($path, $mode);
	}
	
	public function loadObject($id)
	{
		return file_get_contents($this->filePath($id));
	}
	
	public function saveObject($id, $object)
	{
		$this->ensureContentDir();
		file_put_contents($this->filePath($id), $object);
	}
	
	public function removeObject($id)
	{
		$path = $this->filePath($id);
		if ( file_exists($path) )
		{
			unlink($path);
		}	
	}	
	
	public function copyObject($id, $newId)
	{
		$srcPath = $this->filePath($id);
		$dstPath = $this->filePath($newId);
		copy($srcPath, $dstPath);	
	}
	
	public function objectId2url($id)
	{
		return $this->fileContentUrl().$id;		
	}
	
	public function url2objectId($url)
	{
		preg_match('/.*([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\-[^\-\.]+(\-[^\.]+)?\..*)/', $url, $matches);
		return $matches[1];
	}
	
	public function getAllObjectIDs($type, $customPattern=null)
	{
		if ( $type == null ) $type = '*';
		
		if ( $customPattern == null )
			$customPattern = '*';
		else
			$customPattern = '-'.$customPattern.'.*';

		$paths = glob($this->fileContentDir().'*-*-*-*-*-'.$type.$customPattern);
		if ( $paths == null ) $paths = array();
		
		$ids = array();
		foreach ( $paths as $path )
		{
			preg_match('/.*([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\-'.$type.'(\-[^\.]+)?\..*)/', $path, $matches);
			if ( $matches && isset($matches[1]) ) {
				$ids[] = $matches[1];
			}
		}
		
		return $ids;
	}
	
	private function idParts($id)
	{
		preg_match('/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})\-([^\-\.]+)(\-([^\.]+))?\.(.*)/', $id, $matches);
		return array(
			'id' => $id,
			'uuid' => $matches[1], 
			'type' => $matches[2], 
			'custom' => ( $matches[4] ? $matches[4] : null), 
			'ext' => $matches[5]
		);
	}
	
	private function filePath($id)
	{
		return $this->fileContentDir().$id;		
	}
	
	protected abstract function fileContentDir();
	
	protected abstract function fileContentUrl();
}