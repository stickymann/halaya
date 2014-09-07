<?php
/**
 * Configuration setup for task automation / scheduler. 
 *
 * $Id: core_automation_config.php 2013-12-14 12:57:46 dnesbit $
 *
 * @package		Halaya
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */

define('CONFIG_FILE',dirname(__FILE__).'core_automation_config.xml');

class AutomationConfig 
{
	public $config = null;
	
	public function __construct()
	{
		$configfile = CONFIG_FILE;
		try
			{
				//check for required fields in xml file
				$xml = file_get_contents($configfile);
				$cfg = new SimpleXMLElement($xml);
				
				//database
				if($cfg->database->server) { $this->config['dbserver'] = sprintf('%s',$cfg->database->server); }
				if($cfg->database->name) { $this->config['dbname'] = sprintf('%s',$cfg->database->name); }
				if($cfg->database->user) { $this->config['dbuser'] = sprintf('%s',$cfg->database->user); }
				if($cfg->database->password) { $this->config['dbpasswd'] = sprintf('%s',$cfg->database->password); }
				$this->config['connectstr'] = sprintf('mysql:host=%s;dbname=%s', $this->config['dbserver'], $this->config['dbname']);

				//tables
				if($cfg->tables->tb_schedulers) { $this->config['tb_schedulers'] = sprintf('%s',$cfg->tables->tb_schedulers); }
				if($cfg->tables->tb_pids) { $this->config['tb_pids'] = sprintf('%s',$cfg->tables->tb_pids); }
				
				//external programs
				if($cfg->extprogs->scheduler) { $this->config['scheduler'] = sprintf('%s',$cfg->extprogs->scheduler); }
				
			}
		catch (Exception $e) 
			{
				$desc='Configuration File Error : '.$e->getMessage();
				print $desc;
			}
	}
	
	public function get_config()
	{
		return $this->config;
	}
} //End AutomationConfig 
