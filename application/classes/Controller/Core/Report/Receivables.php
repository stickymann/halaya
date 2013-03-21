<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Standard receivable report. 
 *
 * $Id: Receivable.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Report_Receivables extends Controller_Core_Sitereport
{

	public function __construct()
    {
		parent::__construct("receivables_rpt");
	}	
		
	public function action_index()
    {
      $this->process_request();
    }

	public function report_run()
	{
		$branch_id = $this->OBJPOST['branch_id'];
		$start_date = $this->OBJPOST['start_date'];
		$end_date = $this->OBJPOST['end_date'];
		$where = ""; $filter = ""; $HTML =""; $RESULT="";
		
		/*query filter*/
		if($branch_id != "" ) { $filter .= sprintf('branch_id >= "%s" AND ',$branch_id); $where=" AND";}
		if($start_date != "" ) { $filter .= sprintf('order_date >= "%s" AND ',$start_date); $where=" AND";}
		if($end_date != "" ) { $filter .= sprintf('order_date <= "%s" AND ',$end_date); $where=" AND";}
		$filter = substr_replace($filter, '', -5);
		
		/*summary totals*/
		$querystr=<<<_SQL_
SELECT 
SUM(order_total) AS total_sales,
SUM(payment_total) AS total_payments,
SUM(balance) AS balance FROM vw_orderbalances 
WHERE balance > 0 AND NOT ((order_status = "QUOTATION") OR (order_status = "QUOTATION.EXPIRED") OR (order_status = "ORDER.CANCELLED")) 
_SQL_;
		$querystr = sprintf('%s %s %s',$querystr,$where,$filter);
		$result = $this->sitemodel->execute_select_query($querystr);
		if($result) 
		{
			$totals = $result[0];
			$RESULT .= '<table>'."\n";
			$RESULT .= sprintf('<tr><td><span class="rpth4">Total Sales : </span></td><td align="right"><span class="rpth4">%s</span></td></tr>',"$ ".number_format($totals->total_sales, 2, '.', ','))."\n";
			$RESULT .= sprintf('<tr><td><span class="rpth4">Total Payments : </span></td><td align="right"><span class="rpth4">%s</span></td></tr>',"$ ".number_format($totals->total_payments, 2, '.', ','))."\n";
			$RESULT .= sprintf('<tr><td><span class="rpth4">Outstanding Balance : </span></td><td align="right"><span class="rpth4">%s</span></td></tr>',"$ ".number_format($totals->balance, 2, '.', ','))."\n";
			$RESULT .= '</table>'."\n";
		}
		else
		{
			$RESULT .= 'No Result.<br>';		
		}
		
		$querystr=<<<_SQL_
SELECT 
branch_id as branch, 
customer_id, 
IF(first_name="Co.",last_name,CONCAT(first_name," ",last_name)) AS customer_name,
GROUP_CONCAT(IF(balance > 0,order_id,"") SEPARATOR ' ') AS order_ids,
SUM(order_total) AS order_total,
SUM(payment_total) AS payment_total,
SUM(balance) AS balance
FROM vw_orderbalances
WHERE 
balance > 0
AND NOT ((order_status = "QUOTATION") OR (order_status = "QUOTATION.EXPIRED") OR (order_status = "ORDER.CANCELLED"))  
_SQL_;
		$groupby = 'GROUP BY customer_id,branch_id ORDER BY customer_id;';
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
							if($i==4){$align = 'align="right"'; }
							$header .= sprintf('<th %s><b>%s</b></th>',$align,$headtxt);
						}
						else
						{
							if($i>0)
							{
								if($i==3){$align = 'align="right"'; }
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
					$data .= sprintf('<td width="10%s">%s</td><td width="10%s">%s</td><td width="25%s">%s</td><td width="15%s">%s</td><td align="right">%s</td><td align="right">%s</td><td align="right">%s</td>',"%",$linerec['branch'],"%",$linerec['customer_id'],"%",$linerec['customer_name'],"%",$linerec['order_ids'],"$ ".number_format($linerec['order_total'], 2, '.', ','),"$ ".number_format($linerec['payment_total'], 2, '.', ','),"$ ".number_format($linerec['balance'], 2, '.', ',')); 
				}
				else
				{
					$data .= sprintf('<td width="10%s">%s</td><td width="34%s">%s</td><td width="16%s">%s</td><td align="right">%s</td><td align="right">%s</td><td align="right">%s</td>',"%",$linerec['customer_id'],"%",$linerec['customer_name'],"%",$linerec['order_ids'],"$ ".number_format($linerec['order_total'], 2, '.', ','),"$ ".number_format($linerec['payment_total'], 2, '.', ','),"$ ".number_format($linerec['balance'], 2, '.', ',')); 
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

}//End Controller_Core_Report_Receivables
