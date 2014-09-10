<?php
/**
 * Customer operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: customerops.php 2013-12-14 14:52:46 dnesbit $
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

class CustomerOps 
{
	public $cfg 	= null;
	public $dbops 	= null;
	public $fileops = null;
	private $customer_data = null;
	private $appurl = "";
	private $customer_filename = "";
	private $customer_archive_filename = "";
	private $tb_live = "";
	private $tb_hist = "";
	private $chglog_tb_live = "";
	private $customer_processing_opt = "api/v2/customers?format=xml";
	private $address_processing_opt = "api/v2/addresses?format=xml";
	private $taxids = array();
	
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
		$this->tb_live = $config['tb_customers'];
		$this->tb_hist = $config['tb_customers']."_hs";
		$this->chglog_tb_live = $config['tb_changelogs'];
	}
	
	public function set_customer_filename($filename)
	{
		$this->customer_filename = $filename;
		$datestr = date('YmdHis');
		$this->customer_archive_filename = sprintf("%s_%s[ %s ].txt",$datestr,"CUSTOMER",$filename);
	}
	
	public function get_customer_filename()
	{
		return $this->customer_filename;
	}
	
	public function get_customer_archive_filename()
	{
		return $this->customer_archive_filename;
	}
	
	public function get_customer_filepath()
	{
		return $this->current_import."/".$this->customer_filename;
	}
	
	public function get_customer_archive_filepath()
	{
		return $this->archive_import."/".$this->customer_archive_filename;
	}
	
	public function set_customer_data()
	{
		$this->customer_data = $this->fileops->structure_file_data( $this->get_customer_filepath() );
	}
	
	public function get_customer_data()
	{
		return $this->customer_data;
	}
	
	public function is_valid_phone_number($numstr)
	{
		if( strlen($numstr) > 0 )
		{
			if( $numstr[strlen($numstr) - 1] == "-" )
			{
				return FALSE;
			}
			else
			{
				$vals = preg_split('/ /',$numstr);
				if( $vals[0] == "(   )" || $vals[0] == "(    )" || $vals[0] == "(     )" )
				{
					$vals[0] = "(868)";
					$numstr = $vals[0]." ".$vals[1];
				}
				return $numstr;
			}
		}
		return FALSE;
	}
		
	private function process_handshake_customer_xml($xmldata,$update_type,$type="string")
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
			$arr['id']			= sprintf('%s',$object->id);
			$arr['tax_id']		= sprintf('%s',$object->taxID);
			$arr['customer_objid']	= sprintf('%s',$object->objID);
			//$arr['name']		= sprintf('%s',$object->name);
			//$arr['contact']	= sprintf('%s',$object->contact);
//print "<b>[DEBUG]---></b> "; print( sprintf('%s',$object->defaultShipTo) ); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
			$vals = preg_split('/\//',sprintf('%s',$object->defaultShipTo));
//print "<b>[DEBUG]---></b> "; print_r( $vals ); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
			$arr['address_objid']	=  $vals[4];
			//$arr['street']	=  "";
			//$arr['city']		=  "";
			//$arr['country']	=  "";
			//$arr['phone']		=  "";
			$arr['customergroup_objid']		= sprintf('%s',$object->customerGroup->objID);
			$arr['customergroup_id']		= sprintf('%s',$object->customerGroup->id);
			
			$arr['inputter']		= "SYSINPUT";
			$arr['input_date']		= date('Y-m-d H:i:s'); 
			$arr['authorizer']		= "SYSAUTH";
			$arr['auth_date']		= date('Y-m-d H:i:s'); 
			$arr['record_status']	= "LIVE";
			$arr['current_no']		= "1";
			
if( !in_array ($arr['tax_id'],$this->taxids) ) 	//remove this line when duplicate tax_id fixed in Handshake
{
array_push($this->taxids, $arr['tax_id']);  //remove this line when duplicate tax_id fixed in Handshake
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
}											//remove this line when duplicate taxids fixed in Handshake

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
	
	private function process_handshake_address_xml($xmldata,$type="string")
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
//print "<b>[DEBUG]---></b> "; print( sprintf('%s',$object->btCustomer) ); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
			$vals = preg_split('/\//',sprintf('%s',$object->btCustomer));
//print "<b>[DEBUG]---></b> "; print_r( $vals ); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
			$customer_objid = $vals[4];
			
			$arr['id']			= "";
			$arr['street']		= sprintf('%s',$object->street);
			$arr['city']		= sprintf('%s',$object->city);
			$arr['country']		= sprintf('%s',$object->country);
			$arr['phone']		= sprintf('%s',$object->phone);
			$arr['hash']		= ""; 
			
			if( $this->dbops->record_exist($this->tb_live, "customer_objid", $customer_objid) )
			{ 
				$querystr = sprintf('SELECT id,name,contact FROM %s WHERE %s = "%s"',$this->tb_live,"customer_objid", $customer_objid);
				$formdata = $this->dbops->execute_select_query($querystr);
				$record	  = $formdata[0];
				
				$arr['id']		= $record['id'];
				$arr['hash'] 	= hash('sha256',$record['name'].$record['contact'].$arr['street'].$arr['city'].$arr['country'].$arr['phone'] );;
								
				$count = $this->dbops->update_record($this->tb_live, $arr);
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
	
	public function update_customer_with_handshake_data($update_type)
	{
		$RESULT = ""; $total_inserts = 0;
		$url	= $this->appurl.$this->customer_processing_opt;
		$GET_REMOTE_DATA = TRUE;
		while( $GET_REMOTE_DATA )
		{
			$xml = $this->curlops->get_remote_data($url,$status);
			if( $status['http_code'] == 200 )
			{
				$meta = $this->process_handshake_customer_xml($xml,$update_type);
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
	
	public function update_address_with_handshake_data()
	{
		$RESULT = ""; $total_inserts = 0;
		$url	= $this->appurl.$this->address_processing_opt;
		$GET_REMOTE_DATA = TRUE;
		while( $GET_REMOTE_DATA )
		{
			$xml = $this->curlops->get_remote_data($url,$status);
			if( $status['http_code'] == 200 )
			{
				$meta = $this->process_handshake_address_xml($xml);
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
		
	public function get_handshake_customer($RESET=FALSE)
	{
		$RESULT = "";
		$querystr = sprintf('SELECT COUNT(id) as counter from %s',$this->tb_live);
		$result = $this->dbops->execute_select_query($querystr);
		$record	  = $result[0]; $counter = $record['counter'];
		if( $counter == 0 )
		{
			$RESULT = $this->update_customer_with_handshake_data("INSERT");
			//$RESULT .= $this->update_address_with_handshake_data();
		}
		else if ( $counter > 0 )
		{
			if( $RESET )
			{
				$RESULT = $this->update_customer_with_handshake_data("UPDATE");
				//$RESULT .= $this->update_address_with_handshake_data();
			}
			else
			{
				if( $this->dbops->last_changelog_have_new_records("CUSTOMER") )
				{
					$RESULT = $this->update_customer_with_handshake_data("UPDATE");
					//$RESULT .= $this->update_address_with_handshake_data();
				}
			}
		}
	}

	public function process_customer()
	{
		$this->get_handshake_customer();
		$datalist = $this->get_customer_data();
		$changelog_id = 'CCL-'.date('Ymd-His');
		$xmlrows_new = ""; $xmlrows_edit = "";
/*
$value = Array
(
    [0] => 3K'S PLUMBING & ELECTRICAL SUPPLIES
    [1] => MR/MISS
    [2] => 
    [3] => MAIN ROAD LOUIS DOR LAND SETTLEMENT
    [4] => 
    [5] => TOBAGO
    [6] => (868) 495-3165
    [7] => (868) 314-3109
    [8] => (868)    -
    [9] => COD
    [10] => N
    [11] => 10
    [12] => 10TBG01902
)
*/
		foreach($datalist as $key => $value)
		{
			// field mapping
			$daceasy_id = $value[12];
			$name		= $value[0];
			$contact	= $value[1];
			$email		= $value[2];
			$street		= $value[3];
			$city		= $value[4];
			$country	= $value[5];
			$phone1		= $value[6];
			$phone2		= $value[7];
			$fax		= $value[8];
			$paymentterms = $value[9];
			$unknown	= $value[10];
			$psalemancode = $value[11];
						
			// codes that start with "9" do not exist in Handshake and should be excluded
			if($daceasy_id[0] != "6")
			{
				$phone = ""; 
				if( $num = $this->is_valid_phone_number($phone1) )
				{
					$phone .= $num." / ";
				}
				
				if( $num = $this->is_valid_phone_number($phone2) )
				{
					$phone .= $num." / ";
				}
				
				if( $num = $this->is_valid_phone_number($fax) )
				{
					$phone .= "F".$num." / ";
				}
				$phone = substr_replace($phone, '', -3);
				$hash = hash('sha256',$name.$contact.$street.$city.$country.$phone);
//print "[DEBUG]--->\n"; print_r ($value ); print( sprintf("\n[line %s - %s, %s]\n",__LINE__,__FUNCTION__,__FILE__) );
				if( $this->dbops->record_exist($this->tb_live,"tax_id",$daceasy_id) )
				{
					$querystr = sprintf('SELECT id,tax_id,hash,current_no FROM %s WHERE %s = "%s"',$this->tb_live,"tax_id",$daceasy_id);
//print "[DEBUG]--->\n"; print( $querystr ); print( sprintf("\n[line %s - %s, %s]\n",__LINE__,__FUNCTION__,__FILE__) );
					$formdata = $this->dbops->execute_select_query($querystr);
					$record	  = $formdata[0];
//print "[DEBUG]--->\n"; print("FIHASH:".$hash."\nDBHASH:".$record['hash']); print( sprintf("\n[line %s - %s, %s]\n",__LINE__,__FUNCTION__,__FILE__) );
					if( $hash != $record['hash'] )
					{
						$arr['id']			= $record['id'];
						$arr['tax_id']		= $daceasy_id;
						$arr['name']		= $name;
						$arr['contact']		= $contact;
						$arr['street']		= $street;
						$arr['city']		= $city;
						$arr['country']		= $country;
						$arr['phone']		= $phone;
						$arr['hash']		= $hash; 
						$arr['input_date']	= date('Y-m-d H:i:s'); 
						$arr['input_date']	= date('Y-m-d H:i:s'); 
						$arr['auth_date']	= date('Y-m-d H:i:s'); 
						$arr['current_no']	= $record['current_no'] + 1;
						if( $this->dbops->insert_from_table_to_table($this->tb_hist,$this->tb_live,$record['id'],$record['current_no']) )
						{
							if( $count = $this->dbops->update_record($this->tb_live, $arr) )
							{
$xmlrows_edit .= sprintf('<row><id>%s</id><tax_id>%s</tax_id><name>%s</name><contact>%s</contact><street>%s</street><city>%s</city><country>%s</country><phone>%s</phone><entry>EDIT</entry></row>',$record['id'],$daceasy_id,$name,$contact,$street,$city,$country,$phone)."\n";
							}
						}
					}
				}
				else
				{
					usleep(1000000);
					$arr['id'] 			= date('YmdHis'); 
					$arr['tax_id']		= $daceasy_id;
					$arr['name']		= $name;
					$arr['contact']		= $contact;
					$arr['street']		= $street;
					$arr['city']		= $city;
					$arr['country']		= $country;
					$arr['phone']		= $phone;
					$arr['hash']		= $hash; 
					$arr['inputter']	= "SYSINPUT";
					$arr['input_date']	= date('Y-m-d H:i:s'); 
					$arr['authorizer']	= "SYSAUTH";
					$arr['auth_date']	= date('Y-m-d H:i:s'); 
					$arr['record_status'] = "LIVE";
					$arr['current_no']	= "1";
					if( $count = $this->dbops->insert_record($this->tb_live, $arr) )
					{
$xmlrows_new .= sprintf('<row><id>%s</id><tax_id>%s</tax_id><name>%s</name><contact>%s</contact><street>%s</street><city>%s</city><country>%s</country><phone>%s</phone><entry>NEW</entry></row>',$arr['id'],$daceasy_id,$name,$contact,$street,$city,$country,$phone)."\n";
					}
				}
			}
		
		}
		
		$xmlrows  = "<rows>\n"."<!-- ########### NEW CUSTOMERS ########### -->\n".$xmlrows_new."<!-- ########### EXISTING CUSTOMERS ########### -->\n".$xmlrows_edit."</rows>\n";
		$xmllines = "<?xml version=\'1.0\' standalone=\'yes\'?>\n<formfields>\n";
		$xmllines .= "<header><column>Id</column><column>TaxId</column><column>Name</column><column>Contact</column><column>Street</column><column>City</column><column>Country</column><column>Phone</column><column>Entry</column></header>\n";
		$xmllines .= $xmlrows."</formfields>\n";
