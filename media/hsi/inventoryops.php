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
require_once(dirname(__FILE__).'/curlops.php');

define("OUT_OF_STOCK","[OUT OF STOCK] ");

class InventoryOps 
{
	public $cfg 	= null;
	public $dbops 	= null;
	public $fileops = null;
	private $inventory_data = null;
	private $appurl = "";
	private $current_import = "";
	private $current_export = "";
	private $archive_import = "";
	private $archive_export = "";
	private $inventory_filename = "";
	private $tb_live = "";
	private $tb_hist = "";
	private $invchglog_tb_live = "";
	private $inventory_processing_opt = "api/v2/items?format=xml";
	
	public function __construct()
	{
		$this->cfg		= new HSIConfig();
		$config 		= $this->cfg->get_config();
		$this->appurl	= $config['appurl'];
		$this->dbops	= new DbOps($config);
		$this->fileops 	= new FileOps($config);
		$this->curlops 	= new CurlOps($config);
		
		$this->current_import = $config['current_import'];
		$this->current_export = $config['current_export'];
		$this->archive_import = $config['archive_import'];
		$this->archive_export = $config['archive_export'];
		$this->tb_live = $config['tb_inventorys'];
		$this->tb_hist = $config['tb_inventorys']."_hs";
		$this->chglog_tb_live = $config['tb_changelogs'];
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
	
	private function process_handshake_inventory_xml($xmldata,$update_type,$type="string")
	{
		$meta = array(); $total = 0; $faillist = "";
		if( $type == "file" )
		{ 
			$response = simplexml_load_file($xmldata); 
		} 
		else 
		{ 
			$response = simplexml_load_string($xmldata);
		}

		$batch_id = 'BDO-'.date('Ymd-His');
		foreach ($response->objects->object as $object)
		{
			$arr = array(); 
			$arr['id']				= sprintf('%s',$object->sku);
			$arr['item_objid']		= sprintf('%s',$object->objID);
			$arr['category_objid']	= sprintf('%s',$object->category->objID);
			$arr['category']		= str_replace('"',' _in_ ', sprintf('%s',$object->category->id) );
			$arr['inputter']		= "SYSINPUT";
			$arr['input_date']		= date('Y-m-d H:i:s'); 
			$arr['authorizer']		= "SYSAUTH";
			$arr['auth_date']		= date('Y-m-d H:i:s'); 
			$arr['record_status']	= "LIVE";
			$arr['current_no']		= "1";
			
			if( $update_type == "UPDATE" )
			{
				if( $this->dbops->record_exist($this->tb_live, "id", $arr['id']) )
				{ 
					$querystr = sprintf('SELECT id,current_no FROM %s WHERE %s = "%s"',$this->tb_live,"id",$arr['id']);
					$formdata = $this->dbops->execute_select_query($querystr);
					$record	  = $formdata[0];
					$arr['current_no']	= $record['current_no'] + 1;
					if( $this->dbops->insert_from_table_to_table($this->tb_hist,$this->tb_live,$arr['id'],$record['current_no']) )
					{
						$count = $this->dbops->update_record($this->tb_live, $arr);
						if($count > 0) { $total = $total + $count; } else { $faillist .= $arr['id'].",";}
					}
				}
			}
			else if ( $update_type == "INSERT" )
			{
				$count = $this->dbops->insert_record($this->tb_live, $arr);
				if($count > 0) { $total = $total + $count; } else { $faillist .= $arr['id'].",";}
			}
		}
		$faillist = substr_replace($faillist, '', -1);

		$meta['next']			= sprintf('%s',$response->meta->next);
		$meta['total_count']	= sprintf('%s',$response->meta->total_count);
		$meta['total_inserts']	= $total;
		$meta['previous']		= sprintf('%s',$response->meta->previous);
		$meta['limit']			= sprintf('%s',$response->meta->limit);
		$meta['offset']			= sprintf('%s',$response->meta->offset);
		$meta['faillist']		= $faillist;
		return $meta;
	}
			
	public function update_inventory_with_handshake_data($update_type)
	{
		$RESULT = ""; $total_inserts = 0;
		$url	= $this->appurl.$this->inventory_processing_opt;
		$GET_REMOTE_DATA = TRUE;
		while( $GET_REMOTE_DATA )
		{
			$xml = $this->curlops->get_remote_data($url,$status);
			if( $status['http_code'] == 200 )
			{
				$meta = $this->process_handshake_inventory_xml($xml,$update_type);
				if( $meta['next'] == "" )
				{
					$GET_REMOTE_DATA = FALSE;
				}
				else
				{
					$url = $this->appurl.$meta['next'];
					$RESULT .= sprintf('Processing records up to offset : %s<br>',$meta['offset']);
					$RESULT .= sprintf('Fail list : %s<br>',$meta['faillist']);
					$RESULT .= sprintf('Records refreshed : %s<br><hr>',$meta['total_inserts']);
					$total_inserts = $total_inserts + $meta['total_inserts'];
				}
			}
			usleep(1000000);
		}
	
		$RESULT .= sprintf('Processing records up to offset : %s<br>',$meta['offset']);
		$RESULT .= sprintf('Fail list : %s<br>',$meta['faillist']);
		$RESULT .= sprintf('Records refreshed : %s<br><hr>',$meta['total_inserts']);
		$total_inserts = $total_inserts + $meta['total_inserts'];
	
		$total_count = $meta['total_count']; 
		$total_failed = $total_count - $total_inserts;
		$RESULT .= sprintf('<b>Summary</b><br>Total Download : %s',$total_count);
		return $RESULT;
	}
	
	public function get_handshake_inventory($RESET=FALSE)
	{
		$RESULT = "";
		$querystr = sprintf('SELECT COUNT(id) as counter from %s',$this->tb_live);
		$result = $this->dbops->execute_select_query($querystr);
		$record	  = $result[0]; $counter = $record['counter'];
		if( $counter == 0 )
		{
			$RESULT = $this->update_inventory_with_handshake_data("INSERT");
		}
		else if ( $counter > 0 )
		{
			if( $RESET )
			{
				$RESULT = $this->update_inventory_with_handshake_data("UPDATE");
			}
			else
			{
				if( $this->dbops->last_changelog_have_new_records("INVENTORY") )
				{
					$RESULT = $this->update_inventory_with_handshake_data("UPDATE");
				}
			}
		}
	}
	
	public function process_inventory()
	{
		$this->get_handshake_inventory();
		//exit(0);		
		$datalist = $this->get_inventory_data();
/*
   `id` int(11) unsigned NOT NULL,
  `item_objid` int(11) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `category_objid` int(11) unsigned DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `availunits` float(16,1) DEFAULT NULL,
  `taxable` enum('Y','N') DEFAULT NULL,
  `unitprice` float(16,2) DEFAULT NULL,
  `hash1` varchar(64) DEFAULT NULL,
  `hash2` varchar(64) DEFAULT NULL,
  `inputter` varchar(50) NOT NULL,
  `input_date` datetime NOT NULL,
  `authorizer` varchar(50) NOT NULL,
  `auth_date` datetime NOT NULL,
  `record_status` char(4) NOT NULL,
  `current_no` int(11) NOT NULL,		
*/		
		$changelog_id = 'ICL-'.date('Ymd-His');
		$xmlrows = "";
		
		foreach($datalist as $key => $value)
		{
			$code = $value[0];
			//codes that start with "9" do not exist in Handshake and should be excluded
			if($code[0] != "9")
			{
				if( $value[2] < 1 )
				{
					$value[1] = $value[1].OUT_OF_STOCK;
				}
				$hash1 = hash('sha256',$value[2].$value[3]);
				$hash2 = hash('sha256',$value[1].$value[4]);
				
				if( $this->dbops->record_exist($this->tb_live,"id",$value[0]) )
				{
					$UPDATE = FALSE; $PUSH = FALSE;
					$querystr = sprintf('SELECT id,item_objid,category_objid,category,hash1,hash2,current_no FROM %s WHERE %s = "%s"',$this->tb_live,"id",$value[0]);
					$formdata = $this->dbops->execute_select_query($querystr);
					$record	  = $formdata[0];
					if( $hash1 != $record['hash1'] )
					{
						$arr['availunits'] 	= $value[2];
						$arr['taxable'] 	= $value[3];
						$arr['hash1'] 		= $hash1;
						$UPDATE = TRUE;
					}
					
					if( $hash2 != $record['hash2'] )
					{
						$arr['description'] = $value[1];
						$arr['unitprice'] 	= $value[4];
						$arr['hash2'] 		= $hash2;
						$PUSH = TRUE;
					}
					
					if($UPDATE || $PUSH)
					{
						$arr['id']	= $value[0];
						$arr['input_date']	= date('Y-m-d H:i:s'); 
						$arr['input_date']	= date('Y-m-d H:i:s'); 
						$arr['auth_date']	= date('Y-m-d H:i:s'); 
						$arr['current_no']	= $record['current_no'] + 1;
						if( $this->dbops->insert_from_table_to_table($this->tb_hist,$this->tb_live,$value[0],$record['current_no']) )
						{
							if( $count = $this->dbops->update_record($this->tb_live, $arr) )
							{
//$xmlrows .= sprintf('<row><code>%s</code><description>%s</description><availunits>%s</availunits><taxable>%s</taxable><unitprice>%s</unitprice><entry>EDIT</entry></row>',$arr['id'],str_replace('&','&amp;', $arr['description']),$arr['availunits'],$arr['taxable'],$arr['unitprice'])."\n";
$xmlrows .= sprintf('<row><code>%s</code><objid>%s</objid><description>%s</description><category>%s</category><availunits>%s</availunits><taxable>%s</taxable><unitprice>%s</unitprice><entry>EDIT</entry></row>',$arr['id'],$record['item_objid'],str_replace('&','&amp;', $arr['description']),str_replace('&','&amp;', $record['category']),$arr['availunits'],$arr['taxable'],$arr['unitprice'])."\n";
							}
						}
					}
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
					if( $count = $this->dbops->insert_record($this->tb_live, $arr) )
					{
$xmlrows .= sprintf('<row><code>%s</code><objid>%s</objid><description>%s</description><category>%s</category><availunits>%s</availunits><taxable>%s</taxable><unitprice>%s</unitprice><entry>NEW</entry></row>',$arr['id'],"",str_replace('&','&amp;', $arr['description']),"NEWITEM",$arr['availunits'],$arr['taxable'],$arr['unitprice'])."\n";
					}
				}
			}
		
		}
		
		$xmlrows  = "<rows>\n".$xmlrows."</rows>\n";
		$xmllines = "<?xml version=\'1.0\' standalone=\'yes\'?>\n<formfields>\n";
		$xmllines .= "<header><column>Code</column><column>ObjID</column><column>Description</column><column>Category</column><column>Availunits</column><column>Taxable</column><column>Unitprice</column><column>Entry</column></header>\n";
		$xmllines .= $xmlrows."</formfields>\n";
		
		$chglog['id']			= $this->dbops->create_record_id($this->chglog_tb_live);
		$chglog['changelog_id']	= $changelog_id;
		$chglog['type']			= "INVENTORY";
		$xmllines = str_replace("&","&amp;",$xmllines);
		$chglog['changelog_details'] = $xmllines;
		$chglog['inputter']		= "SYSINPUT";
		$chglog['input_date']	= date('Y-m-d H:i:s'); 
		$chglog['authorizer']	= "SYSAUTH";
		$chglog['auth_date']	= date('Y-m-d H:i:s'); 
		$chglog['record_status'] = "LIVE";
		$chglog['current_no']	= "1";
		$count = $this->dbops->insert_record($this->chglog_tb_live, $chglog);
	}

} //End InventoryOps
