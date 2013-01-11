<?php defined('SYSPATH') or die('No direct script access.');
 
return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate Id.',
		'default' => 'Id: invalid input.',
    ),
	
	'vw' => array
    (
        'not_empty' => 'Message Read: required.',
		'min_length' => 'Message Read: must be Y or N.',
		'max_length' => 'Message Read: must be Y or N.',
		'default' => 'Message Read: invalid input.',
    ),

	'recipient' => array
    (
        'not_empty' => 'Recipient: required.',
        'min_length' => 'Recipient: must be 1 - 50 characters.',
		'max_length' => 'Recipient: must be 1 - 50 characters.',
		'default' => 'Recipient: invalid input.',
    ),
		
	'sender' => array
    (
        'not_empty' => 'Sender: required.',
        'min_length' => 'Sender: must be between 2 - 50 characters.',
		'max_length' => 'Sender: must be between 2 - 50 characters.',
        'default' => 'Sender: invalid input.',
    ),
	
	'subject' => array
    (
        'not_empty' => 'Subject: required.',
        'min_length' => 'Subject: must be between 1 - 255 characters.',
		'max_length' => 'Subject: must be between 1 - 255 characters.',
        'default' => 'Subject: invalid input.',
    ),
	
	'body' => array
    (
        'not_empty' => 'Message: required.',
        'default' => 'Message: invalid input.',
    )  
);
