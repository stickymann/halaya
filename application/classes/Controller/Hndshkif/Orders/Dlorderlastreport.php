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
		$batch_table = "hsi_dlorderbatchs";
		$querystr = sprintf('SELECT batch_id FROM %s ORDER BY batch_id DESC LIMIT 1', $batch_table);
		if( $batchresult = $this->sitemodel->execute_select_query($querystr) )
		{
			$val = (array) $batchresult[0];
			$batch_id = $val['batch_id'];
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
			
				foreach($result as $index => $record)
				{
					$record = (array) $record;
					
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
					$RESULT	.= $this->view_xml_table("orderlines",$record['orderlines']);
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
		
		$REPORT_HTML = $HTML.$RESULT; 
		$this->content->pagebody .= $REPORT_HTML;
		$this->content->pagebody .= sprintf('<br><div>Run Date : %s<div>',$rundate);
		$this->content->pagebody .= sprintf('<div>Run By : %s</div>',Auth::instance()->get_user()->idname);
		$this->content->pagebody .= "</div>";
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

}//End Controller_Hndshkif_Orders_Dlorderlastreport
