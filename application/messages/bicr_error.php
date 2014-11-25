<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'batchrequest_id' => array
    (
        'not_empty' => 'Batchrequest Id: required.',
		'min_length' => 'Batchrequest Id: must be 16 - 40 characters.',
        'max_length' => 'Batchrequest Id: must be 16 - 40 characters.',
        'msg_duplicate' => 'Batchrequest Id: duplicate order id.',
		'default' => 'Batchrequest Id: invalid input.'
    ),
	
	'requesttype' => array
    (
        'not_empty' => 'Requesttype: required.',
		'default' => 'Batchrequest Id: invalid input.'
    ),

	'cc_id' => array
    (
        'ccid_required' => 'Charge Customer Id: required.',
		'ccid_clear' => 'Charge Customer Id: should be blank.',
		'default' => 'Batchrequest Id: invalid input.'
    ),
	
	'description' => array
    (
        'not_empty' => 'Description: required.',
		'min_length' => 'Description: must be 5 - 255 characters.',
        'max_length' => 'Description: must be 5 - 255 characters.',
		'default' => 'Description: invalid input.'
    ),
	
	'start_date' => array
    (
        'not_empty' => 'Start Date: required.',
		'min_length' => 'Start Date: must be 10 characters.',
		'max_length' => 'Start Date: must be 10 characters.',
        'date' => 'Start Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Start Date: invalid input.'
    ),
	
	'end_date' => array
    (
        'not_empty' => 'End Date: required.',
		'min_length' => 'End Date: must be 10 characters.',
		'max_length' => 'End Date: must be 10 characters.',
        'date' => 'End Date: date format is incorrect (YYYY-MM-DD).',
		'enddate_failed' => 'End Date: start date greater than end date.',
		'default' => 'Batch Date: invalid input.'
    )
);