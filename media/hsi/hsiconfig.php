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
				
				//database
				if($cfg->database->server) { $this->hsiconfig['dbserver'] = sprintf('%s',$cfg->database->server); }
				if($cfg->database->name) { $this->hsiconfig['dbname'] = sprintf('%s',$cfg->database->name); }
				if($cfg->database->user) { $this->hsiconfig['dbuser'] = sprintf('%s',$cfg->database->user); }
				if($cfg->database->password) { $this->hsiconfig['dbpasswd'] = sprintf('%s',$cfg->database->password); }
				$this->hsiconfig['connectstr'] = sprintf('mysql:host=%s;dbname=%s', $this->hsiconfig['dbserver'], $this->hsiconfig['dbname']);

				//hanshake
				if($cfg->handshake->appurl) { $this->hsiconfig['appurl'] = sprintf('%s',$cfg->handshake->appurl); }
				if($cfg->handshake->apikey) { $this->hsiconfig['hs_apikey'] = sprintf('%s',$cfg->handshake->apikey); }
				
				//tax
				if($cfg->tax->vat) { $this->hsiconfig['vat'] = sprintf('%s',$cfg->tax->vat); } else { $this->hsiconfig['vat'] = 0; }
								
				//folders
				if($cfg->folders->current_import) { $this->hsiconfig['current_import'] = sprintf('%s',$cfg->folders->current_import); }
				if($cfg->folders->current_export) { $this->hsiconfig['current_export'] = sprintf('%s',$cfg->folders->current_export); }
				if($cfg->folders->archive_import) { $this->hsiconfig['archive_import'] = sprintf('%s',$cfg->folders->archive_import); }
				if($cfg->folders->archive_export) { $this->hsiconfig['archive_export'] = sprintf('%s',$cfg->folders->archive_export); }
				if($cfg->folders->archive_log) 	  { $this->hsiconfig['archive_log']    = sprintf('%s',$cfg->folders->archive_log); }
				
				//tables
				if($cfg->tables->tb_orders) { $this->hsiconfig['tb_orders'] = sprintf('%s',$cfg->tables->tb_orders); }
				if($cfg->tables->tb_dlorderbatchs) { $this->hsiconfig['tb_dlorderbatchs'] = sprintf('%s',$cfg->tables->tb_dlorderbatchs); }
				if($cfg->tables->tb_changelogs) { $this->hsiconfig['tb_changelogs'] = sprintf('%s',$cfg->tables->tb_changelogs); }
				if($cfg->tables->tb_inventorys) { $this->hsiconfig['tb_inventorys'] = sprintf('%s',$cfg->tables->tb_inventorys); }
				if($cfg->tables->tb_customers) { $this->hsiconfig['tb_customers'] = sprintf('%s',$cfg->tables->tb_customers); }
				if($cfg->tables->tb_schedulers) { $this->hsiconfig['tb_schedulers'] = sprintf('%s',$cfg->tables->tb_schedulers); }
				if($cfg->tables->tb_pidregs) { $this->hsiconfig['tb_pidregs'] = sprintf('%s',$cfg->tables->tb_pidregs); }
				
				//printers
				$this->hsiconfig['prn_picklist'] = array('printer'=>'PDF','copies'=>'1'); 
				if($cfg->printers->picklist) 
				{ 
					$picklist['printer'] = sprintf('%s',$cfg->printers->picklist->printer); 
					$picklist['copies'] = sprintf('%s',$cfg->printers->picklist->copies); 
					$this->hsiconfig['prn_picklist'] = $picklist;
				} 
				
				//ranges
				$this->hsiconfig['fittings'] =  array('lower'=>'0','upper'=>'0'); 
				$this->hsiconfig['pipes'] 	 =  array('lower'=>'0','upper'=>'0'); 
				$this->hsiconfig['pumps'] 	 =  array('lower'=>'0','upper'=>'0'); 
				if($cfg->ranges->fittings) 
				{ 
					$fittings['lower'] = sprintf('%s',$cfg->ranges->fittings->lower); 
					$fittings['upper'] = sprintf('%s',$cfg->ranges->fittings->upper);
					$this->hsiconfig['fittings'] =  $fittings;
				}
				
				if($cfg->ranges->pipes) 
				{ 
					$pipes['lower'] = sprintf('%s',$cfg->ranges->pipes->lower); 
					$pipes['upper'] = sprintf('%s',$cfg->ranges->pipes->upper);
					$this->hsiconfig['pipes'] =  $pipes;
				}
				
				if($cfg->ranges->pumps) 
				{ 
					$pumps['lower'] = sprintf('%s',$cfg->ranges->pumps->lower); 
					$pumps['upper'] = sprintf('%s',$cfg->ranges->pumps->upper);
					$this->hsiconfig['pumps'] =  $pumps;
				}
				
				//external programs
				if($cfg->extprogs->scheduler) { $this->hsiconfig['scheduler'] = sprintf('%s',$cfg->extprogs->scheduler); }
				
			}
		catch (Exception $e) 
			{
				$desc = 'Configuration File Error : '.$e->getMessage();
				print $desc;
			}
	}
	
	public function get_config()
	{
		return $this->hsiconfig;
	}
}
