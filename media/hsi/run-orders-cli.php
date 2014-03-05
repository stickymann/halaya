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

$auto = true;
$orderops = new OrderOps();
$orderops->update_orders($auto);

?>
