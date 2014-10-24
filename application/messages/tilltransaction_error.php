<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate Id.',
		'default' => 'Id: invalid input.'
    ),
	
	'tilltransaction_id' => array
    (
        'not_empty' => 'Tilltransaction Id: required.',
        'max_length' => 'Tilltransaction Id: must be 16 characters.',
        'msg_duplicate' => 'Tilltransaction Id: duplicate Tilltransaction Id.',
		'default' => 'Tilltransaction Id: invalid input.'
    ),

	'till_id' => array
    (
        'not_empty' => 'Till Id: required.',
        'min_length' => 'Till Id: must be 2 - 59 characters.',
		'max_length' => 'Till Id: must be 2 - 59 characters.',
        'msg_till' => 'Till Id: Not current user till or till does not exist.',
		'default' => 'Till Id: invalid input.'
    ),
	
	'amount' => array
    (
        'not_empty' => 'Amount: required.',
		'numeric' => 'Amount: must be numeric.',
        'default' => 'Amount: invalid input.'
    ),
	
	'transaction_type' => array
    (
        'not_empty' => 'Transaction Type: required.',
        'in_array' => 'Transaction Type: must be CASH, CHEQUE, CREDIT.CARD, DEBIT.CARD.',
		'default' => 'Transaction Type: invalid input.'
    ),

	'transaction_date' => array
    (
        'not_empty' => 'Transaction Date: required.',
        'date' => 'Transaction Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Transaction Date: invalid input.'
    ),

	'movement' => array
    (
        'not_empty' => 'Movement: required.',
		'in_array' => 'Movement: must be "IN" or "OUT".',
  		'default' => 'Movement: invalid input.',
    ),

	'reason' => array
    (
        'not_empty' => 'Reason: required.',
        'min_length' => 'Reason: must be 2 - 255 characters.',
		'max_length' => 'Reason: must be 2 - 255 characters.',
		'default' => 'Reason: invalid input.'
    )
);