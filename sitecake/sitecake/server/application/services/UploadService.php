<?php

/**
 * The <code>UploadService</code> is used by the sitecake editor 
 * to upload files to the server. Based on the upload instructions,
 * the service is responsible to execute additional actions on the
 * uploaded content (resizing images, creating thumbnails, etc.).
 * As the respose, the service has to return access URLs for the
 * uploaded content.
 * 
 * The files are uploaded as application/octet-stream.
 * The upload instructions are stored as request header properties.
 * 
 * The response is an array with the following elements:
 * <code>status</code> - int - 0 if OK, -1 the service call failed
 * <code>errorMessage</code> - string, present if <code>status</code> is not 0
 * <code>url</code>
 * <code>id</code>
 * <code>resizedUrl</code>
 * <code>resizedWidth</code>
 * <code>resizedHeight</code>
 * <code>thumbnailUrl</code>
 * <code>thumbnailWidth</code>
 * <code>thumbnailHeight</code>
 * 
 * @return array
 */
interface UploadService
{
	public function upload();	
}

?>