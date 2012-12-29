<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Generates random text string. 
 *
 * $Id: Siteradstr.php 2012-12-28 00:00:00 dnesbit $
 *
 * @application Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license    
 */
class Controller_Core_Siterandstr extends Controller
{
	private $randstr = '';
	
	public function __construct($length=10)
	{
		$this->randstr_gen($length);
	}

	public function get_random_string()
	{
		return $this->randstr;
	}

	public function get_new_random_string($length=10)
	{
		$this->randstr_gen($length);
		return $this->randstr;
	}
	
	function randstr_gen($length)
	{
		$random= "";
		srand((double)microtime()*1000000);
		$char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$char_list .= "abcdefghijklmnopqrstuvwxyz";
		$char_list .= "1234567890";
		// Add the special characters to $char_list if needed

		for($i = 0; $i < $length; $i++)  
		{    
			$random .= substr($char_list,(rand()%(strlen($char_list))), 1);  
		}  
		$this->randstr = $random;
	}

} //End Core_Siterandstr

