<?php
/**
 * Displays interface status and start/stop options. 
 *
 * $Id: run-orders-cli.php 2014-03-03 17:00:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */

/*
//New Id Generator
require_once(dirname(__FILE__).'/customerops.php');
$firstname = "PAJ";
$lastname  = "MAHABIR";

//$firstname = "";
//$lastname  = "RAJ GENERAL HARDWARE AND AUTO SUPPLIES";
$customerops = new CustomerOps();
print sprintf("Last Name  : %s\n",$lastname);
print sprintf("Customer Id: %s\n",$customerops->get_new_id("hs_customers",$firstname,$lastname));
*/

/*
//Push Inventory
require_once(dirname(__FILE__).'/inventoryops.php');
$changelog_id = "ICL-20140918-022430";
$inventoryops = new InventoryOps();
print $inventoryops->new_item_category_objid."\n";
$inventoryops->push_handshake_inventory($changelog_id);
*/

/*
// Object Update
require_once(dirname(__FILE__).'/objectops.php');
$mapping_id = "NEW-ITEM.CATEGORY";
$objectops = new ObjectOps();
$result = $objectops->update_object_data($mapping_id); 
print $result."\n";
*/

/*
// Import Files
require_once(dirname(__FILE__).'/fileops.php');
$fileops = new FileOps();
$fileops->process_import_files();
*/


$auto = true;
$batch_id = "BDO-20150206-193042"; 
$order_id = "895115"; //only pumps
//orderentry batch
require_once(dirname(__FILE__).'/orderentryops.php');
$orderentry = new OrderEntryOps();
$orderentry->create_batch_entry($batch_id,$auto);
$orderentry->process_orderentry_files($batch_id,$auto);

//orderentry by order
//$orderentry->create_order_entry($order_id,$auto);
//$orderentry->process_orderentry_files("ORD-".$order_id,$auto);


/*
//prints
require_once(dirname(__FILE__).'/printerwriteops.php');
$printerwrite = new PrinterWriteOps();
$printerwrite->create_batch_picklists($batch_id,$auto);
$printerwrite->create_order_picklist($order_id,null,null,$auto);
$printerwrite->process_pdf_queue();
*/

/*
// sample ids
$batch_id = "BDO-20140806-161006"; // 1
$batch_id = "BDO-20140807-131054"; // 16
$batch_id = "BDO-20140819-123008"; // 25 
$batch_id = "BDO-20140808-080004"; // 3 
$batch_id = "BDO-20140820-100006";

$order_id = "893504";
$order_id = "894115"; //many lineitems
$order_id = "846376"; //with ntx
$order_id = "881741";
$order_id = "1688847";  //all three warehouses
*/
?>
