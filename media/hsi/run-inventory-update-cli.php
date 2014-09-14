<?php
/**
 * Command line inventory update.  
 *
 * $Id: run-inventory-update-cli.php 2014-07-03 23:48:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */

//prevent running more than one instance
require_once(dirname(__FILE__).'/procops.php');
require_once(dirname(__FILE__).'/fileops.php');
require_once(dirname(__FILE__).'/inventoryops.php');

$grep_arg = basename(__FILE__);
if( ProcOps::process_exist($grep_arg) )
{
	die("Process already exist, terminating now!\n");
}

$delete_bad_files=true;
$fileops = new FileOps();
$filespecs = $fileops->process_import_files();
$fileops->write_errorlog_import_files($filespecs,$delete_bad_files);
$changelogs = array(); 

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
			array_push($changelogs, $changelog_id);
			
			//data import data file
			$inventoryops->archive_inventory_datafile();
		}
	}
}

if( $fileops->config['push_inventory'] )
{
	foreach($changelogs as $index => $changelog_id)
	{
		$inventoryops->push_handshake_inventory($changelog_id);
	}
}
