<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Terminates a running scheduler process.
 *
 * $Id: terminateprocess.php 2014-09-22 03:04:15 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */

class Controller_Hndshkif_Settings_Terminateprocess extends Controller_Include
{
	public $template	= 'site.view'; 
	public $auto_render = TRUE; //defaults to true, renders the template after the controller method is done
	public $OBJPOST		= array();
	public $param		= array();

	public function __construct()
    {
		parent::__construct();
		
		if(Auth::instance()->logged_in())
		{
			$this->template->username = Auth::instance()->get_user()->username;
		}
		else
		{
			$this->template->username = 'expired';
			HTTP::redirect('autologout');
		}
		
		$htmlhead = new Controller_Core_Sitehtml( $this->get_htmlhead() );
		
		$this->sitedb = new Model_SiteDB;
		$this->template->head = '';
		$this->template->content = '';
		$this->template->menutitle = '';
		$this->template->userbttns = '';
		
		$this->OBJPOST = $_POST;
		$this->param['param_id']	= "hndshkif_settings_terminateprocess";
		$this->param['controller']	= "terminateprocess";
		$this->param['inputview']	= "default_input";
		$this->param['pageheader']	= "Terminate Process";
		$this->param['htmlhead']	= $htmlhead->get_html();
	}	
		
	public function action_index()
    {
      $this->process_request();
	}

	public static function redirect_to_login()
	{
		HTTP::redirect('autologout');
	}

	function process_request()
	{
		if( isset($_REQUEST['pid']) )
        {
			$this->kill_process($_REQUEST['pid']);
        }
		$this->input();
	}
		
	function get_htmlhead()
	{	
		$head  = sprintf('%s',HTML::style( $this->css['site'], array('screen') ))."\n"; 
		$head .= sprintf('%s',HTML::style( $this->css['tablesorterblue'], array('screen') ))."\n"; 
		$head .= sprintf('%s',HTML::style( $this->css['easyui_gray'], array('screen') ))."\n"; 
		$head .= sprintf('%s',HTML::style( $this->css['easyui_icon'], array('screen') ))."\n"; 
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['jquery']) ))."\n";
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['tablesorter']) ))."\n";
		return $head;	
	}
 
	public function set_page_content($_head='',$_body='')
	{
		$this->template->head = $_head;
		$this->template->content = $_body;
	}

	public function input()
	{
		$this->input_form();
		$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
	}

	public function input_form()
	{
		$content = new View($this->param['inputview']);

		$content->pageheader = $this->param['pageheader'];
		$formtag = "";
		$pagebody = new Controller_Core_Sitehtml( $formtag );
		$pagebody->add( $this->form_layout() );
				
		$pagebody->add( sprintf('<input type="hidden" id="js_idname" name="js_idname" value="%s"/>',$this->template->username) );
		$pagebody->add('<input type="hidden" id="branch_id" name="branch_id" value=""/>');
		$pagebody->add("<div id='pofilter'></div>\n");
		$pagebody->add("<div id='polistresult'></div>\n");
	
		$content->pagebody = $pagebody->get_html();
		$this->param['htmlbody'] = $content;
	}
	
	public function form_layout()
	{
		$schduler = new Controller_Hndshkif_Settings_Scheduler();
		$querystr = sprintf('SELECT fullpath FROM %s', $schduler->param['tb_live']);
		$result = $schduler->model->execute_select_query($querystr);
		$HTML = "";
		$pid_r = array();
		$output_format = "%p;%a";
		
		
		foreach($result as $index => $obj)
		{
			$fullpath =  $obj->fullpath;
			$basename = basename($obj->fullpath);
			$HTML .= $basename."<br>";
			$output = array();
			$cmd = sprintf('ps -axo "%s" | grep -i "%s"',$output_format,$basename);
			exec($cmd,$output);
			//parse original ps output and remove grep entries
			foreach($output as $index => $value)
			{
				if( preg_match('/grep /i', $value) ) 
				{  
					unset($output[$index]); 
				}
				else
				{
					array_push($pid_r,$output[$index]);
				}
			}
			//$pid_r = array_merge($pid_r,$output);
		}

		$TABLE_ROWS = "<tbody>\n";
		$baseurl = URL::base();
		foreach($pid_r as $index => $value)
		{
			$arr = explode(";",$value);
			$kill_url = sprintf('%sindex.php/%s?pid=%s',$baseurl,$this->param['param_id'],$arr[0]);
			$TABLE_ROWS .= sprintf('<tr valign="top"><td>%s</td><td>%s</td><td><a href="%s">kill</a></td></tr>',$arr[0],$arr[1],$kill_url)."\n";
		}
		$TABLE_ROWS .= "</tbody>\n";
		
		$HTML = <<<_HTML_
<div style="backgroun-color:ffffff;">
<table id="table" class="tablesorter" border="0" cellpadding="0" cellspacing="1" width "50%">	
<thead>
	<tr><th>PID</th><th>SCRIPT</th><th>ACTION</th></tr>
</thead>
$TABLE_ROWS
</table>
</div>
<script>
	$(
		function()
		{	 
			$("#table").tablesorter({sortList:[[0,0]], widgets: ['zebra']});
			$("#options").tablesorter({sortList: [[0,0]], headers: { 3:{sorter: false}, 4:{sorter: false}}});
			}
		);
</script>
_HTML_;
		return $HTML;
	}
	
	public function kill_process($pid)
	{
		$cmd = sprintf('sudo kill -9 %s',$pid);
		exec($cmd,$output);
	}

} //End Controller_Hndshkif_Settings_Terminateprocess
