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
require_once(dirname(__FILE__).'/objectops.php');

define("NEW_CUSTOMER_USER_GROUP","NEW-CUSTOMER.USER.GROUP");
define("NEW_CUSTOMER_CUSTOMER_GROUP","NEW-CUSTOMER.CUSTOMER.GROUP");

class CustomerOps 
{
	public $config 	= null;
	public $dbops 	= null;
	public $fileops = null;
	public $curlops = null;
	private $customer_data = null;
	private $appurl = "";
	private $customer_filename = "";
	private $customer_archive_filename = "";
	private $tb_live = "";
	private $tb_hist = "";
	private $chglog_tb_live = "";
	private $object_tb_live = "";
	private $customer_processing_opt = "";
	private $address_processing_opt = "";
	private $taxids = array();
	public $customer_group_objid = 0;
	public $user_group_objid = 0;
	
	public function __construct()
	{
		$cfg		= new HSIConfig();
		$this->config	= $cfg->get_config();
		$this->appurl	= $this->config['appurl'];
		$this->dbops	= new DbOps($this->config);
		$this->fileops 	= new FileOps($this->config);
		$this->curlops 	= new CurlOps($this->config);
		
		$this->current_import = $this->config['current_import'];
		$this->current_export = $this->config['current_export'];
		$this->archive_import = $this->config['archive_import'];
		$this->archive_export = $this->config['archive_export'];
		$this->tb_live = $this->config['tb_customers'];
		$this->tb_hist = $this->config['tb_customers']."_hs";
		$this->chglog_tb_live = $this->config['tb_changelogs'];
		$this->object_tb_live = $this->config['tb_objects'];
		$this->push_customer = $this->config['push_customer'];
		
		$this->customer_processing_opt = $this->config['hs_apiver']."customers?format=xml";
		$this->address_processing_opt = $this->config['hs_apiver']."addresses?format=xml";
		$this->set_customer_groups();
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
		return $this->current_import.$this->customer_filename;
	}
	
	public function get_customer_archive_filepath()
	{
		return $this->archive_import.$this->customer_archive_filename;
	}
	
	public function set_customer_data()
	{
		$this->customer_data = $this->fileops->structure_file_data( $this->get_customer_filepath() );
	}
	
	public function get_customer_data()
	{
		return $this->customer_data;
	}
	
	public function set_customer_groups()
	{
		$querystr = sprintf('SELECT hs_objid FROM %s WHERE mapping_id = "%s"',$this->object_tb_live,NEW_CUSTOMER_USER_GROUP);
		if( $result = $this->dbops->execute_select_query($querystr) )
		{
			$mapping = $result[0];
			$this->user_group_objid = $mapping['hs_objid'];
		}
		else
		{
			die();
		}
		
		$querystr = sprintf('SELECT hs_objid FROM %s WHERE mapping_id = "%s"',$this->object_tb_live,NEW_CUSTOMER_CUSTOMER_GROUP);
		if( $result = $this->dbops->execute_select_query($querystr) )
		{
			$mapping = $result[0];
			$this->customer_group_objid = $mapping['hs_objid'];
		}
		else
		{
			die();
		}
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
				$numstr = str_replace("(   )","(868)",$numstr);
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
			$arr['id']					= sprintf('%s',$object->id);
			$arr['tax_id']				= sprintf('%s',$object->taxID);
			$arr['customer_objid']		= sprintf('%s',$object->objID);
			$arr['name']				= sprintf('%s',$object->name);
			$arr['contact']				= sprintf('%s',$object->contact);
			$vals 						= preg_split('/\//',sprintf('%s',$object->defaultShipTo));
			$arr['address_objid']		= $vals[4];
			$arr['street']				= sprintf('%s',$object->billTo->street);
			$arr['city']				= sprintf('%s',$object->billTo->city);
			$arr['country']				= sprintf('%s',$object->billTo->country);
			$arr['phone']				= sprintf('%s',$object->billTo->phone);
			$arr['fax']					= sprintf('%s',$object->billTo->fax);
			$arr['email']				= sprintf('%s',$object->email);
			$arr['payment_terms']		= sprintf('%s',$object->paymentTerms);
			$arr['customergroup_objid']	= sprintf('%s',$object->customerGroup->objID);
			$arr['customergroup_id']	= sprintf('%s',$object->customerGroup->id);
			$arr['usergroup_objid']		= sprintf('%s',$object->userGroup->objID);
			$arr['usergroup_id']		= sprintf('%s',$object->userGroup->id);
			$arr['hash']				= hash('sha256',$arr['name'].$arr['contact'].$arr['street'].$arr['city'].$arr['country'].$arr['phone'].$arr['fax'].$arr['email'].$arr['payment_terms']);
			$arr['inputter']			= "SYSINPUT";
			$arr['input_date']			= date('Y-m-d H:i:s'); 
			$arr['authorizer']			= "SYSAUTH";
			$arr['auth_date']			= date('Y-m-d H:i:s'); 
			$arr['record_status']		= "LIVE";
			$arr['current_no']			= "1";

if($arr['tax_id']=="XXXX0001") { print_r($arr);	}	
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
			$vals = preg_split('/\//',sprintf('%s',$object->btCustomer));
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
			else
			{
				$GET_REMOTE_DATA = FALSE;
			}
			usleep(1000000);
		}
		
		if( $meta )
		{
			$RESULT .= sprintf('Processing records up to offset : %s<br>',$meta['offset']);
			$RESULT .= sprintf('Fail list : %s<br>',$meta['faillist']);
			$RESULT .= sprintf('Records refreshed : %s<br><hr>',$meta['total_inserts']);
			$total_inserts = $total_inserts + $meta['total_inserts'];
	
			$total_count = $meta['total_count']; 
			$total_failed = $total_count - $total_inserts;
			$RESULT .= sprintf('<b>Summary</b><br>Total Download : %s',$total_count);
		}
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
    [2] => emailuser@test.com
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
			if( intval($value[9]) > 0 ) { $payment_terms = sprintf('NET %s',$value[9]); } else { $payment_terms = $value[9]; }
			$unknown	= $value[10];
			$psalemancode = $value[11];
						
