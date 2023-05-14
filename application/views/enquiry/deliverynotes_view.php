<?php
$params = array();
$sitedb = new Model_SiteDB;

$arr = $sitedb->get_controller_params("customer");
$param['customer_param_id'] = $arr['param_id'];

$arr = $sitedb->get_controller_params("order");
$param['order_param_id'] = $arr['param_id'];

$arr = $sitedb->get_controller_params("deliverynote");
$param['deliverynote_param_id'] = $arr['param_id'];
$param['pdfbuilder'] = "core_pdfbuilder";

print_to_screen($enquiryrecords,$pagination,$labels,$config,$param);

function deliverynote_information($item,$labels,$param)
{
	$baseurl = URL::base()."index.php/";
	$deliverynote_param_id = $param['deliverynote_param_id'];
	
	$label_01 = $labels['id'];					$item_01 = $item->id;
	$label_02 = $labels['deliverynote_id'];		$item_02 = $item->deliverynote_id;
	$label_03 = $labels['deliverynote_date'];	$item_03 = $item->deliverynote_date;
	$label_04 = $labels['status'];				$item_04 = $item->status;
	$label_05 = $labels['delivered_by'];		$item_05 = $item->delivered_by;
	$label_06 = $labels['delivery_date'];		$item_06 = $item->delivery_date;
	$label_07 = $labels['returned_signed_by'];	$item_07 = $item->returned_signed_by;
	$label_08 = $labels['returned_signed_date'];$item_08 = $item->returned_signed_date;
	$label_09 = $labels['comments'];			$item_09 = $item->comments;
	$label_10 = $labels['inventory_checkout_status'];	$item_10 = $item->inventory_checkout_status;


	$HTML=<<<_HTML_
	<table width="100%">
			<tr valign=top>
				<td width="50%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td class="ne_td1" width="30%">Delivery Note Id: </td>
						<td class="ne_td2"><a href="$baseurl$deliverynote_param_id/index/$item_02" target="enquiry" title="Edit Delivery Note">$item_02</a> [ $item_01 ]</td></tr>
						<tr valign=top><td class="ne_td1">Delivery Note Date: </td><td class="ne_td2">$item_03</td></tr>
						<tr valign=top><td class="ne_td1">Status : </td><td class="ne_td2">$item_04</td></tr>
						<tr valign=top><td class="ne_td1">Checkout Status : </td><td class="ne_td2">$item_10</td></tr>
					</table>
				</td>
				<td width="50%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td  class="ne_td1" width="35%">Delivered By : </td><td class="ne_td2">$item_05</td></tr>
						<tr valign=top><td class="ne_td1">Delivered Date : </td><td class="ne_td2">$item_06</td></tr>	
						<tr valign=top><td class="ne_td1">Signed By : </td><td class="ne_td2">$item_07</td></tr>
						<tr valign=top><td class="ne_td1">Signed Date : </td><td class="ne_td2">$item_08</td></tr>
						<tr valign=top><td class="ne_td1">Comments : </td><td class="ne_td2">$item_09</td></tr>
					</table>
				</td>
			</tr>
	</table>
_HTML_;
	return $HTML;
}

