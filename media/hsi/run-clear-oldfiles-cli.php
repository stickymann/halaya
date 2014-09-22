<?php
/**
 * Deletes old log and backup files after a specified number of days.  
 *
 * $Id: run-clear-oldfiles-cli.php 2014-09-21 04:45:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */

require_once(dirname(__FILE__).'/procops.php');
require_once(dirname(__FILE__).'/fileops.php');

define("MAX_DAYS_BEFORE_BACKUP_DELETE",2);
define("MAX_DAYS_BEFORE_LOGS_DELETE",180);
define("MAX_DAYS_BEFORE_ARCHIVE_HSI_IMPORT_DELETE",180);
define("MAX_DAYS_BEFORE_ARCHIVE_ORDER_ENTRY_IMPORT_DELETE",180);
define("MAX_DAYS_BEFORE_CURRENT_HSI_IMPORT_DELETE",7);

//prevent running more than one instance
$grep_arg = basename(__FILE__);
if( ProcOps::process_exist($grep_arg) )
{
	die("Process already running, exiting now!\n");
}

$fileops = new FileOps();

//delete backup files older than 30 days
$fileops->delete_files_after_days( $fileops->config['backupdir'], MAX_DAYS_BEFORE_BACKUP_DELETE );

//delete log files older than 180 days
$fileops->delete_files_after_days( $fileops->config['archive_log'], MAX_DAYS_BEFORE_LOGS_DELETE );

//delete archive/hsi_import files older than 180 days
$fileops->delete_files_after_days( $fileops->config['archive_import'], MAX_DAYS_BEFORE_ARCHIVE_HSI_IMPORT_DELETE );

//delete archive/order_entry_import files older than 180 days
$fileops->delete_files_after_days( $fileops->config['archive_export'], MAX_DAYS_BEFORE_ARCHIVE_ORDER_ENTRY_IMPORT_DELETE );

//delete current/hsi_import errorlog files older than 7 days
$fileops->delete_files_after_days( $fileops->config['current_import'], MAX_DAYS_BEFORE_CURRENT_HSI_IMPORT_DELETE );
?>
