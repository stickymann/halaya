<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Create controller parameter definition record. 
 *
 * $Id: Param.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Developer_Param extends Controller_Core_Site
{
	public function __construct() 
	{
		parent::__construct("param");
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
			->rule('param_id','not_empty')
			->rule('param_id','min_length', array(':value', 3))->rule('param_id','max_length', array(':value', 255))
			->rule('param_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['param_id']));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function insert_sys_autoid_startnum()
	{
		$querystr = sprintf('INSERT INTO _sys_autoids(tb_inau,counter) VALUES("%s","%s");', $_POST['tb_inau'], "1001");
		$this->param['primarymodel']->execute_insert_query($querystr);
	}

	public function authorize_post_insert_new_record()
	{
		$this->insert_sys_autoid_startnum();
	}

}//End Controller_Core_Developer_Param