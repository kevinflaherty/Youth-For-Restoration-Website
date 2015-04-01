<?php

class StorageManagerImpl extends AbstractFileStorageManager
{
	
	protected function fileContentDir()
	{
		return CONTENT_DIR.DS;
	}
	
	protected function fileContentUrl()
	{
		return CONTENT_BASE_URL.'/';
	}	
}