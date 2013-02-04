<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Runs table reset. 
 *
 * $Id: Tableresetrun.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Developer_Tableresetrun extends Controller_Core_Site
{
	public function __construct() 
	{
		parent::__construct("tableresetrun");
	}

	public function action_index($opt="")
	{
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}

	function input_validation()
	{
		$_POST['csv_id']	= strtoupper($_POST['csv_id']);
		
		$post = $_POST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('resetconfig_id','not_empty')
			->rule('resetconfig_id','min_length', array(':value', 3))->rule('resetconfig_id','max_length', array(':value', 30))
			->rule('resetconfig_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['resetconfig_id']));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

}//End Controller_Core_Developer_Tableresetrun