<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'idname' => array
    (
        'not_empty' => 'User Id: required.',
        'alpha' => 'User Id: must have only alphabetic characters.',
        'min_length' => 'User Id: must be 3 - 50 characters.',
		'max_length' => 'User Id: must be 3 - 50 characters.',
        'msg_duplicate' => 'User Id: duplicate user id.',
		'default' => 'User Id: invalid input.'
    )
);