function order_information($item,$labels,$param)
{
	$baseurl = URL::base()."index.php/";
	$customer_param_id = $param['customer_param_id'];
	$order_param_id = $param['order_param_id'];
	
	$label_01 = $labels['invoice_id'];		$item_01 = $item->invoice_id;
	$label_02 = $labels['order_id'];		$item_02 = $item->order_id;
	$label_03 = $labels['branch_id'];		$item_03 = $item->branch_id;
	$label_04 = $labels['inputter'];		$item_04 = $item->inputter;
	$label_05 = $labels['order_date'];		$item_05 = $item->order_date;
	$label_06 = $labels['input_date'];		$item_06 = $item->input_date;
	$label_07 = $labels['customer_id'];		$item_07 = $item->customer_id;
	$label_08 = $labels['phone_mobile1'];	$item_08 = $item->phone_mobile1;
	$label_09 = $labels['phone_home'];		$item_09 = $item->phone_home;
	$label_10 = $labels['phone_work'];		$item_10 = $item->phone_work;
	$label_15 = $labels['is_co'];			$item_15 = $item->is_co;
	$label_16 = $labels['cc_id'];			$item_16 = $item->cc_id;

	if($item->customer_type == 'COMPANY'){$fullname = $item->last_name;}else{$fullname = $item->first_name.' '.$item->last_name;}
	$address = $item->address1.', '.$item->address2.'<br>'.$item->city;
	$HTML=<<<_HTML_
	<table  width="100%">
			<tr valign=top>
				<td width="50%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td class="ne_td1">$label_02 : </td>
						<td class="ne_td2"><a href='$baseurl$order_param_id/index/$item_02' target='enquiry' title='Edit Order'>$item_02</a> [ $item_01 ]</td></tr>	
						<tr valign=top><td class="ne_td1" width="30%">Customer Name : </td>
						<td class="ne_td2"><a href="$baseurl$customer_param_id/index/$item_07" target="enquiry" title="Edit Customer">$fullname</a> [ $item_07 ]</td></tr>
						<tr valign=top><td class="ne_td1">Address : </td><td class="ne_td2">$address</td></tr>
						<tr valign=top><td class="ne_td1">$label_08 : </td><td class="ne_td2">$item_08</td></tr>
						<tr valign=top><td class="ne_td1">$label_09 : </td><td class="ne_td2">$item_09</td></tr>
						<tr valign=top><td class="ne_td1">$label_10 : </td><td class="ne_td2">$item_10</td></tr>
					</table>
				</td>
				<td width="50%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td class="ne_td1"  width="35%">$label_15 : </td><td class="ne_td2" >$item_15</td></tr>
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

function deliverynote_items($item)
{
	$dnote = new Controller_Core_Sales_Deliverynote();
	$deliverynote_id =  $item->deliverynote_id;
	$TABLEHEADER = ""; $TABLEROWS =""; $HTML = "";
	$querystr = sprintf('select details from %s where deliverynote_id = "%s"',$dnote->param['tb_live'],$deliverynote_id);
	$results = $dnote->param['primarymodel']->execute_select_query($querystr);
	$subopt  = $dnote->param['primarymodel']->get_form_subtable_options("deliverynote","details");
	$HTML = view_xml_table($results[0]->details,$subopt);	
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
	$id = $item->id;										$label_id = $labels['id'];
	$deliverynote_id = $item->deliverynote_id;				$label_deliverynote_id = $labels['deliverynote_id'];
	$deliverynote_date = $item->deliverynote_date;			$label_deliverynote_date = $labels['deliverynote_date'];						
	$status = $item->status;								$label_status = $labels['status'];
	$delivered_by = $item->delivered_by;					$label_delivered_by = $labels['delivered_by'];
	$delivery_date = $item->delivery_date;					$label_delivery_date = $labels['delivery_date'];
	$invoice_id = $item->invoice_id;						$label_invoice_id = $labels['invoice_id'];
	$order_id = $item->order_id;							$label_order_id = $labels['order_id'];	
	$branch_id = $item->branch_id;							$label_branch_id = $labels['branch_id'];
	$inputter = $item->inputter;							$label_inputter = $labels['inputter'];
	$is_co = $item->is_co;									$label_is_co = $labels['is_co'];	
	$cc_id = $item->cc_id;									$label_cc_id = $labels['cc_id'];
	$first_name = $item->first_name;						$label_first_name = $labels['first_name'];
	$last_name = $item->last_name;							$label_last_name = $labels['last_name'];
	$customer_type = $item->customer_type;					$label_customer_type = $labels['customer_type'];	
	$address1 = $item->address1;							$label_address1 = $labels['address1'];
	$address2 = $item->address2;							$label_address2 = $labels['address2'];
	$city = $item->city;									$label_city = $labels['city'];
	$phone_mobile1 = $item->phone_mobile1;					$label_phone_mobile1 = $labels['phone_mobile1'];	
	$phone_home = $item->phone_home;						$label_phone_home = $labels['phone_home'];
	$phone_work = $item->phone_work;						$label_phone_work = $labels['phone_work'];
	$inventory_checkout_status = $item->inventory_checkout_status;	$label_inventory_checkout_status = $labels['inventory_checkout_status'];
	$comments = $item->comments;							$label_comments = $labels['comments'];	

	$XML=<<<_XML_
<fields>
	<id><label>$label_id</label><value>$id</value></id>	
	<deliverynote_id><label>$label_deliverynote_id</label><value>$deliverynote_id</value></deliverynote_id>	
	<deliverynote_date><label>$label_deliverynote_date</label><value>$deliverynote_date</value></deliverynote_date>	
	<status><label>$label_status</label><value>$status</value></status>	
	<delivered_by><label>$label_delivered_by</label><value>$delivered_by</value></delivered_by>	
	<delivery_date><label>$label_delivery_date</label><value>$delivery_date</value></delivery_date>	
	<invoice_id><label>$label_invoice_id</label><value>$invoice_id</value></invoice_id>
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
	<inventory_checkout_status><label>$label_inventory_checkout_status</label><value>$inventory_checkout_status</value></inventory_checkout_status>
	<comments><label>$label_comments</label><value>$comments</value></comments>
</fields>
_XML_;
	return $XML;
}

function print_to_screen($enquiryrecords,$pagination,$labels,$config,$param)
{
	$deliverynote_information = ""; $order_information = ""; $deliverynote_items = "";
	foreach ($enquiryrecords as $item )
	{
		$deliverynote_information = deliverynote_information($item,$labels,$param);
		$order_information = order_information($item,$labels,$param);
		$deliverynote_items = deliverynote_items($item);
		
		$num = rand(0,999999);
		$num = str_pad($num, 6, "0", STR_PAD_LEFT);
		$deliverynote_id = 'PDF'.date("YmdHis").$num;
		$refreshurl		= sprintf('<a href="%s" title="Refresh Page"><img src="%s" align="middle";></a>',$config['refresh_url'],$config['refresh_icon']);
		$totalrecords	= sprintf('<b>Total :</b> %s ',$config['total_items']);
		$pager			= sprintf('%s',$pagination);
		$pdfurl			= ""; 
		if($config['printable'])
		{
			$pdfurl = sprintf('[ <a href=%s/%s/index/%s target=_blank><b>Delivery Note</b></a> ] ',URL::base()."index.php/",$param['pdfbuilder'],$deliverynote_id)."\n";
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
	<div id="ne_shd">Delivery Note Information</div>
	<div id="ne_scb">$deliverynote_information</div><br>
	<div id="ne_shd">Order Information</div>
	<div id="ne_sct">$order_information</div><br>
	<div id="ne_shd">Delivery Note Items</div>
	<div id="ne_scb">$deliverynote_items</div>	
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
		$pdf_data = str_replace('&','and',$pdf_data); 

		$pdf = new Controller_Core_Sysadmin_Pdf();
		$arr['pdf_id']			= $deliverynote_id;
		$arr['pdf_template']	= "DELIVERYNOTE";
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

	}
}
?>

