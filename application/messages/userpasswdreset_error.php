<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'password' => array
    (
        'not_empty' => 'User Password: required.',
        'alpha' => 'User Password: must have only alphabetic characters.',
		'min_length' => 'Recipient: must be 1 - 50 characters.',
		'max_length' => 'Recipient: must be 1 - 50 characters.',
		'default' => 'User Password: invalid input.'
    )
);