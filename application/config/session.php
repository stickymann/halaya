<?php defined('SYSPATH') OR die('No direct script access.');

return array(
	'cookie' => array(
		'name' => 'aX53eT97Yk',
		'lifetime' => 1314000,
		'encrypted' => FALSE,
	),
);

/*
return array(
    'native' => array(
        'name' => 'halaya_session_native',
        'lifetime' => 1209600,
		'encrypted' => FALSE,
    ),
    'cookie' => array(
        'name' => 'halaya_session_cookie',
        'encrypted' => TRUE,
        'lifetime' => 28800,
    ),
    'database' => array(
        'name' => 'halaya_session_database',
        'encrypted' => TRUE,
        'lifetime' => 28800,
        'group' => 'default',
        'table' => '_sys_sessions',
        'columns' => array(
            'session_id'  => 'session_id',
            'last_active' => 'last_active',
            'contents'    => 'contents'
        ),
        'gc' => 500,
    ),
);
*/
