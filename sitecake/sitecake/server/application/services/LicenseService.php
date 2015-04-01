<?php

/**
 * <code>LicenseService</code> is used by the sitecake editor
 * to obtain the license.
 * The licence is a message of the following format, encrypted with
 * a private RSA key whose public version is embeded into the sitecake
 * editor, and than encodend into a Base64 string:
 * 
 * cml:<license level>:<domain name>:<expire date>
 * 
 * <license level> - 0, 1, 2, ... 0 is the highest/full level
 * <domain name> - the domain that the license is issued for. if starts with '.', subdomains are allowed
 * <expire date> - the date until the license is valid, in the YYYYMMDD format
 * 
 * example:
 * cml:0:.sitebop.com:20101001
 * 
 * The RSA keys can be of any length but, because of the computation load on the client side,
 * it should not exceed 768 bits.
 */
interface LicenseService
{
	
	/**
	 * Returns the sitecake editor license.
	 * 
	 * The response is an array with the following elements:
	 * <code>status</code> - int, possible outcomes:
	 * -1 - call failed because of an (execution) error
	 *  0 - the license found and returned
	 *  1 - the license does not exists
	 *  
	 * <code>errorMessage</code> - string, present if <code>status</code> is -1 or 1
	 * <code>license</code> - the license itself
	 * 
	 * @return array the service response
	 */	
	public function get();	
}

?>