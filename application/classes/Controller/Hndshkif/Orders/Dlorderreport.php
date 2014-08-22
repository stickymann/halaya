<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Handshake orders download report. 
 *
 * $Id: Dlorderreport.php 2014-03-04 17:00:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
 
class Controller_Hndshkif_Orders_Dlorderreport extends Controller_Core_Sitereport
{
	public function __construct()
    {
		parent::__construct("dlorderreport_rpt");
		$this->rptparam['htmlhead'] .= HTML::script( $this->randomize('media/js/hndshkif.dlorderlastreport.js') );
		$this->pdfbuilder = "hndshkif_orders_picklistpdfbuilder";
		$this->pdftoprinter = "hndshkif_orders_picklisttoprinter";
	}	
		
	public function action_index()
    {
      $this->process_request();
    }

	public function report_run()
	{
		$batch_id = $this->OBJPOST['batch_id'];
		$orders_table = 'hsi_orders';
		$HTML =""; $RESULT="";
		$order_count = 0;
		
		$querystr = sprintf('SELECT * FROM %s WHERE batch_id = "%s" ORDER BY id', $orders_table,$batch_id);
		$result = $this->sitemodel->execute_select_query($querystr);
		
		if($result) 
		{
			$s1 = "border:1px solid silver; font-family:verdana,arial,helvetica,sans-serif; font-size:1em; text-align:left; border-collapse:collapse;";
			$s2 = "border:1px solid silver; font-weight:bold; padding:2px; background:#ebf2f9; color:black; width:120px;";
			$s3 = "border:1px solid silver; font-weight:normal; padding:2px; background:#ebf2f9; color:black; width:150px;";
			$s4 = "border:1px solid silver; font-weight:normal; padding:2px; background:#ebf2f9; color:black; width:450px;";
			
			$picklis_print = false;
			$querystr = sprintf('SELECT print_mode FROM %s WHERE config_id="DEFAULT"',"hsi_configs");
			if( $mode = $this->sitemodel->execute_select_query($querystr) )
			{
				$picklist_print = true;
			}
						
			foreach($result as $index => $record)
			{
				$pl_links = "";
				$record  = (array) $record;
				$pl_pdf  = sprintf('<a href=%sindex.php/%s/index/%s target=_blank title="Picklist PDF"><b>pdf</b></a>',URL::base(),$this->pdfbuilder,$record['id'])."\n";							
				//$pl_prnt = sprintf('<a href=%sindex.php/%s/index/%s target=_blank title="Send Picklist To Printer"><b>print</b></a>',URL::base(),$this->pdftoprinter,$record['id'])."\n";							
				$pl_prnt = sprintf('<a href="javascript:void(0)" onclick=window.dlorderlastreport.PrintDialogOpen("%s") title="Send Picklist To Printer"><b>print</b></a>',$record['id'])."\n";
					
				if($picklist_print)
				{
					$printmode = $mode[0]->print_mode;
					switch($printmode)
					{
						case "PRINTER":
							$pl_links = sprintf('[ %s ]',$pl_prnt);
						break;
					
						case "SCREEN":
							$pl_links = sprintf('[ %s ]',$pl_pdf); 
						break;
						
						case "BOTH":
							$pl_links = sprintf('[ %s , %s ]',$pl_pdf,$pl_prnt);
						break;
					}
				}
								
				$RESULT .= sprintf('<div>');
				$RESULT .= sprintf('<table style="%s">',$s1)."\n";
				$RESULT .= sprintf('<tbody>')."\n";
				$RESULT .= sprintf('<tr valign="top">')."\n";
				$RESULT .= sprintf('<td style="%s">Order Id :</td><td style="%s">%s &nbsp %s</td><td style="%s">Name :</td><td style="%s">%s</td>',$s2,$s3,$record['id'],$pl_links,$s2,$s4,$record['name'])."\n";
				$RESULT .= sprintf('</tr>')."\n";
				$RESULT .= sprintf('<tr valign="top">')."\n";
				$RESULT .= sprintf('<td style="%s">Customer Id :</td><td style="%s">%s</td><td style="%s">Contact :</td><td style="%s">%s</td>',$s2,$s3,$record['customer_id'],$s2,$s4,$record['contact'])."\n";
				$RESULT .= sprintf('</tr>')."\n";
				$RESULT .= sprintf('<tr valign="top">')."\n";
				$RESULT .= sprintf('<td style="%s">Tax Id :</td><td style="%s">%s</td><td style="%s">Street :</td><td style="%s">%s</td>',$s2,$s3,$record['tax_id'],$s2,$s4,$record['street'])."\n";
				$RESULT .= sprintf('</tr>')."\n";
				$RESULT .= sprintf('<tr valign="top">')."\n";
				$RESULT .= sprintf('<td style="%s">Order Date :</td><td style="%s">%s</td><td style="%s">City :</td><td style="%s">%s</td>',$s2,$s3,$record['cdate'],$s2,$s4,$record['city'])."\n";
				$RESULT .= sprintf('</tr>')."\n";
				$RESULT .= sprintf('<tr valign="top">')."\n";
				$RESULT .= sprintf('<td style="%s">Order Time :</td><td style="%s">%s</td><td style="%s">Country :</td><td style="%s">%s</td>',$s2,$s3,$record['ctime'],$s2,$s4,$record['country'])."\n";
				$RESULT .= sprintf('</tr>')."\n";
				$RESULT .= sprintf('<tr valign="top">')."\n";
				$RESULT .= sprintf('<td style="%s">Payment Terms :</td><td style="%s">%s</td><td style="%s">Phone :</td><td style="%s">%s</td>',$s2,$s3,$record['paymentterms'],$s2,$s4,$record['phone'])."\n";
				$RESULT .= sprintf('</tr>')."\n";
				$RESULT .= '</tbody>'."\n".'</table>'."\n";
				$RESULT	.= $this->view_xml_table("orderlines",$record['orderlines']);
				$RESULT .= sprintf('</div><br><br>');
				$order_count++;
			}
		}
		else
		{
			$RESULT .= 'No Result.<br>';		
		}

		/*set defaults*/
		$rundate = date("Y-m-d H:i:s");
		
		$HTML .= '<div id="e" style="padding:5px 5px 5px 5px;">';
		$HTML .= sprintf('<div id="rptbp" class="rpth2">Batch : %s </div>',$batch_id);
		$HTML .= sprintf('<div style="font-size:1.2em; font-weight:bold; padding:0px 0px 5px 0px;">Total Orders : %s</div>',$order_count);
		$PO_HTML =  $this->custom_dialog_window();
		
		$REPORT_HTML = $HTML.$RESULT.$PO_HTML; 
		$this->content->pagebody .= $REPORT_HTML;
		$this->content->pagebody .= sprintf('<br><div>Run Date : %s<div>',$rundate);
		$this->content->pagebody .= sprintf('<div>Run By : %s</div>',Auth::instance()->get_user()->idname);
		$this->content->pagebody .= "</div>";
	}
	
