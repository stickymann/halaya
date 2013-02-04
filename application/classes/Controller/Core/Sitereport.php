<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Global report controller, this where all the reporting magic happens. 
 *
 * $Id: Sitereport.php 2012-12-31 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sitereport extends Controller_Include
{
	public $template	= "site.view"; //defaults to template but you can set your own view file
	public $param		= array();
	public $form		= array();
	public $formdata	= array();
	public $label		= array();  
	public $formopts	= array();  
	public $popout		= array();
	public $colon		= " :";
	
	public function __construct($controller)
    {
       	if(!Auth::instance()->logged_in())
		{
			Controller_Core_Site::redirect_to_login();	
		} 
			
		parent::__construct();
		$this->db = Database::instance();
		$this->model = new Model_ReportDB();
		$this->sitemodel = new Model_SiteDB;
				
		$this->controller = $controller;
		$this->viewable =  false;
		$this->printable = false;
		$this->template->head = '';
		$this->template->content = '';
		$this->template->menutitle = '';
		$this->template->userbttns = '';
		
		$this->rptparam = $this->get_report_controller_params(trim($controller));
//print "<b>[DEBUG]---></b> "; print_r($this->rptparam); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		
		
		$sc = new Controller_Core_Sitecontrol($this->rptparam['reportdef_id']);
		$perm = $sc->get_available_input_permissions();
//print "<b>[DEBUG]---></b> "; print_r($perm); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		
		if($perm['vw']) {$this->viewable = true;}
		if($perm['pr']) {$this->printable = true;}
	
		if(Auth::instance()->logged_in())
		{
			$this->template->username = Auth::instance()->get_user()->username;
		}
		else
			$this->template->username = 'expired';
			
		// By adding this we are making the database object available to all controllers that extend Report_Controller
		$user = ORM::factory('User')->where('username','=',$this->template->username)->find();
		$this->template->idname = $user->idname;
		
		// Application shared stylesheets and javascripts
		$htmlhead = new Controller_Core_Sitehtml;
		
		// External
		$htmlhead->add( HTML::style($this->css['tablesorterblue'], array('screen')) );
		$htmlhead->add( HTML::style($this->css['easyui'], array('screen')) );
		$htmlhead->add( HTML::style($this->css['easyui_icon'], array('screen')) );
		$htmlhead->add( HTML::style($this->css['datepick'], array('screen')) );

		// Internal
		$htmlhead->add( HTML::style($this->css['site'], array('screen')) );

		// External
		$htmlhead->add( HTML::script($this->js['jquery']) );
		$htmlhead->add( HTML::script($this->js['easyui']) );
		$htmlhead->add( HTML::script($this->js['datepick']) );
		$htmlhead->add( HTML::script($this->js['tablesorter']) );
		$htmlhead->add( HTML::script($this->js['tablesorterpager']) );
		$htmlhead->add( HTML::script($this->js['datevalidate']) );
		
		// Internal
		$htmlhead->add( HTML::script($this->js['siteutils']) );
		$htmlhead->add( HTML::script($this->js['enquiry']) );
		$htmlhead->add( HTML::script($this->js['popout']) );
		
		$this->rptparam['htmlhead'] = $htmlhead->get_html();
	}

	function process_request()
	{
		$this->page_initialize();
		if($this->viewable)
		{	
			$this->set_pageheader();
			if($this->rptparam['showfilter'])
			{
				/*setup form field,  fill arrays with default value*/
				$this->formdata = $this->get_report_controller_formdefs($this->controller);
				$this->set_formfields_and_labels();
				if($_POST)
				{
					$this->report_run();
				}
				else
				{
					$this->report_filter();
				}
			}
			else
			{
				$this->report_run();
			}
		}
		$this->set_page_content($this->rptparam['htmlhead'],$this->content);
	}
	
	public function page_initialize()
	{
		$this->content	= new View($this->rptparam['view']);
		$this->content->pageheader = 'Report - '.$this->rptparam['rptheader'];
		$this->content->pagebody = '<span style="color:red;"><b>Insufficient Permissions</b></span></td><td>';
	}
	
	
	public function set_pageheader()
	{
		$this->content->pageheader = 'Report - '.$this->rptparam['rptheader'];
		$this->content->pagebody = "";
	}

	public function set_page_content($_head='',$_body='')
	{
		$this->template->head = $_head;
		$this->template->content = $_body;
	}

	public function get_report_controller_params($controller)
	{
		$arrobj = $this->model->get_report_params($controller);
		$arr = (array) $arrobj[$controller];
		return $arr;
	}
	
	function get_report_controller_formdefs($controller)
	{
		$arrobj = $this->model->get_report_formdefs($controller);
		return $arrobj;
	}

	function set_formfields_and_labels()
	{
		$formfields = new SimpleXMLElement($this->formdata->formfields);
		foreach ($formfields->field as $field)
		{		
			$key = sprintf('%s',$field->name);
			$this->form[$key] = sprintf('%s',$field->value);
			$this->label[$key] = sprintf('%s',$field->label);
			$this->formopts[$key]['options'] = sprintf('%s',$field->options);
			$this->formopts[$key]['inputtype'] = sprintf('%s',$field->type);
			if($field->popout)
			{
				$this->popout[$key]['enable'] = sprintf('%s',$field->popout->enable);
				$this->popout[$key]['table'] = sprintf('%s',$field->popout->table);
				$this->popout[$key]['selectfields'] = sprintf('%s',$field->popout->selectfields);
				$this->popout[$key]['idfield'] = sprintf('%s',$field->popout->idfield);
			}
		}
	}

	public function report_filter()
	{
		if(!$this->form)
		{
			$str = '<div class="frmmsg">A Filter Form Error Occurred, Please Try Again.</div>';
			$pagebody = new Sitehtml_Controller($str);
		}
		else
		{
			$pagebody = new Controller_Core_Sitehtml(Form::open($this->rptparam['reportdef_id'],array('id'=>$this->rptparam['reportdef_id'],'name'=>$this->rptparam['reportdef_id'])));
			$pagebody->add("<div id='i'>\n");
			$pagebody->add("<table>\n");
			$pagebody->add("<tr valign='center'><td colspan=2><input type='submit' id='submit' name='submit' value='Create Report' class='bttn'/></td></tr>\n");
			foreach($this->form as $key => $value)
			{
				$POPOUT_HTML =""; $DATEICON_HTML=""; $table =""; $fields=""; $idstr=""; $po_type=""; $disabled="" ; $style="";
				if(!(isset($this->formopts[$key]['options']))){unset($this->form[$key]);unset($_POST[$key]); continue;}
				
				$options = $this->formopts[$key]['options'];
				switch($this->formopts[$key]['inputtype'])
				{	
					case 'input':
					case 'date':
						$POPOUT_HTML = $this->create_popout($key);
						if($this->formopts[$key]['inputtype']=="date")
						{
							$DATEICON_HTML = $this->create_date_popout($key);
						}
						$pagebody->add('<tr valign="center"><td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
						$pagebody->add('<td>');
						$pagebody->add(sprintf('<input type="text" class="input-r" id="%s" name="%s" value="%s" %s />',$key,$key,$this->form[$key],$options));
						$pagebody->add($DATEICON_HTML.$POPOUT_HTML);
						$pagebody->add('</td></tr>'."\n");
					break;
					
					case 'dropdown':
						list($arrval,$arrtxt) = explode("::",$options);
						$selection = array_combine(explode(",",$arrval),explode(",",$arrtxt));
						$pagebody->add('<tr valign="center"><td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
						$pagebody->add('<td>');
						$pagebody->add(Form::select($key, $selection, NULL, array('id'=>$key) ))."\n";
						$pagebody->add('</td></tr>'."\n");
					break;
				}
			}
			
			$pagebody->add("</table>");
			$pagebody->add("</div>");
			$pagebody->add(Form::close());
			$pagebody->add($this->popout_selector_win());
			$this->content->pagebody = $pagebody->get_html();
		}
	}

	public function create_popout($key)
	{
		$POPOUT_HTML = "";
		if(isset($this->popout[$key]))
		{
			if (preg_match('/yes/i', $this->popout[$key]['enable']) || $this->popout[$key]['enable']==1) 
			{
				$fields = sprintf('"%s"',$this->popout[$key]['selectfields']);
				$table	= sprintf('"%s"',$this->popout[$key]['table']);
				$idfield  = sprintf('"%s"',$this->popout[$key]['idfield']);
				$returnfield = sprintf('"%s"',$key);
				$baseurl = sprintf('<img src="%smedia/img/site/%s" align=absbottom>',URL::base(),"lubw020.png");
				$POPOUT_HTML = sprintf('<a href = "javascript:void(0)" onclick=window.popout.SelectorOpen(%s,%s,%s,%s) class="aimg">&nbsp %s &nbsp</a>',$fields,$table,$idfield,$returnfield,$baseurl);
			}
		}
		return $POPOUT_HTML;
	}

	public function create_date_popout($key)
	{
		$baseurl = URL::base();
		$iconurl = $baseurl."media/css/calendar-blue.gif";
		$TEXT=<<<_text_
		<script type="text/javascript">
			$(function() 
			{
				$('#$key').datepick(
				{
					showOnFocus: false, 
					showTrigger: '<span class="dateicon">&nbsp&nbsp<img src="$iconurl" align="absbottom">&nbsp</span>',
					dateFormat: 'yyyy-mm-dd',
					yearRange: '1900:c+100',
					showAnim: '',
					alignment: 'bottomLeft',
					onSelect: function() { $('#$key').focus(); }
				});
			});
	</script>
_text_;
		return $TEXT;
	}
	
	public function popout_selector_win()
	{
		$HTML = <<<_HTML_
		<div id="light" class="white_content" buttons="#light-buttons">
			<div id="pofilter"></div>
			<div id="poresult"></div>
		</div>
		<div id="light-buttons" style="background-color:#ebf2f9; display:none;"><a href="#" class="easyui-linkbutton" onclick="javascript:popout.SelectorClose()">Close</a></div>
		<div id="fade" class="black_overlay"></div>
_HTML_;
		return $HTML;
	}
	
	/*abstracts*/
	public function report_run(){}

}//End  Controller_Core_Sitereport
