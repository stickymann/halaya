<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'order_id' => array
    (
        'not_empty' => 'Order Id: required.',
        'min_length' => 'Order Id: must be 16 characters.',
		'max_length' => 'Order Id: must be 16 characters.',
        'msg_duplicate' => 'Order Id: duplicate order id.',
		'default' => 'Order Id: invalid input.'
    ),

	'branch_id' => array
    (
        'not_empty' => 'Branch Id: required.',
        'min_length' => 'Branch Id: must be between 2 - 50 letters.',
		'max_length' => 'Branch Id: must be between 2 - 50 letters.',
        'default' => 'Branch Id: invalid input.'
    ), 

	'customer_id' => array
    (
        'not_empty' => 'Customer Id: required.',
        'min_length' => 'Customer Id: must be 8 characters.',
		'max_length' => 'Customer Id: must be 8 characters.',
		'default' => 'Customer Id: invalid input.'
    ),
	
	'order_status' => array
    (
        'not_empty' => 'Order Status: required.',
        'min_length' => 'Order Status: must be 3 - 20 characters.',
		'max_length' => 'Order Status: must be 3 - 20 characters.',
        'msg_new' => 'Order Status: status cannot be "NEW" for filled order.',
		'default' => 'Order Status: invalid input.'
    ),	
	
	'order_date' => array
    (
        'not_empty' => 'Order Date: required.',
		'min_length' => 'Order Date: must be 10 characters.',
		'max_length' => 'Order Date: must be 10 characters.',
        'date' => 'Order Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Order Date: invalid input.'
    ),
	
	'status_change_date' => array
    (
        'not_empty' => 'Status Change Date: required.',
		'min_length' => 'Status Change Date: must be 10 characters.',
		'max_length' => 'Status Change Date: must be 10 characters.',
        'date' => 'Status Change Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Status Change Date: invalid input.'
    ),
	
	'quotation_date' => array
    (
        'not_empty' => 'Quotation Date: required.',
		'min_length' => 'Quotation Date: must be 10 characters.',
		'max_length' => 'Quotation Date: must be 10 characters.',
        'date' => 'Quotation Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Quotation Date: invalid input.'
    ),
		
	'invoice_date' => array
    (
        'not_empty' => 'Invoice Date: required.',
		'min_length' => 'Invoice Date: must be 10 characters.',
		'max_length' => 'Invoice Date: must be 10 characters.',
        'date' => 'Invoice Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Invoice Date: invalid input.'
    ),

	'order_details' => array
    (
        'not_empty' => 'Order Details: required.',
		'zero_orderdetails' => 'Order Details: zero products selected, at least one required.', 
		'usertext_required' => 'Order Details: user text required, must be 3 or more characters.',
        'default' => 'Order Details: invalid input.'
    ),

	'inventory_checkout_type' => array
    (
        'not_empty' => 'Inventory Checkout Type: required.',
		'in_array' => 'Inventory Checkout Status: AUTO OR MANUAL',
        'default' => 'Inventory Checkout Type: invalid input.'
    ),
	
	'inventory_checkout_status' => array
    (
        'not_empty' => 'Inventory Checkout Status: required.',
		'in_array' => 'Inventory Checkout Status: NONE, PARTIAL OR COMPLETED',
        'default' => 'Inventory Checkout Status: invalid input.'
    )
);