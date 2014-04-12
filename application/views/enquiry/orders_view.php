<?php
$params = array();
$sitedb = new Model_SiteDB;

$arr = $sitedb->get_controller_params("customer");
$param['customer_param_id'] = $arr['param_id'];

$arr = $sitedb->get_controller_params("order");
$param['order_param_id'] = $arr['param_id'];

$arr = $sitedb->get_controller_params("payment");
$param['payment_param_id'] = $arr['param_id'];

$arr = $sitedb->get_controller_params("inventchkout");
$param['inventchkout_param_id'] = $arr['param_id'];

$arr = $sitedb->get_controller_params("deliverynote");
$param['deliverynote_param_id'] = $arr['param_id'];
$param['pdfbuilder'] = "core_pdfbuilder";


print_to_screen($enquiryrecords,$pagination,$labels,$config,$param);

function get_section1($item,$labels,$param)
{
	$baseurl = URL::base();
	$customer_param_id = $param['customer_param_id'];
	$order_param_id = $param['order_param_id'];

	$label_01 = $labels['id'];				$item_01 = $item->id;
	$label_02 = $labels['order_id'];		$item_02 = $item->order_id;
	$label_03 = $labels['branch_id'];		$item_03 = $item->branch_id;
	$label_04 = $labels['inputter'];		$item_04 = $item->inputter;
	$label_05 = $labels['order_date'];		$item_05 = $item->order_date;
	$label_06 = $labels['input_date'];		$item_06 = $item->input_date;
	$label_07 = $labels['customer_id'];		$item_07 = $item->customer_id;
	$label_08 = $labels['phone_mobile1'];	$item_08 = $item->phone_mobile1;
	$label_09 = $labels['phone_home'];		$item_09 = $item->phone_home;
	$label_10 = $labels['phone_work'];		$item_10 = $item->phone_work;
	$label_13 = $labels['comments'];		$item_13 = $item->comments;
	$label_14 = $labels['invoice_note'];	$item_14 = $item->invoice_note;
	$label_15 = $labels['is_co'];			$item_15 = $item->is_co;
	$label_16 = $labels['cc_id'];			$item_16 = $item->cc_id;

	if($item->customer_type == 'COMPANY'){$fullname = $item->last_name;}else{$fullname = $item->first_name.' '.$item->last_name;}
	$address = $item->address1.', '.$item->address2.'<br>'.$item->city;
	$indexpage = "index.php";
	$HTML=<<<_HTML_
	<table  width="100%">
			<tr valign=top>
				<td width="50%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td class="ne_td1" width="30%">Customer Name : </td>
						<td class="ne_td2" ><a href="$baseurl$indexpage/$customer_param_id/index/$item_07" target="enquiry" title="Edit Customer">$fullname</a> [ $item_07 ]</td></tr>
						<tr valign=top><td class="ne_td1">Address : </td><td class="ne_td2" >$address</td></tr>
						<tr valign=top><td class="ne_td1">$label_08 : </td><td class="ne_td2" >$item_08</td></tr>
						<tr valign=top><td class="ne_td1">$label_09 : </td><td class="ne_td2" >$item_09</td></tr>
						<tr valign=top><td class="ne_td1">$label_10 : </td><td class="ne_td2" >$item_10</td></tr>
						<tr valign=top><td class="ne_td1">$label_14 : </td><td class="ne_td2" >$item_14</td></tr>	
						<tr valign=top><td class="ne_td1">$label_13 : </td><td class="ne_td2" >$item_13</td></tr>
					</table>
				</td>
				<td width="50%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td class="ne_td1" width='35%'>$label_01 : </td><td class="ne_td2" >$item_01</td></tr>
						<tr valign=top><td class="ne_td1">$label_02 : </td>
						<td class="ne_td2" ><a href='$baseurl$indexpage/$order_param_id/index/$item_02' target='enquiry' title='Edit Order'>$item_02</a></td></tr>	
						<tr valign=top><td class="ne_td1">$label_15 : </td><td class="ne_td2" >$item_15</td></tr>
						<tr valign=top><td class="ne_td1">$label_16 : </td><td class="ne_td2" >$item_16</td></tr>
						<tr valign=top><td class="ne_td1">$label_03 : </td><td class="ne_td2" >$item_03</td></tr>
						<tr valign=top><td class="ne_td1">$label_04 : </td><td class="ne_td2" >$item_04</td></tr>
						<tr valign=top><td class="ne_td1">$label_05 : </td><td class="ne_td2" >$item_05</td></tr>
						<tr valign=top><td class="ne_td1">$label_06 : </td><td class="ne_td2" >$item_06</td></tr>	
					</table>
				</td>
			</tr>
		</table>
		

_HTML_;
	return $HTML;
}

