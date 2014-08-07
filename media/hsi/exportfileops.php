<?php
/**
 * Export file operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: exportfile.php 2014-04-01 08:15:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
require_once(dirname(__FILE__).'/hsiconfig.php');
require_once(dirname(__FILE__).'/dbops.php');
require_once(dirname(__FILE__).'/fileops.php');


class ExportFileOps
{
	public $cfg 	= null;
	public $dbops 	= null;
	public $fileops = null;
	private $config = null;
	private $idfield = "id";
	
	public function __construct()
	{
		$this->cfg		= new HSIConfig();
		$this->config 	= $this->cfg->get_config();
		$this->dbops	= new DbOps($this->config);
		$this->fileops 	= new FileOps($this->config);
	}
	
	public function create_exportfile($batch_id,$auto=false)
	{
		//$logtext = str_replace("<br>","\r\n",$logtext);
		//$logtext = str_replace("<b>","[",$logtext);
		//$logtext = str_replace("</b>","]",$logtext);
		//$logtext = str_replace("<hr>","---------- ---------- ---------- ----------\r\n",$logtext);
		$logtext = sprintf("ExportFileWrite: %s",$batch_id);
		
		$dir = $this->config['current_export'];  
		if($auto) { $autotext = "AUTO"; } else { $autotext = "MANU"; }
		$filename = sprintf("%s/%s.%s.TAX.txt",$dir,$batch_id,$autotext);
		
		$oldumask = umask(0);
		try
		{
			if ($handle = fopen($filename, 'w+')) 
			{
				fwrite($handle, $logtext);
				fclose($handle);
				//chmod($filename, OUTFILE_PERMISSION);
			}
		}
		catch (Exception $e) { }
		umask($oldumask);
	}

} //End ExportFileOps
