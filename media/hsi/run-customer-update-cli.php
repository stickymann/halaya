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

require_once(dirname(__FILE__).'/fileops.php');
require_once(dirname(__FILE__).'/customerops.php');

$fileops = new FileOps();
$filespecs = $fileops->process_import_files();
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
		//$changelog_id = $inventoryops->process_inventory();
		//array_push($changelogs, $changelog_id);
	}
}

if( $fileops->config['push_customer'] )
{
	foreach($changelogs as $index => $changelog_id)
	{
		//$customerops->push_handshake_inventory($changelog_id);
	}
}
