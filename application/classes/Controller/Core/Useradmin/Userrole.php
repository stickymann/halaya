<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Assigns roles to user. 
 *
 * $Id: Userrole.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Useradmin_Userrole extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("userrole");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.userrole.js') );
	}

	function insert_roles_users()
	{
		$user = ORM::factory('User')->where('idname','=',$this->OBJPOST['idname'])->find();
		$role = ORM::factory('Role')->where('name','=','login')->find();
		
		//delete old user roles
		$querystr = sprintf('DELETE FROM %s WHERE user_id = "%s" AND role_id !="%s"','roles_users',$user->id,$role->id);
		if( $this->param['primarymodel']->execute_delete_query($querystr) ) { /* wait for deletions */ }
		
		//insert new user roles
		$rolecount = count($rolelist = preg_split('/,/',$this->OBJPOST['roles']));
		if(!($rolelist[0] == ''))
		{
			foreach($rolelist as $key => $val)
			{
				$role = ORM::factory('Role')->where('name','=',$val)->find();
				$querystr = sprintf('INSERT INTO %s (user_id,role_id) values("%s","%s")',"roles_users",$user->id,$role->id);
				if( $this->param['primarymodel']->execute_insert_query($querystr) ) { /* wait for insertions */ }
			}
		}
	}
	
	public function authorize_post_insert_new_record()
	{
		$this->insert_roles_users();
	}
	
	public function authorize_post_update_existing_record()
	{
		$this->insert_roles_users();
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
			->rule('idname','not_empty')
			->rule('idname','min_length', array(':value', 3))->rule('idname','max_length', array(':value', 50))
			->rule('idname', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['idname']));
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Core_Useradmin_Userrole
