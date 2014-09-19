<?php defined('SYSPATH') or die('No direct script access.');
/**
 * DacEasy id enerator.
 *
 * $Id: Idgenerator.php 2014-09-19 17:00:49 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
class Controller_Hndshkif_Customers_Idgenerator extends Controller_Include
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
		$this->param['param_id']	= "hndshkif_customers_idgenerator";
		$this->param['controller']	= "idgenerator";
		$this->param['inputview']	= "default_input";
		$this->param['pageheader']	= "Custome Id Generator";
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
		$head .= sprintf('%s',HTML::script( $this->randomize('media/js/hndshkif.idgenerator.js') ))."\n";
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
	
	function form_layout()
	{
		$HTML = <<<_HTML_
<table>
<tr valign="center">
	<td><label for="customer_id">Customer Id</label> :</td>
	<td><input type="text" class="input-i" id="customer_id" name="customer_id" value="" size=20 style="background-color: #ebebe0;" readonly /></td>
</tr>

<tr valign="center">
	<td><label for="customer_type">Customer Type</label> :</td>
	<td>
		<select id="customer_type" name="customer_type">
			<option value="COMPANY">COMPANY  </option>
			<option value="INDIVIDUAL">INDIVIDUAL</option>
		</select>
	</td>
</tr>

<tr valign="center">
	<td><label for="first_name">First Name</label> :</td>
	<td><input type="text" class="input-i" id="first_name" name="first_name" value="" size=50   /></td>
</tr>

<tr valign="center">
	<td><label for="last_name">Last Name</label> :</td>
	<td><input type="text" class="input-i" id="last_name" name="last_name" value="" size=50   /></td>
</tr>
</table>
_HTML_;
		return $HTML;
	}

} //End Controller_Hndshkif_Customers_Idgenerator
