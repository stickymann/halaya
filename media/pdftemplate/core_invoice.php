<?php
$formfields = new SimpleXMLElement($data);
$this->pdf->AddPage();

$page_config['formfields'] = $formfields;
$page_config['pdf_margin_left'] = $pdf_margin_left; 
$page_config['pdf_margin_right'] = $pdf_margin_right; 
$page_config['pdf_margin_top'] = $pdf_margin_top; 
$page_config['pdf_margin_bottom'] = $pdf_margin_bottom; 
$page_config['title'] = "INVOICE";
$page_config['title_height'] = 7;
$page_config['invoice_info_cellwidth'] = 96;
$page_config['invoice_info_cellheight'] = 15;
$page_config['invoice_info_posx_l'] = $pdf_margin_left;
$page_config['invoice_info_posx_r'] = 108;
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
$page_config['col1_width'] = 30;
$page_config['col2_width'] = 68;
$page_config['col3_width'] = 12;  
$page_config['col4_width'] = 12;
$page_config['col5_width'] = 25;
$page_config['col6_width'] = 20;	
$page_config['col7_width'] = 25;

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
	$od = new Orderdetail_Controller();
	$table = $od->param['tb_live'];
	$fields = $od->model->getFormFields("orderdetail");
	$prefix = "";
	$formfields = $page_config['formfields'];
	$item = $formfields->fields;
	$order_id = $item->order_id->value;

	$where = sprintf('where order_id = "%s"',$order_id);
	$orderby = "order by id";
	$result = $od->model->getAllRecsByFields($table,$fields,$prefix,$where,$orderby);
	if($result)
	{
		$html = '<table border="0" cellspacing="3" cellpadding="2" >'; 
		foreach($result as $index => $row)
		{
			if($row->user_text !="?") {$description = $row->description."<br>".nl2br($row->user_text); }else { $description = $row->description; }
			if($row->discount_type=="PERCENT")
			{
				$discount = (($row->qty*$row->unit_price) * $row->discount_amount / 100);
			}
			else
			{
				$discount =  $row->discount_amount;
			}
		
			if($row->taxable == "Y"){ $tax_amount = (($row->qty*$row->unit_price)-$discount) * ($row->tax_percentage/ 100); }
			else { $tax_amount ="0.00"; }
			$extended = ($row->qty*$row->unit_price) - $discount;

			$html .= '<tr>';
			$html .= sprintf('<td width="78">%s</td>',$row->product_id);
			$html .= sprintf('<td width="190" >%s<br></td>',$description);
			$html .= sprintf('<td width="30" align="center">%s</td>',$row->taxable);
			$html .= sprintf('<td width="31" align="center">%s</td>',$row->qty);
			$html .= sprintf('<td width="68" align="right">%s</td>',number_format($row->unit_price, 2, '.', ','));
			$html .= sprintf('<td width="55" align="right">%s</td>',number_format($discount, 2, '.', ','));
			$html .= sprintf('<td width="68" align="right">%s</td>',number_format($extended, 2, '.', ','));
			$html .= '</tr>';
		}
		$html .= '</table>';
		$html .= '<table><tr><td height="120"></td></tr></table>'; 
	}

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
	<table width="350" border=0 cellspacing=0 cellpadding=2 >
		<tr valign=top><td width="85"><b>Customer Name :</b> </td><td>$fullname</td></tr>
		<tr valign=top><td><b>Address :</b> </td><td>$address</td></tr>
		<tr valign=top><td><b>Phone :</b> </td><td>$phone</td></tr>
	</table>
