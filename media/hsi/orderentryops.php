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
//$debugline = sprintf("%s \t %s \t %s \t %s \t %s \t %s \t %s\n","daceasy_id","sku   ","tax","unitprice","custprice","availunits","description");
//print $debugline;	
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
		$querystr = sprintf('SELECT id,tax_id,paymentterms,cdate,ctime,orderlines FROM %s WHERE id = "%s"',"hsi_orders",$order_id);
		if( $result   = $this->dbops->execute_select_query($querystr) )
		{
			$order = $result[0];
			$xml = $order['orderlines'];
		
			$this->order_count++;
			$auto_order_id = str_pad($this->order_count, 10, "0", STR_PAD_LEFT);
			$field = array(); 
			$exist_TAXHDR = false;
			$exist_NTXHDR = false;
			$total_TAXHDR = 0;
			$total_NTXHDR = 0;
			$total_TAXVAT = 0;
			$total_NTXVAT = 0;
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
							$result   = $this->dbops->execute_select_query($querystr);
							
							$description = $result[0]['description'];
							$availunits = $result[0]['availunits'];
							$taxable = $result[0]['taxable'];
							$unitprice = $result[0]['unitprice'];
							$customer_price = 0;
							
							//line item in stock
							if( $availunits > 0 )
							{
								$customer_price = $this->get_customer_price($order['tax_id'],$sku,$unitprice);
								if( $taxable == "N" ) 
								{ 
									$this->count_NTXDTL++;
									$count_ntxhdr++;
									$count_orderlines = $count_ntxhdr;
									$total_NTXHDR = $total_NTXHDR + ($customer_price * $qty);
									$total_NTXVAT = $total_NTXVAT + round($customer_price * $qty * $vat,2);
									if( !$exist_NTXHDR )
									{
										$this->arr_NTXHDR = $this->addline_hdr($this->arr_NTXHDR,$order,$auto_order_id);
										$this->arr_NTXADR = $this->addline_adr($this->arr_NTXADR,$auto_order_id);								
										$exist_NTXHDR = true;
									} 
								}
								else 
								{ 
									$this->count_TAXDTL++;
									$count_taxhdr++;
									$count_orderlines = $count_taxhdr;
									$istaxable = 1; $vat = $this->config['vat'];
									$total_TAXHDR = $total_TAXHDR + ($customer_price * $qty);
									$total_TAXVAT = $total_TAXVAT + round($customer_price * $qty * $vat,2);
									if( !$exist_TAXHDR )
									{
										$this->arr_TAXHDR = $this->addline_hdr($this->arr_TAXHDR,$order,$auto_order_id);
										$this->arr_TAXADR = $this->addline_adr($this->arr_TAXADR,$auto_order_id);
										$exist_TAXHDR = true;
									} 
								}
															
								$field[0]  = sprintf('"%s"',$auto_order_id);
								$field[1]  = sprintf('%s',$count_orderlines);
								$field[2]  = sprintf('%s',"3");  
								$field[3]  = sprintf('"%s"',$sku);
								$field[4]  = sprintf('"%s"',$description);
								$field[5]  = sprintf('"%s"',""); //unknown 
								$field[6]  = sprintf('"%s"',substr($order['tax_id'],0,2)); //first two characters
								$field[7]  = sprintf('"%s"',$order['tax_id']);
								$field[8]  = sprintf('"%s"',"EACH"); //default to "EACH", can also be "LENGTH" 
								$field[9]  = sprintf('"%s"',$istaxable); 
								$field[10] = sprintf('"%s"',number_format($vat*100,2,'.','')); 
								$field[11] = sprintf('%s',"1"); //unknown, use default value 
								$field[12] = sprintf('%s',"1"); //unknown, use default value 
								$field[13] = sprintf('%s',"Y"); //unknown, use default value 
								$field[14] = sprintf('%s',"N"); //unknown, use default value 
								$field[15] = sprintf('%s',"Y"); //unknown, use default value 
								$field[16] = sprintf('%s',"N"); //unknown, use default value 
								$field[17] = sprintf('%s',"");  //unknown, use default value 
								$field[18] = sprintf('%s',$qty); 
								$field[19] = sprintf('%s',"0"); //unknown, use default value 
								$field[20] = sprintf('%s',"0"); //unknown, use default value 
								$field[21] = sprintf('%s',$qty);
								$field[22] = sprintf('%s',$customer_price);
								$field[23] = sprintf('%s',number_format(0,2,'.','')); 
								$field[24] = sprintf('%s',number_format(0,2,'.',''));
								$field[25] = sprintf('%s',number_format($customer_price * $qty,2,'.','')); //line total
								$field[26] = sprintf('%s',number_format($customer_price * $qty * $vat,2,'.','')); //line tax total
								$field[27] = sprintf('%s',"0"); //unknown, use default value 
								$field[28] = sprintf('%s',"0"); //unknown, use default value 
								$field[29] = sprintf('%s',str_replace("-","",$order['cdate'])); //order date
								$field[30] = sprintf('%s',"0"); //unknown, use default value 
								$field[31] = sprintf('%s',"0"); //unknown, use default value 
								$field[32] = sprintf('%s',"");  //unknown, use default value 
								
								$line = join(',',$field);
								
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
//$debugline = sprintf("%s \t %s \t %s \t %s \t %s \t %s \t %s\n",$order['tax_id'],$sku,$taxable,str_pad($unitprice,8," ",STR_PAD_LEFT),str_pad(number_format($customer_price,2,'.',''),8," ",STR_PAD_LEFT),str_pad($availunits,8," ",STR_PAD_LEFT),$description);
//print $debugline;						
						}
					}
				}
				//Add order totals here, summation calculations can only be inserted after rows/lines are processed
				$size_arr_TAXHDR = sizeof($this->arr_TAXHDR);
				$size_arr_NTXHDR = sizeof($this->arr_NTXHDR);
				$idx_tax = $size_arr_TAXHDR - 1;
				$idx_ntx = $size_arr_NTXHDR - 1;
				
				if( $idx_tax > -1 )
				{$istaxable = 0; $vat = 0; 
					$this->arr_TAXHDR[$idx_tax] = str_replace("%SUBTOTAL%",number_format($total_TAXHDR,2,'.',''),$this->arr_TAXHDR[$idx_tax]); 
					$this->arr_TAXHDR[$idx_tax] = str_replace("%SALESTAX%",number_format($total_TAXVAT,2,'.',''),$this->arr_TAXHDR[$idx_tax]); 
					$this->arr_TAXHDR[$idx_tax] = str_replace("%TAXABLE%" ,$istaxable,$this->arr_TAXHDR[$idx_tax]); 
					$this->arr_TAXHDR[$idx_tax] = str_replace("%VAT%"     ,number_format($vat,2,'.',''),$this->arr_TAXHDR[$idx_tax]); 
				}
				
				if( $idx_ntx > -1 )
				{
					$this->arr_NTXHDR[$idx_ntx] = str_replace("%SUBTOTAL%",number_format($total_NTXHDR,2,'.',''),$this->arr_NTXHDR[$idx_ntx]); 				
					$this->arr_NTXHDR[$idx_ntx] = str_replace("%SALESTAX%",number_format($total_NTXVAT,2,'.',''),$this->arr_NTXHDR[$idx_ntx]); 				
					$this->arr_NTXHDR[$idx_ntx] = str_replace("%TAXABLE%" ,$istaxable,$this->arr_NTXHDR[$idx_ntx]); 
					$this->arr_NTXHDR[$idx_ntx] = str_replace("%VAT%"     ,number_format($vat,2,'.',''),$this->arr_NTXHDR[$idx_ntx]); 
				}
			}
		catch (Exception $e) 
			{
				$desc = 'XML Error : '.$e->getMessage();
				print $desc;
			}
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
		
	private function addline_hdr($arr_line,$order,$auto_order_id)
	{
		$field = array();
		$table = "hsi_customers";
		$querystr = sprintf('SELECT id,tax_id,name,contact,street,city,country,phone FROM %s WHERE tax_id = "%s"',$table,$order['tax_id']);
		$result   = $this->dbops->execute_select_query($querystr);
		$customer = $result[0];
		
		//get payment terms number	
		$payment_terms = 0;
		if( preg_match('/NET/i', $order['paymentterms']) )
		{
			$payment_terms = trim( str_replace("NET","",$order['paymentterms']) );
		}
		else if( preg_match('/P.D./i', $order['paymentterms']) )
		{
			$payment_terms = trim( str_replace("P.D.","",$order['paymentterms']) );
		}
		
		//payment due date, add payment terms number to order date 
		$due_date = strtotime( "+".$payment_terms." days", strtotime($order['cdate']) );
		$payment_due_by = date("Y-m-d",$due_date);
		
		//get salesperson code, first 2 digits
		$salesperson_code = substr($order['tax_id'],0,2);
		
		//change ctime to upload format
		$ctime = substr( str_replace(":","",$order['ctime']),0,4 );
			
		$field[0]  = sprintf('"%s"',$auto_order_id);
		$field[1]  = sprintf('%s',"");                     //Unknown Field
		$field[2]  = sprintf('%s',"");                     //Unknown Field
		$field[3]  = sprintf('"%s"',$order['tax_id']);     //Customer Code 
		$field[4]  = sprintf('"%s"',$customer['name']);    //Billing Address (Line 1)
		$field[5]  = sprintf('"%s"',$customer['contact']); //Billing Address (Line 2)
		$field[6]  = sprintf('"%s"',$customer['street']);  //Billing Address (Line 3)
		$field[7]  = sprintf('"%s"',$customer['city']);    //Billing Address (Line 4)
		$field[8]  = sprintf('"%s"',"");                   //Billing Address (Line 5)
		$field[9]  = sprintf('"%s"',"");                   //Billing Address (Line 6)
		
		$field[10] = sprintf('"%s"',"     -");             //Billing Address (Line 7)
		$field[11] = sprintf('"%s"',$customer['country']); //Billing Address (Line 8)
		$field[12] = sprintf('"%s"',$customer['phone']);	 //Billing Address (Line 9)
		$field[13] = sprintf('"%s"',"%TAXABLE%");          //Tax
		$field[14] = sprintf('%s',"%VAT%");                //Tax Percentage
		$field[15] = sprintf('"%s"',$payment_terms);       //Terms                   
		$field[16] = sprintf('%s',str_replace("-","",$order['cdate'])); //Order Date
		$field[17] = sprintf('%s',str_replace("-","",$order['cdate'])); //Request Date
		$field[18] = sprintf('%s',"0");                    //Ship Date
		$field[19] = sprintf('%s',str_replace("-","",$order['cdate'])); //Early Payment Date
		
		$field[20] = sprintf('%s',str_replace("-","",$payment_due_by)); //Payment Due By
		$field[21] = sprintf('%s',"0.00");                 //Early Payment Discount
		$field[22] = sprintf('"%s"',$salesperson_code);    //Salesperson Code
		$field[23] = sprintf('"%s"',"HSI");                //Order Entry Person
		$field[24] = sprintf('"%s"',"");                   //Zone Code
		$field[25] = sprintf('"%s"',"");                   //FOB
		$field[26] = sprintf('"%s"',"");                   //Ship Via
		$field[27] = sprintf('"%s"',"");                   //Media Code
		$field[28] = sprintf('"%s"',"");                   //Job ID
		$field[29] = sprintf('"%s"',"CASH");               //Method of Payment
		
		$field[30] = sprintf('"%s"',$order['id']);         //Reference
		$field[31] = sprintf('%s',"%SUBTOTAL%");           //Sub Total
		$field[32] = sprintf('%s',"%SALESTAX%");           //Sales Tax
		$field[33] = sprintf('%s',"0.00");                 //Payment
		$field[34] = sprintf('%s',"0.00");                 //Freight
		$field[35] = sprintf('%s',"%SUBTOTAL%");           //Sub Total
		$field[36] = sprintf('%s',"%SUBTOTAL%");           //Sub Total
		$field[37] = sprintf('%s',"");                     //Unknown Field
		$field[38] = sprintf('%s',"Y");                    //Allow Back Orders
		$field[39] = sprintf('%s',"S");                    //Unknown Field
		
		$field[40] = sprintf('%s',$ctime);                 //Order Time
		$field[41] = sprintf('"%s"',"");                   //Unknown Field
		$field[42] = sprintf('"%s"',"lb");                 //Unknown Field
		$field[43] = sprintf('%s',"0.000");                //Payment
		$field[44] = sprintf('%s',"0.000");                //Unknown Field
		$field[45] = sprintf('%s',"");                     //Unknown Field
					
		$line 	 = join(',',$field);
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
//print sprintf("TAX FILES: \n%s\n%s\n",$current_filepath,$archive_filepath);		
		}
		
		if( $this->count_NTXDTL > 0 )
		{
			$taxtype = "NTX";
			$current_filepath = sprintf("%s/%s.%s.%s.%s.txt",$current_export_dir,$datestr,$file_id,$autostr,$taxtype);
			$archive_filepath = sprintf("%s/%s.%s.%s.%s.txt",$archive_export_dir,$datestr,$file_id,$autostr,$taxtype);
			$this->write_orderentry_file($current_filepath,$taxtype);
			$this->write_orderentry_file($archive_filepath,$taxtype);
//print sprintf("NTX FILES: \n%s\n%s\n",$current_filepath,$archive_filepath);		
		}
	}

} //End OrderEntryOps
