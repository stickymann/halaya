<?php
/**
 * Order Entry input file creation for Handshake to DacEasy Interface automation. 
 *
 * $Id: orderentryops.php 2014-04-01 08:15:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
require_once(dirname(__FILE__).'/hsiconfig.php');
require_once(dirname(__FILE__).'/dbops.php');
require_once(dirname(__FILE__).'/fileops.php');

class OrderEntryOps
{
	public $cfg 	= null;
	public $dbops 	= null;
	public $fileops = null;
	private $config = null;
	private $idfield = "id";
	private $arr_TAXHDR = array();
	private $arr_NTXHDR = array();
	private $arr_TAXADR = array();
	private $arr_NTXADR = array();
	private $arr_TAXDTL = array();
	private $arr_NTXDTL = array();
	private $count_TAXDTL = 0;
	private $count_NTXDTL = 0;
	private $order_count = 0;
	private $sectionhdr = array
		(
			"header"=>"DEOELINK__,",
			"endhdr"=>"@ENDHDR___",
			"endadr"=>"@ENDADR___",
			"enddtl"=>"@ENDDTL___",
			"footer"=>"@ENSERIAL_"
		);
	
	public function __construct()
	{
		$this->cfg		= new HSIConfig();
		$this->config 	= $this->cfg->get_config();
		$this->dbops	= new DbOps($this->config);
		$this->fileops	= new FileOps($this->config);
	}
	
	public function create_batch_entry($batch_id,$auto=false)
	{
$debugline = sprintf("%s \t %s \t %s \t %s \t %s \t %s \t %s\n","daceasy_id","sku   ","tax","unitprice","custprice","availunits","description");
print $debugline;	
		$querystr = sprintf('SELECT id FROM %s WHERE batch_id = "%s"',"hsi_orders",$batch_id);
		$result   = $this->dbops->execute_select_query($querystr);
		foreach($result as $key => $value)
		{
			$order_id = $value['id'];
			$this->create_order_entry($order_id,$auto);
		}
	}
	
	public function create_order_entry($order_id,$auto=false)
	{
		$querystr = sprintf('SELECT id,tax_id,name,street,city,country,orderlines,cdate FROM %s WHERE id = "%s"',"hsi_orders",$order_id);
		$result   = $this->dbops->execute_select_query($querystr);
		$customer = $result[0];
		$xml = $customer['orderlines'];
		
		$this->order_count++;
		$auto_order_id = str_pad($this->order_count, 10, "0", STR_PAD_LEFT);
		$arr = array(); 
		$exist_TAXHDR = false;
		$exist_NTXHDR = false;
		$count_taxhdr = -1;
		$count_ntxhdr = -1;
		$taxable = "";
		
		//parse xml orders
		try
			{
				$formfields = new SimpleXMLElement($xml);
				if($formfields->rows) 
				{
					foreach ($formfields->rows->row as $row) 
					{ 
						$sku = sprintf('%s',$row->sku);
						$qty = sprintf('%s',$row->qty);
						$table = "hsi_inventorys";
						$istaxable = 0; $vat = 0; 
						$taxable = "";
													
						//line item exist inventory
						if( $this->dbops->record_exist($table,"id",$sku) )
						{
							$querystr = sprintf('SELECT description,availunits,taxable,unitprice FROM %s WHERE id = "%s"',$table,$sku);
//print $querystr."\n"; 							
							$result   = $this->dbops->execute_select_query($querystr);
							
							$description = $result[0]['description'];
							$availunits = $result[0]['availunits'];
							$taxable = $result[0]['taxable'];
							$unitprice = $result[0]['unitprice'];
							$customer_price = 0;
							
							//line item in stock
							if( $availunits > 0 )
							{
								if( $taxable == "N" ) 
								{ 
									$this->count_NTXDTL++;
									$count_ntxhdr++;
									$count_orderlines = $count_ntxhdr;
									if( !$exist_NTXHDR )
									{
										$exist_NTXHDR = true;
									}
								}
								else 
								{ 
									$this->count_TAXDTL++;
									$count_taxhdr++;
									$count_orderlines = $count_taxhdr;
									$istaxable = 1; $vat = $this->config['vat'];
									if( !$exist_TAXHDR )
									{
										$exist_TAXHDR = true;
									}
								}
								
								$customer_price = $this->get_customer_price($customer['tax_id'],$sku,$unitprice);
								
								$arr[0]  = sprintf('"%s"',$auto_order_id);
								$arr[1]  = sprintf('%s',$count_orderlines);
								$arr[2]  = sprintf('%s',"3");  
								$arr[3]  = sprintf('"%s"',$sku);
								$arr[4]  = sprintf('"%s"',$description);
								$arr[5]  = sprintf('"%s"',""); //unknown 
								$arr[6]  = sprintf('"%s"',substr($customer['tax_id'],0,2)); //first two characters
								$arr[7]  = sprintf('"%s"',$customer['tax_id']);
								$arr[8]  = sprintf('"%s"',"EACH"); //default to "EACH", can also be "LENGTH" 
								$arr[9]  = sprintf('"%s"',$istaxable); 
								$arr[10] = sprintf('"%s"',number_format($vat*100,2,'.','')); 
								$arr[11] = sprintf('%s',"1"); //unknown, use default value 
								$arr[12] = sprintf('%s',"1"); //unknown, use default value 
								$arr[13] = sprintf('%s',"Y"); //unknown, use default value 
								$arr[14] = sprintf('%s',"N"); //unknown, use default value 
								$arr[15] = sprintf('%s',"Y"); //unknown, use default value 
								$arr[16] = sprintf('%s',"N"); //unknown, use default value 
								$arr[17] = sprintf('%s',""); //unknown, use default value 
								$arr[18] = sprintf('%s',$qty); 
								$arr[19] = sprintf('%s',"0"); //unknown, use default value 
								$arr[20] = sprintf('%s',"0"); //unknown, use default value 
								$arr[21] = sprintf('%s',$qty);
								$arr[22] = sprintf('%s',$customer_price);
								$arr[23] = sprintf('%s',number_format(0,2,'.','')); 
								$arr[24] = sprintf('%s',number_format(0,2,'.',''));
								$arr[25] = sprintf('%s',number_format($customer_price * $qty,2,'.','')); //line total
								$arr[26] = sprintf('%s',number_format($customer_price * $qty * $vat,2,'.','')); //line tax total
								$arr[27] = sprintf('%s',"0"); //unknown, use default value 
								$arr[28] = sprintf('%s',"0"); //unknown, use default value 
								$arr[29] = sprintf('%s',str_replace("-","",$customer['cdate'])); //order date
								$arr[30] = sprintf('%s',"0"); //unknown, use default value 
								$arr[31] = sprintf('%s',"0"); //unknown, use default value 
								$arr[32] = sprintf('%s',""); //unknown, use default value 
								
								$line = join(',',$arr);
								
								//Add order lines here
								if( $taxable == "N" ) 
								{ 
									array_push($this->arr_NTXDTL, $line);	
								}
								else 
								{ 
									array_push($this->arr_TAXDTL, $line);	
								}
							}
$debugline = sprintf("%s \t %s \t %s \t %s \t %s \t %s \t %s\n",$customer['tax_id'],$sku,$taxable,str_pad($unitprice,8," ",STR_PAD_LEFT),str_pad(number_format($customer_price,2,'.',''),8," ",STR_PAD_LEFT),str_pad($availunits,8," ",STR_PAD_LEFT),$description);
print $debugline;						
						}
					}
				}
				//Add order main info here, summation calculations can only be done after rows/lines are processed
				if( $taxable == "N" ) 
				{ 
					$this->arr_NTXHDR = $this->addline_hdr($this->arr_NTXHDR,$customer,$auto_order_id);
					$this->arr_NTXADR = $this->addline_adr($this->arr_NTXADR,$auto_order_id);								
				}
				else
				{									
					$this->arr_TAXHDR = $this->addline_hdr($this->arr_TAXHDR,$customer,$auto_order_id);
					$this->arr_TAXADR = $this->addline_adr($this->arr_TAXADR,$auto_order_id);
				}
			}
		catch (Exception $e) 
			{
				$desc = 'XML Error : '.$e->getMessage();
				print $desc;
			}
	}
	
	private function get_customer_price($daceasy_id,$sku,$unitprice)
	{
		//IMPLEMENT BUSINESS RULES HERE!
		
		if( $sku >= 100010 && $sku <= 203604)
		{ 
			//Apply discounts
			$PriceDefault 	= $unitprice; 			//Price2 (02)
			$PriceA			= $PriceDefault * .95; 	//Price1 (01)
			$PriceB			= $PriceA * .95; 		//PriceBase (05)
			$PriceC			= $PriceB * .98; 		//PriceALR (07)
		
			$len =  strlen($daceasy_id);
			if( $len == 10 )
			{
				//get customer group id, last 2 digits
				$customer_group = substr($daceasy_id,8,2);
				switch( $customer_group )
				{
						case "01":
							$unitprice = $PriceA;
						break;
					
						case "02":
							$unitprice = $PriceDefault;
						break;
						
						case "05":
							$unitprice = $PriceB;
						break;
						
						case "07":
							$unitprice = $PriceC;
						break;
				}
			}
		}
		return round($unitprice,2);
	}
		
	private function addline_hdr($arr_line,$arr_hdr,$auto_order_id)
	{
		$arr[0]  = sprintf('"%s"',$auto_order_id);
		$arr[1]  = sprintf('%s',"");
		$arr[2]  = sprintf('%s',"");  
		$arr[3]  = sprintf('"%s"',$arr_hdr['tax_id']);
		$line 	 = join(',',$arr);
		array_push($arr_line, $line);	
		return $arr_line;
	}
	
	private function addline_adr($arr_line,$auto_order_id)
	{
		$line = sprintf('"%s","","","","","","","","","",',$auto_order_id);
		array_push($arr_line, $line);	
		return $arr_line;
	}
		
	private function get_oefile_header()
	{
		$data = sprintf("%s\r\n",$this->sectionhdr['header']);
		return $data;
	}

	private function get_oefile_footer()
	{
		$data = sprintf("%s\r\n",$this->sectionhdr['footer']);
		return $data;
	}
	
	private function get_oefile_enddata($hdr,$arr)
	{
		$data = sprintf("%s\r\n",$hdr);
		foreach($arr as $key => $value)
		{
			$data .= sprintf("%s\r\n",$value);
		}
		return $data;
	}

	private function write_orderentry_file($filepath,$type)
	{
		$HDR = array(); $ADR = array(); $DTL = array();
		
		if( $type == "TAX" )
		{
			$HDR = $this->arr_TAXHDR;
			$ADR = $this->arr_TAXADR;	
			$DTL = $this->arr_TAXDTL;
		}
		else if( $type == "NTX" )
		{
			$HDR = $this->arr_NTXHDR;
			$ADR = $this->arr_NTXADR;	
			$DTL = $this->arr_NTXDTL;
		}
	
		$filedata  = $this->get_oefile_header();
		$filedata .= $this->get_oefile_enddata($this->sectionhdr['endhdr'],$HDR);
		$filedata .= $this->get_oefile_enddata($this->sectionhdr['endadr'],$ADR);
		$filedata .= $this->get_oefile_enddata($this->sectionhdr['enddtl'],$DTL);
		$filedata .= $this->get_oefile_footer();
		$this->fileops->write_file($filepath,$filedata);
	}
	
	public function process_orderentry_files($file_id="",$auto=false)
	{
		if( $auto ) { $autostr = "AUTO"; } else { $autostr = "MANU"; }
		//BDO-20140818-161016.AUTO.TAX.txt
		$datestr = date('YmdHis');
		$current_export_dir = $this->config['current_export'];
		$archive_export_dir = $this->config['archive_export'];	
		
		if( $this->count_TAXDTL > 0 )
		{	
			$taxtype = "TAX";
			$current_filepath = sprintf("%s/%s.%s.%s.%s.txt",$current_export_dir,$datestr,$file_id,$autostr,$taxtype);
			$archive_filepath = sprintf("%s/%s.%s.%s.%s.txt",$archive_export_dir,$datestr,$file_id,$autostr,$taxtype);
			$this->write_orderentry_file($current_filepath,$taxtype);
			$this->write_orderentry_file($archive_filepath,$taxtype);

print sprintf("TAX FILES: \n%s\n%s\n",$current_filepath,$archive_filepath);		
		}
		
		if( $this->count_NTXDTL > 0 )
		{
			$taxtype = "NTX";
			$current_filepath = sprintf("%s/%s.%s.%s.%s.txt",$current_export_dir,$datestr,$file_id,$autostr,$taxtype);
			$archive_filepath = sprintf("%s/%s.%s.%s.%s.txt",$archive_export_dir,$datestr,$file_id,$autostr,$taxtype);
			$this->write_orderentry_file($current_filepath,$taxtype);
			$this->write_orderentry_file($archive_filepath,$taxtype);
			
print sprintf("NTX FILES: \n%s\n%s\n",$current_filepath,$archive_filepath);		
		}
	}

} //End OrderEntryOps
