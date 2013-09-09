<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cell group records. 
 *
 * $Id: Cell.php 2013-09-08 22:45:19 dnesbit $
 *
 * @package		Soulmap
 * @module	    lovecenter
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Soulmap_Cell_Cell extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('cell');
		$this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/soulmap.cell.js') );
	}

	function input_validation()
	{
		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('cell_id','not_empty')
			->rule('cell_id','min_length', array(':value', 12))->rule('cell_id','max_length', array(':value', 12))
			->rule('cell_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['cell_id']));
		$validation
			->rule('net_id','not_empty')
			->rule('net_id','min_length', array(':value', 2))->rule('net_id','max_length', array(':value', 50));
		$validation
			->rule('leader_id','not_empty')
			->rule('leader_id','min_length', array(':value', 8))->rule('leader_id','max_length', array(':value', 8));	
		$validation
			->rule('description','not_empty')
			->rule('description','min_length', array(':value', 2))->rule('description','max_length', array(':value', 255));
		$validation
			->rule('launch_date','not_empty')
			->rule('launch_date','date');			
		$validation
			->rule('location','not_empty')
			->rule('location','min_length', array(':value', 2))->rule('location','max_length', array(':value', 255));
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
			->rule('offering_target','not_empty')
			->rule('offering_target','numeric');
		$validation
			->rule('active','not_empty')
			->rule('active','in_array', array(':value', array('Y', 'N')));	
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Soulmap_Cell_Cell
