<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.',
    ),
	
	'name' => array
    (
        'not_empty' => 'Role Name: required.',
        'alpha' => 'Role Name: must have only alphabetic characters.',
		'min_length' => 'Role Name: must be between 3 - 50 characters.',
        'max_length' => 'Role Name: must be between 3 - 50 characters.',
        'msg_duplicate' => 'Role Name: duplicate role name.',
		'default' => 'Role Name: invalid input.',
    ),

	'description' => array
    (
        'not_empty' => 'Description: required.',
        'alpha' => 'Description: must have only alphabetic characters.',
		'min_length' => 'Description: must be between 3 - 50 characters.',
        'max_length' => 'Description: must be between 3 - 50 characters.',
		'default' => 'Description: invalid input.',
    )
);