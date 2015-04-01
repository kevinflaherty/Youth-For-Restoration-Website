<?php

interface StorageManager
{
	/**
	 * The generic binary object type.
	 */
    const TYPE_BINARY = 1;
    
    /**
     * The text/html object type.
     */
    const TYPE_TEXT = 2;
    
    /**
     * The image type.
     */
    const TYPE_IMAGE = 3;
    
    /**
     * Returns a unique object identifier based on the 
     * given object type, mime/extension and custom information.
     * 
     * @param int $type the object type (e.g. <code>TYPE_TEXT</code>)
     * @param string $ext the object's extension
     * @param string $custom additional custom information that will be embedded into the id
     * @param string $uuid if provided, this UUID value will be used to construct object ID
     * @return the object id
     */
	public function objectId($type, $ext, $custom=null, $uuid=null);
	
	/**
	 * Returns the parts (type, extension and custom) of the given ID.
	 * 
	 * @param string $id the ID to be exploded
	 * @return array('type'=> , 'ext'=>, 'custom'=>, 'uuid'=>)
	 */
	public function explodeObjectId($id);
	
	/**
	 * Checks if the object exists for the given ID.
	 * 
	 * @param boolean $id <code>true</code> if the object exists, <code>false</code> otherwise
	 * @throws Zend_Exception in case of an error
	 */
	public function objectExists($id);
	
	/**
	 * Creates and returns an appropriate stream that can be used to
	 * read/write object content. The caller is responsible of stream
	 * closing.
	 * 
	 * @param string $id the object's ID
	 * @param string $mode specifies the stream mode (same as the <code>fopen()</code> 
	 * 					function's <code>mode</code> param)
	 * @return a handle to the stream
	 * @throws Zend_Exception in case of an error
	 */
	public function getObjectStream($id, $mode);
	
	/**
	 * Returns the approprate stream path/url for the given object ID.
	 *
	 * @param string $id the object ID
	 */
	public function getObjectStreamUrl($id);
	
	/**
	 * Loads and return the specified object.
	 * 
	 * @param string $id
	 * @return the specified object
	 */
	public function loadObject($id);
	
	/**
	 * Saves the given object under the given ID.
	 * 
	 * @param string $id
	 * @param mixed $object object to be saved
	 * @throws Zend_Exception in case of an error
	 */
	public function saveObject($id, $object);
	
	/**
	 * Removes the designated object.
	 * 
	 * @param string $id the ID of the object that should be removed
	 */
	public function removeObject($id);
	
	/**
	 * Duplicates the object usting the new ID.
	 * 
	 * @param string $id the ID of the object that is to be copied
	 * @param string $newId the ID of the duplicate
	 */
	public function copyObject($id, $newId);
	
	/**
	 * Returns an URL that can be used to access the object.
	 *
	 * @param string $id the object id
	 * @return a string that represents the access URL
	 */
	public function objectId2url($id);
	
	/**
	 * Returns the object ID based on the given URL

	 * @param string $url object URL
	 * @return string the object ID
	 */
	public function url2objectId($url);
	
	/**
	 * Returns IDs of all existant objects filtered
	 * by the given type and custom part.
	 * 
	 * @param int $type the object type
	 * @param string $custom the search pattern for the custom part of the object id (e.g. 'draft*')
	 */
	public function getAllObjectIDs($type, $customPattern=null);
}