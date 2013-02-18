<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a charge customer account. 
 *
 * $Id: Chargeaccount.php 2013-02-18 06:38:10 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Customer_Chargeaccount extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('chargeaccount');
		$this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.chargeaccount.js') );
	}

	function input_validation()
	{
		$post = $_POST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('customer_id','not_empty')
			->rule('customer_id','min_length', array(':value', 8))->rule('customer_id','max_length', array(':value', 8))
			->rule('customer_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['customer_id']));
		$validation
			->rule('activation_date','date');
		$validation
			->rule('status_change_date','date');
		$validation
			->rule('active','not_empty')
			->rule('active','in_array', array(':value', array('Y', 'N')));	
				
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Core_Customer_Chargeaccount