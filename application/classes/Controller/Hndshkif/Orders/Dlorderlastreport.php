<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Handshake orders last (most recent) download report. 
 *
 * $Id: Dlorderlastreport.php 2014-03-04 17:00:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
 
class Controller_Hndshkif_Orders_Dlorderlastreport extends Controller_Core_Sitereport
{
	public function __construct()
    {
		parent::__construct("dlorderlastreport_rpt");
		$this->rptparam['htmlhead'] .= HTML::script( $this->randomize('media/js/hndshkif.dlorderlastreport.js') );
		$this->pdfbuilder = "hndshkif_orders_picklistpdfbuilder";
		$this->pdftoprinter = "hndshkif_orders_picklisttoprinter";
	}	
		
	public function action_index()
    {
		$this->page_initialize();
		if( $this->viewable )
		{	
			$this->set_pageheader();
			$this->set_page_content($this->rptparam['htmlhead'],$this->content);
			$this->report_run();
		}
    }

	public function report_run()
	{
		if( isset($_REQUEST['batch_id']) )
		{ 
			$batch_id = $_REQUEST['batch_id']; 
		} 
		else 
		{
			$batch_table = "hsi_dlorderbatchs";
			$querystr = sprintf('SELECT batch_id FROM %s ORDER BY batch_id DESC LIMIT 1', $batch_table);
			if( $batchresult = $this->sitemodel->execute_select_query($querystr) )
			{
				$val = (array) $batchresult[0];
				$batch_id = $val['batch_id'];
			}
			else
			{
				$batch_id != "";
			}
		}
	
		if( $batch_id != "")
		{
			$orders_table = 'hsi_orders';
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
				$querystr = sprintf('SELECT print_mode,config_xml FROM %s WHERE config_id="DEFAULT"',"hsi_configs");
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
				if($subkey = "sku")
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
		
		$s1 = "border:1px solid silver; font-family:verdana,arial,helvetica,sans-serif; font-size:1em; text-align:left; border-collapse:collapse;";
		$s4 = "border:1px solid silver; font-weight:normal; padding:2px; background:#ebf2f9;; color:black;";
		$PRINT_MATRIX = "\n".sprintf('<table style="%s">',$s1)."\n";
		$PRINT_MATRIX_HEADER = ""; $PRINT_MATRIX_BODY = "";
		if($wh_exist) 
		{ 
			$PRINT_MATRIX_HEADER .= sprintf('<th style="%s">%s</th>',$s4,"Warehouse")."\n"; 
			$PRINT_MATRIX_BODY .= sprintf('<td style="%s"><a href=%sindex.php/%s/index/%s?scrnopt=warehouse target=_blank title="Picklist Warehouse PDF"><b>pdf</b></a></td>',$s4,URL::base(),$this->pdfbuilder,$record['id'])."\n";
		}
		
		
		if($pr_exist) { $PRINT_MATRIX_HEADER .= sprintf('<th style="%s">%s</th>',$s4,"Pump Room")."\n"; }
		if($py_exist) { $PRINT_MATRIX_HEADER .= sprintf('<th style="%s">%s</th>',$s4,"Pipe Yard")."\n"; }
		$PRINT_MATRIX_HEADER = '<tr valign="top">'."\n".$PRINT_MATRIX_HEADER.'</tr>'."\n";
		$PRINT_MATRIX_BODY = '<tr valign="top">'."\n".$PRINT_MATRIX_BODY.'</tr>'."\n";
		$PRINT_MATRIX = $PRINT_MATRIX.$PRINT_MATRIX_HEADER.$PRINT_MATRIX_BODY."</table>"."\n";
		
		/*
				$pl_links = "";
					$pl_pdf  = sprintf('<a href=%sindex.php/%s/index/%s?scrnopt=warehouse target=_blank title="Picklist PDF"><b>pdf_wh</b></a>',URL::base(),$this->pdfbuilder,$record['id'])."\n";
					$pl_pdf  .= sprintf('<a href=%sindex.php/%s/index/%s?scrnopt=pumproom target=_blank title="Picklist PDF"><b>pdf_pr</b></a>',URL::base(),$this->pdfbuilder,$record['id'])."\n";
					$pl_pdf  .= sprintf('<a href=%sindex.php/%s/index/%s?scrnopt=pipeyard target=_blank title="Picklist PDF"><b>pdf_py</b></a>',URL::base(),$this->pdfbuilder,$record['id'])."\n";
					
					//$pl_prnt = sprintf('<a href=%sindex.php/%s/index/%s target=_blank title="Send Picklist To Printer"><b>print</b></a>',URL::base(),$this->pdftoprinter,$record['id'])."\n";							
					$pl_prnt = sprintf('<a href="javascript:void(0)" onclick=window.dlorderlastreport.PrintDialogOpen("%s") title="Send Picklist To Printer"><b>print</b></a>',$record['id'])."\n";
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
		*/		
		
		
		$HTML .= $TABLEHEADER.$TABLEROWS."\n"."</table>"."\n";
		$HTML = $PRINT_MATRIX.$HTML;
		return $HTML;
	}
	
	public function action_customfilter()
	{
		//$this->page_initialize();
		$this->content	= new View($this->rptparam['view']);
		$this->content->pageheader = "";
		$this->content->pagebody = "";
		$this->set_pageheader();
				
		$baseurl = URL::base();
		$sid	 = "batches";
		$chfunc	 = "dlorderlastreport.LoadBatchReport";
		$table 	 = "hsi_dlorderbatchs";
		$rfield	 = "batch_id";
		$sfield	 = "batch_date";
		$current_date = date('Y-m-d');
				
		//http://localhost/hndshkif/index.php/hndshkif_dbreqs?option=dynacombo&sid=batches&chfunc=dlorderlastreport.LoadBatchReport&table=hsi_dlorderbatchs&rfield=batch_id&sfield=batch_date&sval=2014-08-14
		$baseurl = URL::base(TRUE,'http');
		$url = sprintf('%sindex.php/hndshkif_dbreqs?option=dynacombo&sid=%s&chfunc=%s&table=%s&rfield=%s&sfield=%s&sval=%s',$baseurl,$sid,$chfunc,$table,$rfield,$sfield,$current_date);
		$dynacombo = Controller_Core_Sitehtml::get_html_from_url($url);
		
		$HTML = <<<_HTML_
<div style="font-size: 1.6em;">
<div id="ne_ff">
<form>
<fieldset>
<legend>Search Filter</legend>
<table>
<tr valign="top">
	<td>Batch Date</td>
	<td>Batch Id</td>
</tr>
<tr valign="top">
	<td>
		<input type="text" id="batch_date" size="12" value="$current_date">
		<script type="text/javascript">
			$(function() 
			{
				$('#batch_date').datepick(
				{
					showOnFocus: false, 
					showTrigger: '<span class="dateicon">&nbsp&nbsp<img src="/hndshkif/media/css/calendar-blue.gif" align="absbottom">&nbsp</span>',
					dateFormat: 'yyyy-mm-dd',
					yearRange: '1900:c+100',
					showAnim: '',
					alignment: 'bottomLeft',
					onSelect: function() { $('#batch_date').focus(); }
				});
			});
		</script>
	</td>
	<td id="dynacombo">$dynacombo</td>
</tr>	
</table>
</fieldset>
<input type="hidden" id="controller" value="core_enquiry_orders">
</form>
</div>	
</div>
_HTML_;
		$this->content->pagebody .= $HTML;
		$this->set_page_content($this->rptparam['htmlhead'],$this->content);
	}

}//End Controller_Hndshkif_Orders_Dlorderlastreport
