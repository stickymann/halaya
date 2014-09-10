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
		$this->rptparam['htmlhead'] .= HTML::script( $this->randomize('media/js/hndshkif.dlorderreport.js') );
		$this->dlor = new Controller_Hndshkif_Orders_Dlorderreport();
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
			$batch_table = $this->dlor->config['tb_dlorderbatchs'];
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
			$pagebody = $this->dlor->create_report($batch_id);
			$this->content->pagebody = $pagebody;
		}
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
		$table 	 = $this->dlor->config['tb_dlorderbatchs'];
		$rfield	 = "batch_id";
		$sfield	 = "batch_date";
		$current_date = date('Y-m-d');
		
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
