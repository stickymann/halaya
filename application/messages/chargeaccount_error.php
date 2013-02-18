<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'customer_id' => array
    (
        'not_empty' => 'Customer Id: required.',
		'min_length' => 'Customer Id: must be 8 characters.',
        'max_length' => 'Customer Id: must be 8 characters.',
        'msg_duplicate' => 'Customer Id: duplicate customer id.',
		'default' => 'Customer Id: invalid input.'
    ),
		
	'activation_date' => array
    (
        'date' => 'Activation Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Activation Date: invalid input.'
    ),

	'status_change_date' => array
    (
        'date' => 'Status Change Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Status Change Date: invalid input.'
    ),
	
	'active' => array
    (
        'not_empty' => 'Active: required, must be[Y/N].',
        'in_array' => 'Active: must be[Y/N].',
        'default' => 'Active: invalid input.'
    )
);
