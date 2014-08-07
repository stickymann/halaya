<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Concatenates text into one string. 
 *
 * $Id: Sitehtml.php 2012-12-31 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sitehtml extends Controller
{
	private $html = '';
	
	public function __construct($string='')
	{
		//parent::__construct();
		$this->html = $string."\n"; 
	}
	
	/**
     * Adds text and/or html
     *
     * @param   string  $string  The text to add
     */
	public function add($string)
	{
		$this->html .= $string."\n";
	}

	/**
     * Gets concatenated text and/or html
     *
     * @return  string			The concatenated text and/or html
     */
	public function get_html()
	{
		return $this->html;
	}
	
	/**
     * Gets text or html from a url via curl
     *
     * @return  string			The url text or html
     */
	public static function get_html_from_url($url)
	{
		//requires curl extension to be turned on
		/* STEP 1. let’s create a cookie file */
		//$ckfile = tempnam ("/tmp", "CURLCOOKIE"); 
		
		/* STEP 2. visit the homepage to set the cookie properly */
		//$ch = curl_init ("app");
		//curl_setopt ($ch, CURLOPT_COOKIEJAR, $ckfile);
		//curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		//$output = curl_exec ($ch);
		
		/* STEP 3. visit required url*/
		ob_start();
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 0);
		//curl_setopt ($ch, CURLOPT_COOKIEFILE, $ckfile);
		//curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		$ok = curl_exec($ch);
		curl_close($ch);
		$text = ob_get_contents();
		ob_end_clean();	
		return $text;
		//other options,
		//curl_setopt($ch, CURLOPT_HEADER, 1);
		//curl_setopt($ch, CURLOPT_NOBODY, 1);
		//curl_setopt($ch, CURLOPT_URL,$url);
		//curl_setopt($ch, CURLOPT_POST, 0);
		//curl_setopt($ch, CURLOPT_USERPWD, $cred)
	}

} //End Core_Sitehtml

