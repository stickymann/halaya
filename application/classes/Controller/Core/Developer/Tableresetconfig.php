<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a database table reset configuration record. 
 *
 * $Id: Tableresetconfig.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Developer_Tableresetconfig extends Controller_Core_Site
{
	public function __construct() 
	{
		parent::__construct("tableresetconfig");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}

	public function action_index($opt="")
	{
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.tableresetconfig.js') );
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
			->rule('reset_id','not_empty')
			->rule('reset_id','min_length', array(':value', 1))->rule('reset_id','max_length', array(':value', 50))
			->rule('reset_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['reset_id']));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

}//End Controller_Core_Developer_Tableresetconfig