<?php
/**
 * Order operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: orderops.php 2013-09-13 16:15:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
require_once(dirname(__FILE__).'/hsiconfig.php');
require_once(dirname(__FILE__).'/dbops.php');
require_once(dirname(__FILE__).'/curlops.php');

class OrderOps 
{
	public $cfg 	= null;
	public $dbops 	= null;
	public $curlops = null;
	private $orders_table = "";
	private $dlorderbatchs_tb_live = "";
	private $idfield = "id";
	private $appurl = "";
	private $order_processing_opt = "api/v2/orders.xml?status=Processing";
	
	public function __construct()
	{
		$this->cfg		= new HSIConfig();
		$config 		= $this->cfg->get_config();
		$this->appurl	= $config['appurl'];
		$this->dbops	= new DbOps($config);
		$this->curlops 	= new CurlOps($config);
		$this->orders_table = $config['tb_orders'];
		$this->dlorderbatchs_tb_live = $config['tb_dlorderbatchs'];
	}
	
	private function process_orders_xml($xmldata,$auto,$type="string")
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
		$xmlrows = ""; $rowcount = 0;
		
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
			$arr['orderlines']		= $this->get_order_lines($object);
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
				if($count > 0) 
				{ 
					$rowcount++;
$xmlrows .= sprintf('<row><order_id>%s</order_id><customer_id>%s</customer_id><tax_id>%s</tax_id><name>%s</name><contact>%s</contact></row>',$arr['id'],$arr['customer_id'],$arr['tax_id'],$arr['name'],$arr['contact'])."\n";
					$total = $total + $count; 
				} 
				else { $faillist .= $arr['id'].",";}
//print "<b>[DEBUG]---></b> "; print_r($arr); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
			}
		}
		$faillist = substr_replace($faillist, '', -1);		
		
		//create batch record only if valid orders exist
		if( $rowcount > 0 )
		{
			$xmlrows  = "<rows>\n".$xmlrows."</rows>\n";
			$xmllines = "<?xml version=\'1.0\' standalone=\'yes\'?>\n<formfields>\n";
			$xmllines .= "<header><column>Order Id</column><column>Customer Id</column><column>Tax Id</column><column>Name</column><column>Contact</column></header>\n";
			$xmllines .= $xmlrows."</formfields>\n";
			
			$log['id']		= $this->dbops->create_record_id($this->dlorderbatchs_tb_live);
			$log['batch_id']	= $batch_id ;
			$log['batch_date']	= date('Y-m-d'); 
		
			if($auto) { $desc = "Automated"; } else { $desc = "Manual"; }
			$description = sprintf("%s order batch download",$desc);
			$log['description']	= $description; 
		
			$xmllines = str_replace("&","&amp",$xmllines);
			$log['batch_details'] = $xmllines;
		
			$log['summary'] = sprintf("Total Sucessful Orders : %s\nFailist : %s",$total,$faillist);
		
			$log['inputter']		= "SYSINPUT";
			$log['input_date']	= date('Y-m-d H:i:s'); 
			$log['authorizer']	= "SYSAUTH";
			$log['auth_date']	= date('Y-m-d H:i:s'); 
			$log['record_status'] = "LIVE";
			$log['current_no']	= "1";
			$count = $this->dbops->insert_record($this->dlorderbatchs_tb_live, $log);
		}
		
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
	
	private function get_order_lines($object)
	{
		$xmlrows = "";
		foreach ($object->lines->object as $lineobj)
		{		
			$arr = array();
			$arr['sku'] 		= sprintf('%s',$lineobj->sku);
			$arr['description'] = str_replace('"','in',sprintf('%s',$lineobj->description));
			$arr['qty'] 		= sprintf('%s',$lineobj->qty);
			$arr['unitprice'] 	= sprintf('%s',$lineobj->unitPrice);
			$arr['total'] 		= sprintf('%s',$lineobj->total);
			$xmlrows .= sprintf('<row><sku>%s</sku><description>%s</description><qty>%s</qty><unitprice>%s</unitprice><total>%s</total></row>',$arr['sku'],$arr['description'],$arr['qty'],$arr['unitprice'],$arr['total'])."\n";
			
		} 
		$xmlrows  = "<rows>\n".$xmlrows."</rows>\n";
		$xmllines = "<?xml version=\'1.0\' standalone=\'yes\'?>\n<formfields>\n";
		$xmllines .= "<header><column>Sku</column><column>Description</column><column>Qty</column><column>Unitprice</column><column>Total</column></header>\n";
		$xmllines .= $xmlrows."</formfields>\n";
		return $xmllines;
	}
	
	public function update_orders($auto=false)
	{
		$RESULT = ""; $total_inserts = 0;
		$url	= $this->appurl.$this->order_processing_opt;
		$GET_REMOTE_DATA = TRUE;
		while( $GET_REMOTE_DATA )
		{
			$xml = $this->curlops->get_remote_data($url,$status);
			if( $status['http_code'] == 200 )
			{
				$meta = $this->process_orders_xml($xml,$auto);
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
