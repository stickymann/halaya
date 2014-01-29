<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'id' => array
    (
        'not_empty' => 'Id: required.',
        'msg_duplicate' => 'Id: duplicate id.',
		'default' => 'Id: invalid input.'
    ),
	
	'net_id' => array
    (
        'not_empty' => 'Net Id: required.',
		'min_length' => 'Net Id: must be 2 - 50 characters.',
        'max_length' => 'Net Id: must be 2 - 50 characters.',
        'msg_duplicate' => 'Net Id: duplicate order id.',
		'default' => 'Net Id: invalid input.'
    ),
	
	'leader_id' => array
    (
        'not_empty' => 'Leader Id: required.',
        'min_length' => 'Leader Id: must be 8 characters.',
		'max_length' => 'Leader Id: must be 8 characters.',
		'default' => 'Leader Id: invalid input.'
    ),
    
	'description' => array
    (
        'not_empty' => 'Description: required.',
		'min_length' => 'Description: must be 2 - 255 characters.',
        'max_length' => 'Description: must be 2 - 255 characters.',
		'default' => 'Description: invalid input.'
    ),
    
    'age_start' => array
    (
        'not_empty' => 'Age Start: required.',
        'numeric' => 'Age Start: must be integer.',
		'min_length' => 'Age Start: must be 1 - 3 digits.',
        'max_length' => 'Age Start: must be 1 - 3 digits.',
		'default' => 'Age Start: invalid input.'
    ),
    
    'age_complete' => array
    (
        'not_empty' => 'Age Complete: required.',
        'numeric' => 'Age Complete: must be integer.',
		'min_length' => 'Age Complete: must be 1 - 3 digits.',
        'max_length' => 'Age Complete: must be 1 - 3 digits.',
		'default' => 'Age Complete: invalid input.'
    ),
    
    'launch_date' => array
    (
        'not_empty' => 'Launch Date: required.',
		'min_length' => 'Launch Date: must be 10 characters.',
		'max_length' => 'Launch Date: must be 10 characters.',
        'date' => 'Launch Date: date format is incorrect (YYYY-MM-DD).',
		'default' => 'Launch Date: invalid input.'
    ),
    
    'location' => array
    (
        'not_empty' => 'Location: required.',
		'min_length' => 'Location: must be 2 - 255 characters.',
        'max_length' => 'Location: must be 2 - 255 characters.',
		'default' => 'Location: invalid input.'
    ),
    
    'meeting_day' => array
    (
        'not_empty' => 'Meeting Day: required.',
		'min_length' => 'Meeting Day: must be 2 - 21 characters.',
        'max_length' => 'Meeting Day: must be 2 - 21 characters.',
		'default' => 'Meeting Day: invalid input.'
    ),
    
    'meeting_time' => array
    (
        'not_empty' => 'Meeting Time: required.',
		'min_length' => 'Meeting Time: must be 4 - 8 characters.',
        'max_length' => 'Meeting Time: must be 4 - 8 characters.',
		'default' => 'Meeting Time: invalid input.'
    ),
    
    'meeting_duration_min' => array
    (
        'not_empty' => 'Meeting Duration: required.',
        'numeric' => 'Meeting Duration: must be integer.',
		'default' => 'Meeting Duration: invalid input.'
    ),
    
    'recurrence_id' => array
    (
        'not_empty' => 'Recurrence Id: required.',
		'min_length' => 'Recurrence Id: must be 2 - 50 characters.',
        'max_length' => 'Recurrence Id: must be 2 - 50 characters.',
		'default' => 'Recurrence Id: invalid input.'
    ),
    
    'active' => array
    (
        'not_empty' => 'Active: required.',
		'in_array' => 'Active: must be Y or N.',
  		'default' => 'Active: invalid input.'
    )
);
