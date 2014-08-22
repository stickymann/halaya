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
	private $inventory_data = null;
	private $appurl = "";
	private $customer_filename = "";
	private $tb_live = "";
	private $tb_hist = "";
	private $chglog_tb_live = "";
	private $customer_processing_opt = "api/v2/customers?format=xml";
	private $address_processing_opt = "api/v2/addresses?format=xml";

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
	}
	
	public function get_customer_filename()
	{
		return $this->customer_filename;
	}
	
	public function get_customer_filepath()
	{
		return $this->current_import."/".$this->customer_filename;
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
			$arr['name']		= sprintf('%s',$object->name);
			$arr['contact']		= sprintf('%s',$object->contact);
print "<b>[DEBUG]---></b> "; print( sprintf('%s',$object->defaultShipTo) ); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
			$vals = preg_split('/\//',sprintf('%s',$object->defaultShipTo));
print "<b>[DEBUG]---></b> "; print_r( $vals ); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
			$arr['address_objid']	=  $vals[4];
			$arr['street']		=  "";
			$arr['city']		=  "";
			$arr['country']		=  "";
			$arr['phone']		=  "";
			$arr['customergroup_objid']		= sprintf('%s',$object->customerGroup->objID);
			$arr['customergroup_id']		= sprintf('%s',$object->customerGroup->id);
			
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
print "<b>[DEBUG]---></b> "; print( sprintf('%s',$object->btCustomer) ); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
			$vals = preg_split('/\//',sprintf('%s',$object->btCustomer));
print "<b>[DEBUG]---></b> "; print_r( $vals ); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
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
			$RESULT .= $this->update_address_with_handshake_data();
		}
		else if ( $counter > 0 )
		{
			if( $RESET )
			{
				$RESULT = $this->update_customer_with_handshake_data("UPDATE");
				$RESULT .= $this->update_address_with_handshake_data();
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
		$xmlrows = "";

		
		foreach($datalist as $key => $value)
		{
			$code = $value[0];
			//codes that start with "9" do not exist in Handshake and should be excluded
			if($code[0] != "6")
			{
				$phone = ""; 
				if( $num = $this->is_valid_phone_number($value[6]) )
				{
					$phone .= $num." / ";
				}
				
				if( $num = $this->is_valid_phone_number($value[7]) )
				{
					$phone .= $num." / ";
				}
				
				if( $num = $this->is_valid_phone_number($value[8]) )
				{
					$phone .= "F".$num." / ";
				}
				$phone = substr_replace($phone, '', -3);
				$hash = hash('sha256',$value[1].$value[2].$value[3].$value[4].$value[5].$phone);
				
				if( $this->dbops->record_exist($this->tb_live,"tax_id",$value[0]) )
				{
					$querystr = sprintf('SELECT id,hash,current_no FROM %s WHERE %s = "%s"',$this->tb_live,"tax_id",$value[0]);
print "[DEBUG]--->\n"; print( $querystr ); print( sprintf("\n[line %s - %s, %s]\n",__LINE__,__FUNCTION__,__FILE__) );
					
					
					$formdata = $this->dbops->execute_select_query($querystr);
					$record	  = $formdata[0];
print "[DEBUG]--->\n"; print("FIHASH:".$hash."\nDBHASH:".$record['hash']); print( sprintf("\n[line %s - %s, %s]\n",__LINE__,__FUNCTION__,__FILE__) );
										
					
					
					if( $hash != $record['hash'] )
					{
						$arr['id']			= $record['id'];
						$arr['name']		= $value[1];
						$arr['country']		= $value[2];
						$arr['street']		= $value[3];
						$arr['city']		= $value[4];
						$arr['country']		= $value[5];
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
$xmlrows .= sprintf('<row><id>%s</id><tax_id>%s</tax_id><name>%s</name><contact>%s</contact><street>%s</street><city>%s</city><country>%s</country><phone>%s</phone><entry>EDIT</entry></row>',$record['id'],$value[0],$value[1],$value[2],$value[3],$value[4],$value[5],$phone)."\n";
							}
						}
					}
				}
				else
				{
					usleep(1000000);
					$arr['id'] 			= date('YmdHis'); 
					$arr['tax_id']		= $value[0];
					$arr['name']		= $value[1];
					$arr['country']		= $value[2];
					$arr['street']		= $value[3];
					$arr['city']		= $value[4];
					$arr['country']		= $value[5];
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
$xmlrows .= sprintf('<row><id>%s</id><tax_id>%s</tax_id><name>%s</name><contact>%s</contact><street>%s</street><city>%s</city><country>%s</country><phone>%s</phone><entry>NEW</entry></row>',$record['id'],$value[0],$value[1],$value[2],$value[3],$value[4],$value[5],$phone)."\n";
					}
				}
			}
		
		}
		
		$xmlrows  = "<rows>\n".$xmlrows."</rows>\n";
		$xmllines = "<?xml version=\'1.0\' standalone=\'yes\'?>\n<formfields>\n";
		$xmllines .= "<header><column>Id</column><column>TaxId</column><column>Name</column><column>Contact</column><column>Street</column><column>City</column><column>Country</column><column>Phone</column><column>Entry</column></header>\n";
		$xmllines .= $xmlrows."</formfields>\n";
		
		$chglog['id']			= $this->dbops->create_record_id($this->chglog_tb_live);
		$chglog['changelog_id']	= $changelog_id;
		$chglog['type']			= "CUSTOMER";
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

} //End CustomerOps
