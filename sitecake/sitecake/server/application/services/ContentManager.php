<?php

interface ContentManager {
	public function isPublicContentSet($container);
	public function getPublicContent($container);
	public function setPublicContent($container, $content);
	public function isDraftContentSet($container);
	public function getDraftContent($container);
	public function setDraftContent($container, $content);
	public function getAllPublicContainers();
	public function getAllDraftContainers();
}