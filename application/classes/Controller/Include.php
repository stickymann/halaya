<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Include controller for all global css and javascript. 
 *
 * $Id: Include.php 2012-12-28 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license     
 */
abstract class Controller_Include extends Controller 
{
	/**
	 *  Setup routine.
	 */
	public $global_app_title = "Halaya";
	public $auto_render = TRUE;

	//javascript
	public $randomstring = ""; 
	public $js = array(
		'jquery' => 'media/js/jquery-1.8.3.min.js',
		//'jquery' => 'media/js/jquery-1.7.min.js',
		'easyui' => 'media/js/jquery.easyui.min.js',
		'datepick' => 'media/js/jquery.datepick.js',
		'jquery_form' => 'media/js/jquery.form-2.4.0.min.js',
		'jqeasy_dropdown' => 'media/js/jqeasy.dropdown.min.js',
		'cookie' => 'media/js/jquery.cookie.js',
		'treeview' => 'media/js/jquery.treeview.js',
		'tablesorter' => 'media/js/jquery.tablesorter.js',
		'tablesorterpager' => 'media/js/jquery.tablesorter.pager.js',
		'datevalidate' => 'media/js/jquery.datevalidate.js',
		'siteutils' => 'media/js/core.siteutils.js',
		'sideinfo' => 'media/js/core.sideinfo.js',
		'enquiry' => 'media/js/core.enquiry.js',
		'popout' => 'media/js/core.popoutselector.js'
	);

	//css
	public $css = array(
		'easyui'=> 'media/css/easyui/gray/easyui.css',
		'easyui_icon' => 'media/css/easyui/icon.css',
		'easyui_gray' => 'media/css/easyui/gray/easyui.css',
		'site' => 'media/css/site.css',
		'datepick' => 'media/css/custom.datepick.css',
		'jqeasy' => 'media/css/jqeasy.css',
		'treeview' => 'media/css/jquery.treeview.css',
		'screen' => 'media/css/screen.css',
		'tablesorterblue' => 'media/css/tablesorterblue.css',
		'tablesortergreen' => 'media/css/tablesortergreen.css'
	);

	//site logos. icons and others images
	public $img = array(
		'logo_front' => 'media/img/login/halaya.750w.png',
		'logo_app' => 'media/img/banner/halaya.logo.small.png',
		'signout' => 'media/img/banner/logout7525.jpg'
	);

	public function __construct()
    {
		parent::__construct(Request::initial(),new Response);
     	/** 
		 *	Random string injection to prevent javascript caching
		 */
		if ($this->auto_render === TRUE)
		{
			// Load the template
			
			$this->template = View::factory($this->template);
			$this->template->title = $this->get_htmlhead_title();
			$this->template->head = "";
		}
	
		foreach($this->img as $key => $value)
		{
			$this->img[$key] = URL::base().$value;
		}
		
		$rs = new Controller_Core_Siterandstr();
		$this->randomstring	= sprintf('?rash=%s',$rs->get_random_string());
		foreach($this->js as $key => $value)
		{
			$this->js[$key] = $this->randomize($value);
		}
	}
	
	public function randomize($str)
	{
		return $str.$this->randomstring;
	}

	public function get_htmlhead_title()
	{
		$head = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n";
		$head .= sprintf('<title>%s</title>',$this->global_app_title)."\n";
		return $head;
	}
	
	public function after()
	{
		if ($this->auto_render === TRUE)
		{
			$this->response->body( $this->template->render() );
		}
		parent::after();
	}

} // End Include
