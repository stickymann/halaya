<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'request_id' => array
    (
        'not_empty' => 'Request Id: required.',
		'min_length' => 'Request Id: must be 2 - 30 characters.',
        'max_length' => 'Request Id: must be 2 - 30 characters.',
        'msg_duplicate' => 'Request Id: duplicate id',
		'default' => 'Request Id: invalid input.'
    ),

	'run' => array
    (
        'not_empty' => 'Update Orders: required.',
		'data_errors_exist' => 'Update Orders: one or more import files contain data errors.',
        'file_not_exist' => 'Update Orders: no import files exist.',
        'default' => 'Update Orders: invalid input.'
    )
);
