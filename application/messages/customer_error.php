<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'customer_id' => array
    (
        'not_empty' => 'Customer Id: required.',
		'min_length' => 'Customer Id: must be 8 characters.',
        'max_length' => 'Customer Id: must be 8 characters.',
        'msg_duplicate' => 'Customer Id: duplicate customer id.',
		'default' => 'Customer Id: invalid input.'
    ),

	'customer_type' => array
    (
        'not_empty' => 'Customer Type: required.',
		'min_length' => 'Customer Type: must be 2 - 50 characters.',
        'max_length' => 'Customer Type: must be 2 - 50 characters.',
		'default' => 'Customer Type: invalid input.'
    ),

	'business_type' => array
    (
        'not_empty' => 'Business Type: required.',
		'min_length' => 'Business Type: must be 2 - 50 characters.',
        'max_length' => 'Business Type: must be 2 - 50 characters.',
		'default' => 'Business Type: invalid input.'
    ),
		
	'first_name' => array
    (
        'not_empty' => 'First Name: required.',
        'min_length' => 'First Name: must be between 2 - 255 letters.',
		'max_length' => 'First Name: must be between 2 - 255 letters.',
        'default' => 'First Name: invalid input.'
    ),  
	
	'last_name' => array
    (
        'not_empty' => 'Last Name: required.',
        'min_length' => 'Last Name: must be between 2 - 255 letters.',
		'max_length' => 'Last Name: must be between 2 - 255 letters.',
        'default' => 'Last Name: invalid input.'
    ),    

    'address1' => array
    (
        'not_empty' => 'Street Address: Street Address is required.',
        'min_length' => 'Street Address: Street Address must be between 1 - 255 letters.',
		'max_length' => 'Street Address: Street Address must be between 1 - 255 letters.',
        'default' => 'Street Address: invalid input.'
    ),
	
	'city' => array
    (
        'not_empty' => 'City: required.',
        'min_length' => 'City: must be between 1 - 255 letters.',
		'max_length' => 'City: must be between 1 - 255 letters.',
        'default' => 'City: invalid input.'
    ),
	
	'region_id' => array
    (
        'not_empty' => 'Region Id: required.',
        'numeric' => 'Region Id: must be numeric.',
        'default' => 'Region Id: invalid input.'
    ),
	
	'country_id' => array
    (
        'not_empty' => 'Country Id: required.',
        'min_length' => 'Country Id: must be 2 letters.',
		'max_length' => 'Country Id: must be 2 letters.',
		'default' => 'Country Id: invalid input.'
    ),
	
	'date_of_birth' => array
    (
        'min_length' => 'Date Of Birth: must be 10 characters.',
		'max_length' => 'Date Of Birth: must be 10 characters.',
        'date' => 'Date Of Birth: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Date Of Birth: invalid input.'
    ),
	
	'gender' => array
    (
        'not_empty' => 'Gender: required, must be[M/F/N].',
        'alpha' => 'Gender: must be[M/F/N].',
		'min_length' => 'Gender: must be 1 character.',
        'max_length' => 'Gender: must be 1 character.',
        'default' => 'Gender: invalid input.'
    ),

	'email_address' => array
    (
        'email' => 'Email Address: format is incorrect.',
        'default' => 'Email Address: invalid input.'
    ),
	
	'phone_home' => array
    (
        'numeric' => 'Phone Number(home): must be 7 digits.',
        'default' => 'Phone Number(home): invalid input.'
    ),

	'phone_work' => array
    (
        'numeric' => 'Phone Number(work): must be 7 digits.',
        'default' => 'Phone Number(work): invalid input.'
    ),

	'phone_mobile1' => array
    (
        'not_empty' => 'Phone Number(mobile1): required.',
		'numeric' => 'Phone Number(mobile1): must be 7 digits.',
        'default' => 'Phone Number(mobile1): invalid input.'
    ),

	'phone_mobile2' => array
    (
        'numeric' => 'Phone Number(mobile2): must be 7 digits.',
        'default' => 'Phone Number(mobile2): invalid input.'
    ),
	
	'driver_permit' => array
    (
        'not_empty' => 'Driver\'s Permit: required.',
        'default' => 'Driver\'s Permit: invalid input.'
    ),
	
	'driver_permit_expiry_date' => array
    (
        'min_length' => 'Driver\'s Permit Expiry Date: must be 10 characters.',
		'max_length' => 'Driver\'s Permit Expiry Date: must be 10 characters.',
        'date' => 'Driver\'s Permit Expiry Date: format is incorrect (YYYY-MM-DD).',
		'default' => 'Driver\'s Permit Expiry Date: invalid input.'
    ),
		
	'branch_id' => array
    (
        'not_empty' => 'Branch Id: required.',
        'min_length' => 'Branch Id: must be between 2 - 50 letters.',
		'max_length' => 'Branch Id: must be between 2 - 50 letters.',
        'default' => 'Branch Id: invalid input.'
    )
);