function get_section2($item,$labels)
{
	$label_01 = $labels['quotation_date'];	$item_01 = $item->quotation_date;
	$label_02 = $labels['invoice_date'];	$item_02 = $item->invoice_date;
	$label_03 = $labels['order_status'];	$item_03 = $item->order_status;
	$label_04 = $labels['inventory_checkout_status'];	$item_04 = $item->inventory_checkout_status;
	$label_05 = $labels['inventory_update_type'];	$item_05 = $item->inventory_update_type;
	$label_06 = $labels['order_total'];		$item_06 = "$ ".number_format($item->order_total, 2, '.', ',');
	$label_07 = $labels['payment_total'];	$item_07 = "$ ".number_format($item->payment_total, 2, '.', ',');
	$label_08 = $labels['balance'];	$item_08 = "$ ".number_format($item->balance, 2, '.', ',');

	$HTML=<<<_HTML_
	<table width="100%">
			<tr valign=top>
				<td width="50%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td class="ne_td1" width="30%">$label_01 : </th><td class="ne_td2" >$item_01</td></tr>
						<tr valign=top><td class="ne_td1">$label_03 : </td><td class="ne_td2">$item_03</td></tr>
						<tr valign=top><td class="ne_td1">$label_04 : </td><td class="ne_td2">$item_04</td></tr>
						<tr valign=top><td class="ne_td1">$label_05 : </td><td class="ne_td2">$item_05</td></tr>
					</table>
				</td>
				<td width="50%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td class="ne_td1" width="35%">$label_02 : </th><td class="ne_td2" >$item_02</td></tr>
						<tr valign=top><td class="ne_td1">$label_06 : </td><td class="ne_td2">$item_06</td></tr>	
						<tr valign=top><td class="ne_td1">$label_07 : </td><td class="ne_td2">$item_07</td></tr>
						<tr valign=top><td class="ne_td1">$label_08 : </td><td class="ne_td2">$item_08</td></tr>
					</table>
				</td>
			</tr>
		</table>
_HTML_;
	return $HTML;
}

function order_details_subform($item)
{
	$order = new Controller_Core_Sales_Order();
	$subcontroller = $order->subform['order_details']['subformcontroller'];
	$idfield = $order->param['indexfield'];
	$idval =  $item->order_id;
	$current_no = $item->current_no;
	$results = $order->param['primarymodel']->get_subForm_view_records($subcontroller,$idfield,$idval,$current_no,false,$labels);
	$HTML  = subform_html($results,$labels);
	$HTML .= '<table class="viewtext" width="72%">';
	$HTML .= sprintf('<tr><td style="text-align:left; padding 0px 5px 0px 0px; color:black;"><b>Sub Total :</b> %s </td>',"$ ".number_format($item->extended_total, 2, '.', ','));
	$HTML .= sprintf('<td style="text-align:left; padding 0px 5px 0px 0px; color:black;"><b>Discount Total :</b> %s </td>',"$ ".number_format($item->discount_total, 2, '.', ','));
	$HTML .= sprintf('<td style="text-align:left; padding 0px 5px 0px 0px; color:black;"><b>Tax Total :</b> %s </td>',"$ ".number_format($item->tax_total, 2, '.', ''));
	$HTML .= sprintf('<td  style="text-align:left; padding 0px 5px 0px 0px; color:black;"><b>GRAND TOTAL :</b> %s </b></td></tr>',"$ ".number_format($item->order_total, 2, '.', ','));
	$HTML .= '</table>';
	return $HTML;
}
	
