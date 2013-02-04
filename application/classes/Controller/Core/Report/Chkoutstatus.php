<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Standard inventory checkout status report. 
 *
 * $Id: Chkoutstatus.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Report_Chkoutstatus extends Controller_Core_Sitereport
{
	public function __construct()
    {
		parent::__construct("chkoutstatus_rpt");
	}	
		
	public function action_index()
    {
      $this->process_request();
    }

	public function report_run()
	{
		$branch_id = $_POST['branch_id'];
		$checkout_status = $_POST['checkout_status'];
		$start_date = $_POST['start_date'];
		$end_date = $_POST['end_date'];
		$where = ""; $filter = ""; $HTML =""; $RESULT="";
		
		/*query filter*/
		if($branch_id != "" ) { $filter .= sprintf('branch_id >= "%s" AND ',$branch_id); $where=" AND";}
		if($start_date != "" ) { $filter .= sprintf('order_date >= "%s" AND ',$start_date); $where=" AND";}
		if($end_date != "" ) { $filter .= sprintf('order_date <= "%s" AND ',$end_date); $where=" AND";}
		$filter = substr_replace($filter, '', -5);
		
		/*summary totals*/
		$querystr=<<<_SQL_
SELECT 
count(order_id) as record_count
FROM vw_inventory_checkout_status
WHERE NOT ((order_status = "QUOTATION") OR (order_status = "QUOTATION.EXPIRED") OR (order_status = "ORDER.CANCELLED")) 
AND checkout_status = "$checkout_status"
_SQL_;
		$querystr = sprintf('%s %s %s',$querystr,$where,$filter);
		$result = $this->sitemodel->execute_select_query($querystr);
		if($result) 
		{
			$totals = $result[0];
			$RESULT .= '<table>'."\n";
			$RESULT .= sprintf('<tr><td><span class="rpth4">Checkout Status : </td><td><span class="rpth4">%s</span></td></tr>',$checkout_status)."\n";
			$RESULT .= sprintf('<tr><td><span class="rpth4">Record Count : </span></td><td><span class="rpth4">%s</span></td></tr>',$totals->record_count)."\n";
			$RESULT .= '</table>'."\n";
		}
		else
		{
			$RESULT .= 'No Result.<br>';		
		}
		
		$querystr=<<<_SQL_
SELECT 
branch_id AS branch,
customer_id, 
customer_name,
order_id,
order_details,
checkout_details,
order_total,
payment_total,
balance
FROM vw_inventory_checkout_status
WHERE NOT ((order_status = "QUOTATION") OR (order_status = "QUOTATION.EXPIRED") OR (order_status = "ORDER.CANCELLED")) 
AND checkout_status = "$checkout_status"
_SQL_;
		$groupby = 'ORDER BY order_id,customer_id;';
		$querystr = sprintf('%s %s %s %s',$querystr,$where,$filter,$groupby);
		$printstr = $querystr;
		$result = $this->sitemodel->execute_select_query($querystr);
		if($branch_id == "") { $branch = "ALL"; } else { $branch = $branch_id; }

		if($result) 
		{
			$RESULT .= sprintf('<br><div></div>');
			$RESULT .= '<table id="rpttbl">'."\n";
			$firstpass = true;
			foreach($result as $row => $linerec)
			{	
				$linerec = (array)$linerec;
				$header = ''; $data = ''; $align = ''; $i = 0;
				foreach ($linerec as $key => $value)
				{
					if($firstpass)
					{
						$headtxt = Controller_Core_Site::strtotitlecase(str_replace("_"," ",$key));
						if($branch == "ALL")
						{
							if($i==6){$align = 'align="right"'; }
							$header .= sprintf('<th %s><b>%s</b></th>',$align,$headtxt);
						}
						else
						{
							if($i>0)
							{
								if($i==6){$align = 'align="right"'; }
								$header .= sprintf('<th %s><b>%s</b></th>',$align,$headtxt);
							}
						}
						$i++;
					}
				}
			
				if($firstpass)
				{
					$header = "\n".'<thead>'."\n".'<tr valign="top">'.$header.'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
					$RESULT .=$header;
				}
			
				if($branch == "ALL")
				{
					$data .= sprintf('<td>%s</td>',$linerec['branch']);
					$data .= sprintf('<td width="2%s">%s</td>',"%",$linerec['customer_id']);
					$data .= sprintf('<td>%s</td>',$linerec['customer_name']);
					$data .= sprintf('<td>%s</td>',$linerec['order_id']);
					$data .= sprintf('<td width="10%s">%s</td>',"%",$linerec['order_details']);
					$data .= sprintf('<td width="10%s">%s</td>',"%",$linerec['checkout_details']);
					$data .= sprintf('<td align="right">%s</td>',"$ ".number_format($linerec['order_total'], 2, '.', ',')); 
					$data .= sprintf('<td align="right">%s</td>',"$ ".number_format($linerec['payment_total'], 2, '.', ',')); 
					$data .= sprintf('<td align="right">%s</td>',"$ ".number_format($linerec['balance'], 2, '.', ',')); 
				}
				else
				{
					$data .= sprintf('<td>%s</td>',$linerec['customer_id']);
					$data .= sprintf('<td width="2%s">%s</td>',"%",$linerec['customer_name']);
					$data .= sprintf('<td>%s</td>',$linerec['order_id']);
					$data .= sprintf('<td width="10%s">%s</td>',"%",$linerec['order_details']);
					$data .= sprintf('<td width="10%s">%s</td>',"%",$linerec['checkout_details']);
					$data .= sprintf('<td align="right">%s</td>',"$ ".number_format($linerec['order_total'], 2, '.', ',')); 
					$data .= sprintf('<td align="right">%s</td>',"$ ".number_format($linerec['payment_total'], 2, '.', ',')); 
					$data .= sprintf('<td align="right">%s</td>',"$ ".number_format($linerec['balance'], 2, '.', ',')); 
				}
				
				$data = '<tr valign="top">'.$data.'</tr>'."\n"; 
				$RESULT .= $data;
				$firstpass = false;
			}
			$RESULT .='</tbody>'."\n".'</table>'."\n";
		}
		else
		{
			$RESULT .= 'No Result.<br>';		
		}

		/*set defaults*/
		if($start_date == "") { $start_date = "2012-01-01"; }
		if($end_date == "") { $end_date = date("Y-m-d"); }
		$rundate = date("Y-m-d H:i:s");
		
		$sdate = new DateTime($start_date);
		$start_date = $sdate->format('d M Y');
		$edate = new DateTime($end_date);
		$end_date = $edate->format('d M Y');

		$HTML .= '<div id="e" style="padding:5px 5px 5px 5px;">';
		$HTML .= sprintf('<div id="rptbp" class="rpth2"> For Period : %s - %s (Branch : %s)</div>',$start_date,$end_date,$branch);
		
		$REPORT_HTML = $HTML.$RESULT; 
		$this->content->pagebody .= $REPORT_HTML;
		$this->content->pagebody .= sprintf('<br><div>Run Date : %s<div>',$rundate);
		$this->content->pagebody .= sprintf('<div>Run By : %s</div>',Auth::instance()->get_user()->idname);
		$this->content->pagebody .= "</div>";
	}

}//End Controller_Core_Report_Chkoutstatus
