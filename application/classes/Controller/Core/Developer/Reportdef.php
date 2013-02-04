<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates report definition record. 
 *
 * $Id: Reportdef.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Developer_Reportdef extends Controller_Core_Site
{
	public function __construct() 
	{
		parent::__construct("reportdef");
	}

	public function action_index($opt="")
	{
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}

	function input_validation()
	{
		$post = $_POST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('reportdef_id','not_empty')
			->rule('reportdef_id','min_length', array(':value', 3))->rule('reportdef_id','max_length', array(':value', 255))
			->rule('reportdef_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['reportdef_id']));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

}//End Controller_Core_Developer_Reportdef