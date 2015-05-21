<?php
/**
 * Backs up application and database.
 *
 * $Id: run-hndshkif.backup-cli.php 2014-09-21 06:46:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */

require_once(dirname(__FILE__).'/procops.php');
require_once(dirname(__FILE__).'/fileops.php');
require_once(dirname(__FILE__).'/curlops.php');

// constants
define("HNDSHKI_BACKUP_DEBUG",false);
define("ENV","LIVE");
define("WEBSERVER","localhost");

//prevent running more than one instance
$grep_arg = basename(__FILE__);
if( ProcOps::process_exist($grep_arg) )
{
	die("Process already running, exiting now!\n");
}

$fileops = new FileOps();
$curlops = new CurlOps();
$NOW = date("Ymd-His");

// get relevant directory names
$approot = rtrim ( $fileops->config['approot'],"/" );
$approot_r = explode("/",$approot);
$num_of_dirs = count($approot_r);
$approotlvlup = "";
for( $i=0; $i < $num_of_dirs-1; $i++) { $approotlvlup .= $approot_r[$i]."/"; }  

// config based constants
define("APPNAME",$approot_r[$num_of_dirs-1]);
define("APPRO0T",$fileops->config['approot']);
define("APPRO0TLVLUP",$approotlvlup);
define("APPURLBASE","http://".WEBSERVER."/".APPNAME);
define("DBSERVER",$fileops->config['dbserver']); 
define("DBNAME",$fileops->config['dbname']); 
define("DBUSER",$fileops->config['dbuser']);
define("DBPASS",$fileops->config['dbpasswd']);
define("BACKUPDIR",$fileops->config['backupdir']);
define("DB_BACKUP_NAME",sprintf('%s_%s_%s',$NOW,ENV,DBNAME));
define("DB_FULL_FILE",sprintf('%s_full.sql',DB_BACKUP_NAME));
define("DB_STRUCTURE_FILE",sprintf('%s_structure.sql',DB_BACKUP_NAME));
define("DB_DATA_FILE",sprintf('%s_data.sql',DB_BACKUP_NAME));
define("DB_BACKUP_FILE",sprintf('%s.database.tar.gz',DB_BACKUP_NAME));
define("APP_BACKUP_FILE",sprintf('%s.application.tar.gz',DB_BACKUP_NAME));

if( HNDSHKI_BACKUP_DEBUG )
{ 
	print APPNAME."\n"; print APPRO0T."\n"; print APPRO0TLVLUP."\n"; print APPURLBASE."\n";
	print BACKUPDIR."\n"; 	print DB_BACKUP_NAME."\n"; 	print DB_FULL_FILE."\n"; 
	print DB_STRUCTURE_FILE."\n"; print DB_DATA_FILE."\n"; print DB_BACKUP_FILE."\n";
}
chdir(APPRO0T);

// update application and database version numbers
if( HNDSHKI_BACKUP_DEBUG ){ print getcwd() . "\n"; }
$cmd = "git log --format='%H' -n 1";
exec($cmd, $output);
$GIT_COMMIT = $output[0];
$version_update_url = sprintf('%s/index.php/core_ajaxtodb?option=versionupdate&appver=%s&dbver=%s&env=%s',APPURLBASE,$GIT_COMMIT,$NOW,ENV);
if( HNDSHKI_BACKUP_DEBUG ){ print $GIT_COMMIT."\n"; print $version_update_url."\n"; }
$use_password = false;
$curlops->get_remote_data($version_update_url,$status,$use_password);

// Backup database
// dump application database to sql files
$cmd_r[0] = sprintf('mysqldump -u %s -p%s --routines %s > %s%s',DBUSER,DBPASS,DBNAME,BACKUPDIR,DB_FULL_FILE);
$cmd_r[1] = sprintf('mysqldump -u %s -p%s --routines --no-data=true %s > %s%s',DBUSER,DBPASS,DBNAME,BACKUPDIR,DB_STRUCTURE_FILE);
$cmd_r[2] = sprintf('mysqldump -u %s -p%s  --no-create-info --compact --extended-insert=false %s > %s%s',DBUSER,DBPASS,DBNAME,BACKUPDIR,DB_DATA_FILE);
foreach($cmd_r as $index => $cmd)
{
	exec($cmd, $output);
}

// tar.gz sql files
chdir(BACKUPDIR);
$cmd = sprintf('tar -czf %s *.sql',DB_BACKUP_FILE);
exec($cmd, $output);

//delete sql files
$cmd = "rm -f *.sql";
exec($cmd, $output);

// Backup application
chdir(APPRO0TLVLUP);
$cmd = sprintf('tar -czf %s%s %s',BACKUPDIR,APP_BACKUP_FILE,APPNAME);
exec($cmd, $output);
?>
