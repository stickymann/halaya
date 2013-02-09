<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'inventory_id' => array
    (
        'not_empty' => 'Inventory Id: required.',
		'min_length' => 'Inventory Id: must be 3 - 101 characters.',
        'max_length' => 'Inventory Id: must be 3 - 101 characters.',
        'msg_duplicate' => 'Inventory Id: duplicate product/branch combo.',
		'default' => 'Inventory Id: invalid input.'
    ),

	'product_id' => array
    (
        'not_empty' => 'Product Id: required.',
		'min_length' => 'Product Id: must be 3 - 50 characters.',
        'max_length' => 'Product Id: must be 3 - 50 characters.',
		'default' => 'Product Id: invalid input.'
    ),

	'branch_id' => array
    (
        'not_empty' => 'Branch Id: required.',
		'min_length' => 'Branch Id: must be 2 - 50 characters.',
        'max_length' => 'Branch Id: must be 2 - 50 characters.',
		'default' => 'Branch Id: invalid input.'
    ),
	
	'reorder_level' => array
    (
        'numeric' => 'Reorder Level: must be numeric.',
        'default' => 'Reorder Level: invalid input.'
    ),
	
	'qty_reserved' => array
    (
        'numeric' => 'Quantity Reserved: must be numeric.',
        'default' => 'Quantity Reserved:  invalid input.'
    ),
	
	'qty_reserved' => array
    (
        'numeric' => 'Quantity Reserved: must be numeric.',
        'default' => 'Quantity Reserved:  invalid input.'
    ),
	
	'qty_backordered' => array
    (
        'numeric' => 'Quantity Backordered: must be numeric.',
        'default' => 'Quantity Backordered:  invalid input.'
    ),

	'last_update_type' => array
    (
        'not_empty' => 'Update Type: required.',
		'min_length' => 'Update Type: must be 3 - 50 characters.',
        'max_length' => 'Update Type: must be 3 - 50 characters.',
        'default' => 'Update Type: invalid input.'
    )
);
