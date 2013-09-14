<?php
/**
 * Order operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: OrderOps.php 2013-09-13 16:15:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */

require_once(dirname(__FILE__).'/dbops.php');
require_once(dirname(__FILE__).'/curlops.php');

class OrderOps 
{
	public $dbops 	= null;
	public $curlops = null;
	private $orders_table = "dlorders";
	private $idfield = "id";
	private $appurl = "";
	private $order_processing_opt = "api/v2/orders.xml?status=Processing";
	
	public function __construct()
	{
		$this->dbops	= new DbOps();
		$this->curlops 	= new CurlOps();
				
		$configfile = dirname(__FILE__).'/hsiconfig.xml';
		try
			{
				//check for required fields in xml file
				$xml = file_get_contents($configfile);
				$config = new SimpleXMLElement($xml);
				if($config->handshake->appurl) { $this->appurl = sprintf('%s',$config->handshake->appurl); }
			}
		catch (Exception $e) 
			{
				$desc='Configuration File Error : '.$e->getMessage();
				print $desc;
			}
	}
	
	function process_orders_xml($xmldata,$type="string")
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
/*
id;int(11);unsigned NOT NULL
order_id;varchar(12);NOT NULL
batch_id;varchar(20);NOT NULL
customer_id;varchar(14);NOT NULL
tax_id;varchar(10);NOT NULL
name;varchar(50);NOT NULL
contact;varchar(50);NOT NULL
street;varchar(50);NOT NULL
country;varchar(50);NOT NULL
phone;varchar(21);NOT NULL
paymentterms;varchar(20);NOT NULL
cdate;date;NOT NULL
ctime;time;NOT NULL
orderlines;text;DEFAULT NULL; 
*/
		$batch_id = 'BDO-'.date('Ymd-His');
		foreach ($response->objects->object as $object)
		{
			$arr = array(); 
			$arr['id']				= sprintf('%s',$object->objID);
			$arr['order_id']		= sprintf('%s',$object->id);
			$arr['batch_id']		= $batch_id;
			$arr['customer_id']		= sprintf('%s',$object->customer->id);
			$arr['tax_id']			= sprintf('%s',$object->customer->taxID);
			$arr['name']			= sprintf('%s',$object->customer->name);
			$arr['contact']			= sprintf('%s',$object->customer->contact);
			$arr['street']			= sprintf('%s',$object->customer->billTo->street);
			$arr['city']			= sprintf('%s',$object->customer->billTo->city);
			$arr['country']			= sprintf('%s',$object->customer->billTo->country);
			$arr['phone']			= sprintf('%s',$object->customer->billTo->phone);
			$arr['paymentterms']	= sprintf('%s',$object->paymentTerms);
			$arr['cdate']			= sprintf('%s',$object->cdate);
			$ctimevals 				= preg_split('/T/',sprintf('%s',$object->ctime));
			$arr['ctime']			= str_replace("Z","", $ctimevals[1]);
			$arr['orderlines']		= "";
			$arr['inputter']		= "SYSINPUT";
			$arr['input_date']		= date('Y-m-d H:i:s'); 
			$arr['authorizer']		= "SYSAUTH";
			$arr['auth_date']		= date('Y-m-d H:i:s'); 
			$arr['record_status']	= "LIVE";
			$arr['current_no']		= "1";
			
			
			if( $this->dbops->record_exist($this->orders_table, $this->idfield, $arr['id']) )
			{ 
				//do nothing
			}
			else
			{
				$count = $this->dbops->insert_record($this->orders_table, $arr);
				if($count > 0) { $total = $total + $count; } else { $faillist .= $arr['id'].",";}
//print "<b>[DEBUG]---></b> "; print_r($arr); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
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
//print "<b>[DEBUG]---></b> "; print_r($meta); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		return $meta;
	}
	
	function update_orders()
	{
		$RESULT = ""; $total_inserts = 0;
		$url	= $this->appurl.$this->order_processing_opt;
		$GET_REMOTE_DATA = TRUE;
		while( $GET_REMOTE_DATA )
		{
			$xml = $this->curlops->get_remote_data($url,$status);
			if( $status['http_code'] == 200 )
			{
				$meta = $this->process_orders_xml($xml);
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
//$RESULT .= sprintf('<b>Summary</b><br>Total Processed : %s, Total Refreshed : %s, Failed : %s<br><hr>',$total_count,$total_failed,$total_inserts);
		$RESULT .= sprintf('<b>Summary</b><br>Total Download : %s',$total_count);
		return $RESULT;
	}

} //End OrderOps
