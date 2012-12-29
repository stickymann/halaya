<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(

	'driver'       => 'ORM',
	'hash_method'  => 'sha256',
	'hash_key'     => 'as',
	'lifetime'     => 28800,
	'session_type' => Session::$default,
	'session_key'  => 'auth_user1',

	// Username/password combinations for the Auth File driver
	'users' => array(
		'admin' => 'b3154acf3a344170077d11bdb5fff31532f679a1919e716a02',
	),
);
