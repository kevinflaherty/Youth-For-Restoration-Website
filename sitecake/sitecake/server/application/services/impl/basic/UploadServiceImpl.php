<?php

class UploadServiceImpl extends AbstractService implements UploadService
{
	
	private static $forbiddenExt = array(  'php', 'php5', 'php4', 'php3', 'phtml', 'phpt' );
	
	private $storageManager;
	
	public function __construct()
	{
		$this->storageManager = DiManager::get('StorageManager');
		//$this->logger = Zend_Registry::get( 'defaultLogger' );	
	}

	public function upload()
	{
		return $this->safeCall('_upload');
	}
	
	protected function _upload()
	{
		
		$fileName = $_SERVER['HTTP_X_UPLOADOBJECT_FILENAME'];
		$comps = explode(".", $fileName);
		$fileExt = $comps[count($comps)-1];
		
		if ( array_search(strtolower($fileExt), self::$forbiddenExt) ) {
			throw new Exception("Not allowed file type.");
		}
		
		if ( $fileExt)
		$imageUpload = isSet($_SERVER['HTTP_X_IMAGEUPLOAD_RESIZEWIDTH']) || isSet($_SERVER['HTTP_X_IMAGEUPLOAD_THUMBDIM']);

		if ( $imageUpload )
			$objectId = $this->storageManager->objectId(StorageManager::TYPE_IMAGE, $fileExt, 'orig');
		else
			$objectId = $this->storageManager->objectId(StorageManager::TYPE_BINARY, $fileExt);
					
		$out = fopen($this->storageManager->getObjectStreamUrl($objectId), "wb");
		
		if ($out)
		{
			$in = fopen("php://input", "rb");

			if ($in)
			{
				while ($chunk = fread($in, 4096))
				{
					fwrite($out, $chunk);
				}
			}
			else
			{
				throw new Exception("Failed to open input stream");
			}

			fclose($out);
		}
		else
		{
			throw new Exception("Failed to open output stream.");
		}

		$objectIdParts = $this->storageManager->explodeObjectId($objectId);
		$this->result['id'] = $objectIdParts['uuid'];
		
		if ( $imageUpload )
		{
			$this->result['url'] = $this->storageManager->objectId2url($objectId);
			
			if ( isSet($_SERVER['HTTP_X_IMAGEUPLOAD_THUMBDIM']) )
			{
				$thumbnailDimension = $_SERVER['HTTP_X_IMAGEUPLOAD_THUMBDIM'];
				$thumbObjectId = $this->storageManager->objectId(StorageManager::TYPE_IMAGE, $fileExt, 'thumb', $objectIdParts['uuid']);
				
				$imageTransformer = new ImageTransform();
				
				$imageTransformer->load($this->storageManager->getObjectStreamUrl($objectId));
				$imageTransformer->resizeToDimension($thumbnailDimension);
				$imageTransformer->save($this->storageManager->getObjectStreamUrl($thumbObjectId));
				$this->result['thumbnailUrl'] = $this->storageManager->objectId2url($thumbObjectId);
				$this->result['thumbnailWidth'] = $imageTransformer->getWidth();
				$this->result['thumbnailHeight'] = $imageTransformer->getHeight();
			}

			if ( isSet($_SERVER['HTTP_X_IMAGEUPLOAD_RESIZEWIDTH']) && $_SERVER['HTTP_X_IMAGEUPLOAD_RESIZEWIDTH'] != 0 )
			{
				$resizedWidth = $_SERVER['HTTP_X_IMAGEUPLOAD_RESIZEWIDTH'];
				$resizedObjectId = $this->storageManager->objectId(StorageManager::TYPE_IMAGE, $fileExt, 'draft', $objectIdParts['uuid']);
				
				$imageTransformer = new ImageTransform();
				
				$imageTransformer->load($this->storageManager->getObjectStreamUrl($objectId));
				if ( $imageTransformer->getWidth() <= $resizedWidth )
				{
					$this->storageManager->copyObject($objectId, $resizedObjectId);
				}
				else
				{
					$imageTransformer->resizeToWidth($resizedWidth);
					$imageTransformer->save($this->storageManager->getObjectStreamUrl($resizedObjectId));
				}
				$this->result['resizedUrl'] = $this->storageManager->objectId2url($resizedObjectId);
				$this->result['resizedWidth'] = $imageTransformer->getWidth();
				$this->result['resizedHeight'] = $imageTransformer->getHeight();
			}
		}
		else
		{
			$this->result['url'] = $this->storageManager->objectId2url($objectId);
		}
	}
}

?>