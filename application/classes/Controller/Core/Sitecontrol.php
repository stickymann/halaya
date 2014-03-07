<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates user CRUD (HVA) selection controls based security profile permissions.  
 *
 * $Id: Sitecontrol.php 2012-12-31 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sitecontrol extends Controller
{	
	public $OBJPOST = array();
	public $param = array();

	public function __construct($param_id="",$controller="",$idname="")
    {
		$this->OBJPOST = $_POST;
		$this->param['primarymodel'] = new Model_SiteDB();
		$this->param['url_input']	 = $param_id;
		$this->param['controller']	 = $controller;
		$this->param['controls']	 = array();
		$this->param['idname'] = $idname;
		$this->param['globalauthmodeon'] = false;
		$this->param['controllerauthmodeon'] = false;
		$this->param['globalindexfldon'] = false;
	}
	
	public function set_global_auth_mode_on($val)
	{
		$this->param['globalauthmodeon'] = $val;
	}
	
	public function set_controller_auth_mode_on($val)
	{
		$this->param['controllerauthmodeon'] = $val;
	}

	public function set_global_index_fld_on($val)
	{
		$this->param['globalindexfldon'] = $val;
	}

	public function set_input_controls()
	{
		
		$control = array('if'=>false,'vw'=>false,'pr'=>false,'nw'=>false,'cp'=>false,'iw'=>false,'in'=>false,'ao'=>false,'as'=>false,'rj'=>false,'de'=>false,'hd'=>false,'va'=>false,'df'=>false,'ls'=>false,'hs'=>false,'is'=>false,'ex'=>false);
		$menu = $this->param['primarymodel']->get_user_menu_controls('menudefs_users','url_input',$this->param['url_input'],Auth::instance()->get_user()->idname);
		if($menu)
		{
			$lookup = preg_split('/,/',$menu->controls_input);
			foreach($lookup as $key)
			{
				$control[$key]=true;
			}

			$lookup = preg_split('/,/',$menu->controls_enquiry);
			foreach($lookup as $key)
			{
				$control[$key]=true;
			}

			if(!$this->param['globalauthmodeon'] || !$this->param['controllerauthmodeon'])
			{
				$control['ao']=false;
				$control['as']=false;
			}

			if(!$this->param['globalindexfldon'])
			$control['if']=false;
			$this->param['controls'] = $control;
		}
		else
		{
			$this->param['controls'] = array('vw'=>false,'pr'=>false,'nw'=>false,'cp'=>false,'iw'=>false,'in'=>false,'ao'=>false,'as'=>false,'rj'=>false,'de'=>false,'hd'=>false,'va'=>false,'df'=>false,'ls'=>false,'hs'=>false,'is'=>false,'ex'=>false);
		}
	}
	
	public function get_input_controls()
	{	
		$post =$this->OBJPOST;
		$controls = "";
		$func='?';
		
		$ctrl = $this->param['controls'];
//print "<b>[DEBUG]---></b> "; print_r($ctrl); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		if(!$post)
		{
			if( isset($ctrl['if']) )
			{
				$controls .= Form::submit('submit','Submit',array('id'=>'submit','class="bttn"')).'&nbsp</td><td>';
				//should i check for if key exist in controls array? with frontend errors should never occur 
				//if (array_key_exists('vw', $this->param['controls']->control['vw']))
			
				if($ctrl['vw']) 
					$controls .= Form::radio('func','v',TRUE,array('id'=>'func')).Form::label('func','view').'&nbsp';
			
				if($ctrl['nw']) 
					$controls .= Form::radio('func','n',FALSE,array('id'=>'func')).Form::label('func','new').'&nbsp';
			
				if($ctrl['cp']) 
					$controls .= Form::radio('func','c',FALSE,array('id'=>'func')).Form::label('func','copy').'&nbsp';
						
				if($ctrl['in']) 
					{$controls .= Form::radio('func','i',FALSE,array('id'=>'func')).Form::label('func','edit').'&nbsp';}
				else if($ctrl['iw']) 
					{$controls .= Form::radio('func','w',FALSE,array('id'=>'func')).Form::label('func','edit new').'&nbsp';}

				if($ctrl['ao'] || $ctrl['as']) 
				{
					$controls .= Form::radio('func','a',FALSE,array('id'=>'func')).Form::label('func','authorize').'&nbsp';
					$controls .=Form::hidden('auth','y',array('id'=>'auth'));
				}
				else
				{
					$controls .=Form::hidden('auth','',array('id'=>'func'));
				}
			}
			else
			{
				$controls .= '<span style="color:red;"><b>Insufficient Permissions</b></span></td><td>';
			}
			
			if($ctrl['rj'])
			{
				if(!($ctrl['ao'] && $ctrl['as']))
				{
					$controls .= Form::radio('func','a',FALSE,array('id'=>'func')).Form::label('func','reject').'&nbsp';
				}
				$controls .=Form::hidden('rjct','y',array('id'=>'rjct'));
			}
			else
			{
				$controls .=Form::hidden('rjct','',array('id'=>'rjct'));
			}

			if($ctrl['de']) 
				$controls .= Form::radio('func','d',FALSE,array('id'=>'func')).Form::label('func','delete').'&nbsp';
			
			if($ctrl['is'] || $this->param['controller'] == "message")
			{
				$result = $this->param['primarymodel']->get_params($this->param['controller']);
				$table_inau = $result[ $this->param['controller'] ]->tb_inau;	
				$table_live = $result[ $this->param['controller'] ]->tb_live;
				$notify_str = ""; $notify_str_ur = ""; $notify_str_us = ""; $prnt=false;

				if($ctrl['is'] && !($this->param['controller'] == "message")) 
				{
					$count = $this->param['primarymodel']->get_record_count($table_inau);
					if($count > 0) {$notify_str = sprintf(' Unauthorized(%s) ',$count); $prnt=true; }
				}
			
				if($this->param['controller'] == "message")
				{
					$a=false; $b=false; 
					$count_ur = $this->param['primarymodel']->get_record_count_unread_messages($table_live,$this->param['idname']);
					$count_us = $this->param['primarymodel']->get_record_count_unsent_messages($table_inau,$this->param['idname']);
					if($count_ur > 0) {$notify_str_ur = sprintf(' Unread Messages(%s) ',$count_ur); $a=true;} 
					if($count_us > 0) {$notify_str_us = sprintf(' Unsent Messages(%s) ',$count_us); $b=true;}
					if($a || $b) {$prnt=true;}
				}
				
				if($prnt)
				{
					$notify_str = $notify_str.$notify_str_ur.$notify_str_us;
					$controls .= sprintf('</td><td class="notify" style="color:#fff; background-color:red; text-align:right;"><b>%s</b></td>',$notify_str);
				}
				else { $controls .= '</td><td></td>';}
			}
		}
		else if($post['func']=='n' || $post['func']=='i' || $post['func']=='c' || $post['func']=='w' || $post['func']=='l')
		{
			// setButtonClicked() function need to manage recordlocks when navigating away from page
			if($ctrl['hd'])
			{
				$controls .= $this->input_form_button('Hold');
			}
			if($ctrl['va'])
			{
				$controls .= $this->input_form_button('Validate');
			}
			$controls .= $this->input_form_button('Commit');
			$controls .= $this->input_form_button('Cancel');
		}
		else if($post['func']=='a')
		{
			if($post['auth']=='y')
			{
				$controls .= $this->input_form_button('Authorize');
			}

			if($post['rjct']=='y')
			{
				$controls .= $this->input_form_button('Reject');
			}
			$controls .= $this->input_form_button('Cancel');
		}
		else if($post['func']=='v')
		{
			$controls .= $this->input_form_button('Cancel');
		}
		else if($post['func']=='d')
		{
			$controls .= $this->input_form_button('Delete');
			$controls .= $this->input_form_button('Cancel');
		}
		return $controls;
	}
	
	public function input_form_button($bttnval)
	{
		$html = sprintf('<input type="submit" id="submit" name="submit" class="bttn" value="%s" onclick=window.siteutils.setButtonClicked("%s") />',$bttnval,$bttnval)."\n"; 
		return $html;
	}

	public function get_enqform_controls()
	{
		//verify against security profile
		$this->set_enquiry_controls();
		return $this->get_enquiry_controls();
	}

	public function set_enquiry_controls()
	{
		$control = array('df'=>false,'ls'=>false,'hs'=>false,'is'=>false,'ex'=>false);
		$menu = $this->param['primarymodel']->get_user_menu_controls('menudefs_users','url_input',$this->param['url_input'],$this->param['idname']);		
		$lookup = preg_split('/,/',$menu->controls_enquiry);
		foreach($lookup as $key)
		{
			$control[$key]=true;
		}
		$this->param['controls'] = $control;
	}
	
	public function get_enquiry_controls()
	{	
		$controls = "";
		$ctrl = $this->param['controls'];
						
		if($ctrl['ls']) 
		{
			$controls .= Form::radio('enqfunc','ls',TRUE,array('id'=>'enqfunc')).Form::label('enqfunc','live').'&nbsp';
		}
		else if($ctrl['df']) 
		{
			$controls .= Form::radio('enqfunc','df',TRUE,array('id'=>'enqfunc')).Form::label('enqfunc','default').'&nbsp';
		}

		if($ctrl['is'])
		{
			$controls .= Form::radio('enqfunc','is',FALSE,array('id'=>'enqfunc')).Form::label('enqfunc','inau').'&nbsp';
		}
		
		if($ctrl['hs'])
		{
			$controls .= Form::radio('enqfunc','hs',FALSE,array('id'=>'enqfunc')).Form::label('enqfunc','hist').'&nbsp';
		}		
		
		if($ctrl['ex'])
		{ 
			$controls .= Form::checkbox('enqexport','0',FALSE,array('id'=>'enqexport')).Form::label('enqexport','export').'&nbsp';
		}			
		$controls .= Form::checkbox('fieldnames','0',FALSE,array('id'=>'fieldnames')).Form::label('fieldnames','fieldnames').'&nbsp';
		$controls .= Form::input('limit','500',array('id'=>'limit','size'=>'8','class'=>'ff')).Form::label('limit','limit').'&nbsp';
		$controls .= Form::checkbox('pager','1',FALSE,array('id'=>'pager')).Form::label('pager','pager').'&nbsp';
		$controls .= '<input type="hidden" id="js_exportid" name="js_exportid">';	
		return $controls;
	}

	public function get_available_input_permissions()
	{
		$this->set_input_controls();
		return $this->param['controls'];
	}

	public function show_tabs($param_id,$controller,$enqtype='default')
	{
		$lookup = $this->param['primarymodel']->get_param_keys();
		(array) $result = $this->param['primarymodel']->get_user_enquiry_tables(Auth::instance()->get_user()->idname);
		$result = (array) $result;
		$selarr = array();
		$SELECT	= '<select id="controllersel" class="ff" onChange=enquiry.makeFilter()>'."\n";
		foreach ($result as $key => $row)
		{
			$row = (array) $row;
			$keymatch = '#';
			if( array_key_exists( $row['url_input'], $lookup) )
			{
				$keymatch = $lookup[ $row['url_input'] ]->controller;
			}
			if($row['url_input'] == $param_id){$selected = "selected";} else {$selected = "";}
			$selarr[ $row['label_input'] ] = sprintf('<option value="%s,%s,%s" %s>%s</option>',$row['url_input'],$keymatch,$row['module'],$selected,$row['label_input'])."\n";
		}
		ksort($selarr);
		$SELECT .= join("",$selarr);
		
		$SELECT .= '</select>'."\n";
		$BTTN = '<input class="bttn" type="submit" name="ButtonGet" value="   Get   " onclick=enquiry.GetResults()>'."\n";
		$baseurl = URL::base(TRUE,'http');
		$_idname = Auth::instance()->get_user()->idname;
		
		$url = sprintf('%sindex.php/core_ajaxtodb?option=enqctrl&param_id=%s&controller=%s&user=%s',$baseurl,$param_id,$controller,$_idname);
		$RADIO = Controller_Core_Sitehtml::get_html_from_url($url); 
		
		$url = sprintf('%sindex.php/core_ajaxtodb?option=filterform&controller=%s&user=%s&enqtype=%s&loadfixedvals=1&rochk=0',$baseurl,$controller,$_idname,$enqtype);
		$FILTERFORM = Controller_Core_Sitehtml::get_html_from_url($url); 
		
		$HTML=<<<_HTML_
		<div id="pagebody">
			<div id="tabs" class="easyui-tabs">
				<div id="filter" title="filter" closable="false">
					<div id="filtersel">$BTTN $SELECT <span id="radios">$RADIO</span></div>
					<div id="filterurl"></div>
					<div id="filterform">$FILTERFORM</div>
				</div>
				
				<div id="live" title="live" closable="false" style="border:0px solid blue;overlow:hidden;">
					<div id="resultlive"></div>
				</div>
				
				<div id="inau" title="inau" closable="false" style="border:0px solid blue;overlow:hidden;">
					<div id="resultinau"></div>
				</div>
				
				<div id="hist" title="hist" closable="false" style="border:0px solid blue;overlow:hidden;">
					<div id="resulthist"></div>
				</div>
				</div> <!--tabs pagebody -->
			<input type="hidden" id="js_idname" name="js_idame" value="$_idname">
	</div> <!--end pagebody -->
_HTML_;
	return $HTML;
	}

} // End Core_Sitecontrol
