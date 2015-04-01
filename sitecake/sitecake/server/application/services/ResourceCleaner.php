<?php

/**
 * Implementations of this interface are responsible of cleaning
 * the content storage of resources that are no longer needed.
 * For example, for removing uploaded image files that are no more
 * referenced from the content HTML.
 * Also, the implementation should split the cleaning process in more
 * steps in order to reduce the time needed to complete the request
 * that it works within.
 */
interface ResourceCleaner
{
	/**
	 * Cleans everything :)
	 */
	public function cleanup();
}