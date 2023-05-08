<?php
$formfields = new SimpleXMLElement($data);
$this->pdf->AddPage();

$page_config['formfields'] = $formfields;
$page_config['pdf_margin_left'] = $pdf_margin_left; 
$page_config['pdf_margin_right'] = $pdf_margin_right; 
$page_config['pdf_margin_top'] = $pdf_margin_top; 
$page_config['pdf_margin_bottom'] = $pdf_margin_bottom; 
$page_config['title'] = "DELIVERY NOTE";
$page_config['title_height'] = 7;
$page_config['invoice_info_cellwidth'] = 96;
$page_config['invoice_info_cellheight'] = 15;
$page_config['invoice_info_posx_l'] = $pdf_margin_left;
$page_config['invoice_info_posx_r'] = 110;
$page_config['invoice_info_posy'] = $pdf_margin_top+10;
$page_config['invoice_info_borderheight'] = 29; //22
$page_config['invoice_info_borderposx'] = $pdf_margin_left;
$page_config['invoice_info_borderposy'] = $pdf_margin_top+9;

$page_config['invoice_summary_borderheight'] = 44;
$page_config['invoice_summary_borderposx'] = $pdf_margin_left;
$page_config['invoice_summary_borderposy'] = $pdf_margin_top+182;
$page_config['summary_cellwidth'] = 110; 
$page_config['summary_cellheight'] = 44; 
$page_config['summary_posx_l'] = $pdf_margin_left;
$page_config['summary_posx_r'] = 124;
$page_config['summary_posy'] = $pdf_margin_top+182;

$page_config['leftshift'] = 0;
$page_config['onepage_height'] = 133; //140
$page_config['fullpage_height'] = 226;
$page_config['firstpage_height'] = 177; //184
$page_config['lastpage_height'] = 182;
$page_config['firstpage_offset'] = 42; //35
$page_config['fullpage_offset'] = 0;
$page_config['pagetype'] = "onepage";
$page_config['details_headerheight'] = 7;
$page_config['col1_width'] = 45;
$page_config['col2_width'] = 120;
$page_config['col3_width'] = 27;  

page_title($this->pdf, $page_config);
invoice_info($this->pdf, $page_config);
invoice_info_border($this->pdf, $page_config);
invoice_details($this->pdf,$page_config);

$numpages = $this->pdf->getNumPages();
for($pagenum=1; $pagenum < $numpages+1; $pagenum++)
{
	if( $pagenum == 1 && $numpages == 1)
	{
		$page_config['pagetype'] = "onepage";
	}
	else if( $pagenum > 1 && $pagenum == $numpages)
	{
		$page_config['pagetype'] = "lastpage";
	}
	else if( $pagenum == 1 && $pagenum != $numpages)
	{
		$page_config['pagetype'] = "firstpage";
	}
	else if( $pagenum > 1 && $pagenum != $numpages)
	{
		$page_config['pagetype'] = "fullpage";
	}
	$this->pdf->setPage($pagenum, false);
	invoice_details_border($this->pdf,$page_config);
}

invoice_summary($this->pdf, $page_config);
invoice_summary_border($this->pdf, $page_config);

//############## invoice ends here ############## 

function invoice_details(&$pdf,$page_config)
{
	$dnote = new Controller_Core_Sales_Deliverynote();
	$formfields = $page_config['formfields'];
	$item = $formfields->fields;
	$deliverynote_id = $item->deliverynote_id->value;
	
	$querystr = sprintf('select details from %s where deliverynote_id = "%s"',$dnote->param['tb_live'],$deliverynote_id);
	$result = $dnote->param['primarymodel']->execute_select_query($querystr);
	$html = "";
	if($result)
	{
		$xml = new SimpleXMLElement($result[0]->details);
		$html .= '<table border="0" cellspacing="3" cellpadding="2" >'; 
		foreach($xml->rows->row as $row)
		{
			$html .= "<tr>";
			foreach ($row->children() as $field)
			{
				$subkey = sprintf('%s',$field->getName() );
				$val	= sprintf('%s',$row->$subkey);
				if( $subkey  == "product_id"){ $html .= sprintf('<td width="155">%s</td>',$val); }
				if( $subkey  == "description"){ $html .= sprintf('<td width="425">%s</td>',$val); }
				if( $subkey  == "filled_qty") { $html .= sprintf('<td width="70" align="center">%s</td>',$val); }
			}
			$html .= "</tr>";
		}
		$html .= '</table>';
		$html .= '<table><tr><td height="120"></td></tr></table>'; 
	}	
	
	//$html = $querystr;
	$detailheight = $page_config['firstpage_height'];
	$headerheight = $page_config['details_headerheight'];
	$offset	= $page_config['firstpage_offset']; 
	$pdf_margin_left= $page_config['pdf_margin_left']; 
	$pdf_margin_top = $page_config['pdf_margin_top']; 
	$pdf->writeHTMLCell(0, $detailheight, $pdf_margin_left, $pdf_margin_top+$offset+$headerheight, $html, 0, 1, 0, true, 'L', true);
}

