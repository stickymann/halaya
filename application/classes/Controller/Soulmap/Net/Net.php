<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Net groups records. 
 *
 * $Id: Net.php 2013-09-08 17:26:04 dnesbit $
 *
 * @package		Soulmap
 * @module	    lovecenter
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Soulmap_Net_Net extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('net');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/soulmap.net.js') );
	}

	function input_validation()
	{
		$this->OBJPOST['net_id'] = strtoupper($this->OBJPOST['net_id']);
		
		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('net_id','not_empty')
			->rule('net_id','min_length', array(':value', 2))->rule('net_id','max_length', array(':value', 50))
			->rule('net_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['net_id']));
		$validation
			->rule('leader_id','not_empty')
			->rule('leader_id','min_length', array(':value', 8))->rule('leader_id','max_length', array(':value', 8));	
		$validation
			->rule('description','not_empty')
			->rule('description','min_length', array(':value', 2))->rule('description','max_length', array(':value', 255));
		$validation
			->rule('age_start','not_empty')
			->rule('age_start','numeric')
			->rule('age_start','min_length', array(':value', 1))->rule('age_start','max_length', array(':value', 3));
		$validation
			->rule('age_complete','not_empty')
			->rule('age_complete','numeric')
			->rule('age_complete','min_length', array(':value', 1))->rule('age_complete','max_length', array(':value', 3));
		$validation
			->rule('launch_date','not_empty')
			->rule('launch_date','date');			
		$validation
			->rule('meeting_day','not_empty')
			->rule('meeting_day','min_length', array(':value', 2))->rule('meeting_day','max_length', array(':value', 21));
		$validation
			->rule('meeting_time','not_empty')
			->rule('meeting_time','min_length', array(':value', 4))->rule('meeting_time','max_length', array(':value', 8));
		$validation
			->rule('meeting_duration_min','not_empty')
			->rule('meeting_duration_min','numeric');	
		$validation
			->rule('recurrence_id','not_empty')
			->rule('recurrence_id','min_length', array(':value', 2))->rule('recurrence_id','max_length', array(':value', 50));
		$validation
			->rule('active','not_empty')
			->rule('active','in_array', array(':value', array('Y', 'N')));		
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Soulmap_Net_Net