			// codes that start with "9" do not exist in Handshake and should be excluded
			if($daceasy_id[0] != "6")
			{
				$phone = ""; 
				if( $num = $this->is_valid_phone_number($phone1) ) { $phone .= $num." / "; }
				if( $num = $this->is_valid_phone_number($phone2) ) { $phone .= $num." / "; }
				if( $num = $this->is_valid_phone_number($fax) ) { $fax = $num;	} else { $fax = ""; }
				$phone = substr_replace($phone, '', -3);
				
				$hash = hash('sha256',$name.$contact.$street.$city.$country.$phone.$fax.$email.$payment_terms);
				
				if( $this->dbops->record_exist($this->tb_live,"tax_id",$daceasy_id) )
				{
					$querystr = sprintf('SELECT id,tax_id,hash,current_no FROM %s WHERE %s = "%s"',$this->tb_live,"tax_id",$daceasy_id);
					$formdata = $this->dbops->execute_select_query($querystr);
					$record	  = $formdata[0];
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
						$arr['fax']			= $fax;
						$arr['email']		= $email;
						$arr['payment_terms'] = $payment_terms;
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
					$arr['fax']			= $fax;
					$arr['email']		= $email;
					$arr['payment_terms'] = $payment_terms;
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
		if ( $count = $this->dbops->insert_record($this->chglog_tb_live, $chglog) ) { /*wait for insertion */ };
		
		//necessary to wait a bit to generate unique changelog ids
		usleep(1000000);
		return $changelog_id;
	}
	
	public function push_handshake_customer($changelog_id)
	{
		$logfile = sprintf('%sPUSH-%s.log.txt',$this->config['archive_log'],$changelog_id);
		$logdata = "";
		$status = array();
		
		$querystr = sprintf('SELECT id,changelog_id,changelog_details FROM %s WHERE changelog_id = "%s"',$this->chglog_tb_live,$changelog_id);
		if( $result = $this->dbops->execute_select_query($querystr) )
		{
			$changelog  = $result[0];
			$formfields = new SimpleXMLElement($changelog['changelog_details']);
			foreach ($formfields->rows->row as $row)
			{
				$tax_id = sprintf('%s',$row->tax_id);
				$querystr = sprintf('SELECT id,tax_id,customer_objid,name,contact,street,city,country,phone,fax,email,payment_terms,customergroup_objid,usergroup_objid FROM %s WHERE tax_id = "%s"',$this->tb_live,$tax_id);
				
				if( $formdata = $this->dbops->execute_select_query($querystr) )
				{
					$customer = $formdata[0];
					$entry = sprintf('%s',$row->entry);
					if( $entry == "EDIT" )
					{
						$arr = array
						(
							"id" 		=> $customer['id'],
							"taxID" 	=> $customer['tax_id'],
							"name" 		=> $customer['name'],
							"contact" 	=> $customer['contact'],
							"email" 	=> $customer['email'],
							"paymentTerms" => $customer['payment_terms'],
							"billTo" => array( "street" => $customer['street'], "city"  => $customer['city'], "country" => $customer['country'], "phone" => $customer['phone'], "fax" => $customer['fax'] )
							//Errors when sending to handshake, excluding for now
							//"customerGroup" => array("resource_uri"	=> "/api/v2/customer_groups/".$customer['customergroup_objid'] ),  
							//"userGroup" 	=> array("resource_uri"	=> "/api/v2/user_groups/".$customer['usergroup_objid'] ) 
						);
						$json_str = json_encode($arr);
						$url = sprintf('%s%s%s/%s',$this->appurl,$this->config['hs_apiver'],"customers",$customer['customer_objid']);

						$response = $this->curlops->put_remote_data($url,$json_str,$status);
						$logdata .= sprintf("PUT [ EXISTING RECORD ]:\r\n%s\r\nRESPONSE:\r\n%s\r\n-----------------------------------------\r\n",$json_str,$response);
					}
					else if ( $entry == "NEW" )
					{
						$arr = array
						(
							"id"  		=> $customer['id'], 
							"taxID" 	=> $customer['tax_id'],
							"name" 		=> $customer['name'],
							"contact" 	=> $customer['contact'],
							"email" 	=> $customer['email'],
							"paymentTerms" => $customer['payment_terms'],
							"billTo" => array( "street" => $customer['street'], "city"  => $customer['city'], "country" => $customer['country'], "phone" => $customer['phone'], "fax" => $customer['fax'] ),
							"userGroup" 	=> array("resource_uri"	=> "/api/v2/user_groups/".$this->user_group_objid )
						);
						$json_str = json_encode($arr);
						$url = sprintf('%s%s%s',$this->appurl,$this->config['hs_apiver'],"customers");
print "POST: ".$json_str."\n";
						$response = $this->curlops->post_remote_data($url,$json_str,$status);
						$logdata .= sprintf("POST [ NEW RECORD ]:\r\n%s\r\nRESPONSE:\r\n%s\r\n-----------------------------------------\r\n",$json_str,$response);
					}
				}
				//cannot exceed API 60 request per second
				usleep(1000000);
			}
			if($logdata == "") {$logdata = "NO NEW OR EDITED RECORDS\r\n"; }
			$this->write_push_logfile($logfile,$logdata);
		}
	}
	
	public function write_push_logfile($filepath,$filedata)
	{
		$this->fileops->write_file($filepath,$filedata);
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
