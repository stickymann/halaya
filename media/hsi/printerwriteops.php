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
require_once(dirname(__FILE__).'/fileops.php');
require_once(dirname(__FILE__).'/tcpdf6_min/tcpdf_config.php');
require_once(dirname(__FILE__).'/tcpdf6_min/tcpdf.php');
require_once(dirname(__FILE__).'/tcpdf6_min/headfootdef.php');

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
		$this->fileops	= new FileOps($this->config);
	}
	
	public function create_batch_picklists($batch_id,$auto=false)
	{
		$querystr = sprintf('SELECT id FROM %s WHERE batch_id = "%s"',"hsi_orders",$batch_id);
		$result   = $this->dbops->execute_select_query($querystr);
		foreach($result as $key => $value)
		{
			$order_id = $value['id'];
			$this->create_order_picklist($order_id,$auto);
		}
	}
	
	public function create_order_picklist($order_id,$auto=false)
	{
		$desc_width = "305";
		$td_br_height = "5";
		$qty_width 	= "40";
		$th_height = "25"; 
		$data = array();
			
		$querystr = sprintf('SELECT id,name,street,city,country,orderlines FROM %s WHERE id = "%s"',"hsi_orders",$order_id);
		$result   = $this->dbops->execute_select_query($querystr);
		$xml = $result[0]['orderlines'];
		$data = $result[0];
		
		//parse xml orders
		try
			{
				$formfields = new SimpleXMLElement($xml);
				if($formfields->rows) 
				{
					$HTML_TABLE_ROWS = "";
					$rowcount = 0;
					foreach ($formfields->rows->row as $row) 
					{ 
						$sku = sprintf('%s',$row->sku);
						$qty = sprintf('%s',$row->qty);
						$table = "hsi_inventorys";
						if( $this->dbops->record_exist($table,"id",$sku) )
						{
							$querystr = sprintf('SELECT description,availunits FROM %s WHERE id = "%s"',$table,$sku);
							$result   = $this->dbops->execute_select_query($querystr);
							$description = $result[0]['description'];
							if( strlen($description) > 40 )
							{
								$rowcount++;
							}
							$availunits	 = $result[0]['availunits'];
							$HTML_TABLE_ROWS .= sprintf('<tr valign="top"><td width="%s">%s<br style="font-size:%spt;"></td><td width="%s" align="right">%s</td></tr>',$desc_width,$description,$td_br_height,$qty_width,$qty)."\n";
							$rowcount++;
						}
					}
				}
				
				$HTML = "<table>\n<thead>\n";
				$HTML .= sprintf('<tr valign="top"><th width="%s" height="%s"><b>ITEM</b></th><th align="right" width="%s" ><b>QTY</b></th></tr>',$desc_width,$th_height,$qty_width )."\n";
				$HTML .= "</thead>\n";
				$HTML .= "<tdata>\n";
				$HTML .= sprintf('%s',$HTML_TABLE_ROWS);
				$HTML .= "</tdata>\n";
				$HTML .= "</table>\n";
				$data['orderlines_table'] = $HTML;
				$data['rowcount'] = $rowcount;
				$pdffile = $this->write_pdf($data,$auto);
				return $pdffile;
			}
		catch (Exception $e) 
			{
				$desc = 'XML Error : '.$e->getMessage();
				print $desc;
			}
	}
	
	public function write_pdf($data,$auto=false)
	{
		$line_height = 7; //mm
		$page_width = 110; //mm
		$osy = 5;	//offset_y
		$ohh = 26; 	//order_header_height
		$olh = $line_height * ($data['rowcount'] + 1); //orderlines height
		$page_height = 20 + $ohh + $olh; //20 is margin/header/footer spacing
		if(	$page_height < $page_width ) { 	$page_height = $page_width; }
		$page_dimensions = array($page_height,$page_width);
		
		//initialize pdf document
		$pdf = new HSIPDF('P', PDF_UNIT, $page_dimensions, true, 'UTF-8', false);
		
		// set document information
		$pdf->SetCreator("Hand Shake to DacEasy Interface");
		$pdf->SetAuthor('');
		$pdf->SetTitle('');
		$pdf->SetSubject('');
		$pdf->SetKeywords('');
				
		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(1, 0, 1);
		$pdf->SetHeaderMargin(1);
		$pdf->SetFooterMargin(10);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, 10);
		
		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		
		// set some language-dependent strings (optional)
		/*
		if (@file_exists(dirname(__FILE__).'/tcpdf6_min/lang/eng.php')) 
		{
			require_once(dirname(__FILE__).'/tcpdf6_min/lang/eng.php');
			$pdf->setLanguageArray($l);
		}*/

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.	
		//$pdf->SetFont('dejavusans', '', 14, '', true);
		$pdf->SetFont('helvetica', '', 10, '', true);

		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();

		// set text shadow effect
		$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

		// Set some content to print
		 $order_id 	= $data['id'];
		 $name = $data['name'];
		 $address = $data['street'];
		 $country = $data['country'];
		 if( $data['city'] != "" ) { $address .= ", ".$data['city']; }
		 //if( $data['country'] != "" ) { $address .= ", ".$data['country']; }
		$orderlines_table = $data["orderlines_table"];
		
		/****** picklist layout ********/
		$order_header_hr_top = <<<_HTML_
