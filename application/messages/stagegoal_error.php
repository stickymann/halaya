<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'status_id' => array
    (
        'not_empty' => 'Status Id: required.',
		'min_length' => 'Status Id: must be 2 - 50 characters.',
        'max_length' => 'Status Id: must be 2 - 50 characters.',
        'msg_duplicate' => 'Status Id: duplicate order id.',
		'default' => 'Status Id: invalid input.'
    ),
	
	'description' => array
    (
        'not_empty' => 'Description: required.',
		'min_length' => 'Description: must be 2 - 255 characters.',
        'max_length' => 'Description: must be 2 - 255 characters.',
		'default' => 'Description: invalid input.'
    ),
    
    'stage' => array
    (
        'not_empty' => 'Stage: required.',
		'min_length' => 'Stage: must be 2 - 255 characters.',
        'max_length' => 'Stage: must be 2 - 255 characters.',
		'default' => 'Stage: invalid input.'
    ),
    
    'goal' => array
    (
        'not_empty' => 'Goal: required.',
		'min_length' => 'Goal: must be 2 - 255 characters.',
        'max_length' => 'Goal: must be 2 - 255 characters.',
		'default' => 'Goal: invalid input.'
    ),
    
    'progression_order' => array
    (
        'not_empty' => 'Progression Order: required.',
        'numeric' => 'Progression Order: must be floating point number (X.XXX).',
		'default' => 'Progression Order: invalid input.'
    )
);
