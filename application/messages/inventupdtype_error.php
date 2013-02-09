<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'update_type_id' => array
    (
        'not_empty' => 'Update Type Id: required.',
		'min_length' => 'Update Type Id: must be 3 - 50 characters.',
        'max_length' => 'Update Type Id: must be 3 - 50 characters.',
        'msg_duplicate' => 'Update Type Id: duplicate id.',
		'default' => 'Update Type Id: invalid input.'
    ),
	
	'description' => array
    (
        'not_empty' => 'Description: required.',
		'min_length' => 'Description: must be 3 - 50 characters.',
        'max_length' => 'Description: must be 3 - 50 characters.',
		'default' => 'Description: invalid input.'
    ),
		
	'stock_movement' => array
    (
        'not_empty' => 'Stock Movement: required.',
		'min_length' => 'Stock Movement: must be 3 - 50 characters.',
        'max_length' => 'Stock Movement: must be 3 - 50 characters.',
		'default' => 'Stock Movement: invalid input.'
    ),
);
