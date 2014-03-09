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

	public function __construct()
    {
		parent::__construct();
		$this->template->head = $this->get_htmlhead();
		$this->template->idname = HTML::chars(Auth::instance()->get_user()->idname);
		$this->template->username = HTML::chars(Auth::instance()->get_user()->username);
		
		$sysconfig = ORM::factory('Sysconfig')->where('sysconfig_id','=',"SYSTEM")->find();
		$this->template->app_version = substr($sysconfig->app_version, 0, 10);
		$this->template->db_version = $sysconfig->db_version;
		$this->template->environment = $sysconfig->environment;
	}
		
	public function action_index() {}

	function get_htmlhead()
	{	
		$head = sprintf('%s',HTML::style($this->css['site'], array('screen')))."\n"; 
		$head .= sprintf('%s',HTML::script($this->js['jquery']))."\n";
		$head .= sprintf('%s',HTML::script($this->js['siteutils']))."\n";
		$head .= HTML::script( $this->randomize('media/js/hndshkif.cmdbox.js') )."\n";
		return $head;	
	}
   
} //End Core_Cmdbox
