<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Global user interface controller, this where all the magic happens. 
 *
 * $Id: Site.php 2012-12-31 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Site extends Controller_Include
{
	public $template	= "site.view";
	public $controller  = NULL; 
	public $param		= array();
	public $formdata	= array();
	public $form		= array();
	public $label		= array();  
	public $formopts	= array();  
	public $sideinfo	= array();
	public $popout		= array();
	public $sidefunc	= array();
	public $sidelink	= array();
	public $subform		= array();
	public $model		= '';  
	public $colon		= " :";
	public $frmaudtfields = array();		
	
	public function __construct($controller)
	{
		parent::__construct();
		$this->set_start_controller($controller);
		$this->db = Database::instance();
		$this->model = new Model_SiteDB;
		//$this->param = $this->get_controller_params('site');
		
		// Initialize view variables
		$this->template->head = '';
		$this->template->userbttns = '';
		$this->template->menutitle = '';
		$this->template->content = '';
		$this->template->auditfields = '';	
		
		if(Auth::instance()->logged_in())
		{
			$this->template->username = Auth::instance()->get_user()->username;
		}
		else
			$this->template->username = 'expired';
		

		// By adding this we are making the database object available to all controllers that extend Site_Controller
        $user = ORM::factory('User')->where('username','=',$this->template->username)->find();
		$this->template->idname = $user->idname; 
		$this->frmaudtfields = $this->merge_form_with_audit_fields($this->form,$this->label);
    
		// Initialize controller params
		$this->param = $this->get_controller_params($this->controller);
		$this->param['url_enquiry'] = $this->param['url_input'] = $this->param['param_id'];
		$this->param['primarymodel'] = new $this->param['primarymodel'];
		$this->param['defaultlookupfields'] = $this->param['primarymodel']->get_formfields($this->controller);
		// Setup form field, fill arrays with default values
		$this->set_sysconfig_global_modes();
		$this->formdata = $this->get_controller_formdefs($this->controller);
		$this->set_formfields_and_labels();
		
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
		$htmlhead->add( HTML::script($this->js['sideinfo']) );
		$htmlhead->add( HTML::script($this->js['enquiry']) );
		$htmlhead->add( HTML::script($this->js['popout']) );
		
		$this->param['htmlhead'] = $htmlhead->get_html();
		$this->param['enqhead'] = $htmlhead->get_html();
	}
	
	public function _before()
	{
       	parent::before();
		$this->db = Database::instance();
		$this->model = new Model_SiteDB;
		//$this->param = $this->get_controller_params('site');
		
		// Initialize these values form constructor template
		$this->template->head = '';
		$this->template->userbttns = '';
		$this->template->menutitle = '';
		$this->template->content = '';
		$this->template->auditfields = '';
	
		if(Auth::instance()->logged_in())
		{
			$this->template->username = Auth::instance()->get_user()->username;
		}
		else
			$this->template->username = 'expired';
		

		// By adding this we are making the database object available to all controllers that extend Site_Controller
        $user = ORM::factory('User')->where('username','=',$this->template->username)->find();
		$this->template->idname = $user->idname; 
		$this->frmaudtfields = $this->merge_form_with_audit_fields($this->form,$this->label);
    
		// Initialize controller params
		$this->param = $this->get_controller_params($this->controller);
		$this->param['url_enquiry'] = $this->param['url_input'] = $this->param['param_id'];
		$this->param['primarymodel'] = new $this->param['primarymodel'];
		$this->param['defaultlookupfields'] = $this->param['primarymodel']->get_formfields($this->controller);
		// Setup form field, fill arrays with default values
		$this->set_sysconfig_global_modes();
		$this->formdata = $this->get_controller_formdefs($this->controller);
		$this->set_formfields_and_labels();
		
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
		$htmlhead->add( HTML::script($this->js['sideinfo']) );
		$htmlhead->add( HTML::script($this->js['enquiry']) );
		$htmlhead->add( HTML::script($this->js['popout']) );
		
		$this->param['htmlhead'] = $htmlhead->get_html();
		$this->param['enqhead'] = $htmlhead->get_html();
	}

	public function set_start_controller($controller)
	{
		$this->controller = $controller;
	}
	
	public static function merge_form_with_audit_fields(&$form,&$label)
	{
		$frmaudtfields = array(
			'inputter' =>		'inputter',
			'input_date' =>		'input_date',
			'authorizer' =>		'authorizer',
			'auth_date' =>		'auth_date',
			'record_status' =>	'record_status',
			'current_no' =>		'current_no'
		);
		$tmp1 = array_reverse($form);
		$tmp2 = array_reverse($frmaudtfields);
		$form  = array_reverse(array_merge($tmp2,$tmp1));
		
		$lblaudtabels = array(
			'inputter' =>		'Inputter',
			'input_date' =>		'Input Date',
			'authorizer' =>		'Authorizer',
			'auth_date' =>		'Auth Date',
			'record_status' =>	'Record Status',
			'current_no' =>		'Current No'
		);
		$tmp1 = array_reverse($label);
		$tmp2 = array_reverse($lblaudtabels);
		$label  = array_reverse(array_merge($tmp2,$tmp1));
		return $frmaudtfields;
	}

	function get_controller_params($controller)
	{
		$arr = $this->model->get_controller_params($controller);
		return $arr;
	}

	function get_controller_formdefs($controller)
	{
		$arrobj = $this->model->get_formdefs($controller);
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
			$this->formopts[$key]['enable_on_new'] = sprintf('%s',$field->onnew);
			$this->formopts[$key]['enable_on_edit'] = sprintf('%s',$field->onedit);			
			if($field->popout)
			{
				$this->popout[$key]['enable'] = sprintf('%s',$field->popout->enable);
				$this->popout[$key]['table'] = sprintf('%s',$field->popout->table);
				$this->popout[$key]['selectfields'] = sprintf('%s',$field->popout->selectfields);
				$this->popout[$key]['idfield'] = sprintf('%s',$field->popout->idfield);
			}
			
			if($field->sideinfo)
			{
				$this->sideinfo[$key]['enable'] = sprintf('%s',$field->sideinfo->enable);
				$this->sideinfo[$key]['table'] = sprintf('%s',$field->sideinfo->table);
				$this->sideinfo[$key]['selectfields'] = sprintf('%s',$field->sideinfo->selectfields);
				$this->sideinfo[$key]['idfield'] = sprintf('%s',$field->sideinfo->idfield);
				$this->sideinfo[$key]['format'] = sprintf('%s',$field->sideinfo->format);
			}

			if($field->sidefunc)
			{
				$this->sidefunc[$key]['enable'] = sprintf('%s',$field->sidefunc->enable);
				$this->sidefunc[$key]['func'] = sprintf('%s',$field->sidefunc->func);
				$this->sidefunc[$key]['idfield'] = sprintf('%s',$field->sidefunc->idfield);
				$this->sidefunc[$key]['format'] = sprintf('%s',$field->sidefunc->format);
			}
		
			if($field->sidelink)
			{
				$i = 0;
				foreach ($field->sidelink->link as $link)
				{
					$this->sidelink[$key][$i] = array('src'=>sprintf('%s',$link->src),'attr'=>sprintf('%s',$link->attr),'text'=>sprintf('%s',$link->text));	
					$i++;
				}
			}
			
			if($field->subform)
			{
				$this->subform[$key]['subformcontroller'] = sprintf('%s',$field->subform->subformcontroller);	
				$this->subform[$key]['subformonnew'] = sprintf('%s',$field->subform->subformonnew);
				$this->subform[$key]['subformonedit']	= sprintf('%s',$field->subform->subformonedit);
			}
		}
	}

	public function get_username()
	{
		return $this->template->username;
	}

	public function get_idname()
	{
		return $this->template->idname;
	}

	public function is_global_auth_mode_on()
	{
		return $this->param['global_authmode_on'];
	}
	
	public function is_controller_auth_mode_on()
	{
		return $this->param['auth_mode_on'];
	}

	public function is_global_index_field_on()
	{
		return $this->param['global_indexfield_on'];
	}

	public function set_sysconfig_global_modes()
	{
		$querystr = sprintf('select id,sysconfig_id,global_authmode_on,global_indexfield_on from sysconfigs where sysconfig_id = "%s"',"SYSTEM");
		$result = $this->param['primarymodel']->execute_select_query($querystr);
		$row = $result[0];
		$this->param['global_authmode_on']		= $row->global_authmode_on;
		$this->param['global_indexfield_on']	= $row->global_indexfield_on;
	}

	public function set_record_status_message($_post,$_action="added to")
	{
		$this->param['recordstatusmsg']="<p><b>&nbsp Record  [ ".$_post['id']." ] ".$_action." ".$_post['record_status']." successfully, <a href=".$this->param['param_id']."/index/".$_post['id'].">Continue.</a></b></p>"; 
	}

	public function set_record_status_message_ihld($_post,$_msg="in IHLD, cannot be Authorized,")
	{
		$this->param['recordstatusmsg']="<p><b>&nbsp Record  [ ".$_post['id']." ] ".$_msg." <a href=".$this->param['param_id']."/index/".$_post['id'].">Continue.</a></b></p>"; 
	}
	
	public function get_record_status_message()
	{
		return $this->param['recordstatusmsg']; 
	}

	public function set_page_content($head='',$body='')
	{
		$this->template->head = $head;
		$this->template->content = $body;
	}

	public static function redirect_to_login()
	{
		HTTP::redirect('autologout');
	}

	function process_index()
	{
		$this->param['pageheader'] = $this->get_page_header($this->param['appheader'],"");
		if(!$_POST)
        {
			$this->app_index();
        }
		else
		{
			if($_POST['submit']=='Submit')
			{
				$this->param['pageheader'] = $this->get_page_header($this->param['appheader'],$_POST['func']);
				$this->param['indexfieldvalue'] = $_POST[$this->param['indexfield']];
				
				switch($_POST['func'])
				{
					case 'v':
						$this->view();
					break;
					
					case 'n':
					case 'c':
					case 'i':
					case 'w':
						$this->input();
					break;

					case 'x':
						//$this->verify();
					break;
					
					case 'a':
						$this->authorize();
					break;

					case 'd':
						$this->livedelete();
					break;
				}
			}
			else if($_POST['submit']=='Hold')
			{
				$this->hold();
			}
			else if($_POST['submit']=='Validate')
			{
				//record lock get "mysteriously" delete on manual validation so write it back!
				$this->validate();
			}
			else if($_POST['submit']=='Commit')
			{
				if($this->input())
				{
					if( (!$this->is_global_auth_mode_on()) || (!$this->is_controller_auth_mode_on()) )
					{
						$_POST['submit']='Authorize';
						$_POST['func']='a'; $_POST['auth']='n'; $_POST['rjct']='n';
						$this->authorize();
					}
				}
			}
			else if($_POST['submit']=='Authorize' || $_POST['submit']=='Reject')
			{
				$this->authorize();
			}
			else if($_POST['submit']=='Delete')
			{
				$this->livedelete();
			}
			else if($_POST['submit']=='Cancel')
			{
				$arr = $this->param['primarymodel']->get_record_lock_by_id($_POST['recordlockid']);
				
				$subform_exist = false;
				if($result = $this->param['primarymodel']->get_subform_controller($this->param['controller']))
				{	
					$subform_exist = true;
					$parent_idfield = $this->param['indexfield'];
					$idval	 = $_POST[$parent_idfield];
					foreach($result as $key => $val)
					{
						$paramdef = $this->param['primarymodel']->get_controller_params($val);
						$subtable_inau[$val]   = $paramdef['tb_inau'];
						$subtable_idxfld[$val] = $paramdef['indexfield'];
					}
				}
					
				if($arr)
				{
					if($this->param['tb_inau'] == $this->param['tb_live'])
					{
						if($arr->pre_status == 'NEW')
						{	//delete INAU record, no change
							$this->param['primarymodel']->delete_record_by_id($this->param['tb_inau'],$_POST['id']);
							if($subform_exist)
							{
								foreach($subtable_inau as $key => $table)
								{
									$this->param['primarymodel']->delete_record_by_field_value($table,$parent_idfield,$idval);
								}
							}
						}
					}
					else
					{
						if($arr->pre_status == 'NEW' || $arr->pre_status == 'LIVE')
						{	//delete INAU record, no change
							$this->param['primarymodel']->delete_record_by_id($this->param['tb_inau'],$_POST['id']);
							if($subform_exist)
							{
								foreach($subtable_inau as $key => $table)
								{
									$sub_idfield = $subtable_idxfld[$key];
									$this->param['primarymodel']->delete_record_by_field_value($table,$parent_idfield,$idval);
								}
							}
						}
						else if($arr->pre_status == 'INAU')
						{	//set status back to INAU, no change
							$this->param['primarymodel']->set_record_status($this->param['tb_inau'],$_POST['id'],'INAU');
							if($subform_exist)
							{
								foreach($subtable_inau as $key => $table)
								{
									$sub_idfield = $subtable_idxfld[$key];
									$this->param['primarymodel']->set_record_status_by_field_value($table,$parent_idfield,$idval,'INAU');
								}
							}
						}
					}
					$this->param['primarymodel']->remove_record_lock_by_id($_POST['recordlockid']);
				}
				HTTP::redirect($this->param['param_id'].'/index/'.$_POST['id']);
			}
		}
	}

	public function get_user_controls()
	{
		//verify against security profile
		$inputcontrols = new Controller_Core_Sitecontrol($this->param['param_id'],$this->param['controller'],$this->template->idname);
		$inputcontrols->set_global_auth_mode_on( $this->is_global_auth_mode_on() ); 
		$inputcontrols->set_controller_auth_mode_on( $this->is_controller_auth_mode_on() ); 
		$inputcontrols->set_global_index_fld_on( $this->is_global_index_field_on() );
		$this->param['permissions'] = $inputcontrols->get_available_input_permissions();
		$inputcontrols->set_input_controls();
		return $inputcontrols->get_input_controls();
	}
		
	public function app_index()
	{
		//check if user session expired;
		if(!Auth::instance()->logged_in())
		{
			$this->redirect_to_login();
		}
		else
		{
			$head = $this->param['htmlhead'];
			$content = new View($this->param['indexview']);
			$content->pageheader = $this->param['pageheader'];
		
			//build form
			$content->pagebody = "";
			$content->pagebody .= Form::open($this->param['param_id'])."\n";
			
			$content->pagebody .= '<table>'."\n";
			$content->pagebody .= '<tr valign="center">'."\n";
			$content->pagebody .= "\t".'<td>'.$this->get_user_controls().'</td>'."\n";
			$content->pagebody .= '</tr>'."\n";
			$permission = $this->param['permissions'];
			if( isset($permission['if']) )
			{	
				$content->pagebody .= '<tr valign="center">'."\n";
				$content->pagebody .= "\t".sprintf('<td>%s %s</td>',Form::label($this->param['indexfield'],$this->param['indexlabel']),$this->colon)."\n";
				$form_input_opts = array('size="50"','maxlength="50"','class="input-i"');
				if( !$permission['if'] ) 
				{
					// if permission false make input field readonly
					array_push($form_input_opts,'readonly');
				}					
				$content->pagebody .= "\t".sprintf('<td align="left">%s</td>',Form::input($this->param['indexfield'], $this->param['indexfieldvalue'], $form_input_opts ))."\n";
				$content->pagebody .= '</tr>'."\n"; 
			}
			$content->pagebody .= '</table>'."\n";
			$content->pagebody .= Form::close()."\n";
			$this->set_page_content($head,$content);
		}
	}

	public function get_page_header($_table,$_func)
	{
		switch ($_func)
		{
			case 'v':
				return $_table." - View";
			break;
			
			case 'n':
				return $_table." - New";
			break;
		
			case 'i':
			case 'input':
				return $_table." - Edit";
			break;
			
			case 'a':
			case 's':
				return $_table." - Authorize";
			break;
			
			case 'd':
				return $_table." - Delete";
			break;
			
			case 'e':
				return $_table." - Enquiry";
			break;

			default:
				return $_table;
		}

	}
		
	public function view()
	{
		$this->param['defaultlookupfields']=array_merge($this->param['defaultlookupfields'],$this->frmaudtfields);
		if(strstr($this->param['indexfieldvalue'],';') !== false)
		{
			$this->view_pre_open_existing_record();		
			$formarr=$this->param['primarymodel']->get_hist_record_by_lookup($this->param['tb_hist'],$this->param['indexfieldvalue'],$this->param['defaultlookupfields'],'v');
			$this->form = (array)$formarr;
		}
		else
		{
			$this->view_pre_open_existing_record();
			$formarr=$this->param['primarymodel']->get_record_by_lookup($this->param['tb_live'],$this->param['tb_inau'],$this->param['indexfield'],$this->param['indexfieldvalue'],$this->param['defaultlookupfields'],'v');
			$this->form = (array)$formarr;
		}
		$this->view_form();
		$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
		$this->view_post_open_existing_record();
	}
	
	public function view_form()
	{
		$content = new View($this->param['viewview']);
		
		//add page/form header
		$content->pageheader = $this->param['pageheader'];
		if(!$this->form)
		{
			$pagebody = new Controller_Core_Sitehtml($this->param['primarymodel']->get_db_err_msg());
		}	
		else
		{	
			// add form
			$pagebody = new Controller_Core_Sitehtml(Form::open($this->param['param_id']));
			$pagebody->add("<table>\n");
			$pagebody->add("<tr valign='center'><td colspan=2>".$this->get_user_controls()."</td></tr>\n");
			//form fields
			foreach($this->form as $key => $value)
			{
				$this->form[$key] = trim($this->form[$key]);
				switch($key)
				{
					case 'inputter':
					case 'input_date':
					case 'authorizer':
					case 'auth_date':
					case 'record_status':
					case 'current_no':
						$pagebody->add('<tr valign="center"><td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
						$pagebody->add('<td><span>'.HTML::chars($this->form[$key]).'</span></td></tr>'."\n");
					break;	
										
					default:
						$pagebody->add("<tr valign='top'>");
						if($this->formopts[$key]['inputtype']=='subform')
						{
							if($this->form['record_status'] == "IHLD" || $this->form['record_status'] == "INAU") { $subtable_type = "inau"; }
							else if ($this->form['record_status'] == "HIST") { $subtable_type = "hist"; }
							else { $subtable_type = "live"; }
							
							$SUBFORM_HTML = $this->view_subform($key,$this->form['current_no'],"brown",$subtable_type);
							$pagebody->add('<td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
							$pagebody->add('<td>');
							$pagebody->add(sprintf('<input type="hidden" id="%s" name="%s" value="%s"/>',$key,$key,$this->form[$key]));
							$pagebody->add('<span class="viewtext">'.$SUBFORM_HTML.'</span>');
						}
						else if($this->formopts[$key]['inputtype'] == "xmltable")
						{
							$XMLTABLE_HTML = $this->view_xml_table($key,$this->form[$key],"brown");
							$pagebody->add('<td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
							$pagebody->add('<td>');
							$pagebody->add(sprintf('<input type="hidden" id="%s" name="%s" value="%s"/>',$key,$key,$this->form[$key]));
							$pagebody->add('<span class="viewtext">'.$XMLTABLE_HTML.'</span>');
						}
						else
						{
							$pagebody->add('<td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
							$pagebody->add('<td>');
							$pagebody->add(sprintf('<input type="hidden" id="%s" name="%s" value="%s"/>',$key,$key,$this->form[$key]));
							$pagebody->add('<span class="viewtext">'.nl2br(HTML::chars($this->form[$key])).'</span>');
						}

						if($this->formopts[$key]['inputtype'] == "input")
						{
							$SIDEINFO_HTML = $this->create_sideinfo($key,$this->formopts[$key]['options']);
							if($SIDEINFO_HTML != ""){ $SIDEINFO_HTML = "&nbsp &nbsp ( ".$SIDEINFO_HTML.")";}
							$pagebody->add($SIDEINFO_HTML);

							$SIDEFUNC_HTML = $this->create_sidefunc($key,$this->formopts[$key]['options']);
							if($SIDEFUNC_HTML != ""){ $SIDEFUNC_HTML = "&nbsp &nbsp ( ".$SIDEFUNC_HTML.")";}
							$pagebody->add($SIDEFUNC_HTML);
							
						}
						$pagebody->add('</td></tr>'."\n"); 
					break;
				}
			}
			$pagebody->add("</table>");
			$pagebody->add(Form::hidden('recordlockid',0,array('id'=>'func')));
			$pagebody->add(Form::hidden('func','*',array('id'=>'func')));
			$pagebody->add(Form::close());
		}
		$content->pagebody = $pagebody->get_html();
		$this->param['htmlbody'] = $content;
	}

	public function input()
	{
		//set index value invalid if hist syntax passed, cannot edit history record 
		if(strstr($this->param['indexfieldvalue'],';') !== false) {$this->param['indexfieldvalue'] = -1;}
		
		if($_POST['func']=='n')
        {
			//create new record in IHLD, populate form 
			if($this->param['tb_inau'] == $this->param['tb_live'])
			{
				//no inau table in tableset, auto_increment on live table, live table usually  linked to external system 
				$formarr=$this->param['primarymodel']->create_blank_record($this->param['tb_live'],$this->param['tb_inau']);
				$formarr->current_no = 1;
			}
			else
			{
				//using ids from _sys_autoids because records from inau tables get deleted and contain on records, auto_increment resets to zero 
				$formarr=$this->param['primarymodel']->create_blank_record($this->param['tb_live'],$this->param['tb_inau']);
			}
			$this->form = (array)$formarr;
			if($this->form)
			{
				$this->param['primarymodel']->set_record_lock(Auth::instance()->get_user()->idname,$this->param['tb_inau'],$this->form['id'],'NEW');
				$this->form['record_status'] = 'IHLD';
				$this->param['primarymodel']->set_record_status($this->param['tb_inau'],$this->form['id'],$this->form['record_status']);;
			}
			$this->input_form($_POST['func']);
			$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
			return true;
        }
		else if($_POST['func']=='c')
        {
			//create new record, copy existing record
			$empty = array();
			$this->param['defaultlookupfields']=array_merge($this->param['defaultlookupfields'],$empty);
			$formarr=$this->param['primarymodel']->get_record_by_lookup($this->param['tb_live'],$this->param['tb_inau'],$this->param['indexfield'],$this->param['indexfieldvalue'],$this->param['defaultlookupfields'],$_POST['func']);
			$newarr=$this->param['primarymodel']->create_blank_record($this->param['tb_live'],$this->param['tb_inau']);
			$arr = (array)$newarr;
			$this->form = array_merge($arr,(array)$formarr);
			$this->form['id'] = $arr['id'];
			if($this->form)
			{
				$this->param['primarymodel']->set_record_lock(Auth::instance()->get_user()->idname,$this->param['tb_inau'],$this->form['id'],'NEW');
				$this->form['record_status'] = 'IHLD';
				$this->param['primarymodel']->set_record_status($this->param['tb_inau'],$this->form['id'],$this->form['record_status']);;
			}
			$this->input_form($_POST['func']);
			$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
			return true;
        }
		else if($_POST['func']=='i' || $_POST['func']=='w')
		{	
			//create edit existing record in IHLD, populate form 
			$this->param['defaultlookupfields']=array_merge($this->param['defaultlookupfields'],$this->frmaudtfields);
			$formarr=$this->param['primarymodel']->get_record_by_lookup($this->param['tb_live'],$this->param['tb_inau'],$this->param['indexfield'],$this->param['indexfieldvalue'],$this->param['defaultlookupfields'],$_POST['func']);
			
			$this->form = (array)$formarr;
			if($this->form)
			{
				if($this->param['tb_inau'] == $this->param['tb_live'])
				{
					$this->param['primarymodel']->set_record_lock(Auth::instance()->get_user()->idname,$this->param['tb_inau'],$this->form['id'],'INAU');
				}
				else
				{
					$this->param['primarymodel']->set_record_lock(Auth::instance()->get_user()->idname,$this->param['tb_inau'],$this->form['id'],$this->form['record_status']);
				}
				$this->form['record_status'] = 'IHLD';
				$this->param['primarymodel']->set_record_status($this->param['tb_inau'],$this->form['id'],$this->form['record_status']);
				if($this->form['current_no']==0){$this->input_form('n');} else {$this->input_form('i');}
			}
			else
			{
				$this->input_form('i');
			}
			$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
			return true;
		}
		else
		{
			if($this->validate())
			{
				//add  formdata add to database, INAU table updated query
				$this->param['primarymodel']->remove_record_lock_by_id($_POST['recordlockid']);
	
				//removing indexes 'submit and 'func' from array, do not need for update, not database fields
				unset($_POST['submit']); unset($_POST['func']); unset($_POST['preval']);unset($_POST['recordlockid']);
				unset($_POST['bttnclicked']); unset($_POST['js_idname']); unset($_POST['js_tmpvar']);
				
				//set audit data
				$_POST['inputter']=Auth::instance()->get_user()->idname; $_POST['authorizer']='SYSINAU';
				$_POST['input_date']=date('Y-m-d H:i:s'); $_POST['auth_date']=date('Y-m-d H:i:s');  $_POST['record_status']='INAU';

				$this->input_pre_update_existing_record();		
				if( $this->param['primarymodel']->update_record($this->param['tb_inau'],$_POST))
				{
					//create  update subform records if any
					$this->create_subform_records();
					$this->input_post_update_existing_record();		
					$this->set_record_status_message($_POST);
										
					$this->param['htmlbody']->pagebody =  $this->get_record_status_message();
					$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
					return true;
				}
				return false;
			}
			else
			{
				return false;
			}
		}
	}
	
	public function input_form($_func)
	{
		$content = new View($this->param['inputview']);
		
		//add page/form header
		$content->pageheader = $this->param['pageheader'];
		
		//input form
		if(!$this->form)
		{
			$pagebody = new Controller_Core_Sitehtml($this->param['primarymodel']->get_db_err_msg());
		}
		else
		{
			//get record lock data
			$lock = $this->param['primarymodel']->get_record_lock(Auth::instance()->get_user()->idname,$this->param['tb_inau'],$this->form['id']);
			// add form
			$pagebody = new Controller_Core_Sitehtml(Form::open($this->param['param_id'],array('id'=>$this->param['param_id'],'name'=>$this->param['param_id'])));
			$pagebody->add('<table>'."\n");
			$pagebody->add('<tr valign="center"><td colspan="2">'.$this->get_user_controls().'</td></tr>'."\n");
		
			foreach($this->form as $key => $value)
			{
				$POPOUT_HTML =""; $SIDEINFO_HTML=""; $SIDELINK_HTML=""; $DATEICON_HTML=""; $table =""; $fields=""; $idstr=""; $po_type=""; $disabled="" ; $style="";
				switch($key)
				{
					case 'inputter':
					case 'input_date':
					case 'authorizer':
					case 'auth_date':
					case 'record_status':
					case 'current_no':
						$pagebody->add('<tr valign="center"><td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
						$pagebody->add('<td>'.sprintf('<input type="hidden" id="%s" name="%s" value="%s" />',$key,$key,$this->form[$key]));
						$pagebody->add('<span>'.$this->form[$key].'</span></td></tr>'."\n"); 
					break;	
				
					default:
						if( !(isset($this->formopts[$key]['options'])) )
						{
							unset($this->form[$key]); unset($_POST[$key]); continue;
						}
						
						$this->formopts[$key]['options'] = str_replace("%FORM%",$this->param['controller'], $this->formopts[$key]['options']); 
						$this->form[$key] = trim($this->form[$key]);
						if($_func == 'n' || $_func == 'c' || ($_func == 'l' && $_POST['current_no'] == 0))
						{ 
							if($this->formopts[$key]['enable_on_new'] == 'readonly' || $this->formopts[$key]['enable_on_new'] == 'readonly_po')
							{
								$disabled = 'readonly';
								if( $this->formopts[$key]['enable_on_new'] == 'readonly') { $style = 'style="background-color: #EBEBE0;"'; }
							}
							else if ($this->formopts[$key]['enable_on_new'] == 'disabled') {$disabled = 'disabled';}
							else { $disabled = "";} /*enabled*/
							$po_type = $this->formopts[$key]['enable_on_new'];
						}
						else if ($_func == 'i' || ($_func == 'l' && $_POST['current_no'] > 0))
						{
							if($this->formopts[$key]['enable_on_edit'] == 'readonly' || $this->formopts[$key]['enable_on_edit'] == 'readonly_po')
							{
								$disabled = 'readonly';
								if( $this->formopts[$key]['enable_on_edit'] == 'readonly') { $style = 'style="background-color: #EBEBE0;"'; }
							}
							else if ($this->formopts[$key]['enable_on_edit'] == 'disabled') {$disabled = 'disabled';}
							else { $disabled = "";} /*enabled*/
							$po_type = $this->formopts[$key]['enable_on_edit'];
						}
					
						$options = $this->formopts[$key]['options'] = $this->formopts[$key]['options'].' '.$style.' '.$disabled;
						switch($this->formopts[$key]['inputtype'])
						{	
							case 'input':
							case 'date':
							/*popout div*/
								if($po_type == 'enabled_po' || $po_type == 'readonly_po')
								{
									$POPOUT_HTML = $this->create_popout($key,$this->form['current_no']);
									$SIDELINK_HTML = $this->create_sidelink($key,$this->form['current_no']);
									if($this->formopts[$key]['inputtype'] == "date")
									{
										$DATEICON_HTML = $this->create_date_popout($key);
									}
								}
							/*side info span*/	
								$SIDEINFO_HTML = $this->create_sideinfo($key,$options);
								$SIDEFUNC_HTML = $this->create_sidefunc($key,$options);
																
								$pagebody->add('<tr valign="center"><td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
								$pagebody->add('<td>');
								$pagebody->add(sprintf('<input type="text" class="input-i" id="%s" name="%s" value="%s" %s />',$key,$key,$this->form[$key],$options));
								$pagebody->add($DATEICON_HTML.$POPOUT_HTML.$SIDELINK_HTML.$SIDEINFO_HTML.$SIDEFUNC_HTML);
								$pagebody->add('</td></tr>'."\n");
								
							break;
					
							case 'hidden':
								$pagebody->add('<tr valign="center"><td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
								$pagebody->add('<td>');
								$pagebody->add(sprintf('<input type="hidden" id="%s" name="%s" value="%s" />',$key,$key,$this->form[$key]));
								$pagebody->add('<span>'.$this->form[$key].'</span></td></tr>'."\n"); 
							break;

							case 'password':
								$SIDELINK_HTML = $this->create_sidelink($key,$this->form['current_no']);
								$pagebody->add('<tr valign="center"><td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
								$pagebody->add('<td>');
								$pagebody->add(sprintf('<input type="password" class="input-i" id="%s" name="%s" value="%s" %s />',$key,$key,$this->form[$key],$options));
								$pagebody->add($SIDELINK_HTML.'</td></tr>'."\n"); 
							break;

							case 'file':
						
							break;

							case 'textarea':
								if($po_type == 'enabled_po' || $po_type == 'readonly_po')
								{
									$SIDELINK_HTML = $this->create_sidelink($key,$this->form['current_no']);
								}
								$pagebody->add('<tr valign="top"><td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
								$pagebody->add('<td>');
								$pagebody->add(sprintf('<textarea class="input-i" id="%s" name="%s" %s>%s</textarea>',$key,$key,$options,$this->form[$key]));
								$pagebody->add($SIDELINK_HTML.'</td></tr>'."\n"); 
							break;
							
							case 'xmltable':
								$XMLTABLE_HTML = $this->edit_xml_table($key,"black");
								$pagebody->add('<td valign="top">'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
								$pagebody->add('<td>');
								$pagebody->add(sprintf('<input type="hidden" id="%s" name="%s" value="%s" />',$key,$key,$this->form[$key]));
								$pagebody->add('<span class="viewtext">'.$XMLTABLE_HTML.'</span></td></tr>');
							break;

							case 'dropdown':
								list($arrval,$arrtxt) = explode("::",$options);
								$selection = array_combine(explode(",",$arrval),explode(",",$arrtxt));
								$pagebody->add('<tr valign="center"><td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
								$pagebody->add('<td>');
								$pagebody->add(Form::select($key, $selection, NULL, array('id'=>$key) ))."\n";
								$pagebody->add('</td></tr>'."\n"); 
							break;

							case 'checkbox':
						
							break;

							case 'radio':
						
							break;

							case 'submit':
						
							break;

							case 'subform':
								if($lock)
								{
									if($_func == 'l')
									{
										$SUBFORM_HTML = $this->create_subform_from_xml($key,$_POST[$key]);
									}
									else
									{
										if($lock->pre_status == "IHLD" || $lock->pre_status == "INAU") {  $subtable_type = "inau"; }
										else { $subtable_type = "live"; }
										$SUBFORM_HTML = $this->create_subform($key,$this->form['current_no'],$subtable_type);
									}
									$pagebody->add('<tr valign="top"><td>'.Form::label($key,$this->label[$key]).$this->colon.'</td>');
									$pagebody->add($SUBFORM_HTML.sprintf('<input type="text" id="%s" name="%s" value="%s" size="1" style="border:0px;width:0px;height:0px;" readonly>',$key,$key,$this->form[$key]));
									$pagebody->add('</td></tr>'."\n"); 
								}
							break;
						}
					break;
				}
			}
			$pagebody->add("</table>");
			$pagebody->add(Form::hidden('func','*',array('id'=>'func')));
			$pagebody->add(Form::hidden('preval',$_func),array('id'=>'preval'));
			$html = sprintf('<input type="hidden" id="bttnclicked" name="bttnclicked" value="%s"/>','false');
			$pagebody->add($html);

			if($lock)
			{
				$html = sprintf('<input type="hidden" id="recordlockid" name="recordlockid" value="%s"/>',$lock->id);
				$pagebody->add($html);
			}
			$html = sprintf('<input type="hidden" id="js_idname" name="js_idname" value="%s"/>',Auth::instance()->get_user()->idname);
			$pagebody->add($html);
			
			#empty js variable for temp values
			$html = sprintf('<input type="hidden" id="js_tmpvar" name="js_tmpvar" value="%s"/>',"");
			$pagebody->add($html);

			$pagebody->add(Form::close());
			$pagebody->add($this->popout_selector_window());
			$pagebody->add($this->custom_dialog_window());
		}
		$content->pagebody = $pagebody->get_html();
		$this->param['htmlbody'] = $content;
	}
	
	public function input_repopulate($_func)
	{
		$_POST['func']=$_func;
		$formarr=$_POST;
		unset($formarr['submit']); unset($formarr['func']); unset($formarr['preval']);unset($formarr['recordlockid']);
		//unset($_POST['recordlockid']);
		// repopulate form fields and show errors
		$this->form = arr::overwrite($formarr, $this->param['validatedpost']);
	}
	
	public function authorize()
	{
		//set index value invalid if hist syntax passed, cannot authorize history record 
		if(strstr($this->param['indexfieldvalue'],';') !== false) {$this->param['indexfieldvalue'] = -1;}
		
		//1) LIVE record inserted into HISTORY
		//2) INAU record record inserted into LIVE in not exist, else updated, count incremented if Approved
		//3) INAU record deleted
		
		if($_POST['submit']=='Submit')
        {
			$_POST['func']=='a';
			$this->param['defaultlookupfields']=array_merge($this->param['defaultlookupfields'],$this->frmaudtfields);
			$formarr=$this->param['primarymodel']->get_record_by_id($this->param['tb_live'],$this->param['indexfield'],$this->param['indexfieldvalue'],$this->param['defaultlookupfields']);
			$liverec = (array)$formarr;
			$formarr=$this->param['primarymodel']->get_record_by_id($this->param['tb_inau'],$this->param['indexfield'],$this->param['indexfieldvalue'],$this->param['defaultlookupfields']);
			$this->form = (array)$formarr;
			if($this->form)
			{
				$this->param['primarymodel']->set_record_lock(Auth::instance()->get_user()->idname,$this->param['tb_inau'],$this->form['id'],$this->form['record_status']);
			}
			$this->authorize_form($liverec);
			$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
		}
		else if($_POST['submit']=='Reject')
		{
			//minimal authform required
			$content = new View('default_authorize');
			$content->pageheader = $this->param['pageheader'];
			$content->pagebody = $this->get_user_controls();
			$this->param['htmlbody'] = $content;

			$this->param['primarymodel']->remove_record_lock_by_id($_POST['recordlockid']);
			//$this->param['primarymodel']->removeRecordLock(Auth::instance()->get_user()->idname,$this->param['tb_inau'],$_POST['id']);
			if($this->param['primarymodel']->delete_record_by_Id($this->param['tb_inau'],$_POST['id']))
			{
				if($this->subform_exist($parent_idfield,$idval,$subtable_live,$subtable_inau,$subtable_hist,$subtable_idxfld))
				{
					foreach($subtable_inau as $key => $table)
					{
						$arr = $this->param['primarymodel']->get_subform_records($table,$parent_idfield,$idval);
						foreach($arr as $index => $row)
						{
							$sub_post = (array)$row;
							if($this->param['primarymodel']->delete_record_by_Id($table,$sub_post['id']))
							{/*do nothing*/} else {	return false; }
						}
					}
				}
				
				$this->set_record_status_message($_POST,"deleted from");
				$this->param['htmlbody']->pagebody =  $this->get_record_status_message();
			}
			else
			{
				$this->param['htmlbody']->pagebody= $this->param['primarymodel']->get_db_err_msg();
			}
			$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
		}
		else if($_POST['submit']=='Authorize')
		{	
			//minimal authform required
			$content = new View('default_authorize');
			$content->pageheader = $this->param['pageheader'];
			$content->pagebody = $this->get_user_controls();
			$this->param['htmlbody'] = $content;
			
			//remove recordlocks first then do other  processing
			if( $this->is_global_auth_mode_on() && $this->is_controller_auth_mode_on() )
			{
				$this->param['primarymodel']->remove_record_lock_by_id($_POST['recordlockid']);
			}
			
			//$this->param['primarymodel']->removeRecordLock(Auth::instance()->get_user()->idname,$this->param['tb_inau'],$_POST['id']);
			if($_POST['record_status']=='INAU')
			{
				//authorization permission is set/used only if auth modes on 
				if( $this->is_global_auth_mode_on() && $this->is_controller_auth_mode_on() )
				{
					$authorize = false;
					$ctrl = $this->param['permissions'];
					if($_POST['inputter']==Auth::instance()->get_user()->idname && $ctrl['as'])
					{ 
						$authorize = true; 
					} 
					else
					{ 
						$this->param['htmlbody']->pagebody = "Inputter = Authorizer, Cannot Self Authorize.";
						$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
					}

					if(!($_POST['inputter']==Auth::instance()->get_user()->idname) && $ctrl['ao'])
					{ 
						$authorize = true; 
					} 
					else
					{ 
						$this->param['htmlbody']->pagebody = "Inputter != Authorizer, Can Only Self Authorize.";
						$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
					}
				}
				else
				{
					$authorize = true; 
				}

				if($authorize)
				{
					$PRINT_POST = $_POST;
					$PRINT_POST['record_status']='LIVE';
					//remove indexes 'submit and 'func' from array, do not need for update, not database fields
					unset($_POST['submit']); unset($_POST['func']); unset($_POST['preval']); unset($_POST['recordlockid']);
					unset($_POST['auth']); unset($_POST['rjct']);
				
					//set audit data
					$_POST['authorizer']=Auth::instance()->get_user()->idname; $_POST['auth_date']=date('Y-m-d H:i:s');
					$_POST['record_status']='LIVE'; 

					if($_POST['current_no']==0) //new record
					{
						$_POST['current_no']++;
						$this->authorize_pre_insert_new_record();
						if( $this->param['primarymodel']->insert_record($this->param['tb_live'],$_POST))
						{
							$subform_exist = false;
							if($this->subform_exist($parent_idfield,$idval,$subtable_live,$subtable_inau,$subtable_hist,$subtable_idxfld))
							{
								$subform_exist = true;
								foreach($subtable_live as $key => $table)
								{
									$arr = $this->param['primarymodel']->get_subform_records($subtable_inau[$key],$parent_idfield,$idval);
									foreach($arr as $index => $row)
									{
										$sub_post = (array)$row;
										$sub_post['authorizer']		= $_POST['authorizer']; 
										$sub_post['auth_date']		= $_POST['auth_date'];
										$sub_post['record_status']	= $_POST['record_status'];
										$sub_post['current_no']		= $_POST['current_no'];
										if($this->param['primarymodel']->insert__record($table,$sub_post))
										{/*do nothing*/} else {	return false; }
									}
								}
							}
														
							$this->authorize_post_insert_new_record();
							$this->set_record_status_message($PRINT_POST);
										
							if($this->param['primarymodel']->delete_record_by_Id($this->param['tb_inau'],$_POST['id']))
							{
								if($subform_exist)
								{
									foreach($subtable_inau as $key => $table)
									{
										$arr = $this->param['primarymodel']->get_subform_records($table,$parent_idfield,$idval);
										foreach($arr as $index => $row)
										{
											$sub_post = (array)$row;
											if($this->param['primarymodel']->delete_record_by_Id($table,$sub_post['id']))
											{/*do nothing*/} else {	return false; }
										}
									}
								}
								$this->param['htmlbody']->pagebody =  $this->get_record_status_message();
								$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
							}
							else
							{
								$this->param['htmlbody']->pagebody= $this->param['primarymodel']->get_db_err_msg();
							}
						}
						else
						{
							$this->param['htmlbody']->pagebody= $this->param['primarymodel']->get_db_err_msg();
						}
					}
					else //existing record
					{
						if($this->param['primarymodel']->insert_from_table_to_table($this->param['tb_hist'],$this->param['tb_live'],$_POST['id']))
						{
							$this->param['primarymodel']->set_record_status_HIST($this->param['tb_hist'],$_POST['id'],$_POST['current_no']);
							$_POST['current_no']++;
							
							$subform_exist = false;
							if($this->subform_exist($parent_idfield,$idval,$subtable_live,$subtable_inau,$subtable_hist,$subtable_idxfld))
							{
								$subform_exist = true;
								foreach($subtable_live as $key => $table)
								{
									$arr = $this->param['primarymodel']->get_subform_records($subtable_live[$key],$parent_idfield,$idval);
									foreach($arr as $index => $row)
									{
										$sub_post = (array)$row;
										if($this->param['primarymodel']->insert_from_table_to_table($subtable_hist[$key],$table,$sub_post['id']))
										{
											$this->param['primarymodel']->set_record_status_HIST($subtable_hist[$key],$sub_post['id'],$sub_post['current_no']);
										}
										else {	return false; }
									}
								}
							}
							
							$this->authorize_pre_update_existing_record();		
							if($this->param['primarymodel']->update_record($this->param['tb_live'],$_POST))
							{
								if($subform_exist)
								{
									foreach($subtable_live as $key => $table)
									{
										/*no of inau records may differ from live after edit*/
										/*delete live records first*/
										$arr = $this->param['primarymodel']->get_subform_records($table,$parent_idfield,$idval);
										foreach($arr as $index => $row)
										{
											$sub_post = (array)$row;
											if($this->param['primarymodel']->delete_record_by_Id($table,$sub_post['id']))
											{/*do nothing*/} else {	return false; }
										}

										/*re-insert inau records into live*/
										$arr = $this->param['primarymodel']->get_subform_records($subtable_inau[$key],$parent_idfield,$idval);
										foreach($arr as $index => $row)
										{
											$sub_post = (array)$row;
											$sub_post['authorizer']		= $_POST['authorizer']; 
											$sub_post['auth_date']		= $_POST['auth_date'];
											$sub_post['record_status']	= $_POST['record_status'];
											$sub_post['current_no']		= $_POST['current_no'];
											if($this->param['primarymodel']->insert__record($table,$sub_post))
											{/*do nothing*/} else {	return false; }
										}
									}
								}

								$this->set_record_status_message($PRINT_POST);
								$isTrue=false;
								if($this->param['tb_inau'] == $this->param['tb_live']){ $isTrue = true; }
								else 
								{ 
									if($isTrue = $this->param['primarymodel']->delete_record_by_Id($this->param['tb_inau'],$_POST['id']))
									{
										if($subform_exist)
										{
											/*cleanup subform inau*/
											foreach($subtable_inau as $key => $table)
											{
												$arr = $this->param['primarymodel']->get_subform_records($table,$parent_idfield,$idval);
												foreach($arr as $index => $row)
												{
													$sub_post = (array)$row;
													if($this->param['primarymodel']->delete_record_by_Id($table,$sub_post['id']))
													{/*do nothing*/} else {	return false; }
												}
											}
										}
									}									
								}
								
								if($isTrue)
								{
									$this->param['htmlbody']->pagebody =  $this->get_record_status_message();
									$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
								}
								else
								{
									$this->param['htmlbody']->pagebody= $this->param['primarymodel']->get_db_err_msg();
								}
								$this->authorize_post_update_existing_record();
							}
							else
							{
								$this->param['htmlbody']->pagebody= $this->param['primarymodel']->get_db_err_msg();
							}
						}
						else
						{
							$this->param['htmlbody']->pagebody= $this->param['primarymodel']->get_db_err_msg();
						}
					}
				}
			}
			else
			{
				$this->set_record_status_message_ihld($_POST);
				$this->param['htmlbody']->pagebody =  $this->get_record_status_message();
				$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
			}
		}
	}

	public function authorize_form($_liverec='')
	{
		$content = new View('default_authorize');
		//add page/form header
		$content->pageheader = $this->param['pageheader'];
		if(!$this->form)
		{
			$pagebody = new Controller_Core_Sitehtml($this->param['primarymodel']->get_db_err_msg());
		}	
		else
		{	
			// add form
			$pagebody = new Controller_Core_Sitehtml(Form::open($this->param['param_id']));
			$pagebody->add("<table>\n");
			$pagebody->add("<tr valign='center'><td colspan=2>".$this->get_user_controls()."</td></tr>\n");
			//form fields
			if($this->form['current_no']==0){$newrec = true;}else{$newrec = false;}
			foreach($this->form as $key => $value)
			{
				$this->form[$key] = trim($this->form[$key]);
				$livetext =''; $SUBFORM_HTML_LIVE = "";
				if(!$newrec)
				{
					if( $this->is_global_auth_mode_on() && $this->is_controller_auth_mode_on() )
					{
						if(!($_liverec[$key]==$this->form[$key]))
						{
							$SIDEINFO_HTML = $this->create_sideinfo($key,$this->formopts[$key]['options'],$_liverec[$key]);
							if($SIDEINFO_HTML != ""){ $SIDEINFO_HTML = "&nbsp &nbsp ( ".$SIDEINFO_HTML.")";}
							$livetext="<br><span class='livetext'>".nl2br(HTML::chars($_liverec[$key])).$SIDEINFO_HTML."</span>";
							
							if(isset($this->formopts[$key]['inputtype']))
							{
								if($this->formopts[$key]['inputtype']=='subform')
								{
									$livetext = $this->view_subform($key,$this->form['current_no'],"green","live");
								}
								else if($this->formopts[$key]['inputtype']=='xmltable')
								{
									$livetext = $this->view_xml_table($key,$this->form[$key],"green");
								}
							}
						}
					}
				}

				switch($key)
				{
					case 'inputter':
					case 'input_date':
					case 'authorizer':
					case 'auth_date':
					case 'record_status':
					case 'current_no':
						$pagebody->add("<tr valign='top'><td>".Form::label($key,$this->label[$key]).$this->colon."</td><td>".Form::hidden($key,$this->form[$key],array('id'=>$key))."<span>".HTML::chars($this->form[$key])."</span></td></tr>\n"); 
					break;	
					default:
						$pagebody->add("<tr valign='top'>");
						if($this->formopts[$key]['inputtype']=='subform')
						{
							$txtval = $this->view_subform($key,$this->form['current_no'],"brown","inau");
							$pagebody->add("<td>".Form::label($key,$this->label[$key]).$this->colon."</td><td>".Form::hidden($key,$this->form[$key],array('id'=>$key))."<span class='viewtext'>".$txtval."</span>");
						}
						else if($this->formopts[$key]['inputtype']=='xmltable')
						{
							$txtval = $this->view_xml_table($key,$this->form[$key],"brown");
							$pagebody->add("<td>".Form::label($key,$this->label[$key]).$this->colon."</td><td>".Form::hidden($key,$this->form[$key],array('id'=>$key))."<span class='viewtext'>".$txtval."<br></span>");
							
						}
						else
						{
							$txtval = $this->form[$key];
							$pagebody->add("<td>".Form::label($key,$this->label[$key]).$this->colon."</td><td>".Form::hidden($key,$this->form[$key],array('id'=>$key))."<span class='viewtext'>".nl2br(HTML::chars($txtval))."</span>");
						}
												
						if($this->formopts[$key]['inputtype']=='input')
						{
							$SIDEINFO_HTML = $this->create_sideinfo($key,$this->formopts[$key]['options']);
							if($SIDEINFO_HTML != ""){ $SIDEINFO_HTML = "&nbsp &nbsp ( ".$SIDEINFO_HTML.")";}
							$pagebody->add($SIDEINFO_HTML);

							$SIDEFUNC_HTML = $this->create_sidefunc($key,$this->formopts[$key]['options']);
							if($SIDEFUNC_HTML != ""){ $SIDEFUNC_HTML = "&nbsp &nbsp ( ".$SIDEFUNC_HTML.")";}
							$pagebody->add($SIDEFUNC_HTML);
						}
						
						$pagebody->add($livetext."</td></tr>\n"); 
					break;
				}
			}
			$pagebody->add("</table>");
			$pagebody->add(Form::hidden('func','*',array('id'=>'func')));
			$lock = $this->param['primarymodel']->get_record_lock(Auth::instance()->get_user()->idname,$this->param['tb_inau'],$this->form['id']);
			if($lock)
			{
				$html = sprintf('<input type="hidden" id="recordlockid" name="recordlockid" value="%s"/>',$lock->id);
				$pagebody->add($html);
			}
			$pagebody->add(Form::close());
		}
		$content->pagebody = $pagebody->get_html();
		$this->param['htmlbody'] = $content;
	}
	
	public function livedelete()
	{
		//set index value invalid if hist syntax passed, cannot delete history record 
		if(strstr($this->param['indexfieldvalue'],';') !== false) {$this->param['indexfieldvalue'] = -1;}
		
		if($_POST['submit']=='Submit')
        {
			if($this->param['primarymodel']->record_exist($this->param['tb_inau'],$this->param['indexfield'],$this->param['indexfieldvalue'],$this->param['indexfieldvalue']))
			{
				$this->livedelete_form();
				$this->param['htmlbody']->pagebody = '<div class="frmmsg">Record [ '.$this->param['indexfieldvalue'].' ] in IHLD or INAU, cannot delete from LIVE.</div>';
			}
			else
			{
				$_POST['func']=='d';
				$this->param['defaultlookupfields']=array_merge($this->param['defaultlookupfields'],$this->frmaudtfields);
				$formarr=$this->param['primarymodel']->get_record_by_id($this->param['tb_live'],$this->param['indexfield'],$this->param['indexfieldvalue'],$this->param['defaultlookupfields']);
				$this->form = (array)$formarr;
				if($this->form)
				{
					$this->param['primarymodel']->set_record_lock(Auth::instance()->get_user()->idname,$this->param['tb_inau'],$this->form['id'],$this->form['record_status']);
				}
				$this->livedelete_form();
			}			
			$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
		}
		else if($_POST['submit']=='Delete')
		{
			$this->livedelete_form();
			if($this->param['primarymodel']->insert_from_table_to_table($this->param['tb_hist'],$this->param['tb_live'],$_POST['id']))
			{
				$this->param['primarymodel']->set_record_status_HIST($this->param['tb_hist'],$_POST['id'],$_POST['current_no']);
				
				$subform_exist = false;
				if($this->subform_exist($parent_idfield,$idval,$subtable_live,$subtable_inau,$subtable_hist,$subtable_idxfld))
				{
					foreach($subtable_live as $key => $table)
					{
						$arr = $this->param['primarymodel']->get_subform_records($subtable_live[$key],$parent_idfield,$idval);
						foreach($arr as $index => $row)
						{
							$sub_post = (array)$row;
							if($this->param['primarymodel']->insert_from_table_to_table($subtable_hist[$key],$table,$sub_post['id']))
							{
								$this->param['primarymodel']->set_record_status_HIST($subtable_hist[$key],$sub_post['id'],$sub_post['current_no']);
							}
							else {	return false; }
						}
					}
					$subform_exist = true;
				}
								
				$this->set_record_status_message($_POST,'deleted from');
				$this->delete_pre_update_existing_record();		
				if($this->param['primarymodel']->delete_record_by_Id($this->param['tb_live'],$_POST['id']))
				{
					if($subform_exist)
					{
						/*cleanup subform inau*/
						foreach($subtable_live as $key => $table)
						{
							$arr = $this->param['primarymodel']->get_subform_records($table,$parent_idfield,$idval);
							foreach($arr as $index => $row)
							{
								$sub_post = (array)$row;
								if($this->param['primarymodel']->delete_record_by_Id($table,$sub_post['id']))
								{/*do nothing*/} else {	return false; }
							}
						}
					}
					$this->delete_post_update_existing_record();	
					$this->param['htmlbody']->pagebody =  $this->get_record_status_message();
					$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
				}
				else
				{
					$this->param['htmlbody']->pagebody= $this->param['primarymodel']->get_db_err_msg();
				}
			}
			else
			{
				$this->param['htmlbody']->pagebody= $this->param['primarymodel']->get_db_err_msg();
			}
		}
	}
	
	public function livedelete_form()
	{
		$content = new View('default_delete');
		//add page/form header
		$content->pageheader = $this->param['pageheader'];
		$pagebody = new Controller_Core_Sitehtml(Form::open($this->param['param_id']));
		//add page/form header
		$content->pageheader = $this->param['pageheader'];
		if(!$this->form)
		{
			$pagebody = new Controller_Core_Sitehtml($this->param['primarymodel']->get_db_err_msg());
		}	
		else
		{	
			// add form
			$pagebody = new Controller_Core_Sitehtml(Form::open($this->param['param_id']));
			$pagebody->add("<table>\n");
			$pagebody->add("<tr valign='center'><td colspan=2>".$this->get_user_controls()."</td></tr>\n");
			//form fields
			foreach($this->form as $key => $value)
			{
				$this->form[$key] = trim($this->form[$key]);
				switch($key)
				{
					case 'inputter':
					case 'input_date':
					case 'authorizer':
					case 'auth_date':
					case 'record_status':
					case 'current_no':
						$pagebody->add("<tr valign='top'><td>".Form::label($key,$this->label[$key]).$this->colon."</td><td>".Form::hidden($key,$this->form[$key],array('id'=>$key))."<span>".HTML::chars($this->form[$key])."</span></td></tr>\n"); 
					break;	
					
					default:
						$pagebody->add("<tr valign='top'>");
						
						if($this->formopts[$key]['inputtype']=='subform')
						{
								
							$SUBFORM_HTML = $this->view_subform($key,$this->form['current_no'],"red","live");
							$pagebody->add("<td>".Form::label($key,$this->label[$key]).$this->colon."</td><td>".Form::hidden($key,$this->form[$key],array('id'=>$key))."<span class='viewtext'>".$SUBFORM_HTML."</span>");
						}
						else if($this->formopts[$key]['inputtype']=='xmltable')
						{
							$XMLTABLE_HTML = $this->view_xml_table($key,$this->form[$key],"red");
							$pagebody->add("<td>".Form::label($key,$this->label[$key]).$this->colon."</td><td>".Form::hidden($key,$this->form[$key],array('id'=>$key))."<span class='viewtext'>".$XMLTABLE_HTML."</span>");
						}
						else
						{
							$pagebody->add("<td>".Form::label($key,$this->label[$key]).$this->colon."</td><td>".Form::hidden($key,$this->form[$key],array('id'=>$key))."<span class='viewtext'>".nl2br(HTML::chars($this->form[$key]))."</span>");
						}
								
						if($this->formopts[$key]['inputtype']=='input')
						{
							$SIDEINFO_HTML = $this->create_sideinfo($key,$this->formopts[$key]['options']);
							if($SIDEINFO_HTML != ""){ $SIDEINFO_HTML = "&nbsp &nbsp ( ".$SIDEINFO_HTML.")";}
							$pagebody->add($SIDEINFO_HTML);
						
							$SIDEFUNC_HTML = $this->create_sidefunc($key,$this->formopts[$key]['options']);
							if($SIDEFUNC_HTML != ""){ $SIDEFUNC_HTML = "&nbsp &nbsp ( ".$SIDEFUNC_HTML.")";}
							$pagebody->add($SIDEFUNC_HTML);
						}
						$pagebody->add("</td></tr>\n");
					
					break;
				}
			}
			$pagebody->add("</table>");
			$pagebody->add(Form::hidden('func','*',array('id'=>'func')));
			$lock = $this->param['primarymodel']->get_record_lock(Auth::instance()->get_user()->idname,$this->param['tb_inau'],$this->form['id']);
			if($lock)
			{
				$html = sprintf('<input type="hidden" id="recordlockid" name="recordlockid" value="%s"/>',$lock->id);
				$pagebody->add($html);
			}
			$pagebody->add(Form::close());
		}
		$content->pagebody = $pagebody->get_html();
		$this->param['htmlbody'] = $content;
	}

	public function hold()
	{
		//add  formdata add to database, INAU table updated
		$this->input_form('i');
		//remove recordlocks before index
		$this->param['primarymodel']->remove_record_lock_by_id($_POST['recordlockid']);
		//removing indexes 'submit and 'func' from array, do not need for update, not database fields
		unset($_POST['submit']); unset($_POST['func']); unset($_POST['preval']); unset($_POST['recordlockid']); unset($_POST['bttnclicked']);
		unset($_POST['js_idname']); unset($_POST['js_tmpvar']);
			
		//set audit data
		$_POST['inputter']=Auth::instance()->get_user()->idname; $_POST['authorizer']='SYSINAU';
		$_POST['input_date']=date('Y-m-d H:i:s'); $_POST['auth_date']=date('Y-m-d H:i:s');  $_POST['record_status']='IHLD';
		
		if( $this->param['primarymodel']->update_record($this->param['tb_inau'],$_POST))
		{
			$this->create_subform_records();
			$this->set_record_status_message($_POST);
			$this->param['htmlbody']->pagebody =  $this->get_record_status_message();
			$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
		}
	}
	
	public function validate()
	{
		$errmsg = "";
		$this->input_validation();
		if (!$this->param['isinputvalid'])
		{
			$this->input_repopulate($_POST['preval']);
			$this->input_form('l');
			
			foreach ($this->param['inputerrors'] as $key => $value)
			{
				$errmsg .= $value.'<br/>';	
			}
			$this->param['htmlbody'] .= $this->validation_alert_window($errmsg);
			$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
			return false;
		}
		else
		{
			$this->input_repopulate($_POST['preval']);
			$this->input_form('l');
			$errmsg .= 'All input data ok, no errors.';	
			if($_POST['submit'] == 'Validate')
			{	
				$this->param['htmlbody'] .= $this->validation_alert_window($errmsg);
				$this->set_page_content($this->param['htmlhead'],$this->param['htmlbody']);
			}
			return true;
		}
	}

	function validation_alert_window($errmsg)
	{
		$HTML= <<<_TEXT_
		<div id="validatewin" class="easyui-dialog" title="Validation Alert" modal="true" resizable="true" buttons="#validatewin-buttons">
 			<div id="validate_error">
			$errmsg
			</div>
		</div>
		<div id="validatewin-buttons"><a href="#" class="easyui-linkbutton" onclick="javascript:$('#validatewin').dialog('close')">Close</a></div>  
_TEXT_;
		return $HTML;
	}

	function input_validation()
	{
		$post = $_POST;	
		//validation rules
		$validation = new Validation($post);
		$validation->pre_filter('trim', TRUE);
		$this->param['isinputvalid'] = $validation->validate();
		$this->param['validatedpost'] = (array) $validation;
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

	function process_enquiry($arr=array())
	{
		if(!Auth::instance()->logged_in())
		{
			//$this->redirect_to_login();	
		}
		else
		{
			$htmlhead = new Controller_Core_Sitehtml;
		
			// External
			$htmlhead->add( HTML::style($this->css['tablesorterblue'], array('screen')) );
			$htmlhead->add( HTML::style($this->css['easyui'], array('screen')) );
			$htmlhead->add( HTML::style($this->css['easyui_icon'], array('screen')) );
		
			// Internal
			$htmlhead->add( HTML::style($this->css['site'], array('screen')) );

			// External
			$htmlhead->add( HTML::script($this->js['jquery']) );
			$htmlhead->add( HTML::script($this->js['easyui']) );
			$htmlhead->add( HTML::script($this->js['tablesorter']) );
			$htmlhead->add( HTML::script($this->js['tablesorterpager']) );

			$controller = $this->param['controller'];
	
			$TEXT = <<<_TEXT_
			<script type="text/javascript">
			controller = "$controller";
			if(controller == "message")
			{
				//sort on firstcolumn(id) desc
				$(function() 
					{ 
						$("#enqrestab").tablesorter({sortList:[[0,1]], widgets: ['zebra']}) 
						.tablesorterPager({container: $("#enqrespager")});
					}
				);
			}
			else
			{
				$(function() 
					{		
						$("#enqrestab").tablesorter({sortList:[[0,0]], widgets: ['zebra']})
						.tablesorterPager({container: $("#enqrespager")});	
						$("#options").tablesorter({sortList: [[0,0]], headers: { 3:{sorter: false}, 4:{sorter: false}}});
					}
				);
			}
			</script> 
_TEXT_;
			$htmlhead->add( $TEXT );
			//$htmltable = '<div id="resdiv" style="border:0px solid red; padding: 0px 0px 0px 0px; overflow:auto;">'."\n";
			$htmltable = '<table id="enqrestab" class="tablesorter" border="0" cellpadding="0" cellspacing="1" width=500%>'."\n";
			$firstpass = true;
			$lbl=$this->label;
			foreach($arr as $row => $linerec)
			{	
				$header = ''; $data = '';
				foreach ($linerec as $key => $value)
				{
					if($firstpass)
					{
						$header .= '<th>'.$lbl[$key].'</th>'; 
					}
					if($key == 'id')
					{
						$data .= '<td>'.html::anchor($this->param['param_id'].'/index/'.$value,$value,array('target'=>'input'));
					}
					else
					{
						$data .= '<td>'.$value.'</td>'; 
					}
				}
				if($firstpass)
				{
					$header = "\n".'<thead>'."\n".'<tr>'.$header.'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
					$htmltable.=$header;
				}
				$data = '<tr>'.$data.'</tr>'."\n"; 
				$htmltable.= $data;
				$firstpass = false;
			}
			$htmltable.= '</tbody>'."\n".'</table><br><br>'."\n";
			//$htmltable.= $this->enquiry_pager();
			$pagebody = new Controller_Core_Sitehtml($htmltable);
			$this->set_page_content($htmlhead->get_html(),$pagebody->get_html());
		}
	}	
	
	function action_enquirydefault()
	{
		if(!Auth::instance()->logged_in())
		{
			//$this->redirect_to_login();	
		}
		else
		{
			//$this->before();
//print "<b>[DEBUG]---></b> "; print_r($this->param); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );

			$sc = new Controller_Core_Sitecontrol();
			$pagehead = new Controller_Core_Sitehtml( $this->param['enqhead'] );
			$pagebody = new Controller_Core_Sitehtml( $sc->show_tabs( $this->param['param_id'],$this->param['controller'] ));
			$this->set_page_content($pagehead->get_html(),$pagebody->get_html());
		}
	}
	
	public static function enquiry_pager()
	{
		$baseurl = URL::base();
		$first = $baseurl.'media/img/site/first.png';
		$last  = $baseurl.'media/img/site/last.png';
		$next  = $baseurl.'media/img/site/next.png';
		$prev  = $baseurl.'media/img/site/prev.png';
		
		$HTML = <<<_HTML_
		<div id="enqrespager" class="pager" style="border:0px solid red;padding:0px 0px 0px 0px;">
			<form>
				<img src="$first" class="first"/>
				<img src="$prev" class="prev"/>
				<input type="text" class="pagedisplay"/>
				<img src="$next" class="next"/>
				<img src="$last" class="last"/>
				<select class="pagesize">
					<option value="10">10</option>
					<option selected="selected" value="20">20</option>
					<option value="30">30</option>
					<option  value="40">40</option>
				</select>
			</form>
			</div>
_HTML_;
		return $HTML;
	}

	public function popout_selector_window()
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
	
	public function custom_dialog_window()
	{
		$HTML = <<<_HTML_
		<div id="chklight" class="white_content"  buttons="#chklight-buttons">
			<div id="chkresult"></div>
		</div>
		<div id="chklight-buttons" style="background-color:#ebf2f9; display:none;"><a href="#" class="easyui-linkbutton" onclick="javascript:siteutils.closeDialog('chklight',false)">Close</a></div>
		<div id="fade" class="black_overlay"></div>
_HTML_;
		return $HTML;
	}

	public function create_popout($key, $current_no)
	{
		$POPOUT_HTML = "";
		if(($this->formopts[$key]['enable_on_edit'] == "readonly" || $this->formopts[$key]['enable_on_edit'] == "disabled") && $_POST['func']=="i" && $current_no > 0)
		{
			return $POPOUT_HTML;
		}
		else if(isset($this->popout[$key]))
		{
			//preg_match('/\.([^\.]*$)/i'
			if (preg_match('/yes/i', $this->popout[$key]['enable']) || $this->popout[$key]['enable']==1) 
			{
				$fields = sprintf('"%s"',$this->popout[$key]['selectfields']);
				$table	= sprintf('"%s"',$this->popout[$key]['table']);
				$idfield  = sprintf('"%s"',$this->popout[$key]['idfield']);
				$returnfield = sprintf('"%s"',$key);
				$baseurl = sprintf('<img src="%smedia/img/site/%s" align=absbottom>',url::base(),"lubw020.png");
				$POPOUT_HTML = sprintf('<a href = "javascript:void(0)" onclick=window.popout.SelectorOpen(%s,%s,%s,%s) class="aimg">&nbsp %s &nbsp</a>',$fields,$table,$idfield,$returnfield,$baseurl);
			}
		}
		return $POPOUT_HTML;
	}
	
	public function create_sideinfo($key,&$options,$val="")
	{
		$SIDEINFO_HTML = "";
		if($val==""){$val=$this->form[$key];}
		if(isset($this->sideinfo[$key]))
		{
			if (preg_match('/yes/i', $this->sideinfo[$key]['enable']) || $this->sideinfo[$key]['enable']==1) 
			{
				$fields			= trim(sprintf('"%s"',$this->sideinfo[$key]['selectfields']));
				$table			= trim(sprintf('"%s"',$this->sideinfo[$key]['table']));
				$idfield		= trim(sprintf('"%s"',$this->sideinfo[$key]['idfield']));
				$returnfield	= trim(sprintf('"%s"',$key));
				$format			= trim(sprintf('"%s"',$this->sideinfo[$key]['format']));
												
				$this->formopts[$key]['options'] = str_replace("%FIELDS%",$fields, $this->formopts[$key]['options']); 
				$this->formopts[$key]['options'] = str_replace("%TABLE%",$table, $this->formopts[$key]['options']); 
				$this->formopts[$key]['options'] = str_replace("%IDFIELD%",$idfield, $this->formopts[$key]['options']); 
				$this->formopts[$key]['options'] = str_replace("%RETFIELD%",$returnfield, $this->formopts[$key]['options']); 
				$this->formopts[$key]['options'] = str_replace("%FORMAT%",$format, $this->formopts[$key]['options']); 
				$idval = sprintf('"%s"',$val);
				$options = $this->formopts[$key]['options'];
								
				$baseurl = URL::base(TRUE,'http');
				$url = sprintf('%score_ajaxtodb?option=sideinfo&fields=%s&table=%s&idfield=%s&idval=%s&format=%s',$baseurl,$fields,$table,$idfield,$idval,$format);
				$url = str_replace('"','',$url);

				$loadval = Controller_Core_Sitehtml::get_HTML_from_url($url);
				$SIDEINFO_HTML = sprintf('<span id="%s_sideinfo" name="%s_sideinfo"> %s</span>',$key,$key,$loadval)."\n";
			}
		}
		return $SIDEINFO_HTML;
	}
	
	public function create_sidefunc($key,&$options,$val="")
	{
		$SIDEFUNC_HTML = "";
		if($val==""){$val=$this->form[$key];}
		if(isset($this->sidefunc[$key]))
		{
			if (preg_match('/yes/i', $this->sidefunc[$key]['enable']) || $this->sidefunc[$key]['enable']==1) 
			{
				$func = sprintf('"%s"',$this->sidefunc[$key]['func']);
				$value	= sprintf('"%s"',$this->form[$key]);
				$format = sprintf('"%s"',$this->sidefunc[$key]['format']);
				$idfield = sprintf('"%s"',$this->sidefunc[$key]['idfield']);
				
				$this->formopts[$key]['options'] = str_replace("%FUNC%",$func, $this->formopts[$key]['options']); 
				$this->formopts[$key]['options'] = str_replace("%PARAMFLD%",$idfield, $this->formopts[$key]['options']); 
				$this->formopts[$key]['options'] = str_replace("%SFFORMAT%",$format, $this->formopts[$key]['options']);

				$options = $this->formopts[$key]['options'];
				$baseurl = URL::base(TRUE,'http');
				$url = sprintf('%score_ajaxtodb?option=sidefunc&func=%s&parameter=%s&format=%s',$baseurl,$func,$value,$format);
				$url = str_replace('"','',$url);

				$loadval = Controller_Core_Sitehtml::get_HTML_from_url($url);
				$SIDEFUNC_HTML = sprintf('<span id="%s_sidefunc" name="%s_sidefunc"> %s</span>',$key,$key,$loadval)."\n";
			}
		}
		return $SIDEFUNC_HTML;
	}

	public function create_sidelink($key,$current_no)
	{
		$SIDEFUNC_LINK = ""; $linkhtml="";
		if(($this->formopts[$key]['enable_on_edit'] == "readonly" || $this->formopts[$key]['enable_on_edit'] == "disabled") && $_POST['func']=="i" && $current_no > 0)
		{
			return $SIDEFUNC_LINK;
		}
		else if(isset($this->sidelink[$key]))
		{
			foreach($this->sidelink[$key] as $indx => $linkarr)
			{
				$linkarr['attr'] = str_replace("%THISFIELD%",$key, $linkarr['attr']); 
				if(strstr($linkarr['text'],'%IMG%') !== false)
				{
					$baseurl = url::base(TRUE,'http');
					$baseurl = str_replace("index.php/","",$baseurl);
					$linkarr['text'] = sprintf('<img src="%s%s" align="absbottom"/>',$baseurl,$linkarr['text']);
					$linkarr['text'] = str_replace("%IMG%","",$linkarr['text']);
				}
				$linkhtml	.= sprintf('<a href="%s" %s>%s</a> ',$linkarr['src'],$linkarr['attr'],$linkarr['text']);
			}
		}
		$SIDEFUNC_LINK = sprintf('<span id="%s_sidelink" name="%s_sidelink"> %s</span>',$key,$key,$linkhtml)."\n";
		return $SIDEFUNC_LINK;
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

	public function create_subform_json_load($key,$current_no,$subtable_type=false)
	{
		$TABLE = ""; $TABLEHEAD = ""; $TABLEROWS = ""; $columnfield = array(); $columnlabel = array(); 
		$subcontroller = $this->subform[$key]['subformcontroller'];
		$subheader	= $this->label[$key];
	
		$columnfield = $this->param['primarymodel']->getSubFormFields($subcontroller,$columnlabel);
		$subopt = $this->param['primarymodel']->getSubFormOptions($subcontroller);
		$idfield = $this->param['indexfield'];
		$idval = $this->form[$idfield];

		$TABLEHEAD .= "<thead>"."\n"."<tr valign='top'>"."\n";
		foreach($subopt as $subkey => $row)
		{
			if(isset($row['width'])){ $width = $row['width']; } else { $width = ""; }
			if(isset($row['align'])){ $align = $row['align']; } else { $align = ""; }
			if(isset($row['formatter'])){ $formatter = $row['formatter']; } else { $formatter = ""; }
			if(isset($row['editor'])){ $editor = $row['editor']; } else { $editor = ""; }
			$subname = $row['subname'];
			$vals = preg_split('/:/',$row['subname']);
			if(is_array($vals) && count($vals)==2)
			{
				$subname = $vals[1];
			}			
			$TABLEHEAD .= "\t".sprintf('<th field="subform_%s_%s" width="%s" align="%s" formatter="%s" editor="%s"><b>%s</b></th>',$key,$subname,$width,$align,$formatter,$editor,$row['sublabel'])."\n";
		}
		
		$TABLEHEAD .= "</tr>"."\n"."</thead>"."\n\n";
		
		$style = 'style="width:800px; height:250px;"'; 
		$baseurl = url::base(TRUE,'http');
		$ttype ="";
		if($subtable_type)
		{
			$ttype =sprintf('&tabletype=%s',$subtable_type);
		}
		$url = sprintf('%score_ajaxtodb?option=jsubrecs&subcontroller=%s&parentfield=%s&idfield=%s&idval=%s&curno=%s%s',$baseurl,$subcontroller,$key,$idfield,$idval,$current_no,$ttype);

$TABLETAG = "\n\n".sprintf('<div id="sf" class="sf"><table %s id="subform_table_%s" resizable="true" title="%s" singleSelect="true" idField="subform_%s_id" url="%s">',$style,$key,$subheader,$key,$url)."\n\n";
$TABLE = $TABLETAG."\n".$TABLEHEAD."</table></div>"."\n";
		$TABLE .= sprintf('<div id="subform_summary_%s"></div>',$key); 
		return $TABLE;
	}

	public function create_subform($key,$current_no,$subtable_type=false)
	{
		$TABLE = ""; $TABLEHEAD = ""; $TABLEROWS = "";
		$subcontroller = $this->subform[$key]['subformcontroller'];
		$subheader	= $this->label[$key];
		$idfield = $this->param['indexfield'];
		$idval   = $this->form[$idfield];
		$subopt  = $this->param['primarymodel']->get_subform_options($subcontroller);
		$results = $this->param['primarymodel']->get_subform_view_records($subcontroller,$idfield,$idval,$current_no,$subtable_type,$labels);
		
		$TABLEHEAD .= "<thead>"."\n"."<tr valign='top'>"."\n";
		foreach($subopt as $subkey => $row)
		{
			if(isset($row['width'])){ $width = $row['width']; } else { $width = ""; }
			if(isset($row['align'])){ $align = $row['align']; } else { $align = ""; }
			if(isset($row['formatter'])){ $formatter = $row['formatter']; } else { $formatter = ""; }
			if(isset($row['editor'])){ $editor = $row['editor']; } else { $editor = ""; }
			$subname = $row['subname'];
			$vals = preg_split('/:/',$row['subname']);
			if(is_array($vals) && count($vals)==2)
			{
				$subname = $vals[1];
			}			
			$TABLEHEAD .= "\t".sprintf('<th field="subform_%s_%s" width="%s" align="%s" formatter="%s" editor="%s"><b>%s</b></th>',$key,$subname,$width,$align,$formatter,$editor,$row['sublabel'])."\n";
		}
		$TABLEHEAD .= "</tr>"."\n"."</thead>"."\n\n";
		
		$TABLEROWS = "\n"."<tbody>"."\n";
		foreach($results as $index => $row)
		{
			$TABLEROWS .= "<tr valign='top'>";
			$obj = (array) $row;
			foreach($obj as $subkey => $val)
			{
				$TABLEROWS .= sprintf('<td field="subform_%s_%s">%s</td>',$key,$subkey,$val);
			}
			$TABLEROWS .= "</tr>";
		}
		$TABLEROWS .= "\n"."</tbody>"."\n";
		$style = 'style="width:800px; height:250px;"'; 
$TABLETAG = "\n\n".sprintf('<div id="sf" class="sf"><table %s id="subform_table_%s" resizable="true" title="%s" singleSelect="true" idField="subform_%s_id">',$style,$key,$subheader,$key)."\n\n";
		$TABLE = $TABLETAG."\n".$TABLEHEAD.$TABLEROWS."</table></div>"."\n";
		$TABLE .= sprintf('<div id="subform_summary_%s"></div>',$key); 
		return $TABLE;
	}

	public function create_subform_from_xml($key,$xml)
	{
		$TABLE = ""; $TABLEHEAD = ""; $TABLEROWS = "";
		$subcontroller = $this->subform[$key]['subformcontroller'];
		$subheader	= $this->label[$key];
		$idfield = $this->param['indexfield'];
		$idval   = $this->form[$idfield];
		$subopt  = $this->param['primarymodel']->get_subform_options($subcontroller);
		
		$rows = new SimpleXMLElement($xml);
		$TABLEHEAD .= "<thead>"."\n"."<tr valign='top'>"."\n";
		foreach($subopt as $subkey => $row)
		{
			if(isset($row['width'])){ $width = $row['width']; } else { $width = ""; }
			if(isset($row['align'])){ $align = $row['align']; } else { $align = ""; }
			if(isset($row['formatter'])){ $formatter = $row['formatter']; } else { $formatter = ""; }
			if(isset($row['editor'])){ $editor = $row['editor']; } else { $editor = ""; }
			$subname = $row['subname'];
			$vals = preg_split('/:/',$row['subname']);
			if(is_array($vals) && count($vals)==2)
			{
				$subname = $vals[1];
			}			
			$TABLEHEAD .= "\t".sprintf('<th field="subform_%s_%s" width="%s" align="%s" formatter="%s" editor="%s"><b>%s</b></th>',$key,$subname,$width,$align,$formatter,$editor,$row['sublabel'])."\n";
		}
		$TABLEHEAD .= "</tr>"."\n"."</thead>"."\n\n";
				
		$TABLEROWS = "\n"."<tbody>"."\n";
		foreach ($rows->row as $row)
		{
			$TABLEROWS .= "<tr valign='top'>";
			foreach($subopt as $subkey => $field)
			{
			//foreach ($row->children() as $field)
			//{
				//$subkey = sprintf('%s',$field->getName() );
				//$val	= sprintf('%s',$row->$subkey);
				$subname = $field['subname'];
				$vals = preg_split('/:/',$field['subname']);
				if(is_array($vals) && count($vals)==2)
				{
					$subname = $vals[1];
				}	
				$val = sprintf('%s',$row->$subname);
				if($val == "undefined" || $val == "null" || $val == "Array") { $val = ""; } 
				$TABLEROWS .= sprintf('<td field="subform_%s_%s">%s</td>',$key,$subname,$val);
			}
			$TABLEROWS .= "</tr>";
		}
		$TABLEROWS .= "\n"."</tbody>"."\n";
		$style = 'style="width:800px; height:350px;"'; 
$TABLETAG = "\n\n".sprintf('<div id="sf" class="sf"><table %s id="subform_table_%s" resizable="true" title="%s" singleSelect="true" idField="subform_%s_id">',$style,$key,$subheader,$key)."\n\n";
		$TABLE = $TABLETAG."\n".$TABLEHEAD.$TABLEROWS."</table></div>"."\n";
		$TABLE .= sprintf('<div id="subform_summary_%s"></div>',$key); 
		return $TABLE;
	}
	
	public function  view_xml_table($key,$xml,$color)
	{
		$controller = $this->param['controller'];
		$TABLEHEADER = ""; $TABLEROWS ="";
		$HTML = "<table id='subformview' width='90%'>"."\n";

		$subopt  = $this->param['primarymodel']->getFormSubTableOptions($controller,$key);
		foreach($subopt as $subkey => $row)
		{
			$sublabel = $row['sublabel'];
			$TABLEHEADER .= sprintf("<th>%s</th>",$sublabel);
		}

		$TABLEHEADER = "<tr valign='top'>".$TABLEHEADER."</tr>"."\n";
		
		$formfields = new SimpleXMLElement($xml);
		foreach($formfields->rows->row as $row)
		{
			$TABLEROWS .= "<tr>";
			foreach ($row->children() as $field)
			{
				$subkey = sprintf('%s',$field->getName() );
				$val	= sprintf('%s',$row->$subkey);
				$TABLEROWS .= sprintf("<td valign='top' style='color:%s;'>%s</td>",$color,$val);
			}
			$TABLEROWS .= "</tr>";
		}

		$HTML .= $TABLEHEADER.$TABLEROWS."\n"."</table>"."\n";
		return $HTML;
	}

	public function  edit_xml_table($key)
	{
		$TABLEHEADER = ""; $TABLEROWS ="";
		$subtable_id = "subform_table_".$key;
		$baseurl = url::base(TRUE,'http');
		$controller = $this->param['controller'];
		$field = $key;
		$idfield = $this->param['indexfield'];
		$idval = $this->form[$idfield];
		$prefix = sprintf('subform_%s_',$key);
		$tabletype = "inau";
$url = sprintf('%score_ajaxtodb?option=jxmldatabyid&controller=%s&field=%s&idfield=%s&idval=%s&prefix=%s&tabletype=%s',$baseurl,$controller,$field,$idfield,$idval,$prefix,$tabletype);
$JSURL = sprintf('<script type="text/javascript">%s_dataurl="%s"</script>',$subtable_id,$url);
$HTML = "\n".'<div id="sf" class="sf">'.sprintf('<table id="%s" class="easyui-datagrid" resizable="true" singleSelect="true"  style="width:800px; height:auto;">',$subtable_id)."\n";
$HTML .= "</table></div>"."\n";

		$COLDEF = $this->xml_subform_columndef($controller,$key);
		$HTML .= $HTML.$JSURL."\n".$COLDEF."\n";
		return $HTML;
	}
	
	public function xml_subform_columndef($controller,$key)
	{
		$COLDEFROW = ""; $coldef = "";
		$subopt  = $this->param['primarymodel']->getFormSubTableOptions($controller,$key);
		foreach($subopt as $subkey => $row)
		{
			$coldef = "";
			$sublabel = $row['sublabel']; 
			if(isset($row['width'])){ $coldef .= sprintf("width:%s,",$row['width']); } else { $width = ""; }
			if(isset($row['align'])){ $coldef .= sprintf("align:'%s',",$row['align']); } else { $align = ""; }
			if(isset($row['formatter'])){ $coldef .= sprintf("formatter:%s,",$row['formatter']); } else { $formatter = ""; }
			if(isset($row['editor'])){ $coldef .= sprintf("editor:%s,",$row['editor']); } else { $editor = ""; }
			$coldef = substr_replace($coldef, '', -1);
			$subname = $row['subname'];
			$vals = preg_split('/:/',$row['subname']);
			if(is_array($vals) && count($vals)==2)
			{
				$subname = $vals[1];
			}			
			//$coldef = sprintf("
			$COLDEFROW .= sprintf("{field:'subform_%s_%s',title:'<b>%s</b>',%s},",$key,$subname,$sublabel,$coldef)."\n";
		}
		
		$colArr = $key."_colArr";
		$DefaultColumns = $key."_DefaultColumns(tt)";
		$TEXT=<<<_text_
		<script type="text/javascript">
		function $DefaultColumns
		{
$colArr = [[
$COLDEFROW
		]]
		}
		</script>
_text_;
		$ADDTIONALTEXT = $this->xml_subform_additional_columndef();
		return $TEXT.$ADDTIONALTEXT;
	}

	public function view_subform($key,$current_no,$color,$subtable_type=false)
	{
		$subcontroller = $this->subform[$key]['subformcontroller'];
		$idfield = $this->param['indexfield'];
		$idval =  $this->form[$idfield];

		$results = $this->param['primarymodel']->getSubFormViewRecords($subcontroller,$idfield,$idval,$current_no,$subtable_type,$labels);
		$HTML  = $this->subform_html($results,$labels,$color);
		$HTML .= $this->subform_summary_html($results,$labels,$color);
		return $HTML;
	}
	
	public function  subform_html($results,$labels,$color)
	{
		$HTML = "<table id='subformview' width='90%'>"."\n";
		$TABLEHEADER = ""; $TABLEROWS ="";
//print_r($results);
//print "[DATA]<hr>";
		foreach($labels as $key => $val)
		{
			$TABLEHEADER .= sprintf("<th>%s</th>",$val);
		}
		$TABLEHEADER = "<tr valign='top'>".$TABLEHEADER."</tr>"."\n";

		foreach($results as $index => $row)
		{
			$TABLEROWS .= "<tr>";
			$obj = (array) $row;
			foreach($obj as $key => $val)
			{
				$TABLEROWS .= sprintf("<td valign='top' style='color:%s;'>%s</td>",$color,$val);
			}
			$TABLEROWS .= "</tr>";
		}

		$HTML .= $TABLEHEADER.$TABLEROWS."\n"."</table>"."\n";
		return $HTML;
	}

	public function subform_exist(&$parent_idfield,&$idval,&$subtable_live,&$subtable_inau,&$subtable_hist,&$subtable_idxfld)
	{
		$subform_exist = false;
		if($result = $this->param['primarymodel']->get_subform_controller($this->param['controller']))
		{	
			$subform_exist = true;
			$parent_idfield = $this->param['indexfield'];
			$idval = $_POST[$parent_idfield];
			foreach($result as $key => $val)
			{
				$paramdef = $this->param['primarymodel']->get_controller_params($val);
				$subtable_inau[$key]   = $paramdef['tb_inau'];
				$subtable_hist[$key]   = $paramdef['tb_hist'];
				$subtable_live[$key]   = $paramdef['tb_live'];
				$subtable_idxfld[$val] = $paramdef['indexfield'];
			}
		}
		return $subform_exist;
	}

	public function create_subform_records()
	{
		if($this->subform_exist($parent_idfield,$idval,$subtable_live,$subtable_inau,$subtable_hist,$subtable_idxfld))
		{
			$rowsExist = false;
			foreach($subtable_inau as $key => $subtable)
			{
				$xml = simplexml_load_string($_POST[$key]);
				$json = json_encode($xml);
				$arr = json_decode($json,TRUE);
	
				if(isset($arr['row'])) 
				{ 
					$tmp = $arr['row'];  
					if(!isset($tmp['id'])) { $arr = $arr['row'];} 
					$rowsExist = true;
				}
	
				$querystr = sprintf('delete from %s where %s = "%s"',$subtable,$parent_idfield,$idval);
				if($result = $this->param['primarymodel']->execute_delete_query($querystr))
				{
					if($rowsExist)
					{
						foreach ($arr as $index => $row)
						{
							$row['inputter']		= $_POST['inputter'];
							$row['authorizer']		= $_POST['authorizer'];
							$row['input_date']		= $_POST['input_date'];
							$row['auth_date']		= $_POST['auth_date'];
							$row['record_status']	= $_POST['record_status'];
							$row['current_no']		= $_POST['current_no'];
					
							$list = $this->subform_field_exclusion_list();
							if(isset($list[$key]))
							{
								foreach($list[$key] as $idx => $fld)
								{
									if(isset($row[$fld])) { unset($row[$fld]); }
								}
							}

							if($row['id'] == "undefined")
							{
								//get id for record, then update fields
								$subformarr = $this->param['primarymodel']->create_blank_record($subtable_live[$key],$subtable);
								$subform = (array) $subformarr;
								array_merge($row,$subform);
								$row['id']	= $subform['id'];
								if($this->param['primarymodel']->update_record($subtable,$row))
								{ } else {	return false; }
							}
							else
							{
								//delete exist record, re-insert with updated fields
								if($this->param['primarymodel']->insert_record($subtable,$row))
								{ } else {	return false; }
							}
						}
					}
				}
			}
		}
	}
	
	public static function strtotitlecase($str)
	{
		$str = strtolower($str);
		return preg_replace('/\b(\w)/e', 'strtoupper("$1")', $str);
	} 

	/*formless functions*/
	public function get_formless_record($idval)
	{
		$this->param['defaultlookupfields']=array_merge($this->param['defaultlookupfields'],$this->frmaudtfields);
		$formarr=$this->param['primarymodel']->get_record_by_lookup($this->param['tb_live'],$this->param['tb_inau'],$this->param['indexfield'],$idval,$this->param['defaultlookupfields'],'i');
		$this->form = (array)$formarr;
		if($this->form)
		{
			$this->form['record_status'] = 'IHLD';
			$this->param['primarymodel']->set_record_status($this->param['tb_inau'],$this->form['id'],$this->form['record_status']);;
			return $this->form;
		}
		return false;
	}
	
	public function update_formless_record($form) 
	{
		$_POST = $form;
		//setup authorization data
		$this->param['pageheader'] = $this->getPageHeader($this->param['appheader'],"");
		$_POST['submit']='Authorize'; $_POST['recordlockid']=0; $_POST['func']=""; 
		$_POST['preval']=""; $_POST['auth']=""; $_POST['rjct']="";
				
		//set audit data
		$_POST['inputter']=Auth::instance()->get_user()->idname; $_POST['authorizer']="";
		$_POST['input_date']=date('Y-m-d H:i:s'); $_POST['auth_date']="";  $_POST['record_status']='INAU';
		
		if( $this->param['primarymodel']->update_record($this->param['tb_inau'],$form))
		{
			//create update subform records if any
			$this->create_subform_records();
			return true;
		}
		return false;
	}
	
	public function duplicate_altid($validation,$field,$id,$alt_id)
    {
		if ($this->param['primarymodel']->is_duplicate_unique_id($this->param['tb_inau'],$field,$id,$alt_id) || $this->param['primarymodel']->is_duplicate_unique_id($this->param['tb_live'],$field,$id,$alt_id))
        {
            $validation->error($field, 'msg_duplicate');
        }
	}
	
	/*abstracts*/
	public function authorize_pre_insert_new_record(){}
	public function authorize_post_insert_new_record(){}
	public function authorize_pre_update_existing_record(){}		
	public function authorize_post_update_existing_record(){}		

	public function input_pre_validation(){}
	public function input_pre_update_existing_record(){}		
	public function input_post_update_existing_record(){}
	
	public function delete_pre_update_existing_record(){}		
	public function delete_post_update_existing_record(){}
	
	public function view_pre_open_existing_record(){}		
	public function view_post_open_existing_record(){}

	public function subform_summary_html($results=null,$labels=null) {}
	public function subform_field_exclusion_list() { return false;}
	public function xml_subform_additional_columndef() { return "";}
}
?>
