<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Generates random text string. 
 *
 * $Id: Siteradstr.php 2012-12-28 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license    
 */
class Controller_Core_Siterandstr extends Controller
{
	private $randstr = '';
    private $randnum = '';
	
	public function __construct($length=10)
	{
		$this->randstr_gen($length);
        $this->randnum_gen();
	}

	public function get_random_string()
	{
		return $this->randstr;
	}
    
    public function get_random_number()
	{
		return $this->randnum;
	}

	public function get_new_random_string($length=10)
	{
		$this->randnum_gen($length);
		return $this->randnum;
	}
    public function get_new_random_number()
	{
		$this->randstr_gen();
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
    
    function randnum_gen()
	{
        $this->randnum = rand(1,1000000000);
    }
    
} //End Core_Siterandstr

