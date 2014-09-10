<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Request to upload DacEasy inventory from import file. 
 *
 * $Id: Getdaceasyinventory.php 2013-09-14 23:31:51 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */

require_once('media/hsi/hsiconfig.php');
require_once('media/hsi/fileops.php');
require_once('media/hsi/inventoryops.php');

class Controller_Hndshkif_Inventory_Getdaceasyinventory extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('getdaceasyinventory');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/hndshkif.getdaceasyinventory.js') );
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
				if( $specs['filetype'] == "INVENTORY" )
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
	
	public function update_inventory()
	{
		$delete_bad_files = true;
		$fileops = new FileOps();
		$filespecs = $fileops->process_import_files();
		$fileops->write_errorlog_import_files($filespecs,$delete_bad_files);
		
		foreach($filespecs as $index => $specs)
		{
			if( $specs['filetype'] == "INVENTORY" )
			{
				//file may get delete if data errors exist, check for its existence
				if( file_exists( $specs['filepath'] ) )
				{
					$inventoryops = new InventoryOps();
					//set filename to process
					$inventoryops->set_inventory_filename($specs['filename']);
					//read data in into array for processing
					$inventoryops->set_inventory_data();
					//process data
					$changelog_id = $inventoryops->process_inventory();
					$inventoryops->archive_inventory_datafile();
				}
			}
		}
		$this->update_run_status($this->param['tb_live'],"N",$this->OBJPOST['request_id']);
	}
	
	public function authorize_post_update_existing_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->update_inventory();
		}
	}

	public function authorize_post_insert_new_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->update_inventory();
		}
	}

} //End Controller_Hndshkif_Orders_Gethandshakeorders
