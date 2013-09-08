<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'eventtype_id' => array
    (
        'not_empty' => 'Eventtype Id: required.',
		'min_length' => 'Eventtype Id: must be 2 - 50 characters.',
        'max_length' => 'Eventtype Id: must be 2 - 50 characters.',
        'msg_duplicate' => 'Eventtype Id: duplicate order id.',
		'default' => 'Eventtype Id: invalid input.'
    ),
	
	'description' => array
    (
        'not_empty' => 'Description: required.',
		'min_length' => 'Description: must be 2 - 255 characters.',
        'max_length' => 'Description: must be 2 - 255 characters.',
		'default' => 'Description: invalid input.'
    )
);
