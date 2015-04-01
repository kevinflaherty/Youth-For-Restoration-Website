<?php

/**
 * The <code>SessionService</code> is used by the sitecake editor to
 * establish, maintain and terminate the editing/admin session.
 * Upon an successfull <code>login()</code> call, the session is started.
 * It lasts until any of the following situations:
 * - an successfull <code>logout()</code> call
 * - the session is expired (by the session timeout)
 * Calling the <code>alive()</code> method, the session timeout timer is resetted. 
 */
interface SessionService
{
	/**
	 * Starts the admin session if the given credential is valid.
	 * Except the <code>RenderService</code>, this is the only service
	 * method that does not requre an authorization check.
	 * 
	 * The response is an array with the following elements:
	 * <code>status</code> - int, possible outcomes:
	 * -1 - call failed because of an (execution) error
	 *  0 - login granted, the session is started
	 *  1 - login failed because of invalid credential
	 *  2 - login failed because the admin session has already begun (locked)
	 *  3 - login failed because of some other reason (the reason decription will be present in the errorMessage)
	 *  
	 * <code>errorMessage</code> - string, present if <code>status</code> is -1 or 3
	 * 
	 * @param string $credential the authentication credential, SHA1 hex hash of the admin password
	 * @return array the service response
	 */
	public function login($credential);

	/**
	 * Requests the authorization credential to be changed/replaced by the new one.
	 *
	 * The response is an array with the following elements:
	 * <code>status</code> - int, possible outcomes:
	 * -1 - call failed because of an (execution) error
	 *  0 - the new credential accepted
	 *  1 - the request failed because of invalid credential
	 *  2 - the new credential is not acceptable
	 *  
	 * <code>errorMessage</code> - string, present if <code>status</code> is -1
	 *
	 * @param string $credential the currently valid credential
	 * @param string $newCredential the new credential
	 * @return array the service response
	 */
	public function change($credential, $newCredential);
	
	/**
	 * Ends the admin session.
	 * 
	 * The response is an array with the following elements:
	 * <code>status</code> - int - 0 if OK, -1 the service call failed
	 * <code>errorMessage</code> - string, present if <code>status</code> is not 0
	 * 
	 * @return array the service response
	 */
	public function logout();

	/**
	 * Refreshes the admin session by resetting the session timeout timer.
	 * 
	 * The response is an array with the following elements:
	 * <code>status</code> - int - 0 if OK, -1 the service call failed
	 * <code>errorMessage</code> - string, present if <code>status</code> is not 0
	 * 
	 * @return array the service response
	 */
	public function alive();
}

?>