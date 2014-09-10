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

require_once('media/hsi/hsiconfig.php');
 
class Controller_Hndshkif_Orders_Dlorderreport extends Controller_Core_Sitereport
{
	public function __construct()
    {
		parent::__construct("dlorderreport_rpt");
		$this->rptparam['htmlhead'] .= HTML::script( $this->randomize('media/js/hndshkif.dlorderreport.js') );
		$this->pdfbuilder = "hndshkif_orders_picklistpdfbuilder";
		$this->pdftoprinter = "hndshkif_orders_picklisttoprinter";
		$this->cfg = new HSIConfig();
		$this->config = $this->cfg->get_config();
	}	
		
	public function action_index()
    {
      $this->process_request();
    }

	public function report_run()
	{
		$batch_id = $this->OBJPOST['batch_id'];
		$pagebody = $this->create_report($batch_id);
		$this->content->pagebody = $pagebody;
	}
	
	public function create_report($batch_id)
	{
		$orders_table = $this->config['tb_orders'];
		$HTML =""; $RESULT="";
		$order_count = 0;
		$pipes = array();  $pumps = array();
		
		$querystr = sprintf('SELECT * FROM %s WHERE batch_id = "%s" ORDER BY id', $orders_table,$batch_id);
		$result = $this->sitemodel->execute_select_query($querystr);
		
		if($result) 
		{
			$s1 = "border:1px solid silver; font-family:verdana,arial,helvetica,sans-serif; font-size:1em; text-align:left; border-collapse:collapse;";
			$s2 = "border:1px solid silver; font-weight:bold; padding:2px; background:#ebf2f9; color:black; width:120px;";
			$s3 = "border:1px solid silver; font-weight:normal; padding:2px; background:#ebf2f9; color:black; width:150px;";
			$s4 = "border:1px solid silver; font-weight:normal; padding:2px; background:#ebf2f9; color:black; width:450px;";
			
			$picklist_print = false;
			$tb_configs = $this->config['tb_configs'];
			$querystr = sprintf('SELECT print_mode,config_xml FROM %s WHERE config_id="DEFAULT"',$tb_configs);
			if( $config = $this->sitemodel->execute_select_query($querystr) )
			{
				$picklist_print = true;
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
			
			foreach($result as $index => $record)
			{
				$record  = (array) $record;
				if($picklist_print)
				{
					$record['printmode'] = $config[0]->print_mode;
				}
				
				$record['pumps'] = $pumps;
				$record['pipes'] = $pipes;
				
				$RESULT .= sprintf('<div>');
				$RESULT .= sprintf('<table style="%s">',$s1)."\n";
				$RESULT .= sprintf('<tbody>')."\n";
				$RESULT .= sprintf('<tr valign="top">')."\n";
				$RESULT .= sprintf('<td style="%s">Order Id :</td><td style="%s">%s</td><td style="%s">Name :</td><td style="%s">%s</td>',$s2,$s3,$record['id'],$s2,$s4,$record['name'])."\n";
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
				$RESULT	.= $this->view_xml_table("orderlines",$record);
				$RESULT .= sprintf('</div><br><br>');
				$order_count++;
			}
		}
		else
		{
			$RESULT .= 'No Result.<br>';		
		}

		// set defaults
		$rundate = date("Y-m-d H:i:s");
		
		$HTML .= '<div id="e" style="padding:5px 5px 5px 5px;">';
		$HTML .= sprintf('<div id="rptbp" class="rpth2">Batch : %s </div>',$batch_id);
		$HTML .= sprintf('<div style="font-size:1.2em; font-weight:bold; padding:0px 0px 5px 0px;">Total Orders : %s</div>',$order_count);
		$PO_HTML =  $this->custom_dialog_window();
		
		$REPORT_HTML = $HTML.$RESULT.$PO_HTML; 
		$pagebody  = $REPORT_HTML;
		$pagebody .= sprintf('<div>Run Date : %s<div>',$rundate);
		$pagebody .= sprintf('<div>Run By : %s</div>',Auth::instance()->get_user()->idname);
		$pagebody .= "</div>";
		return $pagebody;
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
		
	public function  view_xml_table($key,$record)
	{
		$controller = "dlorder";
		$TABLEHEADER = ""; $TABLEROWS ="";
		$widths = array(); $totalwidth = 0;
		$tablewidth = 840;
		$wh_exist = false; $pr_exist = false; $py_exist = false;
		$pipes = $record['pipes'];  $pumps = $record['pumps'];
		$xml = $record['orderlines'];
		
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
				if($subkey == "sku")
				{
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
			}
			$TABLEROWS .= "</tr>\n";
		}
		
		$s1 = "border:1px solid silver; font-family:verdana,arial,helvetica,sans-serif; font-size:1em; text-align:left; border-collapse:collapse;width:60%;";
		$s3 = "border:1px solid silver; font-weight:normal; padding:2px 5px 2px 2px; background:#ebf2f9;color:black;width:2%;";
		$s4 = "border:1px solid silver; font-weight:normal; padding:2px; background:#ebf2f9;color:black; width:25%;";
		$PRINT_MATRIX = "\n".sprintf('<div style="margin: 2px 0px 0px 0px;"><table style="%s">',$s1)."\n";
		$PRINT_MATRIX_PDF = ""; $PRINT_MATRIX_PRINT = "";
		
		// picklist pdf urls
		if($wh_exist) 
		{ 
			$PRINT_MATRIX_PDF .= sprintf('<a href=%sindex.php/%s/index/%s?scrnopt=warehouse target=_blank title="Warehouse Picklist PDF">Warehouse</a> |',URL::base(),$this->pdfbuilder,$record['id'])."\n";
		}
		
		if($py_exist) 
		{ 
			$PRINT_MATRIX_PDF .= sprintf('<a href=%sindex.php/%s/index/%s?scrnopt=pipeyard target=_blank title=" Pipe Yard Picklist PDF">Pipe Yard</a> |',URL::base(),$this->pdfbuilder,$record['id'])."\n";
		}
		
		if($pr_exist) 
		{ 
			$PRINT_MATRIX_PDF .= sprintf('<a href=%sindex.php/%s/index/%s?scrnopt=pumproom target=_blank title="Pump Room Picklist PDF">Pump Room</a> |',URL::base(),$this->pdfbuilder,$record['id'])."\n";
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

}//End Controller_Hndshkif_Orders_Dlorderreport
