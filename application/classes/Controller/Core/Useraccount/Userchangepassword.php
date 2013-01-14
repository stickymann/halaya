<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Changes user's own password. 
 *
 * $Id: Userchangepassword.php 2012-01-12 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Useraccount_Userchangepassword extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("userchangepassword");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.userchangepassword.js') );
	}
	
	function input_validation()
	{
		//encrypt password
		$_POST['password'] = Auth::instance()->hash_password($_POST['password']);
		
		$post = $_POST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('password','not_empty')
			->rule('subject','min_length', array(':value', 64))->rule('subject','max_length', array(':value', 64));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
} // End Core_Useraccount_Userchangepassword