_HTML_;
	$pdf->writeHTMLCell($page_config['invoice_info_cellwidth'], $page_config['invoice_info_cellheight'], $page_config['invoice_info_posx_l'], $page_config['invoice_info_posy'], $HTML_HDR_L, 0, 0, 0, true, 'L', true);

	$id = $item->id->value;				$invoice_date = $item->invoice_date->value;		
	$order_id = $item->order_id->value;	$inputter = $item->inputter->value;
	//bgcolor="red"
	$HTML_HDR_R=<<<_HTML_
	<table width="400" border=0 cellspacing=0 cellpadding=2 >
		<tr valign=top><td width="65"><b>Invoice No. :</b> </td><td>$id</td></tr>
		<tr valign=top><td><b>Order Id :</b> </td><td>$order_id</td></tr>
		<tr valign=top><td><b>Invoice Date :</b> </td><td>$invoice_date</td></tr>
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
		$html = '<table cellpadding="4"><tr valign="top"><td>Tax</td></tr></table>';
		$pdf->writeHTMLCell($cellwidth, $headerheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, $html, 1, 1, 0, true, 'C', true);
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset+$headerheight, "", 1, 1, 0, true, 'C', true);
	}
	else
	{
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, "", 1, 1, 0, true, 'C', true);
	}

	$leftshift=$leftshift+$cellwidth; $cellwidth=$page_config['col4_width'];
	if($page_config['pagetype'] == "firstpage" || $page_config['pagetype'] == "onepage")
	{
		$html = '<table cellpadding="4"><tr valign="top"><td>Qty</td></tr></table>';
		$pdf->writeHTMLCell($cellwidth, $headerheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, $html, 1, 1, 0, true, 'C', true);
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset+$headerheight, "", 1, 1, 0, true, 'C', true);
	}
	else
	{
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, "", 1, 1, 0, true, 'C', true);
	}
	
	$leftshift=$leftshift+$cellwidth; $cellwidth=$page_config['col5_width'];  
	if($page_config['pagetype'] == "firstpage" || $page_config['pagetype'] == "onepage")
	{
		$html = '<table cellpadding="4"><tr valign="top"><td>Unit Price</td></tr></table>';
		$pdf->writeHTMLCell($cellwidth, $headerheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, $html, 1, 1, 0, true, 'C', true);
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset+$headerheight, "", 1, 1, 0, true, 'C', true);
	}
	else
	{
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, "", 1, 1, 0, true, 'C', true);
	}

	$leftshift=$leftshift+$cellwidth; $cellwidth=$page_config['col6_width']; 
	if($page_config['pagetype'] == "firstpage" || $page_config['pagetype'] == "onepage")
	{
		$html = '<table cellpadding="4"><tr valign="top"><td>Discount</td></tr></table>';
		$pdf->writeHTMLCell($cellwidth, $headerheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, $html, 1, 1, 0, true, 'C', true);
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset+$headerheight, "", 1, 1, 0, true, 'C', true);
	}
	else
	{
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, "", 1, 1, 0, true, 'C', true);
	}
	
	$leftshift=$leftshift+$cellwidth; $cellwidth=$page_config['col7_width']; 
	if($page_config['pagetype'] == "firstpage" || $page_config['pagetype'] == "onepage")
	{
		$html = '<table cellpadding="4"><tr valign="top"><td>Extended</td></tr></table>';
		$pdf->writeHTMLCell($cellwidth, $headerheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, $html, 1, 1, 0, true, 'C', true);
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset+$headerheight, "", 1, 1, 0, true, 'C', true);
	}
	else
	{
		$pdf->writeHTMLCell($cellwidth, $detailheight, $pdf_margin_left+$leftshift, $pdf_margin_top+$offset, "", 1, 1, 0, true, 'C', true);
	}
}
function invoice_summary(&$pdf, $page_config)
{
	//customer name  and address
	$formfields = $page_config['formfields'] ;
	$item = $formfields->fields;
	$sub_total		= "$ ".number_format(sprintf('%s',$item->sub_total->value), 2, '.', ',');
	$tax_total		= "$ ".number_format(sprintf('%s',$item->tax_total->value), 2, '.', ',');
	$order_total	= "$ ".number_format(sprintf('%s',$item->order_total->value), 2, '.', ',');
	$discount_total = "$ ".number_format(sprintf('%s',$item->discount_total->value), 2, '.', ',');
	$payment_total	= "$ ".number_format(sprintf('%s',$item->payment_total->value), 2, '.', ',');
	$balance		= "$ ".number_format(sprintf('%s',$item->balance->value), 2, '.', ',');
	$HTML_HDR_L=<<<_HTML_
<div>
<b>SALE OF GOODS AGREEMENT</b><br>
I agree to the terms and conditions as set out on overleaf<br>
(Please read carefully before signing)<br>
<br>
<table width="400" border="0" cellspacing="0" cellpadding="2" >
<tr>
	<td td width="100">Customer Signature<br></td><td>___________________________________</td>
</tr>
<tr>
	<td>Customer Name</td><td>___________________________________<br>(Print)</td>
</tr>
</table>
</div>
_HTML_;
	$pdf->writeHTMLCell($page_config['summary_cellwidth'], $page_config['summary_cellheight'], $page_config['summary_posx_l'], $page_config['summary_posy'], "", 1, 0, 0, true, 'L', true);
	$pdf->writeHTMLCell($page_config['summary_cellwidth']-4, $page_config['summary_cellheight']-4, $page_config['summary_posx_l']+2, $page_config['summary_posy']+2, $HTML_HDR_L, 0, 0, 0, true, 'L', true);

	$HTML_HDR_R=<<<_HTML_
	<div>
	<table width="220" border="0" cellspacing="0" cellpadding="1" >
		<tr valign=bottom><td width="100" style="font-size: 11pt; font-weight: bold;">Sub Total :</td><td style="font-size: 11pt; text-align:right;">$sub_total</td></tr>
		<tr valign=bottom><td style="font-size: 11pt; font-weight: bold;">Tax Total :</td><td style="font-size: 11pt; text-align:right;">$tax_total</td></tr>
		<tr valign=bottom><td style="font-size: 12pt; font-weight: bold;">Grand Total :</td><td style="font-size: 11pt; text-align:right; font-weight: bold;">$order_total</td></tr>
	</table>
	<hr>
	<table width="220" border="0" cellspacing="0" cellpadding="1" >
		<tr valign=bottom><td width="100" style="font-size: 10pt;">Discount Total :</td><td style="font-size: 10pt; text-align:right;">$discount_total</td></tr>
		<tr valign=bottom><td style="font-size: 10pt;">Payment Total :</td><td style="font-size: 10pt; text-align:right;">$payment_total</td></tr>
		<tr valign=bottom><td style="font-size: 10pt;">Balance : </td><td style="font-size: 10pt; text-align:right;">$balance</td></tr>
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