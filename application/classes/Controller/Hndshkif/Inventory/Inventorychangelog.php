<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Inventory changelog. 
 *
 * $Id: Inventorychangelog.php 2014-03-04 17:00:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
 
class Controller_Hndshkif_Inventory_Inventorychangelog extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('inventorychangelog');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/hndshkif.inventorychangelog.js') );
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
			->rule('changelog_id','not_empty')
			->rule('changelog_id','min_length', array(':value', 20))->rule('changelog_id','max_length', array(':value', 16))
			->rule('changelog_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['changelog_id']));
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Hndshkif_Inventory_Inventorychangelog
