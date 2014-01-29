<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate Id.',
		'default' => 'Id: invalid input.'
    ),
	
	'till_id' => array
    (
        'not_empty' => 'Till Id: required.',
        'min_length' => 'Till Id: must be 2 - 59 characters.',
		'max_length' => 'Till Id: must be 2 - 59 characters.',
        'msg_duplicate' => 'Till Id: duplicate Till Id.',
		'default' => 'Till Id: invalid input.'
    ),

	'till_user' => array
    (
        'not_empty' => 'Till User: required.',
        'min_length' => 'Till User: must be 2 - 50 characters.',
		'max_length' => 'Till User: must be 2 - 50 characters.',
		'default' => 'Till User: invalid input.'
    ),

	'till_date' => array
    (
        'not_empty' => 'Till Date: required.',
        'date' => 'Till Date: format is incorrect (YYYY-MM-DD).',
		'default' => 'Till Date: invalid input.'
    ),

	'initial_balance' => array
    (
        'not_empty' => 'Initial Balance: required.',
		'numeric' => 'Initial Balance: must be numeric.',
        'default' => 'Initial Balance: invalid input.'
    ),
	
	'status' => array
    (
        'not_empty' => 'Status: required.',
		'min_length' => 'Status: must be 2 - 20 characters.',
        'max_length' => 'Status: must be 2 - 20 characters.',
		'default' => 'Status: invalid input.'
    ),

	'expiry_date' => array
    (
        'not_empty' => 'Expiry Date: required.',
        'date' => 'Expiry Date: format is incorrect (YYYY-MM-DD).',
		'default' => 'Expiry Date: invalid input.'
    ),

	'expiry_time' => array
    (
        'not_empty' => 'Expiry Time: not_empty, must be (HH:MM:SS).',
		'min_length' => 'Expiry Time: must be (HH:MM:SS).',
        'max_length' => 'Expiry Time: must be (HH:MM:SS).',
        'default' => 'Expiry Time: invalid input.'
    )
);