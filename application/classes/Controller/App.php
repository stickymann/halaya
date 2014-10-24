<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Main application layout. 
 *
 * $Id: App.php 2012-12-28 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_App extends Controller_Include
{
	public $template = 'app.iframe.view';
	
	public function __construct()
	{
		parent::__construct();
		$this->template->head = $this->get_htmlhead($this->global_app_title);
		$this->template->orgname = $this->global_app_title;
	}
	
    public function action_index() {}
	
	function get_htmlhead()
	{	
		$head = sprintf('%s',HTML::style($this->css['site'], array('screen')))."\n"; 
		$head .= sprintf('%s',HTML::style($this->css['easyui_gray'], array('screen')))."\n"; 
		$head .= sprintf('%s',HTML::style($this->css['easyui_icon'], array('screen')))."\n"; 
		$head .= sprintf('%s',HTML::script($this->js['jquery']))."\n";
		$head .= sprintf('%s',HTML::script($this->js['easyui']));
		return $head;	
	}
	
} //End App