	public function custom_dialog_window()
	{
		$HTML = <<<_HTML_
		<div id="chklight" class="white_content"  buttons="#chklight-buttons">
			<div id="chkresult"></div>
		</div>
		<div id="chklight-buttons" style="background-color:#ebf2f9; display:none;">
			<a href="#" class="easyui-linkbutton" onclick="javascript:window.dlorderlastreport.PrintOrder()">Print</a>
			<a href="#" class="easyui-linkbutton" onclick="javascript:siteutils.closeDialog('chklight',false)">Close</a>
		</div>
		<div id="fade" class="black_overlay"></div>
_HTML_;
		return $HTML;
	}
		
	public function  view_xml_table($key,$xml)
	{
		$controller = "dlorder";
		$TABLEHEADER = ""; $TABLEROWS ="";
		$widths = array(); $totalwidth = 0;
		$tablewidth = 840;
		
		$s1 = "border:1px solid silver; font-family:verdana,arial,helvetica,sans-serif; font-size:1em; text-align:left; border-collapse:collapse; margin:1px 0px 0px 0px; background:#ebf2f9;";
		$s2 = "border:1px solid silver; font-weight:bold; padding:2px; background:#ebf2f9;; color:black; width:120px;";
		$s3 = "border:1px solid silver; font-weight:normal; padding:2px; background:#ebf2f9;; color:black; width:150px;";
		$s4 = "border:1px solid silver; font-weight:normal; padding:2px; background:#ebf2f9;; color:black; width:450px;";
		
		$HTML = "\n".sprintf('<table style="%s">',$s1)."\n";
		
		$subopt  =  $this->sitemodel->get_form_subtable_options($controller,$key);
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
				$TABLEROWS .= sprintf('<td valign="top" style="color:%s; width:%s%s;">%s</td>',"black",$width,"px",$val)."\n";
			}
			$TABLEROWS .= "</tr>\n";
		}
		$HTML .= $TABLEHEADER.$TABLEROWS."\n"."</table>"."\n";
		return $HTML;
	}

}//End Controller_Hndshkif_Orders_Dlorderreport
