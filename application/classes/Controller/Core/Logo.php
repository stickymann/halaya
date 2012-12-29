<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Displays organization mini logo with application. 
 *
 * $Id: Siteradstr.php 2012-12-28 00:00:00 dnesbit $
 *
 * @application Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license    
 */
class Controller_Core_Logo extends Controller_Include
{
    public $template = 'logo.view';

	public function before()
    {
		parent::before();
		$this->template->head = $this->get_htmlhead();
		$this->template->logo_app = $this->img['logo_app'];
		$this->template->img_signout = $this->img['signout'];
	}
	
	public function action_index() {}

	function get_htmlhead()
	{	
		$head = sprintf('%s',HTML::style($this->css['site'], array('screen')))."\n"; 
		return $head;	
	}
 
} //End Core_Logo