function  subform_html($results,$labels)
{
	$HTML = '<table id="ne_tlist" width="100%">'."\n";
	$TABLEHEADER = ""; $TABLEROWS ="";
	foreach($labels as $key => $val)
	{
		if(!($key == "id" || $key == "order_id"))
		{
			$TABLEHEADER .= sprintf("<th>%s</th>",$val);
		}
	}
	$TABLEHEADER = "<tr valign='top'>".$TABLEHEADER."</tr>"."\n";

	foreach($results as $index => $row)
	{
		$TABLEROWS .= "<tr>";
		$obj = (array) $row;
		foreach($obj as $key => $val)
		{
			if(!($key == "id" || $key == "order_id"))
			{
				$TABLEROWS .= sprintf('<td valign="top" style="color:black;">%s</td>',$val);
			}
		}
		$TABLEROWS .= "</tr>";
	}

	$HTML .= $TABLEHEADER.$TABLEROWS."\n"."</table>"."\n";
	return $HTML;
}

function payments($item,$param)
{
	$baseurl = URL::base();
	$payment_param_id = $param['payment_param_id'];
	
	$payment = new Controller_Core_Sales_Payment();
	$order_id =  $item->order_id;
	$TABLEHEADER = ""; $TABLEROWS ="";
	$querystr = sprintf('select payment_id,branch_id,till_id,amount,payment_type,payment_date,ref_no,order_id,id from %s where order_id = "%s" and payment_status ="VALID"',$payment->param['tb_live'],$order_id);
	$results = $payment->param['primarymodel']->execute_select_query($querystr);
	$HTML = sprintf('<a href="%sindex.php/%s" target="enquiry" title="New Payment">New Payment</a><br><p id="ne_spacer3"></p>',$baseurl,$payment_param_id);
	$HTML .= '<table id="ne_tlist" width="100%">'."\n";
	$TABLEHEADER = '<tr valign="top"><th>Payment Id</th><th>Branch Id</th><th>Till Id</th><th>Amount</th><th>Payment Type</th><th>Payment Date</th><th>Ref No</th><th>Order Id</th><th>Id</th></tr>'."\n";
	foreach($results as $index => $row)
	{
		$TABLEROWS .= "<tr>";
		$obj = (array) $row;
		foreach($obj as $key => $val)
		{
			if($key == "payment_id")
			{
				$TABLEROWS.= sprintf('<td valign="top"><a href="%sindex.php/%s/index/%s" target="enquiry" title="Edit Payment">%s</a></td>',$baseurl,$payment_param_id,$val,$val);
			}
			else
			{
				$TABLEROWS.= sprintf('<td valign="top" style="color:black;">%s</td>',$val);
			}
		}
		$TABLEROWS .= "</tr>";
	}

	$HTML .= $TABLEHEADER.$TABLEROWS."\n"."</table>"."\n";
	$HTML .= '<table class="viewtext" width="40%">';
	$HTML .= sprintf('<tr><td style="text-align:left; padding 0px 5px 0px 0px; color:black;"><b>Payment Total :</b> %s </td>',"$ ".number_format($item->payment_total, 2, '.', ','));
	$HTML .= sprintf('<td style="text-align:left; padding 0px 5px 0px 0px; color:black;"><b>Balance :</b> %s </td></tr>',"$ ".number_format($item->balance, 2, '.', ','));
	$HTML .= '</table>';
	return $HTML;
}

function inventory_checkout_status($item,$param)
{
	$baseurl = URL::base();
	$inventchkout_param_id = $param['inventchkout_param_id'];
	
	$invchk = new Controller_Core_Sales_Inventchkout();
	$order_id =  $item->order_id;
	$TABLEHEADER = ""; $TABLEROWS =""; 
	$querystr = sprintf('select checkout_details from %s where order_id = "%s"',$invchk->param['tb_live'],$order_id);
	$results = $invchk->param['primarymodel']->execute_select_query($querystr);
		
	$subopt  = $invchk->param['primarymodel']->get_form_subtable_options("inventchkout","checkout_details");
	$HTML = sprintf('Inventory Checkout Id : <a href="%sindex.php/%s/index/%s" target="enquiry" title="Edit Inventory Checkout">%s</a><br><p id="ne_spacer3"></p>',$baseurl,$inventchkout_param_id,$order_id,$order_id);
	if($results)
	{
		$HTML .= view_xml_table($results[0]->checkout_details,$subopt);	
	}
	return $HTML;
}

