<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate Id.',
		'default' => 'Id: invalid input.'
    ),
	
	'product_id' => array
    (
        'not_empty' => 'Product Id: required.',
		'min_length' => 'Product Id: must be 2 - 50 characters.',
        'max_length' => 'Product Id: must be 2 - 50 characters.',
        'msg_duplicate' => 'Product Id: duplicate Product Id.',
		'default' => 'Product Id: invalid input.'
    ),

	'type' => array
    (
        'not_empty' => 'Product Type: required.',
		'min_length' => 'Product Type: must be 2 - 20 characters.',
        'max_length' => 'Product Type: must be 2 - 20 characters.',
		'default' => 'Product Type: invalid input.'
    ),
		
	'package_items' => array
    (
        'not_empty' => 'Package Items: required.',
		'min_length' => 'Package Items: must be between 2 - 255 characters.',
        'max_length' => 'Package Items: must be between 2 - 255 characters.',
        'default' => 'Package Items: invalid input.'
    ),  
	
	'product_description' => array
    (
        'not_empty' => 'Product Description: required.',
		'min_length' => 'Product Description: must be between 2 - 255 characters.',
        'max_length' => 'Product Description: must be between 2 - 255 characters.',
        'default' => 'Product Description: invalid input.'
    ),      
	
	'category' => array
    (
        'not_empty' => 'Category: required.',
		'min_length' => 'Category:  must be between 2 - 50 characters.',
        'max_length' => 'Category:  must be between 2 - 50 characters.',
        'default' => 'Category: invalid input.'
    ),

	'sub_category' => array
    (
        'not_empty' => 'Sub Category: required.',
		'min_length' => 'Sub Category: must be between 2 - 50 characters.',
        'max_length' => 'Sub Category: must be between 2 - 50 characters.',
        'default' => 'Sub Category: invalid input.'
    ),

	'unit_price' => array
    (
        'numeric' => 'Unit Price: must be value number.',
        'default' => 'Unit Price: invalid input.'
    ),

	'tax_percentage' => array
    (
        'numeric' => 'Tax Percentage: must be value number < 100.',
        'default' => 'Tax Percentage: invalid input.'
    ),

	'taxable' => array
    (
        'not_empty' => 'Taxable: required.',
        'in_array' => 'Taxable: must be [Y/N].',
		'default' => 'Taxable: invalid input.'
    ),

	'status' => array
    (
        'not_empty' => 'Status: required.',
        'min_length' => 'Status: must be 2 - 20 characters.',
		'max_length' => 'Status: must be 2 - 20 characters.',
		'default' => 'Status: invalid input.'
    )
);