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

require_once(dirname(__FILE__).'/orderops.php');
require_once(dirname(__FILE__).'/printerwriteops.php');
require_once(dirname(__FILE__).'/orderentryops.php');
	
$auto = true;
$orderops = new OrderOps();
$meta = $orderops->update_orders($auto);
if( $meta['total_inserts'] > 0 )
{
	$printerwrite = new PrinterWriteOps();
	$printerwrite->create_batch_picklists($meta['batch_id'],$auto);
	$printerwrite->process_pdf_queue();
	
	$orderentry = new OrderEntryOps();
	$orderentry->create_batch_entry($meta['batch_id'],$auto);
	$orderentry->process_orderentry_files($meta['batch_id'],$auto);
}
?>