function page_title(&$pdf,$page_config)
{	
	//title
	$html = sprintf('<span style="font-size: 16pt; font-weight: bold;">%s</span>', $page_config['title'] );
	$pdf->writeHTMLCell(0, $page_config['title_height'], $page_config['pdf_margin_left'], $page_config['pdf_margin_top'], $html, 0, 1, 0, true, 'C', true);
}

function invoice_info(&$pdf, $page_config)
{
	//customer name  and address
	$formfields = $page_config['formfields'] ;
	$item = $formfields->fields;
	
	if($item->customer_type->value == 'COMPANY'){$fullname = $item->last_name->value;}else{$fullname = $item->first_name->value.' '.$item->last_name->value;}
	
	$address = $item->address1->value.',<br>';
	if($item->address2->value != "") { $address .= $item->address2->value.',<br>';}
	$address .= $item->city->value;
	
	$phone = "";
	$phone_mobile1 = sprintf('%s',$item->phone_mobile1->value); $phone_home = sprintf('%s',$item->phone_home->value); $phone_work = sprintf('%s',$item->phone_work->value);
	if($phone_mobile1 != "" && $phone_mobile1 != "0") { $phone .= $phone_mobile1;}
	if($phone_home != "" && $phone_home != "0") { $phone .= ', '.$phone_home;}	
	if($phone_work != ""  && $phone_work != "0") { $phone .= ', '.$phone_work;}	
	$HTML_HDR_L=<<<_HTML_
	<table width="375" border="0" cellspacing="0" cellpadding="2" >
		<tr valign=top><td width="100"><b>Customer Name :</b> </td><td>$fullname</td></tr>
		<tr valign=top><td><b>Address :</b> </td><td>$address</td></tr>
		<tr valign=top><td><b>Phone :</b> </td><td>$phone</td></tr>
	</table>
_HTML_;
	$pdf->writeHTMLCell($page_config['invoice_info_cellwidth'], $page_config['invoice_info_cellheight'], $page_config['invoice_info_posx_l'], $page_config['invoice_info_posy'], $HTML_HDR_L, 0, 0, 0, true, 'L', true);

	$id = $item->id->value;
	$deliverynote_id = $item->deliverynote_id->value;
	$deliverynote_date = $item->deliverynote_date->value;		
	$order_id = $item->order_id->value;	
	$invoice_id = $item->invoice_id->value;
	$inputter = $item->inputter->value;
	$cc_id = $item->cc_id->value;		
	$is_co = $item->is_co->value;
	$charge_customer_id = "";
	if( $is_co == "Y"){ $charge_customer_id = sprintf('( %s )',$cc_id); }
	//bgcolor="red"
	$HTML_HDR_R=<<<_HTML_
	<table width="375" border="0" cellspacing="0" cellpadding="2" >
		<tr valign=top><td width="92"><b>Id :</b> </td><td>$deliverynote_id [ $id ]</td></tr>
		<tr valign=top><td><b>Order Id :</b> </td><td>$order_id [ $invoice_id ]</td></tr>
		<tr valign=top><td><b>Charge Order :</b> </td><td>$is_co $charge_customer_id</td></tr>
		<tr valign=top><td><b>Checkout Date :</b> </td><td>$deliverynote_date</td></tr>
		<tr valign=top><td><b>Agent :</b> </td><td>$inputter</td></tr>
	</table>
_HTML_;
	$pdf->writeHTMLCell(0, $page_config['invoice_info_cellheight'], $page_config['invoice_info_posx_r'], $page_config['invoice_info_posy'], $HTML_HDR_R, 0, 1, 0, true, 'L', true);
}

function invoice_info_border(&$pdf, $page_config)
{
	$pdf->writeHTMLCell(0, $page_config['invoice_info_borderheight'],$page_config['invoice_info_borderposx'],$page_config['invoice_info_borderposy'], "", 1, 1, 0, true, 'L', true);
}

