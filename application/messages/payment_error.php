<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate Id.',
		'default' => 'Id: invalid input.'
    ),
	
	'payment_id' => array
    (
        'not_empty' => 'Payment Id: required.',
		'min_length' => 'Payment Id: must be 16 characters.',
        'max_length' => 'Payment Id: must be 16 characters.',
        'msg_duplicate' => 'Payment Id: duplicate Payment Id.',
		'default' => 'Payment Id: invalid input.'
    ),

	'branch_id' => array
    (
        'not_empty' => 'Branch Id: required.',
		'min_length' => 'Branch Id: must be between 2 - 50 letters.',
        'max_length' => 'Branch Id: must be between 2 - 50 letters.',
        'default' => 'Branch Id: invalid input.'
    ),

	'till_id' => array
    (
        'not_empty' => 'Till Id: required.',
		'min_length' => 'Till Id: must be 2 - 59 characters.',
        'max_length' => 'Till Id: must be 2 - 59 characters.',
        'msg_till' => 'Till Id: Not current user till or till does not exist.',
		'default' => 'Till Id: invalid input.'
    ),
	
	'order_id' => array
    (
        'not_empty' => 'Order Id: required.',
		'min_length' => 'Order Id: must be 16 characters.',
        'max_length' => 'Order Id: must be 16 characters.',
		'msg_orderstatus' => 'Order Id: Order Status is QUOTATION, payments not allowed, change to ORDER.CONFIRMED.',
		'default' => 'Order Id: invalid input.'
    ),

	'amount' => array
    (
        'not_empty' => 'Amount: required.',
		'numeric' => 'Amount: must be numeric.',
        'default' => 'Amount: invalid input.'
    ),
	
	'payment_type' => array
    (
        'not_empty' => 'Transaction Type: required.',
        'in_array' => 'Transaction Type: must be 4 - 11 characters.',
		'default' => 'Transaction Type: invalid input.'
    ),
	
	'payment_date' => array
    (
        'not_empty' => 'Payment Date: required.',
        'date' => 'Payment Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Payment Date: invalid input.'
    ),
	
	'payment_status' => array
    (
        'not_empty' => 'Payment Status: required.',
        'in_array' => 'Payment Status: must be 5 - 10 characters.',
		'default' => 'Payment Status: invalid input.'
    )
);