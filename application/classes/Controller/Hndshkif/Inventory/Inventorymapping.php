<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Handshake/DacEasy inventory. 
 *
 * $Id: Inventorymapping.php 2013-12-20 20:25:59 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Hndshkif_Inventory_Inventorymapping extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('inventorymapping');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/hndshkif.inventorymapping.js') );
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
			->rule('inventorymapping_id','not_empty')
			->rule('inventorymapping_id','min_length', array(':value', 16))->rule('inventorymapping_id','max_length', array(':value', 16))
			->rule('inventorymapping_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['inventorymapping_id']));
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Hndshkif_Inventory_Inventorymapping
