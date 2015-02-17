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
		$orders_table = $this->config['tb_orders'];
		$querystr = sprintf('SELECT id FROM %s WHERE batch_id = "%s"',$orders_table,$batch_id);
		$result   = $this->dbops->execute_select_query($querystr);
		foreach($result as $key => $value)
		{
			$order_id = $value['id'];
			$this->create_order_picklist($order_id,null,null,$auto);
		}
	}
	
	public function create_order_picklist($order_id,$scrnopt=null,$prnopt=null,$auto=false)
	{
		$desc_width = "305"; $td_br_height = "5"; $qty_width = "40"; $th_height = "25"; 
		$wrap_width = 40;
		$data = array(); $pdfobj = array(); $pdffile = array();
		$pumps = $this->config['pumps']; 
		$pipes = $this->config['pipes']; 
		$location_set = "";
		
		$orders_table = $this->config['tb_orders'];
		$querystr = sprintf('SELECT id,name,street,city,country,orderlines,notes FROM %s WHERE id = "%s"',$orders_table,$order_id);
		$result   = $this->dbops->execute_select_query($querystr);
		$xml = $result[0]['orderlines'];
		$data['py'] = $data['pr'] = $data['wh'] = $result[0];
		$dataopt['scrnopt'] = $scrnopt; $dataopt['prnopt']  = $prnopt;

		//parse xml orders
		try
			{
				$formfields = new SimpleXMLElement($xml);
				if($formfields->rows) 
				{
					$HTML_TABLE_ROWS_WH = ""; $HTML_TABLE_ROWS_PY = ""; $HTML_TABLE_ROWS_PR = "";
					$rowcount['wh'] = 0; $rowcount['py'] = 0; $rowcount['pr'] = 0; 
					foreach ($formfields->rows->row as $row) 
					{ 
						$sku = sprintf('%s',$row->sku);
						$qty = sprintf('%s',$row->qty);
						$table = $this->config['tb_inventorys'];
						if( $this->dbops->record_exist($table,"id",$sku) )
						{
							$querystr = sprintf('SELECT description,availunits FROM %s WHERE id = "%s"',$table,$sku);
							$result   = $this->dbops->execute_select_query($querystr);
							$description = $result[0]['description'];
							$availunits	 = $result[0]['availunits'];
							
							// pipe yard items
							if( $sku >= $pipes['lower'] && $sku <= $pipes['upper'] )
							{
								if( strlen($description) > $wrap_width ){ $rowcount['py']++; }
								$HTML_TABLE_ROWS_PY .= sprintf('<tr valign="top"><td width="%s">%s<br style="font-size:%spt;"></td><td width="%s" align="right">%s</td></tr>',$desc_width,$description,$td_br_height,$qty_width,$qty)."\n";
								$rowcount['py']++;
							}				
							// pump room items
							else if ( $sku >= $pumps['lower'] && $sku <= $pumps['upper'] )
							{
								if( strlen($description) > $wrap_width ){ $rowcount['pr']++; }
								$HTML_TABLE_ROWS_PR .= sprintf('<tr valign="top"><td width="%s">%s<br style="font-size:%spt;"></td><td width="%s" align="right">%s</td></tr>',$desc_width,$description,$td_br_height,$qty_width,$qty)."\n";
								$rowcount['pr']++;
							}
							// warehouse items
							else
							{
								if( strlen($description) > $wrap_width ){ $rowcount['wh']++; }
								$HTML_TABLE_ROWS_WH .= sprintf('<tr valign="top"><td width="%s">%s<br style="font-size:%spt;"></td><td width="%s" align="right">%s</td></tr>',$desc_width,$description,$td_br_height,$qty_width,$qty)."\n";
								$rowcount['wh']++;
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

				
				if( $rowcount['wh'] > 0 ) { $location_set .= "WH,"; }
				if( $rowcount['py'] > 0 ) { $location_set .= "PY,"; }
				if( $rowcount['pr'] > 0 ) { $location_set .= "PR,"; }
				$location_set = substr_replace($location_set, '', -1);
								
				// create warehouse pdf
				if( $rowcount['wh'] > 0 )
				{
					$WH = sprintf('%s%s%s',$HTML1,$HTML_TABLE_ROWS_WH,$HTML2);
					$data['wh']['orderlines_table'] = $WH;
					$data['wh']['rowcount'] = $rowcount['wh'];
					$data['wh']['title'] = sprintf("[ WAREHOUSE {%s} ]",$location_set);
					//$data['wh'] = $arr;
					$pdfobj['warehouse'] = $this->create_pdf($data['wh']);
				}

				// create pipeyard pdf
				if( $rowcount['py'] > 0 )
				{
					$PY = sprintf('%s%s%s',$HTML1,$HTML_TABLE_ROWS_PY,$HTML2);
					$data['py']['orderlines_table'] = $PY;
					$data['py']['rowcount'] = $rowcount['py'];
					$data['py']['title'] = sprintf("[ PIPE YARD {%s} ]",$location_set);
					//$data['py'] = $arr;
					$pdfobj['pipeyard'] = $this->create_pdf($data['py']);
				}

				// create pumproom pdf
				if( $rowcount['pr'] > 0 )
				{
					$PR = sprintf('%s%s%s',$HTML1,$HTML_TABLE_ROWS_PR,$HTML2);
					$data['pr']['orderlines_table'] = $PR;
					$data['pr']['rowcount'] = $rowcount['pr'];
					$data['pr']['title'] = sprintf("[ PUMP ROOM {%s} ]",$location_set);
					//$data['pr'] = $arr;
					$pdfobj['pumproom'] = $this->create_pdf($data['pr']);
				}

				$dataopt['location'] = "";
				if( $auto )
				{
					foreach($pdfobj as $key => $pdfdata)
					{
						$copies = 1;
						$pdf_exist = false;
						if($key == "warehouse" )
						{
							$copies = $this->config['prn_picklist']['copies_wh'] + 1;
							$pdf_exist = true;
						}
						else if($key == "pipeyard" )
						{
							$copies = $this->config['prn_picklist']['copies_py'] + 1;
							$pdf_exist = true;
						}
						else if($key == "pumproom" )
						{
							$copies = $this->config['prn_picklist']['copies_pr'] + 1;
							$pdf_exist = true;
						}
												
						if($pdf_exist)
						{
							$filename = $pdfdata->filename;
							for($i=1; $i<$copies; $i++)
							{
								$pdfdata->filename = $filename."-".$i.".pdf"; 
								$pdffile[$key] = $this->write_pdf($pdfdata,$dataopt,$auto);
							}
						}
					}
				}
				else
				{
					// choose the appropiate picklist to print or display 
					$opt = ""; $pdffile = "";
					if( !is_null($scrnopt) || !is_null($prnopt) )
					{
						if( !is_null($scrnopt) ) { $opt = $scrnopt; } else if( !is_null($prnopt) ) { $opt = $prnopt; }
						switch($opt)
						{
							case "warehouse":
							if( $rowcount['wh'] > 0 )
							{ 
								$dataopt['location'] = "warehouse";
								$pdffile['warehouse'] = $this->write_pdf($pdfobj['warehouse'],$dataopt,$auto);
							}
							break;
						
							case "pipeyard":
								if( $rowcount['py'] > 0 )
								{
									$dataopt['location'] = "pipeyard";
									$pdffile['pipeyard'] = $this->write_pdf($pdfobj['pipeyard'] ,$dataopt,$auto);
								}
							break;
						
							case "pumproom":
								if( $rowcount['pr'] > 0 )
								{
									$dataopt['location'] = "pumproom";
									$pdffile['pumproom'] = $this->write_pdf($pdfobj['pumproom'] ,$dataopt,$auto);
								}
							break;
						}
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

	public function create_pdf($data)
	{
		$line_height = 7; //mm
		$page_width = 110; //mm
		$osy = 5;	//offset_y
		$ohh = 26; 	//order_header_height
		$notes_exist = false;
		$notes_lines = 0;
		if( $data['notes'] != "" )
		{
			$notes_exist = true;
			$notes = "NOTES : ".$data['notes'];
			if( strlen($notes) > 196)
			{
				$notes_lines = ($line_height - 0.5) * 4;
			}
			else if( strlen($notes) > 128)
			{
				$notes_lines = ($line_height - 0.5) * 3;
			}
			else if( strlen($notes) > 64)
			{
				$notes_lines = ($line_height - 0.5) * 2;
			}
			else
			{
				$notes_lines = ($line_height - 0.5) * 1;
			}
			$notes = $data['notes'];
		} 
		
		$olh = $line_height * ($data['rowcount'] + 1); //orderlines height
		$page_height = 20 + $ohh + $olh + $notes_lines; //20 is margin/header/footer spacing
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
		if( $data['city'] != "" ) 
		{ 
			$address .= ", ".$data['city']; 
			if( strlen($address) > 48 )
			{
				$address = $data['street'];
				$country = $data['city'].", ".$data['country'];
			}
		}
	
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
		if( $notes_exist )
		{
		$notes_text = <<<_HTML_
<b>NOTES :</b> $notes
_HTML_;
			$pdf->writeHTMLCell(0, 0, 0, $osy+$ohh+3, $notes_text, 0, 1, 0, true, 'L', true);
		}
		$pdf->writeHTMLCell(0, 0, 0, $osy+$ohh+$notes_lines+3, $title, 0, 1, 0, true, 'C', true);
		$pdf->writeHTMLCell(0, 0, 0, $osy+$ohh+$notes_lines+3, $orderlines, 0, 1, 0, true, 'L', true);
		$pdf->writeHTMLCell(0, 0, 0, $page_height-15, $order_header_hr_btm, 0, 1, 0, true, 'L', true);
		
//print sprintf("Page Width: %s\nPage Height: %s\nOrdLn Height: %s\n",$page_width,$page_height,$olh);
		/****** end picklist layout ********/
		
		$pdf->filename ="/tmp/hsi#".$order_id."-".date("YmdHis").".pdf";
		usleep(1000000);
		return $pdf;
	}
	
	public function write_pdf($pdf,$dataopt,$auto=false)
	{
		$pdffile = $pdf->filename;
		if( file_exists($pdffile) )
		{ 
			//delete file
			if( unlink($pdffile) ) 
			{ 
				/*wait for deletion*/ 
			}
		}
		
		$picklist = $this->config['prn_picklist'];
		$tb_printq = $this->config['tb_printq'];
		if( $auto && is_null($dataopt['scrnopt']) && is_null($dataopt['prnopt']) )
		{
			$querystr = sprintf('INSERT INTO %s (filename,printer,status) VALUES("%s","%s","%s")',$tb_printq,$pdffile,$picklist['printer'],"NEW");
			$pdf->Output($pdffile, 'F');
			//add to pdf_queue for printing
			$this->dbops->execute_non_select_query($querystr);
		}
		else if( !$auto && !is_null($dataopt['scrnopt']) && is_null($dataopt['prnopt']) )
		{
			if( $dataopt['scrnopt'] == $dataopt['location'] )
			{
				$querystr = sprintf('INSERT INTO %s (filename,printer,status) VALUES("%s","%s","%s")',$tb_printq,$pdffile,$picklist['printer'],"PRINTED");
				$pdf->Output($pdffile, 'I');		
				//add to pdf_queue for printing
				$this->dbops->execute_non_select_query($querystr);
			}
		}
		else if( !$auto && is_null($dataopt['scrnopt']) && !is_null($dataopt['prnopt']) )
		{
			if( $dataopt['prnopt'] == $dataopt['location'] )
			{
				$querystr = sprintf('INSERT INTO %s (filename,printer,status) VALUES("%s","%s","%s")',$tb_printq,$pdffile,$picklist['printer'],"PRINTED");
				$pdf->Output($pdffile, 'F');		
				//add to pdf_queue for printing
				$this->dbops->execute_non_select_query($querystr);
			}
		}
		return $pdffile;
	}
	
	public function process_pdf_queue()
	{
		$tb_configs = $this->config['tb_configs'];
		$tb_printq  = $this->config['tb_printq'];
		//delete "PRINTED" from queue
		$querystr = sprintf('DELETE FROM %s WHERE status="PRINTED"',$tb_printq);
		if( $this->dbops->execute_non_select_query($querystr) ) {/* wait for deletions*/ } 
				
		//get print_mode
		$querystr = sprintf('SELECT print_mode FROM %s WHERE config_id="DEFAULT"',$tb_configs);
		if( $result = $this->dbops->execute_select_query($querystr) )
		{
			$print_mode = $result[0]['print_mode'];
			$printer = $this->config['prn_picklist']['printer'];
			//get queue items
			$querystr = sprintf('SELECT filename,printer,status FROM %s WHERE status="NEW"',$tb_printq);
			if( $queue = $this->dbops->execute_select_query($querystr) )
			{
				foreach($queue as $key => $value )
				{
					$filename = $value['filename'];
					
					switch($print_mode)
					{
						case "NONE":
							$this->fileops->delete_file($filename);
							$querystr = sprintf('DELETE FROM %s WHERE filename="%s"',$tb_printq,$filename);
							if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for deletions*/ } 
						break;
						
						case "PRINTER":
							$cmd = sprintf("lpr -r -P %s %s",$printer,$filename);
							exec($cmd ,$op);
							$querystr = sprintf('DELETE FROM %s WHERE filename="%s"',$tb_printq,$filename);
							if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for deletions*/ } 
						break;
						
						case "SCREEN":
							//$querystr = sprintf('UPDATE %s SET status="PRINTED" WHERE filename="%s"',$tb_printq,$filename);
							//if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for update*/ } 
							
							$querystr = sprintf('DELETE FROM %s WHERE filename="%s"',$tb_printq,$filename);
							if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for deletions*/ } 
							$this->fileops->delete_file($filename);
						break;
						
						case "BOTH":
							$cmd = sprintf("lpr -r -P %s %s",$printer,$filename);
							exec($cmd ,$op);
							//$querystr = sprintf('UPDATE %s SET status="PRINTED" WHERE filename="%s"',$tb_printq,$filename);
							//if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for update*/ } 
							$querystr = sprintf('DELETE FROM %s WHERE filename="%s"',$tb_printq,$filename);
							if( $this->dbops->execute_non_select_query($querystr) ) { /* wait for deletions*/ } 
						break;
					}
				}
			
			}
		}
	}
	
} //End PrinteWriteOps
