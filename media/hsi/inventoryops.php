<?php
/**
 * Inventory operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: inventoryops.php 2013-12-14 14:52:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
require_once(dirname(__FILE__).'/hsiconfig.php');
require_once(dirname(__FILE__).'/dbops.php');
require_once(dirname(__FILE__).'/fileops.php');

define("OUT_OF_STOCK","[OUT OF STOCK] ");

class InventoryOps 
{
	public $cfg 	= null;
	public $dbops 	= null;
	public $fileops = null;
	private $inventory_data = null;
	private $current_import = "";
	private $current_export = "";
	private $archive_import = "";
	private $archive_export = "";
	private $inventory_filename = "";
	private $tb_live = "hsi_inventorys";
	private $tb_hist = "hsi_inventorys_hs"; 
	
	
	public function __construct()
	{
		$this->cfg		= new HSIConfig();
		$config 	= $this->cfg->get_config();
		$this->dbops	= new DbOps($config);
		$this->fileops 	= new FileOps($config);
		$this->current_import = $config['current_import'];
		$this->current_export = $config['current_export'];
		$this->archive_import = $config['archive_import'];
		$this->archive_export = $config['archive_export'];
	}
	
	public function set_inventory_filename($filename)
	{
		$this->inventory_filename = $filename;
	}
	
	public function get_inventory_filename()
	{
		return $this->inventory_filename;
	}
	
	public function get_inventory_filepath()
	{
		return $this->current_import."/".$this->inventory_filename;
	}
	
	public function set_inventory_data()
	{
		$this->inventory_data = $this->fileops->structure_file_data( $this->get_inventory_filepath() );
	}
	
	public function get_inventory_data()
	{
		return $this->inventory_data;
	}
	
	public function process_inventory()
	{
		$datalist = $this->get_inventory_data();
/*
  `id` int(11) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `availunits` float(16,1) NOT NULL,
  `taxable` enum('Y','N') NOT NULL,
  `unitprice` float(16,2) NOT NULL,
  `hash1` varchar(64) NOT NULL,
  `hash2` varchar(64) NOT NULL,
  `inputter` varchar(50) NOT NULL,
  `input_date` datetime NOT NULL,
  `authorizer` varchar(50) NOT NULL,
  `auth_date` datetime NOT NULL,
  `record_status` char(4) NOT NULL,
  `current_no` int(11) NOT NULL,		
*/		
		foreach($datalist as $key => $value)
		{
			$code = $value[0];
			//codes that start with "9" do not exist in Handshake and should be excluded
			if($code[0] != "9")
			{
				if( $value[2] < 1 )
				{
					$value[1] = OUT_OF_STOCK.$value[1];
				}
				$hash1 = hash('sha256',$value[2].$value[3]);
				$hash2 = hash('sha256',$value[1].$value[4]);
				
				if( $this->dbops->record_exist($this->tb_live,"id",$value[0]) )
				{
				
				}
				else
				{
					$arr['id'] 			= $value[0];
					$arr['description'] = $value[1];
					$arr['availunits'] 	= $value[2];
					$arr['taxable'] 	= $value[3];
					$arr['unitprice'] 	= $value[4];
					$arr['hash1'] 		= $hash1;
					$arr['hash2'] 		= $hash2;
					$arr['inputter']	= "SYSINPUT";
					$arr['input_date']	= date('Y-m-d H:i:s'); 
					$arr['authorizer']	= "SYSAUTH";
					$arr['auth_date']	= date('Y-m-d H:i:s'); 
					$arr['record_status'] = "LIVE";
					$arr['current_no']	= "1";
					$count = $this->dbops->insert_record($this->tb_live, $arr);
				}
			}
		}
	}
}
