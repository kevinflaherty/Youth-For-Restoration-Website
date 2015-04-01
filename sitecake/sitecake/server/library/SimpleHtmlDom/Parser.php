<?php

require_once 'simple_html_dom.php';

class SimpleHtmlDom_Parser extends simple_html_dom
{
	public static function file_get_html() {
		return call_user_func_array('file_get_html', $args);
	}
	
	public static function str_get_html($str, $lowercase=true) {
		return str_get_html($str, $lowercase);
	}
	
	public static function dump_html_tree($node, $show_attr=true, $deep=0) {
		return dump_html_tree($node, $show_attr, $deep);
	}
}