<hr style="border: black solid 0px;">	
_HTML_;
		$order_header = <<<_HTML_
<span style="font-size: 15pt; font-weight: bold; text-align: center;">ORDER</span><br>
<span style="font-size: 13pt; font-weight: bold; text-align: center;">$order_id</span><br>
<span style="font-size: 10pt; font-weight: bold; text-align: center;">$name</span><br>
<span style="font-size: 10pt; font-weight: bold; text-align: center;">$address</span><br>
<span style="font-size: 10pt; font-weight: bold; text-align: center;">$country<br>
_HTML_;
		$order_header_hr_btm = <<<_HTML_
<hr style="border: black solid 0px;">	
_HTML_;
		$orderlines = <<<_HTML_
$orderlines_table
_HTML_;

		$pdf->writeHTMLCell(0, 0, 0, $osy, 	 	$order_header_hr_top, 0, 0, 0, true, 'C', true);
		$pdf->writeHTMLCell(0, 0, 0, $osy+1, 	$order_header, 0, 1, 0, true, 'L', true);
		$pdf->writeHTMLCell(0, 0, 0, $osy+$ohh+1, $order_header_hr_btm, 0, 1, 0, true, 'C', true);
		$pdf->writeHTMLCell(0, 0, 0, $osy+$ohh+3, $orderlines, 0, 1, 0, true, 'L', true);
		$pdf->writeHTMLCell(0, 0, 0, $page_height-15, $order_header_hr_btm, 0, 1, 0, true, 'L', true);
		
//print sprintf("Page Width: %s\nPage Height: %s\nOrdLn Height: %s\n",$page_width,$page_height,$olh);
		/****** end picklist layout ********/
			
		//$pdffile = "/tmp/hsi_picklist.pdf";
		$pdffile ="/tmp/hsi#".$order_id."-".date("YmdHis").".pdf";
		if( file_exists($pdffile) )
		{ 
			//delete file
			if( unlink($pdffile) ) 
			{ 
				/*wait for deletion*/ 
			}
		}
		usleep(1000000);
	
		if( $auto )
		{
			$querystr = sprintf('INSERT INTO %s (filename,printer,status) VALUES("%s","%s","%s")',"_hsi_printq",$pdffile,$this->config['prn_picklist'],"NEW");
			$pdf->Output($pdffile, 'F');
		}
		else
		{
			$querystr = sprintf('INSERT INTO %s (filename,printer,status) VALUES("%s","%s","%s")',"_hsi_printq",$pdffile,$this->config['prn_picklist'],"PRINTED");
			$pdf->Output($pdffile, 'I');
		}
		//add to pdf_queue for printing
		$this->dbops->execute_non_select_query($querystr);
		
		return $pdffile;
	}
	
	public function process_pdf_queue()
	{
		//delete "PRINTED" from queue
		$querystr = sprintf('DELETE FROM %s WHERE status="PRINTED"',"_hsi_printq");
		if( $this->dbops->execute_non_select_query($querystr) ) {/* wait for deletions*/ } 
				
		//get print_mode
		$querystr = sprintf('SELECT print_mode FROM %s WHERE config_id="DEFAULT"',"hsi_configs");
		if( $result = $this->dbops->execute_select_query($querystr) )
		{
			$print_mode = $result[0]['print_mode'];
			$printer = $this->config['prn_picklist'];
			//get queue items
			$querystr = sprintf('SELECT filename,printer,status FROM %s WHERE status="NEW"',"_hsi_printq");
			if( $queue = $this->dbops->execute_select_query($querystr) )
			{
				foreach($queue as $key => $value )
				{
					$filename = $value['filename'];
					
					switch($print_mode)
					{
						case "NONE":
							$this->fileops->delete_file($filename);
							$querystr = sprintf('DELETE FROM %s WHERE filename="%s"',"_hsi_printq",$filename);
							if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for deletions*/ } 
						break;
						
						case "PRINTER":
							$cmd = sprintf("lpr -r -P %s %s",$printer,$filename);
							exec($cmd ,$op);
							$querystr = sprintf('DELETE FROM %s WHERE filename="%s"',"_hsi_printq",$filename);
							if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for deletions*/ } 
						break;
						
						case "SCREEN":
							//$querystr = sprintf('UPDATE %s SET status="PRINTED" WHERE filename="%s"',"_hsi_printq",$filename);
							//if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for update*/ } 
							
							$querystr = sprintf('DELETE FROM %s WHERE filename="%s"',"_hsi_printq",$filename);
							if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for deletions*/ } 
							$this->fileops->delete_file($filename);
						break;
						
						case "BOTH":
							$cmd = sprintf("lpr -r -P %s %s",$printer,$filename);
							exec($cmd ,$op);
							//$querystr = sprintf('UPDATE %s SET status="PRINTED" WHERE filename="%s"',"_hsi_printq",$filename);
							//if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for update*/ } 
							$querystr = sprintf('DELETE FROM %s WHERE filename="%s"',"_hsi_printq",$filename);
							if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for deletions*/ } 
						break;
					}
				}
			
			}
		
		}
	}
	
} //End PrinteWriteOps
