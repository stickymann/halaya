<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Core_Sales_Estimator extends Controller_Include
{
	public $template	= 'site.view'; 
	public $auto_render = TRUE; //defaults to true, renders the template after the controller method is done
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
			url::redirect('autologout');
		}
		
		$htmlhead = new Sitehtml_Controller(html::stylesheet(array('media/css/site','media/css/tablesorterblue',$this->easyui_css,$this->easyui_icon),array('screen','screen','screen','screen')));
		$htmlhead->add(html::script(array($this->jquery_js,$this->easyui_js,'media/js/jquery.tablesorter','media/js/siteutils.js'.$this->randomstring,'media/js/popoutselector.js'.$this->randomstring, 'media/js/estimator.js'.$this->randomstring )));
		
		$this->sitedb = new Site_Model;
		$this->template->head = '';
		$this->template->content = '';
		$this->template->menutitle = '';
		$this->template->userbttns = '';

		$this->param['controller'] = "estimator";
		$this->param['inputview'] = "estimator_view";
		$this->param['pageheader'] = "Estimator";
		$this->param['htmlhead'] = $htmlhead->getHtml();
	}	
		
	public function index()
    {
      $this->processRequest();
	}

	public static function redirectToLogin()
	{
		url::redirect('autologout');
	}

	function processRequest()
	{
		if(!$_POST)
        {
			$this->input();
        }
		else
		{
			if($_POST['submit']=='Create Order')
			{
				$this->create_order();
			}
		}
	}

	public function setPageContent($_head='',$_body='')
	{
		$this->template->head = $_head;
		$this->template->content = $_body;
	}

	function input()
	{
		$this->input_form();
		$this->setPageContent($this->param['htmlhead'],$this->param['htmlbody']);
	}

	function input_form()
	{
		$content = new View($this->param['inputview']);
		//add page/form header
		$content->pageheader = $this->param['pageheader'];
		$formtag = form::open($this->param['controller'],array('id'=>$this->param['controller'],'name'=>$this->param['controller']));
		$pagebody = new Sitehtml_Controller( $formtag );
		$pagebody->add( $this->summary_header() );
		$pagebody->add('<div id="ibg">');
		$pagebody->add( $this->order_details_subform() );
		$pagebody->add('<input type="hidden" size=100 id="order_details" name="order_details" value=""/>');
		$pagebody->add("<br></div></form>");

		$pagebody->add("<div id='pofilter'></div>\n");
		$pagebody->add("<div id='polistresult'></div>\n");
		//$pagebody->add("</div>\n");
		
		$content->pagebody = $pagebody->getHtml();
		$this->param['htmlbody'] = $content;
	}
	
	function order_details_subform()
	{
		$sitectlr = new Site_Controller("order");
		$sitectlr->auto_render = FALSE;
		return $sitectlr->createSubForm("order_details",0);
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
		unset($_POST['submit']);
		//set up new order record and insert into order table 
		$order = new Order_Controller();
		$order->auto_render = FALSE;
		
		$arr = $order->param['primarymodel']->createBlankRecord($order->param['tb_live'],$order->param['tb_inau']);
		$arr = (array) $arr;
		
		$baseurl = url::base(TRUE,'http');
		$url = sprintf('%sajaxtodb?option=orderid&controller=order&prefix=ORD&ctrlid=%s',$baseurl,$arr['id']);
		$order_id = Sitehtml_Controller::getHTMLFromUrl($url);
		$order_details = str_replace("%ORDERID%",$order_id, $_POST['order_details']);
		
		$idname = Auth::instance()->get_user()->idname;
		$url = sprintf('%sajaxtodb?option=userbranch&idname=%s',$baseurl,$idname);
		$branch_id = Sitehtml_Controller::getHTMLFromUrl($url);

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
		
		//createSubFormRecords() requires $_POST array
		$_POST = $arr;
		if ( $order->param['primarymodel']->updateRecord($order->param['tb_inau'],$_POST) )
		{
			$order->createSubFormRecords();
			$this->param['recordstatusmsg'] = "<p><b>&nbsp Record  [ <a href='".$order->param['controller']."/index/".$_POST['order_id']."'>".$_POST['order_id']."</a> ] added to ".$_POST['record_status']." successfully, <a href=".$this->param['controller'].">Continue.</a></b></p>"; 
		}
		else
		{
			$this->param['recordstatusmsg'] = $order->param['primarymodel']->getDBErrMsg();
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
		$this->setPageContent($this->param['htmlhead'],$this->param['htmlbody']);
	}
}
?>
