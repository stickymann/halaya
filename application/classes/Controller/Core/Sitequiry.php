<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Global enquiry controller, this where all the enquiry magic happens. 
 *
 * $Id: Sitequiry.php 2012-12-31 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sitequiry extends Controller_Include
{
	public $template = 'site.view';
    public $enqparam = array();
    public $sqlfiles_r = array();
	public $model;

	public function __construct($controller)
    {
       	if(!Auth::instance()->logged_in())
		{
			Controller_Core_Site::redirect_to_login();	
		}
		parent::__construct();
		$this->model = new Model_EnqDB();
		$this->sitemodel = new Model_SiteDB();
        $this->controller = $controller;
		$this->viewable =  false;
		$this->printable = false;
		$this->template->head = '';
		$this->template->userbttns = '';
		$this->refresh_url = '';
		$this->refresh_icon = '';
		$baseurl = URL::base(false,'http');
		$this->refresh_icon = $baseurl."media/img/site/refresh020.png";
		$this->idname = Auth::instance()->get_user()->username;
		
		//$this->template->menutitle = $this->param['enquiry_header']; 
		$this->template->menutitle = '';
		$this->template->content = '';
		
		//add stylesheets and global scripts
		$this->htmlhead = new Controller_Core_Sitehtml( $this->get_htmlhead() );

		//get enquiry param from database 
		$this->enqparam = $this->get_enquiry_controller_params($controller);
		$this->enquirydef_id = $this->enqparam['enquirydef_id'];
//--- print "<b>[DEBUG]---></b> "; print_r($this->enqparam); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		
		$this->enqparam['fieldnames'] = array(); $this->enqparam['labels']  = array();  $this->enqparam['filterfields'] = array(); 
		$this->get_enquiry_formfields($controller);
		$this->enqparam['filterfields'] = $this->sift_filter_fields();
		
		$sc = new Controller_Core_Sitecontrol( $this->enquirydef_id, $controller, $this->idname );
		$perm = $sc->get_available_input_permissions();
		if($perm['vw']) {$this->viewable = true;}
		if($perm['pr']) {$this->printable = true;}
		//$this->viewable = true;
	
	}
	
	function process_request()
	{
		$this->pagerheader();
		if($this->viewable)
		{	
			$this->filter_form();
		}
	}
	
	function get_htmlhead()
	{	
		$head  = sprintf('%s',HTML::style( $this->css['site'], array('screen') ))."\n"; 
		$head .= sprintf('%s',HTML::style( $this->css['easyui_gray'], array('screen') ))."\n"; 
		$this->stylesheet = $head;
		$head .= sprintf('%s',HTML::style( $this->css['easyui_icon'], array('screen') ))."\n"; 
		$head .= sprintf('%s',HTML::style( $this->css['tablesorterblue'], array('screen') ))."\n"; 
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['jquery']) ))."\n";
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['tablesorter']) ))."\n";
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['easyui']) ))."\n";
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['siteutils']) ))."\n";
		$head .= sprintf('%s',HTML::script( $this->randomize($this->js['enquiry']) ))."\n";
		return $head;	
	}
	
	public function action_spage()
	{
		$this->pagerheader();
		if($this->viewable)
		{	
			$op = $_REQUEST['op'];
			$controller = $_REQUEST['controller'];
			$idfield = $_REQUEST['fields'];
			$value	 = $_REQUEST['lkvals'];
			$baseurl = URL::base(TRUE,'http');
			
			$url   = sprintf('page?op=%s&controller=%s&fields=%s&lkvals=%s&page=1',$op,$controller,$idfield,$value);
			$r_url = sprintf('page?op=%s&controller=%s&fields=%s&lkvals=%s&page=1',$op,$controller,$idfield,$value);

			//$r_url = sprintf('index.php/%s?op=%s&controller=%s&fields=%s&lkvals=%s',$controller,$op,$controller,$idfield,$value);
			$this->refresh_url = $baseurl."/".$r_url;
			$pagebody = new Controller_Core_Sitehtml();
			$pagebody->add('<div id="enq_display">');
			$pagebody->add('<iframe src="'.$url.'" frameborder=0 width="100%" height="1200px" scrolling="auto"></iframe>');

			$pagebody->add('</div>');
			$this->content->pagebody = $pagebody->get_html();
			$this->set_page_content($this->htmlhead->get_html(),$this->content);
		}
	}

	public function pagerheader()
	{
		$this->content	= new View('enquiry/default_enquiry_shell');
		$this->content->pageheader = 'Enquiry - '.$this->enqparam['enqheader'];
		$this->content->pagebody = "";
		$this->set_page_content($this->htmlhead->get_html(),$this->content);
	}
	
	public function filter_form()
	{
		$this->htmlhead->add($this->insert_head_script());
		$labels = ""; $fields = "";
		$pagebody = new Controller_Core_Sitehtml();
		if($this->enqparam['showfilter'] == 1)
		{
			$field = $this->enqparam['fieldnames']; $label = $this->enqparam['labels'];
			$html  = sprintf('<div id="ne_ff"><form><fieldset>')."\n";
			$html .= sprintf('<legend>Search Filter</legend>')."\n";
			$html .= sprintf('<table>')."\n";
			
			
			$count = 1;
			foreach($this->enqparam['filterfields'] as $key => $value)
			{
				$labels .= sprintf('<td><label for="%s">%s</label></td>',$field[$key],$label[$key]);
				$fields .= sprintf('<td><input type="text" id="%s" size="12"></td>',$field[$key]);
				if(($count % 7) == 0)
				{
					$html .= "<tr>".$labels."</tr>\n<tr>".$fields."</tr>";
					$labels = ""; $fields = "";
				}
				$count++;
			}
			$html .= "<tr>".$labels."</tr>\n<tr>".$fields."</tr></table></fieldset>\n";
			$html .= sprintf('<input type="hidden" id="controller" value="%s">',$this->enquirydef_id);
			$pagebody->add($html);
			$pagebody->add('</form></div>');
		}
		$pagebody->add('<div id="enq_display"></div>');
		$this->content->pagebody = $pagebody->get_html();
		$this->set_page_content($this->htmlhead->get_html(),$this->content);
	}

	public function insert_head_script()
	{
		$changefuncs = "\n"; $lkvals = ""; 
		foreach($this->enqparam['filterfields'] as $key => $value)
		{
			$changefuncs .= sprintf("\t\t\t$('#%s').keyup(function() {openIFrame();});",$value)."\n";
			$lkvals .=  sprintf("$('#%s').val()+','+",$value);
		}
		$lkvals = substr_replace($lkvals, '', -5); $lkvals .=";";
		$filterfields = join(',',$this->enqparam['filterfields']);
$HTML=<<<_HTML_
		<script type="text/javascript">
		
		$(document).ready(function()
		{
			$changefuncs
			openIFrame();
		});
		
		function openIFrame()
		{
			var ctrlr = $('#controller').val();
			var view = $('#customview').val();
			var fields = "$filterfields";
			var lkvals = $lkvals 
			var url =  ctrlr + "/page?op=like&controller=" + ctrlr + "&fields=" + fields + "&lkvals=" + lkvals + "&page=1";
			//alert(url);
			var iframe = '<iframe src="'+ url +'" frameborder=0 width="100%" height="1200px" scrolling="none"></iframe>';
			$('#enq_display').html(iframe);
		}
		</script>
_HTML_;
		return $HTML;
	}

	public function action_page()
    {
		//$pagenum = strtoupper( $this->request->param('opt') );
		if($this->viewable)
		{	
			$request = $_REQUEST;
			//$config['refresh_url'] = str_replace("#NO#",$pagenum,$this->refresh_url);
			$config['refresh_url'] = $this->refresh_url;
			$config['refresh_icon'] = $this->refresh_icon;
			if( isset($request['page']) ) { $pagenum = $request['page']; } else { $pagenum = 1; }

			/*from enquirydefs*/
			$table = $this->enqparam['tablename'];
            $table = $this->sitemodel->set_vw_table($table);
            $idfield = $this->enqparam['idfield'];
			$orderarr = array($idfield => 'ASC');
			$view = $this->enqparam['view']; 
			$this->template->content = new View($view);
			$sql_offset = $pagenum - 1;

            if($request['lkvals'] == ",")
			{
				//$querystr = sprintf('select %s from %s',$idfield,$table);
                $countstr = sprintf('SELECT COUNT(%s) AS counter FROM %s',$idfield,$table);
                $paging = Pagination::factory(array
				(
					'total_items' => $this->model->count_records($countstr),
					'items_per_page' => 1
				));
                $querystr = sprintf('SELECT %s FROM %s LIMIT %s OFFSET %s',join(',',$this->enqparam['fieldnames']),$table,$paging->items_per_page,$sql_offset);
				$this->template->content->enquiryrecords =  $this->model->browse($querystr);
			} 
			else
			{
				$fields = $request['fields'];
				$lkvals = $request['lkvals'];
				$op = $request['op'];
				$filter = ""; $where = "";
				$lkarr = array_combine(preg_split('/,/',$fields),preg_split('/,/',$lkvals));
				foreach($lkarr as $key => $value)
				{
					if( $value != "" )
					{   
						$where = "WHERE";
						if($op == 'eq'){$filter .= sprintf('%s = "%s%s" AND ',$key,$value,"%");}
						else if($op == 'like')
						{
							$filter .= sprintf('%s LIKE "%s%s" AND ',$key,$value,"%");
						}
					}
				}
				$filter = substr_replace($filter, '', -5);
						
				//$querystr = sprintf('select %s from %s %s %s',join(',',$this->enqparam['fieldnames']),$table,$where,$filter);
				$countstr = sprintf('SELECT COUNT(%s) AS counter FROM %s %s %s',$idfield,$table,$where,$filter);
                $paging = Pagination::factory( array
				(
					'total_items' => $this->model->count_records($countstr),
					'items_per_page' => 1
				));
				$paging->sql_offset = $sql_offset;
                $querystr = sprintf('SELECT %s FROM %s %s %s LIMIT %s OFFSET %s',join(',',$this->enqparam['fieldnames']),$table,$where,$filter,$paging->items_per_page,$sql_offset);
                $this->template->content->enquiryrecords =  $this->model->browse($querystr);
			}
   
			// Render the page links
			$this->template->head   = $this->stylesheet;
			$config['enqheader']	= $this->enqparam['enqheader'];
			$config['controller']	= $this->controller;
			$config['total_items']	= $paging->total_items;
			$config['idname']		= Auth::instance()->get_user()->idname;
			$config['viewable']		= $this->printable;
			$config['printable']	= $this->printable;
			$config['printuser']	= $this->enqparam['printuser'];
			$config['printdatetime']= $this->enqparam['printdatetime'];
			$config['type']			= 'enquiry';
			$config['query']		= $querystr."<hr>";				

			$this->template->content->config = $config;
			$this->template->content->labels = $this->enqparam['labels'];
			$this->template->content->pagination = $paging->render();
		}
	}

	public function set_page_content($head='',$body='')
	{
		$this->template->head = $head;
		$this->template->content = $body;
	}

	public function get_enquiry_controller_params($controller)
	{
		$arrobj = $this->model->get_enquiry_params($controller);
		$arr = (array) $arrobj[$controller];
		return $arr;
	}

	public function get_enquiry_formfields($controller)
	{
		$this->model->get_enq_formfields($controller,$this->enqparam['fieldnames'],$this->enqparam['labels'],$this->enqparam['filterfields']);
	}
	
	public function sift_filter_fields()
	{
		foreach ($this->enqparam['filterfields'] as $key => $value)
		{		
			if (preg_match('/yes/i', $value) ||  $value==1) 
			{
				$arr[$key] = $key;
			}
		}
		return $arr;
	}

	public function action_enquirycustom()
	{
		$sc = new Controller_Core_Sitecontrol();
		$pagehead = new Controller_Core_Sitehtml( $this->htmlhead->get_html() );
		$pagebody = new Controller_Core_Sitehtml( $sc->show_tabs( $this->enquirydef_id, $this->controller, 'custom') );
		$this->set_page_content( $pagehead->get_html(), $pagebody->get_html() );
	}

} //End Controller_Core_Sitequiry
