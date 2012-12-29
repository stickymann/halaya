<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Auth/ORM Role controller. 
 *
 * $Id: Role.php 2012-12-29 00:00:00 dnesbit $
 *
 * @application Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license    
 */
/*
class Model_Role extends ORM 
{
	protected $has_and_belongs_to_many = array('users');
 
	public function unique_key($id = NULL)
	{
		if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id) )
		{
			return 'name';
		}
 
		return parent::unique_key($id);
	}
}
*/
class Model_Role extends Model_Auth_Role
{
	protected $_has_many = array('users' => array('through' => 'roles_users'), 'user_tokens' => array());
}
