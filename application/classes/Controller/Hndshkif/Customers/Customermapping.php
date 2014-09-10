<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Handshake/DacEasy customer.
 *
 * $Id: Customermapping.php 2014-09-07 05:10:45 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */

require_once('media/hsi/customerops.php');

class Controller_Hndshkif_Customers_Customermapping extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('customermapping');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/hndshkif.customermapping.js') );
	}

	function input_validation()
	{
		$post = $this->OBJPOST;	
		//$this->OBJPOST['hash'] = hash('sha256',$this->OBJPOST['tax_id'].$this->OBJPOST['name'].$this->OBJPOST['contact'].$this->OBJPOST['street'].$this->OBJPOST['city'].$this->OBJPOST['phone']);

		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('tax_id','not_empty')
			->rule('tax_id','min_length', array(':value', 4))->rule('tax_id','max_length', array(':value', 12))
			->rule('tax_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['tax_id']));
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Hndshkif_Customers_Customermapping
