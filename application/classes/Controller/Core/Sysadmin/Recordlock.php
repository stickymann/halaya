<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Clears a recordlock. 
 *
 * $Id: Recordlock.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Sysadmin_Recordlock extends Controller_Core_Site
{
	public function __construct() 
	{
		parent::__construct("recordlock");
	}

	public function action_index($opt="")
	{
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
		
	public function livedelete()
	{
		$this->param['primarymodel']->remove_record_lock_by_id($this->param['indexfieldvalue']);
		HTTP::redirect($this->param['param_id']);
	}
	
}//End Controller_Core_Sysadmin_Recordlock