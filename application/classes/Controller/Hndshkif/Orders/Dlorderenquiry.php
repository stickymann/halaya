<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Dlorders enquiry. 
 *
 * $Id: Orders.php 2014-09-05 00:00:00 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
class Controller_Hndshkif_Orders_Dlorderenquiry extends Controller_Core_Sitequiry
{

	public function __construct()
    {
		parent::__construct('dlorderenquiry');
	}	
		
	public function action_index()
    {
      $this->process_request();
    }

} //End Controller_Hndshkif_Orders_Dlorderenquiry
