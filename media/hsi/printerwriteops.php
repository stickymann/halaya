<?php
/**
 * Printer operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: printerwriteops.php 2014-04-01 08:15:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
require_once(dirname(__FILE__).'/hsiconfig.php');
require_once(dirname(__FILE__).'/dbops.php');
;


class PrinterWriteOps
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
	}
	
	public function print_batch($batch_id,$auto=false)
	{
		$querystr = sprintf('SELECT id FROM %s WHERE batch_id = "%s"',"hsi_orders",$batch_id);
		$result   = $this->dbops->execute_select_query($querystr);
		foreach($result as $key => $value)
		{
			$order_id = $value['id'];
			$this->print_order($order_id,$auto);
		}
	}
	
	public function print_order($order_id,$auto=false)
	{
		print $order_id."\n";
	
	}

} //End PrinteWriteOps
