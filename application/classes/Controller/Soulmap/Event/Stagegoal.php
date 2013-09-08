<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Contact of progression through their Christian walk. 
 *
 * $Id: Stagegoal.php 2013-09-08 16:24:23 dnesbit $
 *
 * @package		Soulmap
 * @module	    lovecenter
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Soulmap_Event_Stagegoal extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('stagegoal');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/soulmap.stagegoal.js') );
	}

	function input_validation()
	{
		$this->OBJPOST['status_id']	= strtoupper($this->OBJPOST['status_id']);
		$this->OBJPOST['stage']		= strtoupper($this->OBJPOST['stage']);
		$this->OBJPOST['goal']		= strtoupper($this->OBJPOST['goal']);
				
		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('status_id','not_empty')
			->rule('status_id','min_length', array(':value', 2))->rule('status_id','max_length', array(':value', 50))
			->rule('status_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['status_id']));
		$validation
			->rule('description','not_empty')
			->rule('description','min_length', array(':value', 2))->rule('description','max_length', array(':value', 255));
		$validation
			->rule('stage','not_empty')
			->rule('stage','min_length', array(':value', 2))->rule('stage','max_length', array(':value', 255));
		$validation
			->rule('goal','not_empty')
			->rule('goal','min_length', array(':value', 2))->rule('goal','max_length', array(':value', 255));
		$validation
			->rule('progression_order','not_empty')
			->rule('progression_order','numeric');
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Soulmap_Event_Stagegoal
