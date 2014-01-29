<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates roles and assigns permissions to roles. 
 *
 * $Id: Roleadmin.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Useradmin_Roleadmin extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("roleadmin");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		$head = sprintf('%s',HTML::style($this->css['treeview'], array('screen')))."\n";
		$head .= sprintf('%s',HTML::script($this->js['treeview']))."\n";
		$head .= HTML::script( $this->randomize('media/js/core.roleadmin.js') );
		return $head;
	}

	function input_validation()
	{
		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('name','not_empty')
			->rule('name','min_length', array(':value', 3))->rule('name','max_length', array(':value', 50))
			->rule('name', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['name']));
		$validation
			->rule('description','not_empty')
			->rule('description','min_length', array(':value', 3))->rule('description','max_length', array(':value', 50));
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
}// End Controller_Core_Useradmin_Roleadmin