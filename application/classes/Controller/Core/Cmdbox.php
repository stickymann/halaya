<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Displays login and system information. 
 *
 * $Id: Cmdbox.php 2012-12-28 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license    
 */
class Controller_Core_Cmdbox extends Controller_Include
{
	public $template = 'cmdbox.view';

	public function before()
    {
		parent::before();
		$this->template->head = $this->get_htmlhead();
		$this->template->idname = HTML::chars(Auth::instance()->get_user()->idname);
		$this->template->username = HTML::chars(Auth::instance()->get_user()->username);
		
		$sysconfig = ORM::factory('Sysconfig')->where('sysconfig_id','=',"SYSTEM")->find();
		$this->template->app_version = $sysconfig->sysconfig_id;
		$this->template->db_version = $sysconfig->db_version;
		$this->template->environment = $sysconfig->environment;
	}
		
	public function action_index() {}

	function get_htmlhead()
	{	
		$head = sprintf('%s',HTML::style($this->css['site'], array('screen')))."\n"; 
		return $head;	
	}
   
} //End Core_Cmdbox
