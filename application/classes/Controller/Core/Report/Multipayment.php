<?php defined('SYSPATH') or die('No direct script access.');
/**
 *  Multipayment function. 
 *
 * $Id: Multipayment.php 2014-04-13 10:54:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license   
 */
class Controller_Core_Report_Multipayment extends Controller_Core_Sitereport
{

	public function __construct()
    {
		parent::__construct("multipayment_rpt");
		$this->opt = $this->request->param('opt');
	}	
		
	public function action_index()
    {
      $this->process_request();
    }
	
	public function action_processmultipayments()
	{
		$this->process_multi_payments();	
	}
	
	public function process_multi_payments()
	{
		$this->page_initialize();
		if($this->viewable)
		{	
			$this->set_pageheader();
		}
		$this->set_page_content($this->rptparam['htmlhead'],$this->content);
		
		//var_dump($this->content->pagebody );
		//var_dump($this->opt );
		$this->content->pagebody = '<div id="e">'.$this->opt.'</div>'; 
	}
	
	public function report_run()
	{
		$customer_id = $this->OBJPOST['customer_id'];
		$where = ""; $filter = ""; $HTML =""; $RESULT="";
		
		$pagebody = new Controller_Core_Sitehtml(Form::open($this->rptparam['reportdef_id']."/processmultipayments/".$customer_id,array('id'=>$this->rptparam['reportdef_id'],'name'=>$this->rptparam['reportdef_id'])));
		$pagebody->add("<div id='i'>\n");
		$pagebody->add("<table>\n");
		$pagebody->add("<tr valign='center'><td colspan=2><input type='submit' id='submit' name='submit' value='Create Payments' class='bttn'/></td></tr>\n");
		$pagebody->add("</table>");
		$pagebody->add("</div><br>");
		
		$table = 'vw_orderbalances_nonzero';
		$fields = array('id','order_id','order_total','balance');
		$querystr = sprintf('SELECT %s FROM %s WHERE customer_id = "%s"', join(',',$fields),$table,$customer_id);
		$result = $this->sitemodel->execute_select_query($querystr);
//print "<b>[DEBUG]---></b> "; print_r($result); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		
		if($result)
		{
			$RESULT .= $this->input_form($result);
		
		}
		/*set defaults*/
		$rundate = date("Y-m-d H:i:s");
		
		$HTML .= '<br><div id="e" style="padding:5px 5px 5px 5px;">';
		$HTML .= sprintf('<div id="rptbp" class="rpth2"> Multi Payment For Customer : %s </div>',$customer_id);
		
		$REPORT_HTML = $HTML.$RESULT; 
		$this->content->pagebody = $pagebody->get_html();
		$this->content->pagebody .= $REPORT_HTML;
		$this->content->pagebody .= sprintf('<br><div>Run Date : %s<div>',$rundate);
		$this->content->pagebody .= sprintf('<div>Run By : %s</div>',Auth::instance()->get_user()->idname);
		$this->content->pagebody .= "</div>";
	}
	
	public function input_form($orders)
	{
		$firstpass = true; $input_hdr =""; $input_flds ="";
		$RESULT = '<div id="e">'."\n";
		$RESULT .= "\n".'<table id="potable" class="tablesorter" border="0" cellpadding="0" cellspacing="1" >'."\n";
		foreach($orders as $row => $linerec)
		{	
			//$linerec = (array)$linerec;
			$header = ''; $data = '';
			foreach ($linerec as $key => $value)
			{
				if($firstpass)
				{
					$header .= '<th>'.$key.'</th>'; 
				}
			
			if ($key == "id")
			{
				$data .= sprintf('<td style="font-size:1.2em;">%s</td>',$value); 
				$input_flds = sprintf('<td><input style="text-align: right;" class="input-i" type="text" id="%s_amt" value="0.00" onkeyup="UnCheck();"></td>',$value);
			}
			else
			{
				$data .= sprintf('<td style="font-size:1.2em;">%s</td>',$value); 
			}
		}
		
		if($firstpass)
		{
			$input_hdr = '<th  style="text-align: left;">allocation</th>';
			$header = "\n".'<thead>'."\n".'<tr>'.$header.$input_hdr.'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
			$RESULT .=$header;
		}
		$data = '<tr>'.$data.$input_flds.'</tr>'."\n"; 
		$RESULT .= $data;
		$firstpass = false;
	}
	$RESULT .='</tbody>'."\n".'</table>'."\n".'</div>'."\n";
	$RESULT .= <<<_TEXT_
	<script>
		$(
			function()
			{	 
				$("#potable").tablesorter({sortList:[[0,0]], widgets: ['zebra']});
				$("#options").tablesorter({sortList: [[0,0]], headers: { 3:{sorter: false}, 4:{sorter: false}}});
			}
		);
	</script>
_TEXT_;
	return $RESULT;
	}

}//End Controller_Core_Report_Receivables
