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
		$user = ORM::factory('User')->where('id','=',$_POST['id'])->find();
		$role = ORM::factory('Role')->where('name','=','login')->find();

		//delete old user roles
		$querystr = sprintf('delete from %s where user_id = "%s" and role_id !="%s"','roles_users',$user->id,$role->id);
		if($this->param['primarymodel']->execute_delete_query($querystr))
		{
			//insert new user roles
			$rolecount = count($rolelist = preg_split('/,/',$_POST['roles']));
			if(!($rolelist[0] == ''))
			{
				foreach($rolelist as $key => $val)
				{
					$user->add('roles', ORM::factory('Role', array('name' => $val)));
					$user->save();
				}
			}
		}
		else
			$this->param['htmlbody']->pagebody= $this->param['primarymodel']->get_db_err_msg();
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
		$post = $_POST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('idname','not_empty')
			->rule('idname','min_length', array(':value', 3))->rule('idname','max_length', array(':value', 50))
			->rule('idname', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['idname']));
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Core_Useradmin_Userrole
