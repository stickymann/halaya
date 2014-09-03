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
	
	public function create_batch_picklists($batch_id,$auto=true)
	{
		$querystr = sprintf('SELECT id FROM %s WHERE batch_id = "%s"',"hsi_orders",$batch_id);
		$result   = $this->dbops->execute_select_query($querystr);
		foreach($result as $key => $value)
		{
			$order_id = $value['id'];
			$this->create_order_picklist($order_id,"none",$auto);
		}
	}
	
	public function create_order_picklist($order_id,$scrnopt="????",$auto=false)
	{
		$desc_width = "305"; $td_br_height = "5"; $qty_width = "40"; $th_height = "25"; 
		$wrap_width = 40;
		$data_wh = array(); $data_py = array(); $data_pr = array(); $pdffile = array();
		$pumps = $this->config['pumps']; 
		$pipes = $this->config['pipes']; 
		
		$querystr = sprintf('SELECT id,name,street,city,country,orderlines FROM %s WHERE id = "%s"',"hsi_orders",$order_id);
		$result   = $this->dbops->execute_select_query($querystr);
		$xml = $result[0]['orderlines'];
		$data_py = $data_pr = $data_wh = $result[0];
		$data_py['scrnopt'] = $data_pr['scrnopt'] = $data_wh['scrnopt'] = $scrnopt;
		//parse xml orders
		try
			{
				$formfields = new SimpleXMLElement($xml);
				if($formfields->rows) 
				{
					$HTML_TABLE_ROWS_WH = ""; $HTML_TABLE_ROWS_PY = ""; $HTML_TABLE_ROWS_PR = "";
					$rowcount_wh = 0; $rowcount_py = 0; $rowcount_pr = 0; 
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
							$availunits	 = $result[0]['availunits'];
							
							// pipe yard items
							if( $sku >= $pipes['lower'] && $sku <= $pipes['upper'] )
							{
								if( strlen($description) > $wrap_width ){ $rowcount_py++; }
								$HTML_TABLE_ROWS_PY .= sprintf('<tr valign="top"><td width="%s">%s<br style="font-size:%spt;"></td><td width="%s" align="right">%s</td></tr>',$desc_width,$description,$td_br_height,$qty_width,$qty)."\n";
								$rowcount_py++;
							}				
							// pump room items
							else if ( $sku >= $pumps['lower'] && $sku <= $pumps['upper'] )
							{
								if( strlen($description) > $wrap_width ){ $rowcount_pr++; }
								$HTML_TABLE_ROWS_PR .= sprintf('<tr valign="top"><td width="%s">%s<br style="font-size:%spt;"></td><td width="%s" align="right">%s</td></tr>',$desc_width,$description,$td_br_height,$qty_width,$qty)."\n";
								$rowcount_pr++;
							}
							// warehouse items
							else
							{
								if( strlen($description) > $wrap_width ){ $rowcount_wh++; }
								$HTML_TABLE_ROWS_WH .= sprintf('<tr valign="top"><td width="%s">%s<br style="font-size:%spt;"></td><td width="%s" align="right">%s</td></tr>',$desc_width,$description,$td_br_height,$qty_width,$qty)."\n";
								$rowcount_wh++;
							}
						} 
					}
				}
				
				$HTML1 = "<table>\n<thead>\n";
				$HTML1 .= sprintf('<tr valign="top"><th width="%s" height="%s"><b>ITEM</b></th><th align="right" width="%s" ><b>QTY</b></th></tr>',$desc_width,$th_height,$qty_width )."\n";
				$HTML1 .= "</thead>\n";
				$HTML1 .= "<tdata>\n";
				$HTML2 = "</tdata>\n";
				$HTML2 .= "</table>\n";

				// write warehouse pdf
				if( $rowcount_wh > 0 )
				{
					$WH = sprintf('%s%s%s',$HTML1,$HTML_TABLE_ROWS_WH,$HTML2);
					$data_wh['orderlines_table'] = $WH;
					$data_wh['rowcount'] = $rowcount_wh;
					$data_wh['datopt'] = "warehouse";
					$data_wh['title'] = "[ WAREHOUSE ]";
					$pdffile['warehouse'] = $this->write_pdf($data_wh,$auto);
				}

				// write pipeyard pdf
				if( $rowcount_py > 0 )
				{
					$PY = sprintf('%s%s%s',$HTML1,$HTML_TABLE_ROWS_PY,$HTML2);
					$data_py['orderlines_table'] = $PY;
					$data_py['rowcount'] = $rowcount_py;
					$data_py['datopt'] = "pipeyard";
					$data_py['title'] = "[ PIPE YARD ]";
					$pdffile['pipeyard'] = $this->write_pdf($data_py,$auto);
				}

				// write pumproom pdf
				if( $rowcount_pr > 0 )
				{
					$PR = sprintf('%s%s%s',$HTML1,$HTML_TABLE_ROWS_PR,$HTML2);
					$data_pr['orderlines_table'] = $PR;
					$data_pr['rowcount'] = $rowcount_pr;
					$data_pr['datopt'] = "pumproom";
					$data_pr['title'] = "[ PUMP ROOM ]";
					$pdffile['pumproom'] = $this->write_pdf($data_pr,$auto);
				}

				// print the appropiate picklist to screen 
				if( $scrnopt != "none")
				{
					switch($scrnopt)
					{
						case "warehouse":
							if( $rowcount_wh > 0 )
							{ 
								$pdffile['pipeyard'] = $this->write_pdf($data_wh,$auto);
							}
						break;
						
						case "pipeyard":
							if( $rowcount_py > 0 )
							{
								$pdffile['pipeyard'] = $this->write_pdf($data_py,$auto);
							}
						break;
						
						case "pumproom":
							if( $rowcount_pr > 0 )
							{
								$pdffile['pipeyard'] = $this->write_pdf($data_pr,$auto);
							}
						break;
					}
				}

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
		$title_text = $data['title'];
		
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
		$title = <<<_HTML_
<b>$title_text</b>
_HTML_;
		$orderlines = <<<_HTML_
$orderlines_table
_HTML_;

		$pdf->writeHTMLCell(0, 0, 0, $osy, 	 	$order_header_hr_top, 0, 0, 0, true, 'C', true);
		$pdf->writeHTMLCell(0, 0, 0, $osy+1, 	$order_header, 0, 1, 0, true, 'L', true);
		$pdf->writeHTMLCell(0, 0, 0, $osy+$ohh+1, $order_header_hr_btm, 0, 1, 0, true, 'C', true);
		$pdf->writeHTMLCell(0, 0, 0, $osy+$ohh+3, $title, 0, 1, 0, true, 'C', true);
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
		
		$picklist = $this->config['prn_picklist'];
		if( $auto )
		{
			$querystr = sprintf('INSERT INTO %s (filename,printer,status) VALUES("%s","%s","%s")',"_hsi_printq",$pdffile,$picklist['printer'],"NEW");
			$pdf->Output($pdffile, 'F');
			//add to pdf_queue for printing
			$this->dbops->execute_non_select_query($querystr);
		}
		else
		{
			if( $data['scrnopt'] == $data['datopt'] )
			{
				$querystr = sprintf('INSERT INTO %s (filename,printer,status) VALUES("%s","%s","%s")',"_hsi_printq",$pdffile,$picklist['printer'],"PRINTED");
				$pdf->Output($pdffile, 'I');		
				//add to pdf_queue for printing
				$this->dbops->execute_non_select_query($querystr);
			}
		}
		
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
			$printer = $this->config['prn_picklist']['printer'];
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
