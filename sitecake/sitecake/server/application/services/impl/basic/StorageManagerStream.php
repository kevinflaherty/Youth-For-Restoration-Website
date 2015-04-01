<?php

class StorageManagerStream
{
	private $stream;
	private $storageManager;
	
	public function __construct()
	{
		//$this->logger = Zend_Registry::get( 'defaultLogger' );	
	}
	
	public function stream_open($path, $mode, $options, &$opened_path)
	{
		//$this->logger->log("stream_open($path, $mode, $options", Zend_Log::INFO);
		$this->storageManager = DiManager::get('StorageManager');
		$url = parse_url($path);
		$id = $url["host"];
		$this->stream = $this->storageManager->getObjectStream($id, $mode);
		return !($this->stream === false);
	}

	public function stream_read($count)
	{
		//$this->logger->log("stream_read($count)", Zend_Log::INFO);		
		return fread($this->stream, $count);
	}
	
	public function stream_write($data)
	{
		//$this->logger->log("stream_write(".strlen($data).")", Zend_Log::INFO);		
		return fwrite($this->stream, $data, strlen($data));
	}
	
	public function stream_close()
	{
		//$this->logger->log("stream_close", Zend_Log::INFO);		
		return fclose($this->stream);	
	}
	
	public function stream_tell()
	{
		//$this->logger->log("stream_tell()", Zend_Log::INFO);		
		return ftell($this->stream);	
	}
	
	public function stream_eof()
	{
		//$this->logger->log("stream_eof()", Zend_Log::INFO);		
		return feof($this->stream);
    }
    
	public function stream_seek($offset, $whence)
	{
		//$this->logger->log("stream_seek($offset, $whence)", Zend_Log::INFO);		
		return fseek($this->stream, $offset, $whence);
	}
	
	public function stream_stat()
	{
		//$this->logger->log("stream_stat()", Zend_Log::INFO);		
		return fstat($this->stream);
	}

}