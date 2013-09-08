<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Event types categories for different events. 
 *
 * $Id: Eventtype.php 2013-09-08 00:22:19 dnesbit $
 *
 * @package		Soulmap
 * @module	    lovecenter
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Soulmap_Event_Eventtype extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('eventtype');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/soulmap.eventtype.js') );
	}

	function input_validation()
	{
		$this->OBJPOST['eventtype_id']	= strtoupper($this->OBJPOST['eventtype_id']);
		
		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('eventtype_id','not_empty')
			->rule('eventtype_id','min_length', array(':value', 2))->rule('eventtype_id','max_length', array(':value', 50))
			->rule('eventtype_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['eventtype_id']));
		$validation
			->rule('description','not_empty')
			->rule('description','min_length', array(':value', 2))->rule('description','max_length', array(':value', 255));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Soulmap_Event_Eventtype
