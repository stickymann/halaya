<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Deliverynotes enquiry. 
 *
 * $Id: Deliverynotes.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Enquiry_Deliverynotes extends Controller_Core_Sitequiry
{

	public function __construct()
    {
		parent::__construct('deliverynotes_enq');
	}	
		
	public function action_index()
    {
      $this->process_request();
    }

} //End Controller_Core_Enquiry_Deliverynotes
