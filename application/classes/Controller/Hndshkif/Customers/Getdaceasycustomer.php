<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Request to upload DacEasy customers from import file.
 *
 * $Id: Getdaceasycustomer.php 2014-09-09 23:55:24 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
 
require_once('media/hsi/hsiconfig.php');
require_once('media/hsi/fileops.php');
require_once('media/hsi/customerops.php');
 
class Controller_Hndshkif_Customers_Getdaceasycustomer extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('getdaceasycustomer');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/hndshkif.getdaceasycustomer.js') );
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
			->rule('request_id','min_length', array(':value', 2))->rule('request_id','max_length', array(':value', 30))
			->rule('request_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['request_id']));
		$validation
			->rule('run','not_empty')
			->rule('run', array($this,'validate_import_files'), array(':validation', ':field'));
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
		public function validate_import_files(Validation $validation,$field)
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$error_count = 0; $file_count = 0;
			$delete_bad_files = false;
			$fileops = new FileOps();
			$filespecs = $fileops->process_import_files();
			$fileops->write_errorlog_import_files($filespecs,$delete_bad_files);
			
			foreach($filespecs as $index => $specs)
			{
				if( $specs['filetype'] == "CUSTOMER" )
				{
					$error_count = $error_count + $specs['errors']['total_min']; 
					$file_count++;
				}
			}
			if( $error_count > 0 ) { $validation->error($field, 'data_errors_exist');}
			if( $file_count < 1) { $validation->error($field, 'file_not_exist');}
		}
	}
		
	public function update_run_status($table,$status,$request_id)
	{
		$querystr = sprintf('UPDATE %s SET run = "%s" WHERE request_id = "%s"',$table,$status,$request_id);
		$this->param['primarymodel']->execute_update_query($querystr);
	}
	
	public function update_customers()
	{
		$delete_bad_files = true;
		$fileops = new FileOps();
		$filespecs = $fileops->process_import_files();
		$fileops->write_errorlog_import_files($filespecs,$delete_bad_files);
		
		foreach($filespecs as $index => $specs)
		{
			if( $specs['filetype'] == "CUSTOMER" )
			{
				//file may get delete if data errors exist, check for its existence
				if( file_exists( $specs['filepath'] ) )
				{
					$customerops = new CustomerOps();
					//set filename to process
					$customerops->set_customer_filename($specs['filename']);
					//read data in into array for processing
					$customerops->set_customer_data();
					//process data
					$changelog_id = $customerops->process_customer();
					$customerops->archive_customer_datafile();
				}
			}
		}
		$this->update_run_status($this->param['tb_live'],"N",$this->OBJPOST['request_id']);
	}
	
	public function authorize_post_update_existing_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->update_customers();
		}
	}

	public function authorize_post_insert_new_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->update_customers();
		}
	}

} //End Controller_Hndshkif_Customers_Getdaceasycustomer
