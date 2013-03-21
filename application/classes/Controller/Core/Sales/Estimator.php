<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a estimates of sales order. 
 *
 * $Id: Estimator.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Estimator extends Controller_Include
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
		$this->param['param_id']	= "core_sales_estimator";
		$this->param['controller']	= "estimator";
		$this->param['inputview']	= "estimator.view";
		$this->param['pageheader']	= "Estimator";
		$this->param['htmlhead']	= $htmlhead->get_html();
	}	
		
	public function action_index()
    {
      $this->process_request();
	}

	public static function redirect_to_login()
	{
		URL::redirect('autologout');
	}

	function process_request()
	{
		if(!$this->OBJPOST)
        {
			$this->input();
        }
		else
		{
			if($this->OBJPOST['submit']=='Create Order')
			{
				$this->create_order();
			}
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
		$head .= sprintf('%s',HTML::script( $this->randomize('media/js/core.estimator.js') ))."\n";
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
		$formtag = Form::open($this->param['param_id'],array('id'=>$this->param['param_id'],'name'=>$this->param['param_id']));
		$pagebody = new Controller_Core_Sitehtml( $formtag );
		$pagebody->add( $this->summary_header() );
		$pagebody->add('<div id="ibg">');
		$pagebody->add( $this->order_details_subform() );
		$pagebody->add('<input type="hidden" size=100 id="order_details" name="order_details" value=""/>');
		
		$pagebody->add( sprintf('<input type="hidden" id="js_idname" name="js_idname" value="%s"/>',$this->template->username) );
		$pagebody->add('<input type="hidden" id="branch_id" name="branch_id" value=""/>');
		$pagebody->add('<br><div id="inventory_status"></div>');
		$pagebody->add("<br></div></form>");
		$pagebody->add("<div id='pofilter'></div>\n");
		$pagebody->add("<div id='polistresult'></div>\n");
		//$pagebody->add("</div>\n");
		
		$content->pagebody = $pagebody->get_html();
		$this->param['htmlbody'] = $content;
	}
	
	function order_details_subform()
	{
		$sitectlr = new Controller_Core_Site("order");
		$sitectlr->auto_render = FALSE;
		return $sitectlr->create_subform("order_details",0);
	}

	function summary_header()
	{
		$HTML = <<<_HTML_
		<div id="estimator_info">
			<div id="estimator_bttns"><input type="submit" id="submit" name="submit" value="Create Order" class="bttn" /></div>
			<div id="estimator_summary">
			<table width="100%">
				<tr>
					<td style="text-align:left; padding 0px 5px 0px 0px;"><b>Sub Total :</b> 0.00</td>
					<td style="text-align:left; padding 0px 5px 0px 0px;"><b>Tax Total :</b> 0.00</td>
					<td  style="text-align:left; padding 0px 5px 0px 0px;"><b>GRAND TOTAL : 0.00</b></td>
				</tr>
				</table>	
			</div>
		</div>

_HTML_;
		return $HTML;
	}

	function create_order()
	{
		unset($this->OBJPOST['submit']);
		//set up new order record and insert into order table 
		$order = new Controller_Core_Sales_Order();
		$order->auto_render = FALSE;
		
		$arr = $order->param['primarymodel']->create_blank_record($order->param['tb_live'],$order->param['tb_inau']);
		$arr = (array) $arr;
		
		$baseurl = URL::base(TRUE,'http');
		$url = sprintf('%score_ajaxtodb?option=altid&controller=order&prefix=ORD&ctrlid=%s',$baseurl,$arr['id']);
		$order_id = Controller_Core_Sitehtml::get_html_from_url($url);
		$order_details = str_replace("%ORDERID%",$order_id, $this->OBJPOST['order_details']);
		
		$idname = Auth::instance()->get_user()->idname;
		$url = sprintf('%score_ajaxtodb?option=userbranch&idname=%s',$baseurl,$idname);
		$branch_id = Controller_Core_Sitehtml::get_html_from_url($url);

		$arr['order_id']				= $order_id;
		$arr['branch_id']				= $branch_id;
		$arr['order_status']			= "NEW";
		$arr['order_details']			= $order_details;
		$arr['order_date']				= date('Y-m-d'); 
		$arr['quotation_date']			= date('Y-m-d'); 
		$arr['status_change_date']		= date('Y-m-d'); 
		$arr['inventory_checkout_type'] = "AUTO";
		$arr['inventory_checkout_status'] = "NONE";
		$arr['inputter']				= $idname;
		$arr['input_date']				= date('Y-m-d H:i:s'); 
		$arr['authorizer']				= "";
		$arr['auth_date']				= "0000-00-00"; 
		$arr['record_status']			= "IHLD";
		$arr['current_no']				= "0";
		
		//createSubFormRecords() requires $this->OBJPOST array
		$order->OBJPOST = $arr;
		if ( $order->param['primarymodel']->update_record($order->param['tb_inau'],$order->OBJPOST) )
		{
			$order->create_subform_records();
			$this->param['recordstatusmsg'] = "<p><b>&nbsp Record  [ <a href='".$order->param['param_id']."/index/".$order->OBJPOST['order_id']."'>".$order->OBJPOST['order_id']."</a> ] added to ".$order->OBJPOST['record_status']." successfully, <a href=".$order->param['param_id'].">Continue.</a></b></p>"; 
		}
		else
		{
			$this->param['recordstatusmsg'] = $order->param['primarymodel']->get_db_err_msg();
		}
		$this->create_order_status_info();
	}

	function create_order_status_info()
	{
		$content = new View($this->param['inputview']);
		//add page/form header
		$content->pageheader = $this->param['pageheader'];
		$content->pagebody	 = $this->param['recordstatusmsg'];
		$this->param['htmlbody'] = $content;
		$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
	}
}
?>
