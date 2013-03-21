<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Adds users to system. 
 *
 * $Id: Useradmin.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Useradmin_Useradmin extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("useradmin");
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}

	function input_validation()
	{
		$this->OBJPOST['idname']	= strtoupper($this->OBJPOST['idname']);
		$this->OBJPOST['fullname']	= $this->strtotitlecase($this->OBJPOST['fullname']);

		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('idname','not_empty')
			->rule('idname','min_length', array(':value', 3))->rule('idname','max_length', array(':value', 50))
			->rule('idname', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['idname']));
		$validation
			->rule('username','not_empty')
			->rule('username','min_length', array(':value', 3))->rule('username','max_length', array(':value', 32))
			->rule('username', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['username']));
		$validation
			->rule('fullname','not_empty')
			->rule('fullname','min_length', array(':value', 3))->rule('fullname','max_length', array(':value', 255));
		$validation
			->rule('email','not_empty')
			->rule('email','email');
		$validation
			->rule('enabled','not_empty')
			->rule('enabled','max_length', array(':value', 1));
		$validation
			->rule('expiry_date','date');
		$validation
			->rule('branch_id','not_empty')
			->rule('branch_id','min_length', array(':value', 2))->rule('branch_id','max_length', array(':value', 50));
		$validation
			->rule('department_id','not_empty')
			->rule('department_id','min_length', array(':value', 2))->rule('department_id','max_length', array(':value', 50));
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	function authorize_post_insert_new_record()
	{
		$user = ORM::factory('User')->where('id','=',$this->OBJPOST['id'])->find();
		$user->add('roles', ORM::factory('Role', array('name' => 'login')));
		$user->save();
	}

} // End Controller_Core_Useradmin_Useradmin