<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate Id.',
		'default' => 'Id: invalid input.'
    ),

	'paymentcancel_id' => array
    (
        'not_empty' => 'Payment Cancel Id: required.',
        'min_length' => 'Payment Cancel Id: must be 16 characters.',
		'max_length' => 'Payment Cancel Id: must be 16 characters.',
        'msg_duplicate' => 'Payment Cancel Id: duplicate Payment Cancel Id.',
		'default' => 'Payment Cancel Id: invalid input.'
    ),
	
	'payment_id' => array
    (
        'not_empty' => 'Payment Id: required.',
		'min_length' => 'Payment Id: must be 16 characters.',
        'max_length' => 'Payment Id: must be 16 characters.',
		'record_is_locked' => 'Payment Id: payment record is locked by another user.',
		'payment_id_not_exist' => 'Payment Id: payment record does not exist.',
		'default' => 'Payment Id: invalid input.'
    ),

	'amount' => array
    (
        'not_empty' => 'Amount: required.',
		'numeric' => 'Amount: must be numeric.',
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