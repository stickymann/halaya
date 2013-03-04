<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'deliverynote_id' => array
    (
        'not_empty' => 'Deliverynote Id: required.',
		'min_length' => 'Deliverynote Id: must be 16 characters.',
        'max_length' => 'Deliverynote Id: must be 16 characters.',
        'msg_duplicate' => 'Deliverynote Id: duplicate order id.',
		'default' => 'Deliverynote Id: invalid input.'
    ),

	'order_id' => array
    (
        'not_empty' => 'Order Id: required.',
		'min_length' => 'Order Id: must be 16 characters.',
        'max_length' => 'Order Id: must be 16 characters.',
		'default' => 'Order Id: invalid input.'
    ),

	'deliverynote_date' => array
    (
        'not_empty' => 'Deliverynote Date: required.',
        'date' => 'Deliverynote Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Deliverynote Date: invalid input.'
    ),
	
	'status' => array
    (
        'not_empty' => 'Status: required.',
        'msg_new' => 'Status: status cannot be "NEW" before commit.',
		'default' => 'Status: invalid input.'
    ),
	
	'delivered_by' => array
    (
        'not_empty' => 'Delivered By: required.',
        'min_length' => 'Delivered By: must be 2 - 50 characters.',
		'max_length' => 'Delivered By: must be 2 - 50 characters.',
		'default' => 'Delivered By: invalid input.'
    ),

	'delivery_date' => array
    (
        'required' => 'Delivery Date: not_empty.',
        'date' => 'Delivery Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Delivery Date: invalid input.'
    )
);