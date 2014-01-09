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
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function update_run_status($table,$status,$request_id)
	{
		$querystr = sprintf('UPDATE %s SET run = "%s" WHERE request_id = "%s"',$table,$status,$request_id);
		$this->param['primarymodel']->execute_update_query($querystr);
	}
	
	public function update_inventory()
	{
		$cfg	  = new HSIConfig();
		$config   = $cfg->get_config();
		$fileops  = new FileOps($config);
		$filelist = $fileops->get_all_filenames_in_directory($config['current_import']);
		/* foreach file if files is inventory then process file then move file to archive folder
		 * 
		 * 
		 * 
		 * 
		 */
		
		
		$inventoryops = new InventoryOps();
		$inventoryops->set_inventory_filename($filelist[0]);
		$inventoryops->set_inventory_data();
		$inventoryops->process_inventory();
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
