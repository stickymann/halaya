<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Standard till report. 
 *
 * $Id: Till.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Report_Till extends Controller_Core_Sitereport
{
	public function __construct()
    {
		parent::__construct("till_rpt");
	}	
		
	public function action_index()
    {
      $this->process_request();
    }

	public function report_run()
	{
		$till_owner = $_POST['till_owner'];
		$start_date = $_POST['start_date'];
		$end_date = $_POST['end_date'];
		$where = ""; $filter = ""; $HTML =""; $RESULT=""; $filter1="";
		
		/*query filter*/
		if($till_owner != "" ) { $filter .= sprintf('till_owner = "%s" AND ',$till_owner); $where=" AND";}
		if($start_date != "" ) { $filter .= sprintf('till_date >= "%s" AND ',$start_date); $where=" AND";}
		if($end_date != "" ) { $filter .= sprintf('till_date <= "%s" AND ',$end_date); $where=" AND";}
		$filter = substr_replace($filter, '', -5);
		if($filter != "") {$filter1 = " WHERE ".$filter;} 

		$querystr=<<<_SQL_
SELECT 
till_id,
payment_type_totals,
tillmovements_type_totals,
initial_balance,
payment_total,
tillmovement_total as till_in_out_total,
till_balance
FROM
vw_tills
_SQL_;
		$querystr = sprintf('%s %s',$querystr,$filter1);
		$result = $this->sitemodel->execute_select_query($querystr);
		if($till_owner == "") { $till_owner = "ALL"; }

		if($result) 
		{
			$RESULT .= '<div id="rptbp" class="rpth2">Tills</div>';
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
						if($i>=3){$align = 'align="right"'; }
						$header .= sprintf('<th %s><b>%s</b></th>',$align,$headtxt);
					}
					$i++;
				}
			
				if($firstpass)
				{
					$header = "\n".'<thead>'."\n".'<tr valign="top">'.$header.'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
					$RESULT .=$header;
				}
				
				$data .= sprintf('<td>%s</td>',$linerec['till_id']);
				$data .= sprintf('<td width="15%s">%s</td>',"%",$linerec['payment_type_totals']);
				$data .= sprintf('<td width="12%s">%s</td>',"%",$linerec['tillmovements_type_totals']);
				$data .= sprintf('<td align="right">%s</td>',"$ ".number_format($linerec['initial_balance'], 2, '.', ',')); 
				$data .= sprintf('<td align="right">%s</td>',"$ ".number_format($linerec['payment_total'], 2, '.', ',')); 
				$data .= sprintf('<td align="right">%s</td>',"$ ".number_format($linerec['till_in_out_total'], 2, '.', ',')); 
				$data .= sprintf('<td align="right">%s</td>',"$ ".number_format($linerec['till_balance'], 2, '.', ',')); 

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
		
		/*daily totals line details*/

		$querystr=<<<_SQL_
SELECT
input_date AS date_time,
payment_type,
amount,
ref_no,
payment_id,
till_id,
order_id,
branch_id AS branch
FROM
payments
WHERE payment_status = "VALID"
_SQL_;
		$orderby = 'ORDER BY input_date;';
		$till_owner = $_POST['till_owner'];
		$start_date = $_POST['start_date'];
		$end_date = $_POST['end_date'];
		$where = ""; $filter = "";
		
		/*query filter*/
		if($till_owner != "" ) { $filter .= sprintf('till_id LIKE "%s%s" AND ',$till_owner,"%"); $where=" AND";}
		if($start_date != "" ) { $filter .= sprintf('payment_date >= "%s" AND ',$start_date); $where=" AND";}
		if($end_date != "" ) { $filter .= sprintf('payment_date <= "%s" AND ',$end_date); $where=" AND";}
		$filter = substr_replace($filter, '', -5);

		$querystr = sprintf('%s %s %s %s',$querystr,$where,$filter,$orderby);
		$result = $this->sitemodel->execute_select_query($querystr);
		if($till_owner == "") { $till_owner = "ALL"; } else { $till_owner = $till_owner; }
				
		if($result) 
		{
			$RESULT .= '<br><div id="rptbp" class="rpth2">Payments</div>';
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
						if($i==2){$align = 'align="right"'; } else {$align = 'align="left"'; } 
						$header .= sprintf('<th %s><b>%s</b></th>',$align,$headtxt);
						$i++;
					}
				}
			
				if($firstpass)
				{
					$header = "\n".'<thead>'."\n".'<tr valign="top">'.$header.'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
					$RESULT .=$header;
				}
				
				$data .= sprintf('<td>%s</td>',$linerec['date_time']);
				$data .= sprintf('<td>%s</td>',$linerec['payment_type']);
				$data .= sprintf('<td width="12%s" align="right">%s</td>',"%","$ ".number_format($linerec['amount'], 2, '.', ',')); 
				$data .= sprintf('<td>%s</td>',$linerec['ref_no']);
				$data .= sprintf('<td>%s</td>',$linerec['payment_id']);	
				$data .= sprintf('<td>%s</td>',$linerec['till_id']);
				$data .= sprintf('<td>%s</td>',$linerec['order_id']);	
				$data .= sprintf('<td>%s</td>',$linerec['branch']);	
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
		
		$querystr=<<<_SQL_
