<?php
/**
 * Configuration setup for Handshake to DacEasy Interface automation. 
 *
 * $Id: hsiconfig.php 2013-12-14 12:57:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */

define("CONFIG_FILE","hsiconfig.xml");

class HSIConfig 
{
	public $hsiconfig = null;
	
	public function __construct()
	{
		$configfile = dirname(__FILE__).'/'.CONFIG_FILE;
		try
			{
				//check for required fields in xml file
				$xml = file_get_contents($configfile);
				$cfg = new SimpleXMLElement($xml);
				if($cfg->database->server) { $this->hsiconfig['dbserver'] = sprintf('%s',$cfg->database->server); }
				if($cfg->database->name) { $this->hsiconfig['dbname'] = sprintf('%s',$cfg->database->name); }
				if($cfg->database->user) { $this->hsiconfig['dbuser'] = sprintf('%s',$cfg->database->user); }
				if($cfg->database->password) { $this->hsiconfig['dbpasswd'] = sprintf('%s',$cfg->database->password); }
				if($cfg->handshake->appurl) { $this->hsiconfig['appurl'] = sprintf('%s',$cfg->handshake->appurl); }
				if($cfg->handshake->apikey) { $this->hsiconfig['hs_apikey'] = sprintf('%s',$cfg->handshake->apikey); }
				if($cfg->folders->current_import) { $this->hsiconfig['current_import'] = sprintf('%s',$cfg->folders->current_import); }
				if($cfg->folders->current_export) { $this->hsiconfig['current_export'] = sprintf('%s',$cfg->folders->current_export); }
				if($cfg->folders->archive_import) { $this->hsiconfig['archive_import'] = sprintf('%s',$cfg->folders->archive_import); }
				if($cfg->folders->archive_export) { $this->hsiconfig['archive_export'] = sprintf('%s',$cfg->folders->archive_export); }
				
				if($cfg->tables->tb_changelogs) { $this->hsiconfig['tb_changelogs'] = sprintf('%s',$cfg->tables->tb_changelogs); }
				if($cfg->tables->tb_inventorys) { $this->hsiconfig['tb_inventorys'] = sprintf('%s',$cfg->tables->tb_inventorys); }
	
				$this->hsiconfig['connectstr'] = sprintf('mysql:host=%s;dbname=%s', $this->hsiconfig['dbserver'], $this->hsiconfig['dbname']);
			}
		catch (Exception $e) 
			{
				$desc='Configuration File Error : '.$e->getMessage();
				print $desc;
			}
	}
	
	public function get_config()
	{
		return $this->hsiconfig;
	}
}
