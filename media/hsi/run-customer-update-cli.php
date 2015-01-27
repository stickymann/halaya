<?php
/**
 * Command line customer update.  
 *
 * $Id: run-customer-update-cli.php 2014-07-03 23:48:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */

require_once(dirname(__FILE__).'/procops.php');
require_once(dirname(__FILE__).'/fileops.php');
require_once(dirname(__FILE__).'/customerops.php');

//prevent running more than one instance
$grep_arg = basename(__FILE__);
if( ProcOps::process_exist($grep_arg) )
{
	die("Process already running, exiting now!\n");
}

$delete_bad_files=true;
$fileops = new FileOps();
$filespecs = $fileops->process_import_files();
$fileops->write_errorlog_import_files($filespecs,$delete_bad_files);
$changelogs = array(); 

foreach($filespecs as $index => $specs)
{
	if( $specs['filetype'] == "CUSTOMER" )
	{
		$customerops = new CustomerOps();
		//set filename to process
		$customerops->set_customer_filename($specs['filename']);
		
		//read data in into array for processing
		$customerops->set_customer_data();

		//process data
		$changelog_id = $customerops->process_customer();
		//array_push($changelogs, $changelog_id);
		
		//data import data file
		$customerops->archive_customer_datafile();
		/*
		//push customer
		if( $fileops->config['push_customer'] )
		{
			$customerops->push_handshake_customer($changelog_id);
		}*/
	}
}

