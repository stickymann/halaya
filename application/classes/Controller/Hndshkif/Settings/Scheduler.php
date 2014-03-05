<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Scheduler settings. 
 *
 * $Id: Scheduler.php 2014-03-05 11:39:59 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
class Controller_Hndshkif_Settings_Scheduler extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('scheduler');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/hndshkif.scheduler.js') );
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
			->rule('schedule_id','not_empty')
			->rule('schedule_id','min_length', array(':value', 2))->rule('schedule_id','max_length', array(':value', 50))
			->rule('schedule_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['schedule_id']));
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Hndshkif_Settings_Scheduler
