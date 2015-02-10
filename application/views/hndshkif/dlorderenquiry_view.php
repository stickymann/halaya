<?php
/**
 * Handshake orders enquiry view. 
 *
 * $Id: dlorderenquiry_view.php 2014-09-07 02:00:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */

$param = array();
$param['model'] = new Model_SiteDB;

//$arr = $sitedb->get_controller_params("customermapping");
//$param['customer_param_id'] = $arr['param_id'];

$arr = $param['model']->get_controller_params("dlorder");
$param['order_param_id'] = $arr['param_id'];

$arr = $param['model']->get_controller_params("inventorymapping");
$param['inventory_param_id'] = $arr['param_id'];

$param['pdfbuilder'] = "hndshkif_order_picklistpdfbuilder";
$param['dlorderlastreport'] = "hndshkif_orders_dlorderlastreport";

print_to_screen($enquiryrecords,$pagination,$labels,$config,$param);

function get_section1($item,$labels,$param)
{
	require_once('media/hsi/hsiconfig.php');
	$cfg = new HSIConfig();
	$hsiconfig = $cfg->get_config();
	$tb_customers = $hsiconfig['tb_customers'];
	
	$baseurl = URL::base()."index.php";
	$dlorder = $param['order_param_id'];
	$customer = "hndshkif_customers_customermapping"; //$param['customer_param_id'];
	$batch_report = $param['dlorderlastreport'];

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
	 	
	$customer_group = "";
	$querystr = sprintf('SELECT customergroup_id FROM %s WHERE customer_id = "%s"',$tb_customers,$item_03);
	if( $custgrp_r = $param['model']->execute_select_query($querystr) )
	{
		$custgrp_r = (array) $custgrp_r[0];
		$customer_group = $custgrp_r['customergroup_id'];
	}
	
	$HTML=<<<_HTML_
	<table  width="100%">
			<tr valign=top>
				<td width="40%">
					<table class="ne_tab" width="100%">
						<tr valign=top><td class="ne_td1" width="30%">$label_01 : </td>
						<td class="ne_td2" ><a href="$baseurl/$dlorder/index/$item_01" target="enquiry" title="View Handshake Order">$item_01</a></td></tr>
												
						<tr valign=top><td class="ne_td1">$label_02 : </td>
						<td class="ne_td2" ><a href="$baseurl/$batch_report?batch_id=$item_02" target="enquiry" title="View Batch Report">$item_02</a></td></tr>
						
						<tr valign=top><td class="ne_td1">$label_03 : </td>
						<td class="ne_td2" ><a href="$baseurl/$customer/index/$item_03" target="enquiry" title="View Customer">$item_03</a></td></tr>
						
						<tr valign=top><td class="ne_td1">$label_04 : </td>
						<td class="ne_td2" >$item_04 ($customer_group)</td></tr>
												
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

function order_details_subform($item,$param)
{
	require_once('media/hsi/hsiconfig.php');
	$cfg = new HSIConfig();
	$hsiconfig = $cfg->get_config();
	$picklist_print = false;
	$tb_configs = $hsiconfig['tb_configs'];
	
	$record  = array();
	$record['printmode'] = "NONE";
	$querystr = sprintf('SELECT print_mode,config_xml FROM %s WHERE config_id="DEFAULT"',$tb_configs);
	if( $config = $param['model']->execute_select_query($querystr) )
	{
		$record['printmode'] = $config[0]->print_mode;
		//get ranges
		try
		{
			$cfg = new SimpleXMLElement( $config[0]->config_xml );
			if($cfg->ranges->pipes) 
			{ 
				$pipes['lower'] = sprintf('%s',$cfg->ranges->pipes->lower); 
				$pipes['upper'] = sprintf('%s',$cfg->ranges->pipes->upper);
			}
		
			if($cfg->ranges->pumps) 
			{ 
				$pumps['lower'] = sprintf('%s',$cfg->ranges->pumps->lower); 
				$pumps['upper'] = sprintf('%s',$cfg->ranges->pumps->upper);
			}
		}
		catch (Exception $e) { }
	}

	$record['id'] = $item->id;
	$record['orderlines'] = $item->orderlines;
	$record['pumps'] = $pumps;
	$record['pipes'] = $pipes;
	$record['pdfbuilder'] = "hndshkif_orders_picklistpdfbuilder";
	$record['pdftoprinter'] = "hndshkif_orders_picklisttoprinter";
	$HTML  = view_xml_table("orderlines",$record,$param);
	return $HTML;
}

function view_xml_table($key,$record,$param)
{
	$controller = "dlorder";
	$TABLEHEADER = ""; $TABLEROWS ="";
	$baseurl = URL::base()."index.php";
	$inventory = $param['inventory_param_id'];
	
	$widths = array(); $totalwidth = 0;
	$tablewidth = 840;
	$wh_exist = false; $pr_exist = false; $py_exist = false;
	$pipes = $record['pipes'];  $pumps = $record['pumps'];
	$xml = $record['orderlines'];
		
	$s3 = "border:1px solid silver; font-weight:normal; padding:2px; background:#ebf2f9;; color:black; width:150px;";
	$s4 = "border:1px solid silver; font-weight:normal; padding:2px; background:#ebf2f9;; color:black; width:450px;";

	$HTML = "\n".'<table id="ne_tlist" width="100%">'."\n";
	$subopt  =  $param['model']->get_form_subtable_options($controller,$key);
	foreach($subopt as $subkey => $row)
	{
		$sublabel = $row['sublabel'];
		$widths[ $row['subname'] ] = $row['width'];
		$totalwidth = $totalwidth + $row['width'];
		$TABLEHEADER .= sprintf("<th>%s</th>",$sublabel)."\n";
	}
	$TABLEHEADER = '<tr valign="top">'."\n".$TABLEHEADER.'</tr>'."\n";
	
	$formfields = new SimpleXMLElement($xml);
	foreach($formfields->rows->row as $row)
	{
		$TABLEROWS .= "<tr>\n";
		foreach ($row->children() as $field)
		{
			$subkey = sprintf('%s',$field->getName() );
			$val	= sprintf('%s',$row->$subkey);
			$width  = round( ( $widths[$subkey] / $totalwidth ) * $tablewidth );
			if($subkey == "sku")
			{
				$TABLEROWS .= sprintf('<td valign="top" style="color:%s; width:%s%s;">',"black",$width,"px");
				$TABLEROWS .= sprintf('<a href="%s/%s/index/%s" target="enquiry" title="View Inventory Item">%s</a></td>',$baseurl,$inventory,$val,$val)."\n";
				$sku = $val;
				// pipe yard items
				if( $sku >= $pipes['lower'] && $sku <= $pipes['upper'] )
				{
					$py_exist = true;
				}				
				// pump room items
				else if ( $sku >= $pumps['lower'] && $sku <= $pumps['upper'] )
				{
					$pr_exist = true; 
				}
				// warehouse items
				else
				{
					$wh_exist = true; 
				}
			}
			else
			{
				$TABLEROWS .= sprintf('<td valign="top" style="color:%s; width:%s%s;">%s</td>',"black",$width,"px",$val)."\n";
			}
		}
		$TABLEROWS .= "</tr>\n";
	}
	
	$s1 = "border:1px solid silver; font-family:verdana,arial,helvetica,sans-serif; font-size:1em; text-align:left; border-collapse:collapse;width:60%;";	
	$s3 = "border:1px solid silver; font-weight:normal; padding:2px 5px 2px 2px; background:#ebf2f9;color:black;width:2%;";
	$s4 = "border:1px solid silver; font-weight:normal; padding:2px; background:white;color:black; width:25%;";
	$PRINT_MATRIX = "\n".sprintf('<div style="margin: 2px 0px 0px 0px;"><table style="%s">',$s1)."\n";
	$PRINT_MATRIX_PDF = ""; $PRINT_MATRIX_PRINT = "";
	
	// picklist pdf urls
	if($wh_exist) 
	{ 
		$PRINT_MATRIX_PDF .= sprintf('<a href=%sindex.php/%s/index/%s?scrnopt=warehouse target=_blank title="Warehouse Picklist PDF">Warehouse</a> |',URL::base(),$record['pdfbuilder'],$record['id'])."\n";
	}
	
	if($py_exist) 
	{ 
		$PRINT_MATRIX_PDF .= sprintf('<a href=%sindex.php/%s/index/%s?scrnopt=pipeyard target=_blank title=" Pipe Yard Picklist PDF">Pipe Yard</a> |',URL::base(),$record['pdfbuilder'],$record['id'])."\n";
	}
	
	if($pr_exist) 
	{ 
		$PRINT_MATRIX_PDF .= sprintf('<a href=%sindex.php/%s/index/%s?scrnopt=pumproom target=_blank title="Pump Room Picklist PDF">Pump Room</a> |',URL::base(),$record['pdfbuilder'],$record['id'])."\n";
	}
	$PRINT_MATRIX_PDF = substr_replace($PRINT_MATRIX_PDF, "", -2);
	
	// pick list reprints
	if($wh_exist) 
	{ 
		$PRINT_MATRIX_PRINT .= sprintf('<a href="javascript:void(0)" onclick=window.dlorderlastreport.PrintDialogOpen("%s","%s") title="Send Warehouse Picklist To Printer">Warehouse</a> |',$record['id'],"warehouse")."\n";
	}
	
	if($py_exist) 
	{ 
		$PRINT_MATRIX_PRINT .= sprintf('<a href="javascript:void(0)" onclick=window.dlorderlastreport.PrintDialogOpen("%s","%s") title="Send Pipe Yard Picklist To Printer">Pipe Yard</a> |',$record['id'],"pipeyard")."\n";
	}
	
	if($pr_exist) 
	{ 
		$PRINT_MATRIX_PRINT .= sprintf('<a href="javascript:void(0)" onclick=window.dlorderlastreport.PrintDialogOpen("%s","%s") title="Send Pump Room Picklist To Printer">Pump Room</a> |',$record['id'],"pumproom")."\n";
	}
	$PRINT_MATRIX_PRINT = substr_replace($PRINT_MATRIX_PRINT, "", -2);
	$PRINT_MATRIX_BODY  = '<tr valign="top">';
	switch($record['printmode'])
	{
		case "PRINTER":
			$PRINT_MATRIX_BODY .= sprintf('<td style="%s"><b>PRINT </b></td><td style="%s">%s</td>',$s3,$s4,$PRINT_MATRIX_PRINT);
		break;

		case "SCREEN":
			$PRINT_MATRIX_BODY .= sprintf('<td style="%s"><b>PICKLIST  </b></td><td style="%s">%s</td>',$s3,$s4,$PRINT_MATRIX_PDF);
		break;
						
		case "BOTH":
			$PRINT_MATRIX_BODY .= sprintf('<td style="%s"><b>PICKLIST  </b></td><td style="%s">%s</td>',$s3,$s4,$PRINT_MATRIX_PDF);
			$PRINT_MATRIX_BODY .= sprintf('<td style="%s"><b>PRINT </b></td><td style="%s">%s</td>',$s3,$s4,$PRINT_MATRIX_PRINT);
		break;
	}
	$PRINT_MATRIX_BODY .= '</tr>';
	$PRINT_MATRIX = $PRINT_MATRIX.$PRINT_MATRIX_BODY."\n"."</table></div>"."\n";
	
	$HTML .= $TABLEHEADER.$TABLEROWS."\n"."</table>"."\n";
	$HTML = $PRINT_MATRIX.$HTML;
	return $HTML;
}

function print_to_screen($enquiryrecords,$pagination,$labels,$config,$param)
{
	$section1 = ""; $section2 = "";
	foreach ($enquiryrecords as $item )
	{
		$section1 = get_section1($item,$labels,$param);
		$section2 = order_details_subform($item,$param);
	
		$refreshurl		= sprintf('<a href="%s" title="Refresh Page"><img src="%s" align="middle";></a>',$config['refresh_url'],$config['refresh_icon']);
		$totalrecords	= sprintf('<b>Total :</b> %s ',$config['total_items']);
		$pager			= sprintf('%s',$pagination);
		
$ENQNAV = <<<_HTML_
	<div id="ne_nav">
		<table width="100%">
			<td>$refreshurl</td>
			<td width="10%">$totalrecords</td>
			<td>$pager</td>
		</table>
	</div>
_HTML_;

$ENQBODY = <<<_HTML_
	<div id="ne_shd">Order Information</div>
	<div id="ne_sct">$section1</div><br>
	<div id="ne_shd">Order Details</div>
	<div id="ne_sct">$section2</div><br>
_HTML_;
		print $ENQNAV.$ENQBODY;
	}
}
?>

