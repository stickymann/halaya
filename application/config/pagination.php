<?php defined('SYSPATH') or die('No direct script access.');

return array(

	// Application defaults
	'default' => array
	(
		'current_page'      => array('source' => 'query_string', 'key' => 'page'), // source: "query_string" or "route"
		'view'              => 'pagination/floating',
		'uri_segment'		=> 'opt',
		'auto_hide'         => FALSE,
		'first_page_in_url' => FALSE,
	),
);

/** 2.3 Config
 * @package  Pagination
 *
 * Pagination configuration is defined in groups which allows you to easily switch
 * between different pagination settings for different website sections.
 * Note: all groups inherit and overwrite the default group.
 *
 * Group Options:
 *  directory      - Views folder in which your pagination style templates reside
 *  style          - Pagination style template (matches view filename)
 *  uri_segment    - URI segment (int or 'label') in which the current page number can be found
 *  query_string   - Alternative to uri_segment: query string key that contains the page number
 *  items_per_page - Number of items to display per page
 *  auto_hide      - Automatically hides pagination for single pages
 */
/*
$config['default'] = array
(
	'directory'      => 'pagination',
	'style'          => 'digg',
	'uri_segment'    => 3,
	'query_string'   => '',
	'items_per_page' => 1,
	'auto_hide'      => FALSE,
);
	'default' => array
	(
		'current_page'      => array('source' => 'query_string', 'key' => 'page'), // source: "query_string" or "route"
		'total_items'       => 0,
		'items_per_page'    => 10,
		'view'              => 'pagination/basic',
		'auto_hide'         => TRUE,
		'first_page_in_url' => FALSE,
	),




*/