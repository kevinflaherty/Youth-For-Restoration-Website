<?php

/**
 * Represents a sitecake page template and the template engine
 * at the same time.
 */
interface PageTemplate
{
	/**
	 * Initialize the template instance by the given
	 * page request (e.g. an request URL) that is to be mapped
	 * to an actual HTML page template.
	 *  
	 * @param string $request a page request
	 */
	public function setPageRequest($request);
	
	/**
	 * Checks if the page template has been modified/updated.
	 * 
	 * @return <code>true</code> if the template has been updated
	 */
	public function isUpdated();
	
	/**
	 * Checks if the page could be sc-editable. If the page
	 * is not editable, <code>getPage()</code> returns the
	 * page's static content.
	 * 
	 * @return true if the page should be edited
	 */
	public function isEditable();
	
	/**
	 * Returns an array with content containers names or
	 * an empty array in case the page is not editable.
	 */
	public function getPageContainers();
	
	/**
	 * Sets the page's header (e.g. script blocks).
	 * 
	 * @param string $content header content
	 */
	public function setHeader($content);

	/**
	 * Gets the normalized content of the specified container.
	 * A container content in normalized by removing any non supported
	 * content type.
	 * 
	 * The following is the list of currently supported content types:
	 * 
	 * HEADING1 - text - H1 tag with A,B,I,QUOTE tags
	 * HEADING2 - text - H2 tag with A,B,I,QUOTE tags
	 * HEADING3 - text - H3 tag with A,B,I,QUOTE tags
	 * HEADING4 - text - H4 tag with A,B,I,QUOTE tags
	 * HEADING5 - text - H5 tag with A,B,I,QUOTE tags
	 * HEADING6 - text - H6 tag with A,B,I,QUOTE tags
	 * TEXT     - text - P tag with A,B,I,QUOTE tags
	 * LIST		- text list (ordered, unordered, etc.) - UL tag
	 * IMAGE    - single image - IMG tag (or IMG within an A tag)
	 * FLASH	- any flash/swf content (DIV.sc-flash)
	 * VIDEO	- youtube/vimeo/etc. flash/html5 video (external streaming) - (DIV.sc-video)
	 * SLIDESHOW - image slide show (played with an onpage player) - (DIV.sc-slideshow)
	 * MAP		- google map widget (DIV.sc-map)
	 * HTML		- raw HTML block (DIV.sc-html)
	 * CONTACT	- contact box/form (DIV.sc-contact)
	 * 
	 * @param string $container the container name
	 * @return the normalized container content
	 */
	public function getPageContainer($container);
	
	/**
	 * Returns available styles (per content type) for all
	 * containers. The format of returend styles is as follows:
	 * 
	 * {'<container name 1>': {
	 * 			'<content type 1>': [ 'css class 1', 'css class 2', ...],
	 * 			'<content type 2>': [ 'css class 1', 'css class 2', ...]
	 * 		},
	 * 	'<container name 2>': {
	 * 			'<content type 1>': [ 'css class 1', 'css class 2', ...],
	 * 			'<content type 2>': [ 'css class 1', 'css class 2', ...]
	 * 		}
	 * 	]}
	 * 
	 * @return string JavaScript text
	 */
	public function getPageContainerStyles();
	
	/**
	 * Sets the given content into the specified container.
	 * 
	 * @param string $container the container name
	 * @param string $content the container content
	 */
	public function setPageContainer($container, $content);
	
	/**
	 * Returns the rendered page.
	 */
	public function renderPage();
}