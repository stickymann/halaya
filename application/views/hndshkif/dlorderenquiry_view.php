<?php
$params = array();
$sitedb = new Model_SiteDB;

//$arr = $sitedb->get_controller_params("customermapping");
//$param['customer_param_id'] = $arr['param_id'];

$arr = $sitedb->get_controller_params("dlorder");
$param['order_param_id'] = $arr['param_id'];

//$arr = $sitedb->get_controller_params("inventorymapping");
//$param['inventory_param_id'] = $arr['param_id'];

$param['pdfbuilder'] = "hndshkif_order_picklistpdfbuilder";
$param['dlorderlastreport'] = "hndshkif_orders_dlorderlastreport";

print_to_screen($enquiryrecords,$pagination,$labels,$config,$param);

function get_section1($item,$labels,$param)
{
	$baseurl = URL::base()."index.php";
	$dlorder = $param['order_param_id'];
	$batch_report = $param['dlorderlastreport'];
	
	//$customer_param_id = $param['customer_param_id'];
	//$order_param_id = $param['order_param_id'];

	$label_01 = $labels['id'];				$item_01 = $item->id;
	$label_02 = $labels['batch_id'];		$item_02 = $item->batch_id;
	$label_03 = $labels['customer_id'];		$item_03 = $item->customer_id;
	$label_04 = $labels['tax_id'];			$item_04 = $item->tax_id;
	$label_05 = $labels['input_date'];		$item_05 = $item->cdate;
	$label_06 = $labels['cdate'];			$item_06 = $item->cdate;
	$label_07 = $labels['ctime'];			$item_07 = $item->ctime;
	
	
	$label_08 = $labels['name'];			$item_08 = $item->name;
	$label_09 = $labels['contact'];			$item_09 = $item->contact;
	$label_10 = $labels['street'];			$item_10 = $item->street;
	$label_11 = $labels['city'];			$item_11 = $item->city;
	$label_12 = $labels['country'];			$item_12 = $item->country;
	$label_13 = $labels['phone'];			$item_13 = $item->phone;
	$label_14 = $labels['paymentterms'];	$item_14 = $item->paymentterms;
	 	
/*
<tr valign=top><td class="ne_td1" width="30%">Customer Name : </td>
<td class="ne_td2" ><a href="$baseurl$customer_param_id/index/$item_07" target="enquiry" title="Edit Customer">$fullname</a> [ $item_07 ]</td></tr> 
<tr valign=top><td class="ne_td1">$label_02 : </td>
<td class="ne_td2" ><a href='$baseurl$order_param_id/index/$item_02' target='enquiry' title='Edit Order'>$item_02</a></td></tr>	
 */
//url = siteutils.getBaseURL() + "index.php/hndshkif_orders_dlorderlastreport?batch_id=" + batch_id;
 
	$HTML=<<<_HTML_
	<table  width="100%">
			<tr valign=top>
				<td width="40%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td class="ne_td1" width="30%">$label_01 : </td>
						<td class="ne_td2" ><a href="$baseurl/$batch_report/$item_01" target="enquiry" title="View Batch Report">$item_02</a></td></tr>
												
						<tr valign=top><td class="ne_td1">$label_02 : </td>
						<td class="ne_td2" ><a href="$baseurl/$batch_report?batch_id=$item_02" target="enquiry" title="View Batch Report">$item_02</a></td></tr>
						
						<tr valign=top><td class="ne_td1">$label_03 : </td><td class="ne_td2" >$item_03</td></tr>
						<tr valign=top><td class="ne_td1">$label_04 : </td><td class="ne_td2" >$item_04</td></tr>
						<tr valign=top><td class="ne_td1">$label_05 : </td><td class="ne_td2" >$item_05</td></tr>
						<tr valign=top><td class="ne_td1">$label_06 : </td><td class="ne_td2" >$item_06</td></tr>	
						<tr valign=top><td class="ne_td1">$label_07 : </td><td class="ne_td2" >$item_07</td></tr>
					</table>
				</td>
				<td width="60%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td class="ne_td1" width="30%">$label_08 : </td><td class="ne_td2" >$item_08</td></tr>
						<tr valign=top><td class="ne_td1">$label_09 : </td><td class="ne_td2" >$item_09</td></tr>
						<tr valign=top><td class="ne_td1">$label_10 : </td><td class="ne_td2" >$item_10</td></tr>
						<tr valign=top><td class="ne_td1">$label_11 : </td><td class="ne_td2" >$item_11</td></tr>
						<tr valign=top><td class="ne_td1">$label_12 : </td><td class="ne_td2" >$item_12</td></tr>
						<tr valign=top><td class="ne_td1">$label_13 : </td><td class="ne_td2" >$item_13</td></tr>	
						<tr valign=top><td class="ne_td1">$label_14 : </td><td class="ne_td2" >$item_14</td></tr>	
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
	//$HTML  = subform_html($results,$labels);
	/*
	$HTML .= '<table class="viewtext" width="72%">';
	$HTML .= sprintf('<tr><td style="text-align:left; padding 0px 5px 0px 0px; color:black;"><b>Sub Total :</b> %s </td>',"$ ".number_format($item->extended_total, 2, '.', ','));
	$HTML .= sprintf('<td style="text-align:left; padding 0px 5px 0px 0px; color:black;"><b>Discount Total :</b> %s </td>',"$ ".number_format($item->discount_total, 2, '.', ','));
	$HTML .= sprintf('<td style="text-align:left; padding 0px 5px 0px 0px; color:black;"><b>Tax Total :</b> %s </td>',"$ ".number_format($item->tax_total, 2, '.', ''));
	$HTML .= sprintf('<td  style="text-align:left; padding 0px 5px 0px 0px; color:black;"><b>GRAND TOTAL :</b> %s </b></td></tr>',"$ ".number_format($item->order_total, 2, '.', ','));
	$HTML .= '</table>';
	*/
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

function print_to_screen($enquiryrecords,$pagination,$labels,$config,$param)
{
	$section1 = ""; $section2 = ""; $section3 = ""; 
	$section4 = ""; $section5 = ""; $section6 = "";
	foreach ($enquiryrecords as $item )
	{
		$section1 = get_section1($item,$labels,$param);
		//$section3 = order_details_subform($item);
	
		$refreshurl		= sprintf('<a href="%s" title="Refresh Page"><img src="%s" align="middle";></a>',$config['refresh_url'],$config['refresh_icon']);
		$totalrecords	= sprintf('<b>Total :</b> %s ',$config['total_items']);
		$pager			= sprintf('%s',$pagination);
		$pdfurl			= ""; 
		/*
		if($config['printable'])
		{
			$pdfurl = sprintf('[ <a href=%sindex.php/%s/index/%s target=_blank><b>Quotation</b></a> ] ',URL::base(),$param['pdfbuilder'],$quotation_id)."\n";
			$pdfurl .= sprintf(' [ <a href=%sindex.php/%s/index/%s target=_blank><b>Invoice</b></a> ] ',URL::base(),$param['pdfbuilder'],$invoice_id)."\n";
		}*/
		
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
	<div id="ne_shd">Order Details</div>
	<div id="ne_sct"></div><p id="ne_spacer3"></p>
_HTML_;
		print $ENQNAV.$ENQBODY;
	}
}
?>

