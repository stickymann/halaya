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
				if($cfg->handshake->appurl) { $this->hsiconfig['appurl'] = rtrim( sprintf('%s',$cfg->handshake->appurl), "/" )."/"; }
				if($cfg->handshake->apiversion) { $this->hsiconfig['hs_apiver'] = rtrim( sprintf('%s',$cfg->handshake->apiversion), "/" )."/"; }
				if($cfg->handshake->apikey) { $this->hsiconfig['hs_apikey'] = sprintf('%s',$cfg->handshake->apikey); }
				$this->hsiconfig['push_customer'] = false;
				$this->hsiconfig['push_inventory'] = false;
				$this->hsiconfig['flag_outofstock'] = false;
				if($cfg->handshake->push_customer) 
				{
					$value = strtoupper( sprintf('%s',$cfg->push_customer) );
					if($value = "YES" || $value = "1" ) { $this->hsiconfig['push_customer'] = true; }
				}
				if($cfg->handshake->push_inventory) 
				{
					$value = strtoupper( sprintf('%s',$cfg->push_inventory) );
					if($value = "YES" || $value = "1" ) { $this->hsiconfig['push_inventory'] = true; }
				}
				if($cfg->handshake->flag_outofstock) 
				{
					$value = strtoupper( sprintf('%s',$cfg->flag_outofstock) );
					if($value = "YES" || $value = "1" ) { $this->hsiconfig['flag_outofstock'] = true; }
				}
				
				
				//orderentry
				if($cfg->orderentry->autoid_type) { $this->hsiconfig['autoid_type'] = sprintf('%s',$cfg->orderentry->autoid_type); }
								
				//taxes
				$taxarr = array("0"=>0);
				if($cfg->taxes) 
				{ 
					foreach($cfg->taxes->tax as $tax)
					{
						$code = sprintf('%s',$tax->code);
						$value = sprintf('%s',$tax->value);
						$taxarr[$code] = $value;
					}
				}
				$this->hsiconfig['tax'] = $taxarr;

				//folders
				if($cfg->folders->current_import) { $this->hsiconfig['current_import'] = rtrim( sprintf('%s',$cfg->folders->current_import), "/" )."/"; }
				if($cfg->folders->current_export) { $this->hsiconfig['current_export'] = rtrim( sprintf('%s',$cfg->folders->current_export), "/" )."/"; }
				if($cfg->folders->archive_import) { $this->hsiconfig['archive_import'] = rtrim( sprintf('%s',$cfg->folders->archive_import), "/" )."/"; }
				if($cfg->folders->archive_export) { $this->hsiconfig['archive_export'] = rtrim( sprintf('%s',$cfg->folders->archive_export), "/" )."/"; }
				if($cfg->folders->archive_log) 	  { $this->hsiconfig['archive_log']    = rtrim( sprintf('%s',$cfg->folders->archive_log), "/" )."/"; }
				
				//tables
				if($cfg->tables->tb_configs) { $this->hsiconfig['tb_configs'] = sprintf('%s',$cfg->tables->tb_configs); }
				if($cfg->tables->tb_orders) { $this->hsiconfig['tb_orders'] = sprintf('%s',$cfg->tables->tb_orders); }
				if($cfg->tables->tb_dlorderbatchs) { $this->hsiconfig['tb_dlorderbatchs'] = sprintf('%s',$cfg->tables->tb_dlorderbatchs); }
				if($cfg->tables->tb_changelogs) { $this->hsiconfig['tb_changelogs'] = sprintf('%s',$cfg->tables->tb_changelogs); }
				if($cfg->tables->tb_inventorys) { $this->hsiconfig['tb_inventorys'] = sprintf('%s',$cfg->tables->tb_inventorys); }
				if($cfg->tables->tb_customers) { $this->hsiconfig['tb_customers'] = sprintf('%s',$cfg->tables->tb_customers); }
				if($cfg->tables->tb_objects) { $this->hsiconfig['tb_objects'] = sprintf('%s',$cfg->tables->tb_objects); }
				if($cfg->tables->tb_schedulers) { $this->hsiconfig['tb_schedulers'] = sprintf('%s',$cfg->tables->tb_schedulers); }
				if($cfg->tables->tb_pidregs) { $this->hsiconfig['tb_pidregs'] = sprintf('%s',$cfg->tables->tb_pidregs); }
				if($cfg->tables->tb_printq) { $this->hsiconfig['tb_printq'] = sprintf('%s',$cfg->tables->tb_printq); }
				if($cfg->tables->tb_autoids) { $this->hsiconfig['tb_autoids'] = sprintf('%s',$cfg->tables->tb_autoids); }
				
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
