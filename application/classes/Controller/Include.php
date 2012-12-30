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
class Controller_Include extends Controller_Template 
{
	/**
	 *  Setup routine.
	 */
	
	public $global_app_title = "Halaya";
	
	//javascript
	public $randomstring = ""; 
	public $js = array(
		'jquery' => 'media/js/jquery-1.8.3.min.js',
		'easyui' => 'media/js/jquery.easyui.min.js',
		'datepick' => 'media/js/jquery.datepick.js',
		'jquery_form' => 'media/js/jquery.form-2.4.0.min.js',
		'jqeasy_dropdown' => 'media/js/jqeasy.dropdown.min.js',
		'cookie' => 'media/js/jquery.cookie.js',
		'treeview' => 'media/js/jquery.treeview.js'
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
		'screen' => 'media/css/screen.css'
	);

	//site logos. icons and others images
	public $img = array(
		'logo_front' => 'media/img/login/halaya.750w.png',
		'logo_app' => 'media/img/banner/halaya.logo.small.png',
		'signout' => 'media/img/banner/logout7525.jpg'
	);

	public function before()
    {
		parent::before();
     	/** 
		 *	Random string injection to prevent javascript caching
		 */
		$this->template->title = $this->get_htmlhead_title();
		$this->template->head = "";
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

} // End Include
