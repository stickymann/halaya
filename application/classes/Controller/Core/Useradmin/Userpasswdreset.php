<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Changes a user's password via administrative user. 
 *
 * $Id: Userpasswdreset.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Useradmin_Userpasswdreset extends Controller_Core_Site
{
    public function __construct()
    {
		parent::__construct("userpasswdreset");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		$TEXT=<<<_text_
		<script type="text/javascript">
		$(document).ready(function()
		{
			$('#password').val("");
		});
		</script>
_text_;
		return $TEXT;
	}

	function input_validation()
	{
		//encrypt password
		$this->OBJPOST['password'] = Auth::instance()->hash_password($this->OBJPOST['password']);
		
		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('password','not_empty')
			->rule('password','min_length', array(':value', 64))->rule('password','max_length', array(':value', 64));
	
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
} // End Controller_Core_Useradmin_Userpasswdreset
