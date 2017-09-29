<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: name is required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'idname' => array
    (
        'not_empty' => 'User Id: required.',
        'alpha' => 'User Id: must have only alphabetic characters.',
        'min_length' => 'User Id: must be between 3 - 50 characters.',
		'max_length' => 'User Id: must be between 3 - 50 characters.',
        'msg_duplicate' => 'User Id: duplicate user id.',
		'default' => 'User Id: invalid input.'
    ),
	
    'username' => array
    (
        'not_empty' => 'Signon Name: required.',
        'alpha' => 'Signon Name: must have only alphabetic characters.',
        'min_length' => 'Signon Name: must be between 3 - 32 letters.',
		'max_length' => 'Signon Name: must be between 3 - 32 letters.',
        'msg_duplicate' => 'Signon Name: duplicate signon name.',
		'default' => 'Signon Name: invalid input.'
    ),
	
    'fullname' => array
    (
        'not_empty' => 'Fullname: required.',
        'min_length' => 'Fullname: must be between 3 - 255 letters.',
		'max_length' => 'Fullname: must be between 3 - 255 letters.',
        'default' => 'Fullname: invalid input.'
    ),    

    'email' => array
    (
        'not_empty' => 'Email Address: required.',
        'email' => 'Email Address: format is incorrect.',
        'default' => 'Email Address: invalid input.'
    ),

   'enable' => array
    (
        'not_empty' => 'Enabled: required, must be[Y/N].',
        'alpha' => 'Enabled: must be[Y/N].',
        'min_length' => 'Enabled: must be 1 letter.',
		'max_length' => 'Enabled: must be 1 letter.',
        'default' => 'Enabled: invalid input.'
    ),
	
    'expiry_date' => array
    (
        'not_empty' => 'Expiry Date: required (default 2099-12-31).',
        'date' => 'Expiry Date: invalid date',
        'default' => 'Expiry Date: invalid input.'
    ),    
	
	'branch_id' => array
    (
        'not_empty' => 'Branch Id: required.',
        'min_length' => 'Branch Id: must be between 2 - 50 letters.',
		'max_length' => 'Branch Id: must be between 2 - 50 letters.',
        'default' => 'Branch Id: invalid input.'
    ),

	'department_id' => array
    (
        'not_empty' => 'Department Id: required.',
        'min_length' => 'Department Id: must be between 2 - 50 letters.',
		'max_length' => 'Department Id: must be between 2 - 50 letters.',
        'default' => 'Department Id: invalid input.'
    )
);