<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Request to push customer data to Handshake. 
 *
 * $Id: Pushhandshakecustomer.php 2014-09-09 09:41:08 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
 
require_once('media/hsi/customerops.php');
 
class Controller_Hndshkif_Customers_Pushhandshakecustomer extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('pushhandshakecustomer');
		$this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/hndshkif.pushrequest.js') );
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
			->rule('request_id','not_empty')
			->rule('request_id','min_length', array(':value', 16))->rule('request_id','max_length', array(':value', 16))
			->rule('request_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['request_id']));
		$validation
			->rule('changelog_id','not_empty');
		$validation
			->rule('description','not_empty');
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function update_run_status($table,$status,$request_id)
	{
		$querystr = sprintf('UPDATE %s SET run = "%s" WHERE request_id = "%s"',$table,$status,$request_id);
		$this->param['primarymodel']->execute_update_query($querystr);
	}
	
	public function push_customer()
	{
		$customerops = new CustomerOps();
		$customerops->push_handshake_customer( $this->OBJPOST['changelog_id'] );
		$this->update_run_status($this->param['tb_live'],"N",$this->OBJPOST['request_id']);
	}
	
	public function authorize_post_update_existing_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->push_customer();
		}
	}

	public function authorize_post_insert_new_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->push_customer();
		}
	}

} //End Controller_Hndshkif_Customer_Pushhandshakecustomer