function delivery_note($item,$param)
{
	$baseurl = URL::base(); $indexpage = "index.php";
	$deliverynote_param_id = $param['deliverynote_param_id'];
	
	$dnote = new Controller_Core_Sales_Deliverynote();
	$order_id =  $item->order_id;
	$TABLEHEADER = ""; $TABLEROWS =""; $HTML = "";
	$querystr = sprintf('select id,deliverynote_id,deliverynote_date,details,status,delivered_by,delivery_date,returned_signed_by,returned_signed_date,comments from %s where order_id = "%s"',$dnote->param['tb_live'],$order_id);
	$results = $dnote->param['primarymodel']->execute_select_query($querystr);
	$subopt  = $dnote->param['primarymodel']->get_form_subtable_options("deliverynote","details");
	if($results)
	{
		foreach($results as $key => $row)
		{
		
		$item_01 = $row->id;
		$item_02 = $row->deliverynote_id;
		$item_03 = $row->deliverynote_date;
		$item_04 = $row->details;
		$item_05 = $row->status;
		$item_06 = $row->delivered_by;
		$item_07 = $row->delivery_date;
		$item_08 = $row->returned_signed_by;
		$item_09 = $row->returned_signed_date;
		$item_10 = $row->comments;
	
	$HTML1=<<<_HTML_
	<table width="100%">
			<tr valign=top>
				<td width="50%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td  class="ne_td1" width="30%">Delivery Note Id: </td>
						<td class="ne_td2"><a href="$baseurl$indexpage/$deliverynote_param_id/index/$item_02" target="enquiry" title="Edit Delivery Note">$item_02</a> [ $item_01 ]</td></tr>
						<tr valign=top><td class="ne_td1">Delivery Date: </td><td class="ne_td2">$item_03</td></tr>
						<tr valign=top><td class="ne_td1">Status : </td><td class="ne_td2">$item_05</td></tr>
						<tr valign=top><td class="ne_td1">Comments : </td><td class="ne_td2">$item_10</td></tr>
					</table>
				</td>
				<td width="50%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td class="ne_td1" width="35%">Delivered By : </td><td class="ne_td2">$item_06</td></tr>
						<tr valign=top><td class="ne_td1">Delivered Date : </td><td class="ne_td2">$item_07</td></tr>	
						<tr valign=top><td class="ne_td1">Signed By : </td><td class="ne_td2">$item_08</td></tr>
						<tr valign=top><td class="ne_td1">Signed Date : </td><td class="ne_td2">$item_09</td></tr>
					</table>
				</td>
			</tr>
	</table>
_HTML_;
		$HTML2 = view_xml_table($row->details,$subopt);	
		$HTML .= $HTML1.$HTML2."<p></p>";
		}
	}
	return $HTML;
}

function view_xml_table($xml,&$subopt)
{
	$HTML = '<table id="ne_tlist" width="100%">'."\n";
	$TABLEHEADER = ""; $TABLEROWS ="";

	foreach($subopt as $subkey => $row)
	{
		$sublabel = $row['sublabel'];
		$TABLEHEADER .= sprintf("<th>%s</th>",$sublabel);
	}
	$TABLEHEADER = "<tr valign='top'>".$TABLEHEADER."</tr>"."\n";
	
	$formfields = new SimpleXMLElement($xml);
	foreach($formfields->rows->row as $row)
	{
		$TABLEROWS .= "<tr>";
		foreach ($row->children() as $field)
		{
			$subkey = sprintf('%s',$field->getName() );
			$val	= sprintf('%s',$row->$subkey);
			$TABLEROWS .= sprintf("<td valign='top' style='color:%s;'>%s</td>","black",$val);
		}
		$TABLEROWS .= "</tr>";
	}

	$HTML .= $TABLEHEADER.$TABLEROWS."\n"."</table>"."\n";
	return $HTML;
}

