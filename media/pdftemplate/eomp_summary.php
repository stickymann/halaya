<?php
$formfields = new SimpleXMLElement($data);
$this->pdf->AddPage();

$page_config['formfields'] = $formfields;
$page_config['pdf_margin_left'] = $pdf_margin_left; 
$page_config['pdf_margin_right'] = $pdf_margin_right; 
$page_config['pdf_margin_top'] = $pdf_margin_top; 
$page_config['pdf_margin_bottom'] = $pdf_margin_bottom; 
$page_config['title'] = "INVOICE PAYMENTS SUMMARY";
$page_config['title_height'] = 10;
$page_config['col_width'] = array(30,60,20,25,25,25);
$page_config['col_headers'] = array('Invoice Date','Customer','Invoice #','SubTotal','VAT','Total');
$page_config['batch_description'] = "";

page_title($this->pdf, $page_config);
$page_config['batch_description'] = page_body($this->pdf, $page_config);
page_audit($this->pdf, $page_config);

function page_body(&$pdf,$page_config)
{
	//Header
	$pdf->SetFillColor(211,211,211);
	$pdf->SetTextColor(0);
	$pdf->SetLineWidth(0.1);
	$w = $page_config['col_width'];
	$header = $page_config['col_headers'];
	$num_headers = count($header);
	for($i = 0; $i < $num_headers; ++$i) 
	{
		$pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
	}
	$pdf->Ln();
	// Color and font restoration
	$pdf->SetFillColor(224, 235, 255);
	$fill = 0;
	
	//Line Data
	$formfields = $page_config['formfields'];
	$batch_id = $formfields->batch;
	$sitemodel = new Site_Model();
	
	$table = 'batchinvoices';
	$querystr = sprintf('select batch_description from %s where batch_id = "%s"', $table,$batch_id);
	$desc = $sitemodel->executeSelectQuery($querystr);
	$batch_description = $desc[0]->batch_description;
	
	$table = 'batchinvoicedetails';
	$fields = array('alt_invoice_id','order_date','first_name','last_name','extended_total','tax_total','payment_total');
	$querystr = sprintf('select %s from %s where batch_id = "%s"', join(',',$fields),$table,$batch_id);
	
	$result = $sitemodel->executeSelectQuery($querystr);
	if($result)
	{	
		$subtotal=0; $tax=0; $total=0;
		foreach($result as $index => $row)
		{	$pdf->Cell($w[0], 6, $row->order_date, 'LR', 0, 'C', $fill);
			$pdf->Cell($w[1], 6, $row->first_name." ".$row->last_name, 'LR', 0, 'L', $fill);
			$pdf->Cell($w[2], 6, $row->alt_invoice_id, 'LR', 0, 'C', $fill);
			$pdf->Cell($w[3], 6, number_format($row->extended_total, 2, '.', ','), 'LR', 0, 'R', $fill);
			$pdf->Cell($w[4], 6, number_format($row->tax_total, 2, '.', ','), 'LR', 0, 'R', $fill);
			$pdf->Cell($w[5], 6, number_format($row->payment_total, 2, '.', ','), 'LR', 0, 'R', $fill);
			$subtotal = $subtotal + $row->extended_total;
			$tax = $tax + $row->tax_total;
			$total = $total + $row->payment_total;
			$pdf->Ln();
			//$fill=!$fill;
		}
		//$pdf->Cell(array_sum($w), 0, '', 'T');
		//$pdf->Ln();
		$pdf->Cell($w[0], 6, "TOTALS", 'LBT', 0, 'C', $fill);
		$pdf->Cell($w[1]+$w[2], 6, "", 'RBT', 0, 'L', $fill);
		$pdf->Cell($w[3], 6, number_format($subtotal, 2, '.', ','), 'LRBT', 0, 'R', $fill);
		$pdf->Cell($w[4], 6, number_format($tax, 2, '.', ','), 'LRBT', 0, 'R', $fill);
		$pdf->Cell($w[5], 6, number_format($total, 2, '.', ','), 'LRBT', 0, 'R', $fill);
		$pdf->Ln();
		//$pdf->Cell(array_sum($w), 0, '', 'T');
	}
	return $batch_description;
}

function page_title(&$pdf,$page_config)
{	
	//title
	$html = sprintf('<span style="font-size: 16pt; font-weight: bold;">%s</span>', $page_config['title'] );
	$pdf->writeHTMLCell(0, $page_config['title_height'], $page_config['pdf_margin_left'], $page_config['pdf_margin_top'], $html, 0, 1, 0, true, 'C', true);
}

function page_audit(&$pdf,$page_config)
{
	$formfields = $page_config['formfields'];
	$batch_description = $page_config['batch_description'];
	$batch_id = $formfields->batch;
	$printuser = $formfields->audit->printuser;
	$printdate = $formfields->audit->printdate;
	$html = sprintf('Batch Id : %s <br>Description : %s<br>%s<br>%s',$batch_id,$batch_description,$printuser,$printdate);
	$pdf->Ln(); $pdf->Ln();
	$pdf->writeHTML($html, true, false, true, false, '');
}
?>