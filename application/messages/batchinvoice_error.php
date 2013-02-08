<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'batch_id' => array
    (
        'not_empty' => 'Batch Id: required.',
		'min_length' => 'Batch Id: must be 16 characters.',
        'max_length' => 'Batch Id: must be 16 characters.',
        'msg_duplicate' => 'Batch Id: duplicate order id.',
		'default' => 'Batch Id: invalid input.'
    ),

	'batch_description' => array
    (
        'not_empty' => 'Batch Description: required.',
		'min_length' => 'Batch Description: must be 1 - 255 characters.',
        'max_length' => 'Batch Description: must be 1 - 255 characters.',
		'default' => 'Batch Description: invalid input.'
    ),
	
	'batch_date' => array
    (
        'not_empty' => 'Batch Date: required.',
		'min_length' => 'Batch Date: must be 10 characters.',
		'max_length' => 'Batch Date: must be 10 characters.',
        'date' => 'Batch Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Batch Date: invalid input.'
    ),
	
	'batch_details' => array
    (
        'not_empty' => 'Batch Details: required.',
		'zero_batchdetails' => 'Batch Details: zero orders selected, at least one required.', 
        'default' => 'Batch Details: invalid input.'
    )
);