//print "[DEBUG]--->\n"; print $xmllines; print( sprintf("\n[line %s - %s, %s]\n",__LINE__,__FUNCTION__,__FILE__) );
		
		$chglog['id']			= $this->dbops->create_record_id($this->chglog_tb_live);
		$chglog['changelog_id']	= $changelog_id;
		$chglog['changelog_type'] = "CUSTOMER";
		$chglog['changelog_date'] = date('Y-m-d'); 
		$chglog['input_file']	= $this->get_customer_filename();
		$chglog['archive_file']	= $this->get_customer_archive_filename();
		$xmllines = str_replace("&","&amp;",$xmllines);
		$chglog['changelog_details'] = $xmllines;
		$chglog['inputter']		= "SYSINPUT";
		$chglog['input_date']	= date('Y-m-d H:i:s'); 
		$chglog['authorizer']	= "SYSAUTH";
		$chglog['auth_date']	= date('Y-m-d H:i:s'); 
		$chglog['record_status'] = "LIVE";
		$chglog['current_no']	= "1";
		$count = $this->dbops->insert_record($this->chglog_tb_live, $chglog);
		
		//necessary to wait a bit to generate unique changelog ids
		usleep(1000000);
		return $changelog_id;
	}
	
	public function push_handshake_customer($changelog_id)
	{
print $changelog_id."\n";
	}
	
	public function archive_customer_datafile()
	{
		$archive_import_dir = $this->archive_import;
		$src = $this->get_customer_filepath();
		$dest = $this->get_customer_archive_filepath();
		$filename = $this->get_customer_filename();
		if( !file_exists($archive_import_dir) ){ mkdir($archive_import_dir,0777,true); } 
		$this->fileops->move_file($src,$dest);
	}

} //End CustomerOps
