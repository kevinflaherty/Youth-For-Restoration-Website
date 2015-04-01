<?php

/**
 * The <code>ContentService</code> is used by the sitecake editor to
 * save and publish the edited site content.
 */
interface ContentService
{
	/**
	 * Saves the given page content into the respective content containers.
	 * 
	 * The response is an array with the following elements:
	 * <code>status</code> - int, possible outcomes:
	 * -1 - call failed because of an (execution) error
	 *  0 - the page content saved
	 *  
	 * <code>errorMessage</code> - string, present if <code>status</code> is -1 or 1
	 * 
	 * @param array $params the page content in the following format:
	 * __sc_page - the page name
	 * __sc_content_<container name> - the content of the container (<container name>)
	 * 
	 * @return array the service response
	 */	
	public function save( $params );

	/**
	 * Publish the site content.
	 * 
	 * The response is an array with the following elements:
	 * <code>status</code> - int, possible outcomes:
	 * -1 - call failed because of an (execution) error
	 *  0 - the site published
	 *  
	 * <code>errorMessage</code> - string, present if <code>status</code> is -1 or 1
	 * 
	 * @param array $params the page content in the following format:
	 * __sc_page - the page name
	 * 
	 * @return array the service response
	 */	
	public function publish( $params );
}

?>