<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Displays interface status and start/stop options. 
 *
 * $Id: Interfacestatus.php 2014-03-03 17:00:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
class Controller_Hndshkif_Settings_Interfacestatus extends Controller_Include
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
			URL::redirect('autologout');
		}
		
		$htmlhead = new Controller_Core_Sitehtml( $this->get_htmlhead() );
		
		$this->sitedb = new Model_SiteDB;
		$this->template->head = '';
		$this->template->content = '';
		$this->template->menutitle = '';
		$this->template->userbttns = '';
		
		$this->OBJPOST = $_POST;
		$this->param['param_id']	= "hndshkif_settings_interfacestatus";
		$this->param['controller']	= "interfacestatus";
		$this->param['inputview']	= "default_input";
		$this->param['pageheader']	= "Interface Status";
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
		if(!$this->OBJPOST)
        {
			$this->input();
        }
		else
		{
			/*
			if($this->OBJPOST['submit']=='Create Order')
			{
				$this->create_order();
			}
			*/
		}
	}
		
	function get_htmlhead()
	{	
		$head  = sprintf('%s',HTML::style( $this->css['site'], array('screen') ))."\n"; 
		$head .= sprintf('%s',HTML::style( $this->css['tablesorterblue'], array('screen') ))."\n"; 
		$head .= sprintf('%s',HTML::style( $this->css['easyui_gray'], array('screen') ))."\n"; 
		$head .= sprintf('%s',HTML::style( $this->css['easyui_icon'], array('screen') ))."\n"; 
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['jquery']) ))."\n";
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['tablesorter']) ))."\n";
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['easyui']) ))."\n";
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['siteutils']) ))."\n";
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['popout']) ))."\n";
		$head .= sprintf('%s',HTML::script( $this->randomize('media/js/hndshkif.interfacestatus.js') ))."\n";
		return $head;	
	}
 
	public function set_page_content($_head='',$_body='')
	{
		$this->template->head = $_head;
		$this->template->content = $_body;
	}

	function input()
	{
		$this->input_form();
		$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
	}

	function input_form()
	{
		$content = new View($this->param['inputview']);
		//add page/form header
		$content->pageheader = $this->param['pageheader'];
		//$formtag = '<form method="POST">'."\n";
		$formtag = "";
		$pagebody = new Controller_Core_Sitehtml( $formtag );
		$pagebody->add( $this->summary_info() );
				
		$pagebody->add( sprintf('<input type="hidden" id="js_idname" name="js_idname" value="%s"/>',$this->template->username) );
		$pagebody->add('<input type="hidden" id="branch_id" name="branch_id" value=""/>');
		$pagebody->add('<br><div id="hsi_status"></div>');
		//$pagebody->add("<br></div></form>");
		$pagebody->add("<div id='pofilter'></div>\n");
		$pagebody->add("<div id='polistresult'></div>\n");
		//$pagebody->add("</div>\n");
		
		$content->pagebody = $pagebody->get_html();
		$this->param['htmlbody'] = $content;
	}
	
	function summary_info($RUNNING=FALSE)
	{
		if($RUNNING)
		{ 
			$status = sprintf('<span id="ifstatus" style="color:green">%s</span>',"RUNNING"); 
			$pid 	= sprintf('<span id="ifpid" >%s</span>',""); 
			$bttntxt = "STOP "; 
		} 
		else 
		{ 
			$status = sprintf('<span id="ifstatus" style="color:red">%s</span>',"STOPPED"); 
			$pid	= sprintf('<span id="ifpid" >%s</span>',""); 
			$bttntxt = "START"; 
		}
		
		$HTML = <<<_HTML_
		<div id="estimator_summary">
			<table>
				<tr>
					<td style="text-align:left; padding: 5px 50px 5px 5px;">Status :</td>
					<td style="text-align:right; padding 5px;"><b>$status</b></td>
				</tr>
				<tr>
					<td style="text-align:left; padding: 0px 50px 0px 5px;">Process Id :</td>
					<td style="text-align:right; padding 5px;"><b>$pid</b></td>
				</tr>
					<tr>
					<td style="text-align:left; padding: 5px 50px 5px 5px;">Start / Stop:</td>
					<td style="text-align:right; padding 5px;">
						<input type="submit" id="startstop" name="startstop" value="$bttntxt" />
					</td>
				</tr>
			</table>	
		</div>
_HTML_;
		return $HTML;
	}

} //End Controller_Hndshkif_Settings_Interfacestatus
