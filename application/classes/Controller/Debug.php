<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class of useful PHP debugging functions. 
 *
 * $Id: Debug.php 2012-12-31 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Debug extends Controller
{	
	public static function info() 
	{ 
		$file = 'n/a'; 
		$func = 'n/a'; 
		$line = 'n/a'; 
		$debugTrace = debug_backtrace(); 
		if(isset($debugTrace[1])) 
		{ 
			$file = $debugTrace[1]['file'] ? $debugTrace[1]['file'] : 'n/a'; 
			$line = $debugTrace[1]['line'] ? $debugTrace[1]['line'] : 'n/a'; 
		} 
		if(isset($debugTrace[2])) $func = $debugTrace[2]['function'] ? $debugTrace[2]['function'] : 'n/a'; 
		print "<pre>\n$file, $func, $line\n</pre>"; 
	} 

} // End Debug