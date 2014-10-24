<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Auth/ORM User model.
 *
 * $Id: User.php 2012-12-29 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module		core
 * @author		Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright	(c) 2013
 * @license		
 */
class Model_User extends Model_Auth_User
{
	protected $_has_many = array('roles' => array('through' => 'roles_users'), 'user_tokens' => array());
}
