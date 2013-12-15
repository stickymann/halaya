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
		foreach($datalist as $key => $value)
		{
			$code = $value[0];
			//codes that start with "9" do not exist in Handshake and should be excluded
			if($code[0] != "9")
			{
				
				$hash1 = hash('sha256',$value[2].$value[3]);
				$hash2 = hash('sha256',$value[1].$value[4]);
				
				if( $this->dbops->record_exist($this->tb_live,"id",$value[0] )
				{
				
				}
				else
				{
					$arr['id'] = $value[0];
					
				}
			}
		}
	}
}
