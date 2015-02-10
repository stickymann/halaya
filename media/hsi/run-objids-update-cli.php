<?php
/** 
 * Updates customer and inventory related object ids.
 *
 * $Id: run-objids-update-cli.php 2014-09-21 23:16:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */

require_once(dirname(__FILE__).'/procops.php');
require_once(dirname(__FILE__).'/objectops.php');

//prevent running more than one instance
$grep_arg = basename(__FILE__);
if( ProcOps::process_exist($grep_arg) )
{
	die("Process already running, exiting now!\n");
}

$objects_r = array("items","customers");
$objectops = new ObjectOps();
foreach($objects_r as $index => $object)
{
	$objectops->update_object_ids($object);
}
?>

