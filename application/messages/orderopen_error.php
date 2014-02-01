<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate Id.',
		'default' => 'Id: invalid input.'
    ),

	'orderopen_id' => array
    (
        'not_empty' => 'Order Cancel Id: required.',
        'min_length' => 'Order Cancel Id: must be 16 characters.',
		'max_length' => 'Order Cancel Id: must be 16 characters.',
        'msg_duplicate' => 'Order Cancel Id: duplicate Order Cancel Id.',
		'default' => 'Order Cancel Id: invalid input.'
    ),
	
	'order_id' => array
    (
        'not_empty' => 'Order Id: required.',
		'min_length' => 'Order Id: must be 16 characters.',
        'max_length' => 'Order Id: must be 16 characters.',
		'record_is_locked' => 'Order Id: order is locked by another user.',
		'order_id_not_exist' => 'Order Id: order does not exist.',
        'payment_exist' => 'Order Id: payment exist for order, cancel order instead.',
        'checkout_exist' => 'Order Id: checkout record exist for order, cancel order instead.',
		'default' => 'Order Id: invalid input.'
    ),

	'pre_open_status' => array
    (
        'not_empty' => 'Pre-Cancel Status: required.',
        'default' => 'Amount: invalid input.'
    ),
	
	'reason' => array
    (
        'not_empty' => 'Reason: required.',
        'min_length' => 'Reason: must be 10 - 255 characters.',
		'max_length' => 'Reason: must be 10 - 255 characters.',
		'default' => 'Reason: invalid input.'
    )
);