function invoice_details_border(&$pdf,$page_config)
{
	$pdf_margin_left= $page_config['pdf_margin_left']; 
	$pdf_margin_top = $page_config['pdf_margin_top']; 
	$leftshift		= $page_config['leftshift']; 
	$cellwidth		= $page_config['col1_width']; 
	$headerheight	= $page_config['details_headerheight'];  
	switch($page_config['pagetype'])
	{
		case 'firstpage':
			$detailheight = $page_config['firstpage_height'];
			$offset = $page_config['firstpage_offset'];
		break;

		case 'lastpage':
			$detailheight = $page_config['lastpage_height'];
			$offset = $page_config['fullpage_offset'];
		break;
			
		case 'fullpage':
			$detailheight = $page_config['fullpage_height'];
			$offset = $page_config['fullpage_offset'];
		break;

		case 'onepage':
			$detailheight = $page_config['onepage_height'];
			$offset = $page_config['firstpage_offset'];
		break;
	}
	
	if($page_config['pagetype'] == "firstpage" || $page_config['pagetype'] == "onepage")
	{
		$html = '<table cellpadding="4"><tr valign="top"><td>Product Id</td></tr></table>';
		$pdf->writeHTMLCell($cellwidth, $headerheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, $html, 1, 1, 0, true, 'C', true);
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset+$headerheight, "", 1, 1, 0, true, 'C', true);
	}
	else
	{
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, "", 1, 1, 0, true, 'C', true);
	}

	$leftshift = $leftshift + $cellwidth;  $cellwidth=$page_config['col2_width']; 
	if($page_config['pagetype'] == "firstpage" || $page_config['pagetype'] == "onepage")
	{
		$html = '<table cellpadding="4"><tr valign="top"><td>Description</td></tr></table>';
		$pdf->writeHTMLCell($cellwidth, $headerheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, $html, 1, 1, 0, true, 'C', true);
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset+$headerheight, "", 1, 1, 0, true, 'C', true);
	}
	else
	{
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, "", 1, 1, 0, true, 'C', true);
	}

	$leftshift=$leftshift+$cellwidth; $cellwidth=$page_config['col3_width']; 
	if($page_config['pagetype'] == "firstpage" || $page_config['pagetype'] == "onepage")
	{
		$html = '<table cellpadding="4"><tr valign="top"><td>Quantity</td></tr></table>';
		$pdf->writeHTMLCell($cellwidth, $headerheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, $html, 1, 1, 0, true, 'C', true);
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset+$headerheight, "", 1, 1, 0, true, 'C', true);
	}
}

function invoice_summary(&$pdf, $page_config)
{
	//customer name  and address
	$formfields = $page_config['formfields'] ;
	$item = $formfields->fields;
	$delivered_by = $item->delivered_by->value;
	$delivery_date = $item->delivery_date->value;
	$comments = $item->comments->value;

	$HTML_HDR_L=<<<_HTML_
<div>
<b>DELIVERY OF GOODS AGREEMENT</b><br>
I agree that the items delivered are free of any physical defects.<br>
<br>
<table width="450" border="0" cellspacing="0" cellpadding="2" >
<tr>
	<td td width="120">Customer Signature<br></td><td>___________________________________</td>
</tr>
<tr>
	<td>Customer Name</td><td>___________________________________<br>(Print)</td>
</tr>
<tr>
	<td>Date</td><td>___________________________________<br></td>
</tr>
</table>
</div>
_HTML_;
	$pdf->writeHTMLCell($page_config['summary_cellwidth'], $page_config['summary_cellheight'], $page_config['summary_posx_l'], $page_config['summary_posy'], "", 1, 0, 0, true, 'L', true);
	$pdf->writeHTMLCell($page_config['summary_cellwidth']-4, $page_config['summary_cellheight']-4, $page_config['summary_posx_l']+2, $page_config['summary_posy']+2, $HTML_HDR_L, 0, 0, 0, true, 'L', true);

	$HTML_HDR_R=<<<_HTML_
<div>
<table width="220" border="0" cellspacing="0" cellpadding="1" >
	<tr valign=bottom><td width="90">Delivered By :</td><td>$delivered_by</td></tr>
	<tr valign=bottom><td width="90">Delivery Date :</td><td>$delivery_date</td></tr>
</table>
<table width="220" border="0" cellspacing="0" cellpadding="1" >
	<tr valign=bottom><td>Comments :</td></tr>
	<tr valign=bottom><td>$comments</td></tr>
</table>
</div>
_HTML_;
	$pdf->writeHTMLCell(0, $page_config['summary_cellheight'], $page_config['summary_posx_r'], $page_config['summary_posy'], "", 0, 1, 0, true, 'L', true);
	$pdf->writeHTMLCell(0, $page_config['summary_cellheight']-4, $page_config['summary_posx_r']+2, $page_config['summary_posy']+2, $HTML_HDR_R, 0, 1, 0, true, 'L', true);
}

function invoice_summary_border(&$pdf, $page_config)
{
	$pdf->writeHTMLCell(0, $page_config['invoice_summary_borderheight'],$page_config['invoice_summary_borderposx'],$page_config['invoice_summary_borderposy'], "", 1, 1, 0, true, 'L', true);
}
?>