function make_pdf_xml($item,$labels)
{
	$id = $item->id;						$order_id = $item->order_id;
	$branch_id = $item->branch_id;			$inputter = $item->inputter;
	$is_co = $item->is_co;					$cc_id = $item->cc_id;
	$first_name = $item->first_name;		$last_name = $item->last_name;
	$customer_type = $item->customer_type;	$city = $item->city;
	$address1 = $item->address1;			$address2 = $item->address2;
	$phone_mobile1 = $item->phone_mobile1;	$phone_home = $item->phone_home;		
	$phone_work = $item->phone_work;		$current_no = $item->current_no;
	$invoice_date = $item->invoice_date;	$quotation_date = $item->quotation_date;
	$order_total = $item->order_total;		$payment_total = $item->payment_total; 
	$balance = $item->balance;				$sub_total = $item->extended_total;
	$tax_total = $item->tax_total;			$discount_total = $item->discount_total;
	$invoice_note = $item->invoice_note;

	$label_id = $labels['id'];							$label_order_id = $labels['order_id'];
	$label_branch_id = $labels['branch_id'];			$label_inputter = $labels['inputter'];
	$label_is_co = $labels['is_co'];					$label_cc_id = $labels['cc_id'];
	$label_first_name = $labels['first_name'];			$label_last_name = $labels['last_name'];
	$label_customer_type = $labels['customer_type'];	$label_city = $labels['city'];
	$label_address1 = $labels['address1'];				$label_address2 = $labels['address2'];
	$label_phone_mobile1 = $labels['phone_mobile1'];	$label_phone_home = $labels['phone_home'];		
	$label_phone_work = $labels['phone_work'];			$label_current_no = $labels['current_no'];
	$label_invoice_date = $labels['invoice_date'];		$label_quotation_date = $labels['quotation_date'];
	$label_order_total = $labels['order_total'];		$label_payment_total = $labels['payment_total']; 
	$label_balance = $labels['balance'];				$label_sub_total = $labels['extended_total'];
	$label_tax_total = $labels['tax_total'];			$label_discount_total = $labels['discount_total'];
	$label_invoice_note = $labels['invoice_note'];
	
	$XML=<<<_XML_
<fields>
	<id><label>$label_id</label><value>$id</value></id>	
	<order_id><label>$label_order_id</label><value>$order_id</value></order_id>
	<branch_id><label>$label_branch_id</label><value>$branch_id</value></branch_id>
	<inputter><label>$label_inputter</label><value>$inputter</value></inputter>
	<is_co><label>$label_is_co</label><value>$is_co</value></is_co>
	<cc_id><label>$label_cc_id</label><value>$cc_id</value></cc_id>
	<first_name><label>$label_first_name</label><value>$first_name</value></first_name>
	<last_name><label>$label_last_name</label><value>$last_name</value></last_name>
	<customer_type><label>$label_customer_type</label><value>$customer_type</value></customer_type>
	<address1><label>$label_address1</label><value>$address1</value></address1>
	<address2><label>$label_address2</label><value>$address2</value></address2>
	<city><label>$label_city</label><value>$city</value></city>
	<phone_mobile1><label>$label_phone_mobile1</label><value>$phone_mobile1</value></phone_mobile1>
	<phone_home><label>$label_phone_home</label><value>$phone_home</value></phone_home>
	<phone_work><label>$label_phone_work</label><value>$phone_work</value></phone_work>
	<quotation_date><label>$label_quotation_date</label><value>$quotation_date</value></quotation_date>
	<invoice_date><label>$label_invoice_date</label><value>$invoice_date</value></invoice_date>
	<current_no><label>$label_current_no</label><value>$current_no</value></current_no>
	<sub_total><label>$label_sub_total</label><value>$sub_total</value></sub_total>
	<discount_total><label>$label_discount_total</label><value>$discount_total</value></discount_total>
	<tax_total><label>$label_tax_total</label><value>$tax_total</value></tax_total>
	<order_total><label>$label_order_total</label><value>$order_total</value></order_total>
	<payment_total><label>$label_payment_total</label><value>$payment_total</value></payment_total>
	<balance><label>$label_balance</label><value>$balance</value></balance>
	<invoice_note><label>$label_invoice_note</label><value>$invoice_note</value></invoice_note>
</fields>
_XML_;
	return $XML;
}

