<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Request for creation of DacEasy Order Entry import file. . 
 *
 * $Id: Ordersimportfile.php 2014-08-21 16:35:32 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
 
require_once('media/hsi/orderentryops.php');
 
class Controller_Hndshkif_Orders_Ordersimportfile extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('ordersimportfile');
		$this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/hndshkif.ordersimportfile.js') );
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
			->rule('description','not_empty')
			->rule('description','min_length', array(':value', 3))->rule('description','max_length', array(':value', 255));
		$validation	
			->rule('reference_id','not_empty')
			->rule('reference_id','min_length', array(':value', 1))->rule('reference_id','max_length', array(':value', 50));
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function create_order_entry_import_file()
	{
		$auto = false;
		$orderentry = new OrderEntryOps();
		if( $this->OBJPOST['reference_type'] == "BATCH" )
		{
			$batch_id = $this->OBJPOST['reference_id'];
			$orderentry->create_batch_entry($batch_id,$auto);
			$orderentry->process_orderentry_files($batch_id,$auto);
		}
		else if( $this->OBJPOST['reference_type'] == "ORDER" )
		{
			$order_id = $this->OBJPOST['reference_id'];
			$orderentry->create_order_entry($order_id,$auto);
			$orderentry->process_orderentry_files("ORD-".$order_id,$auto);
		}
	}
	
	public function authorize_post_update_existing_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->create_order_entry_import_file();
		}
	}

	public function authorize_post_insert_new_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->create_order_entry_import_file();
		}
	}

} //End Controller_Hndshkif_Orders_Ordersimportfile
