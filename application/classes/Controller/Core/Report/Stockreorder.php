<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Standard stockreorder report. 
 *
 * $Id: Stockreorder.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Report_Stockreorder extends Controller_Core_Sitereport
{
	public function __construct()
    {
		parent::__construct("stockreorder_rpt");
	}	
		
	public function action_index()
    {
      $this->process_request();
    }

	public function report_run()
	{
		$branch_id = $this->OBJPOST['branch_id'];
		$where = ""; $filter = ""; $HTML =""; $RESULT="";
		
		/*query filter*/
		if($branch_id != "" ) { $filter .= sprintf('branch_id >= "%s" AND ',$branch_id); $where=" WHERE";}
		$filter = substr_replace($filter, '', -5);
		
		/*summary totals*/
		$querystr=<<<_SQL_
SELECT 
branch_id AS branch,
product_id,
qty_instock,
reorder_level,
IF(qty_instock = 0,"OUT.OF.STOCK",IF(qty_instock <= reorder_level,"REORDER.STOCK","STOCK.OK")) AS reorder_status
FROM inventorys
_SQL_;
		$querystr = sprintf('%s %s %s',$querystr,$where,$filter);
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
							if(($i==2)||($i==3)){$align = 'align="center"';} else {$align = 'align="left"';}
							$header .= sprintf('<th %s><b>%s</b></th>',$align,$headtxt);
						}
						else
						{
							if($i>0)
							{
								if(($i==2)||($i==3)){$align = 'align="center"';} else {$align = 'align="left"';}
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
					$data .= sprintf('<td width="20%s">%s</td><td width="40%s">%s</td><td align="center">%s</td><td align="center">%s</td><td align="left">%s</td>',"%",$linerec['branch'],"%",$linerec['product_id'],$linerec['qty_instock'],$linerec['reorder_level'],$linerec['reorder_status']); 
				}
				else
				{
					$data .= sprintf('<td width="40%s">%s</td><td align="center">%s</td><td align="center">%s</td><td align="left">%s</td>',"%",$linerec['product_id'],$linerec['qty_instock'],$linerec['reorder_level'],$linerec['reorder_status']); 
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
		$rundate = date("Y-m-d H:i:s");
		
		$HTML .= '<div id="e" style="padding:5px 5px 5px 5px;">';
		$HTML .= sprintf('<div id="rptbp" class="rpth2">Branch : %s</div>',$branch);
		
		$REPORT_HTML = $HTML.$RESULT; 
		$this->content->pagebody .= $REPORT_HTML;
		$this->content->pagebody .= sprintf('<br><div>Run Date : %s<div>',$rundate);
		$this->content->pagebody .= sprintf('<div>Run By : %s</div>',Auth::instance()->get_user()->idname);
		$this->content->pagebody .= "</div>";
	}

}//End Controller_Core_Report_Stockreorder
