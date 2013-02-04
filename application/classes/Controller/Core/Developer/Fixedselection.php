<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates fixedselection record. 
 *
 * $Id: Fixedselection.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Developer_Fixedselection extends Controller_Core_Site
{
	public function __construct() 
	{
		parent::__construct("fixedselection");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}

	public function action_index($opt="")
	{
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.fixselection.js') );
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
			->rule('fixedselection_id','not_empty')
			->rule('fixedselection_id','min_length', array(':value', 1))->rule('fixedselection_id','max_length', array(':value', 50))
			->rule('fixedselection_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['fixedselection_id']));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

}//End Controller_Core_Developer_Fixedselection
