<?php

/**
 * RenderService is responsible for processing all HTML page requests. That is, all
 * requests that are expected to return an HTML page, either in public or edit mode.
 */
interface RenderService
{
	/**
	 * Renders the requested page using the given view for the given URL path.
	 * The output page has to be rendered accordingly to the current session mode: public or edit mode.
	 * 
	 * If the requested page's (template) HTML contains the following meta tag, the page is always rendered
	 * without any sitecake code regardless of the session mode (public or edit) and 
	 * the meta tag may be removed from the output text:
	 * 
	 * <code>&lt;meta name="sitecake" content="exclude"&gt;</code>
	 * 
	 * Every rendered HTML page that contains SiteCake, regardless of the current session mode must contain
	 * a global JavaScript object - 'sitecakeGlobals'. The following is the list of sitecakeGlobals' properties:
	 * 
	 * editMode - (public, edit) a boolean value (true|false) that signas if the sitecake is in the editing mode
	 * forceLoginDialog - (public) a boolean value (true|false) that signals if the login dialog should be displayed
	 * sessionId - (public, edit) the server-side session ID. Every (AJAX) service request includes this ID
	 * serverVersionId - (public, edit) the SiteCake's server-side version identifier
	 * sessionServiceUrl - (public, edit) the session service's URL (login, logout, heartbeat, etc.)
	 * uploadServiceUrl - (edit) the upload service's URL
	 * contentServiceUrl - (edit) the content service's URL
	 * licenseServiceUrl - (edit) the license service's URL
	 * draftPublished - (edit) boolean, true if the draft has been published
	 * config - (public, edit) a object that contains additional sitecake editor settings
	 *
	 * @param $requestPath string a requested URL path that points to a HTML page
	 * @return string the rendered page
	 */
	public function render($requestPath, $loginAttempt);	
}

?>