function print_to_screen($enquiryrecords,$pagination,$labels,$config,$param)
{
	$section1 = ""; $section2 = ""; $section3 = ""; 
	$section4 = ""; $section5 = ""; $section6 = "";
	foreach ($enquiryrecords as $item )
	{
		$section1 = get_section1($item,$labels,$param);
		$section2 = get_section2($item,$labels);
		$section3 = order_details_subform($item);
		$section4 = payments($item,$param);
		$section5 = inventory_checkout_status($item,$param);
		$section6 = delivery_note($item,$param);
		
		$num = rand(0,999999);
		$num = str_pad($num, 6, "0", STR_PAD_LEFT);
		$invoice_id		= 'INV'.date("YmdHis").$num;
		$quotation_id	= 'QTE'.date("YmdHis").$num;
		$refreshurl		= sprintf('<a href="%s" title="Refresh Page"><img src="%s" align="middle";></a>',$config['refresh_url'],$config['refresh_icon']);
		$totalrecords	= sprintf('<b>Total :</b> %s ',$config['total_items']);
		$pager			= sprintf('%s',$pagination);
		$pdfurl			= ""; 
		
		$non_credit_status = array('INVOICE.FULL.PAID','ZERO.CHARGE');
		$credit_status = array('INVOICE.ISSUED','INVOICE.PART.PAID','INVOICE.FULL.PAID','ZERO.CHARGE');
		
		if($config['printable'])
		{
			$pdfurl = sprintf('[ <a href=%sindex.php/%s/index/%s target=_blank><b>Quotation</b></a> ] ',URL::base(),$param['pdfbuilder'],$quotation_id)."\n";
			if( ($item->is_co == "N" && in_array($item->order_status, $non_credit_status)) ||
				($item->is_co == "Y" && in_array($item->order_status, $credit_status)) )
			{
				$pdfurl .= sprintf(' [ <a href=%sindex.php/%s/index/%s target=_blank><b>Invoice</b></a> ] ',URL::base(),$param['pdfbuilder'],$invoice_id)."\n";
			}
		}
$ENQNAV = <<<_HTML_
	<div id="ne_nav">
		<table width="100%">
			<td>$refreshurl</td>
			<td width="30%">$pdfurl</td>
			<td width="10%">$totalrecords</td>
			<td>$pager</td>
		</table>
	</div>
_HTML_;

$ENQBODY = <<<_HTML_
	<div id="ne_shd">Order Information</div>
	<div id="ne_sct">$section1</div><br>
	<div id="ne_shd">Order Status & Details</div>
	<div id="ne_sct">$section2</div><p id="ne_spacer3"></p>
	<div id="ne_scb">$section3</div><p id="ne_spacer3"></p>
	<div id="ne_scb">$section4</div><br>
	<div id="ne_shd">Inventory Checkout Details</div>
	<div id="ne_scb">$section5</div><br>
	<div id="ne_shd">Delivery Notes</div>
	<div id="ne_scb">$section6</div>
_HTML_;
		print $ENQNAV.$ENQBODY;

		//add xml data to pdfs_is table
		$pdf_xml	= make_pdf_xml($item,$labels);
		$pdf_audit	= "<audit><printuser></printuser><printdate></printdate></audit>";
		if($config['printuser'] || $config['printdatetime'] )
		{
			$pdf_audit = "<audit>"; 
			if($config['printuser']) {$pdf_audit .= sprintf('<printuser>Printed By : %s</printuser>',$config['idname']);} 
			if($config['printdatetime']) {$pdf_audit .= sprintf('<printdate>Print Date : %s</printdate>',date('Y-m-d H:i:s'));} 
			$pdf_audit .= "</audit>"."\n"; 
		}
		$pdf_data = "<?xml version='1.0' standalone='yes'?>"."\n"."<formfields>"."\n";
		$pdf_data .= $pdf_xml."\n".$pdf_audit;
		$pdf_data .= "</formfields>"."\n";
		$pdf_data = str_replace("&","&amp;",$pdf_data); 

		$pdf = new Controller_Core_Sysadmin_Pdf();
		$arr['pdf_id']			= $invoice_id;
		$arr['pdf_template']	= "INVOICE";
		$arr['controller']		= $config['controller'];
		$arr['type']			= $config['type'];
		$arr['data']			= $pdf_data;
		$arr['datatype']		= "xml";
		$arr['idname']			= $config['idname'];
		
		if( $pdf->delete_from_pdf_table($arr) )
		{
			//wait for deletions
		}
		
		$pdf->insert_into_pdf_table_no_delete($arr);
		$arr['pdf_id']			= $quotation_id;
		$arr['pdf_template']	= "QUOTATION";
		$pdf->insert_into_pdf_table_no_delete($arr);
	}
}
?>