SELECT
input_date AS date_time,
transaction_type,
IF(movement="IN",amount,amount*-1) AS amount,
reason,
till_id,
transaction_id
FROM
tilltransactions
_SQL_;
		$orderby = 'ORDER BY input_date;';
		$till_owner = $_POST['till_owner'];
		$start_date = $_POST['start_date'];
		$end_date = $_POST['end_date'];
		$where = ""; $filter = ""; $filter3 = "";
		
		/*query filter*/
		if($till_owner != "" ) { $filter .= sprintf('till_id LIKE "%s%s" AND ',$till_owner,"%"); $where=" AND";}
		if($start_date != "" ) { $filter .= sprintf('transaction_date >= "%s" AND ',$start_date); $where=" AND";}
		if($end_date != "" ) { $filter .= sprintf('transaction_date <= "%s" AND ',$end_date); $where=" AND";}
		$filter = substr_replace($filter, '', -5);
		if($filter != "") {$filter3 = " WHERE ".$filter;} 

		$querystr = sprintf('%s %s %s',$querystr,$filter3,$orderby);
		$result = $this->sitemodel->execute_select_query($querystr);
		if($till_owner == "") { $till_owner = "ALL"; } else { $till_owner = $till_owner; }
				
		if($result) 
		{
			$RESULT .= '<br><div id="rptbp" class="rpth2">Till Transactions (IN/OUT)</div>';
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
						if($i==2){$align = 'align="right"'; } else {$align = 'align="left"'; } 
						$header .= sprintf('<th %s><b>%s</b></th>',$align,$headtxt);
						$i++;
					}
				}
			
				if($firstpass)
				{
					$header = "\n".'<thead>'."\n".'<tr valign="top">'.$header.'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
					$RESULT .=$header;
				}
			
				$data .= sprintf('<td>%s</td>',$linerec['date_time']);
				$data .= sprintf('<td>%s</td>',$linerec['transaction_type']);
				$data .= sprintf('<td width="12%s" align="right">%s</td>',"%","$ ".number_format($linerec['amount'], 2, '.', ',')); 
				$data .= sprintf('<td>%s</td>',$linerec['reason']);
				$data .= sprintf('<td>%s</td>',$linerec['till_id']);
				$data .= sprintf('<td>%s</td>',$linerec['transaction_id']);	
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
		$HTML .= sprintf('<div id="rptbp" class="rpth2"> For Period : %s - %s (Tills : %s)</div>',$start_date,$end_date,$till_owner);
		
		$REPORT_HTML = $HTML.$RESULT; 
		$this->content->pagebody .= $REPORT_HTML;
		$this->content->pagebody .= sprintf('<br><div>Run Date : %s<div>',$rundate);
		$this->content->pagebody .= sprintf('<div>Run By : %s</div>',Auth::instance()->get_user()->idname);
		$this->content->pagebody .= "</div>";
	}

}//End Controller_Core_Report_Till
