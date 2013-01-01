<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Used to update superuser permission(security profile)  
 * whenever a new menu record is inserted into menudef table.
 *
 * $Id: Menusuper.php 2012-12-30 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license    
 */
class Controller_Core_Menusuper extends Controller_Core_Menufunc
{
	public $template = "menutree.view";
	public $render;
    public function before()
	{
		parent::before();
		$this->template->head = $this->get_htmlhead();
		$this->tree = new Model_MenuTreeAll('menudefs', 'menu_id', 'parent_id', 'sortpos');
		$this->tree->rebuild();
		$this->topmenu = $this->tree->get_top_level_menus();
		$this->topmenu_nologin = $this->tree->get_all_top_level_menus_nologin();
		$this->auto_render = false;
		$this->render = $this->request->param('args');
	}
	
	public function action_index()
    {
	   	$this->template ->usermenu = "<ul class='treeview' id='tree'>\n";
		foreach($this->topmenu as $key=>$rec) 
		{
			$this->template ->usermenu .= $this->make_list_items_from_nodes($this->tree->get_children($rec->menu_id,true));
		}
		$this->template ->usermenu .= "</ul>\n";
		if($this->render == "1"){$this->auto_render = true;}
	}

	public function action_printsupers()
	{
		$this->template ->usermenu = '';
		foreach($this->topmenu as $key => $rec) 
		{
			if($rec->module == 'useraccount')
			{
				//rolename 'login' is required by Kohana Auth module
				$rolename = 'login'; 
			}
			else
			{ 
				$rolename = $rec->module."_super";
			}
			$nodes = $this->tree->get_children($rec->menu_id,true);
			$security_profile = $this->make_security_profile($nodes);
			$this->security_profile_info($rolename,$security_profile);
		}
		if($this->render == "1"){$this->auto_render = true;}
	}

	public function action_updatesupers()
	{
		$this->template ->usermenu = '';
		foreach($this->topmenu as $key => $rec) 
		{
			if($rec->module == 'useraccount')
			{  
				//rolename 'login' is required by Kohana Auth module
				$rolename = 'login'; 
			}
			else
			{ 
				$rolename = $rec->module."_super";
			}
			$security_profile = $this->make_security_profile($this->tree->get_children($rec->menu_id,true));
			$role = ORM::factory('Role')->where('name','=',$rolename)->find();
			if($role->name == '') //role not found
			{
				//create new role record
				$newrole = ORM::factory('Role');
				$newrole->name = $rolename;
				$newrole->securityprofile = $security_profile;
				$newrole->description = sprintf('Can access all %s components only.',ucfirst($rolename));
				$newrole->inputter = 'SYSINPUT'; 
				$newrole->authorizer = 'SYSINAU';
				$newrole->input_date = date('Y-m-d H:i:s'); 
				$newrole->auth_date = date('Y-m-d H:i:s');  
				$newrole->record_status = 'LIVE';
				$role->current_no = '1';
				$role->save();
			}
			else
			{
				$role->securityprofile = $security_profile;
				$role->update();
			}
			$this->security_profile_info($rolename,$security_profile);
		}
		if($this->render == "1"){$this->auto_render = true;}
	}

	public function security_profile_info($rolename,$security_profile)
	{
		$header = "[<b>".$rolename."</b>]<br />";
		$security_profile = str_replace("<","&lt;", $security_profile);
		$security_profile = str_replace(">","&gt;",  $security_profile);
		$security_profile = str_replace("\n","\n<br />",  $security_profile);
		$this->template ->usermenu .= $header.$security_profile;
		$this->template ->usermenu .= '<hr />';
	}

	public function action_roleselect($print=false,$spid='',$current_no='1')
    {  	
		$initperms = array('if'=>'','vw'=>'','pr'=>'','nw'=>'','cp'=>'','iw'=>'','in'=>'','ao'=>'','as'=>'','rj'=>'','de'=>'','hd'=>'','va'=>'','df'=>'','ls'=>'','is'=>'','hs'=>'','ex'=>'');
		$sparr[0] = $initperms;
		if($spid !='' && $current_no !=0)
		{
			$db = new Model_SiteDB();
			$sparr = array();
			$fields = array('securityprofile');
			$result = $db->get_record_by_lookup('roles','roles_is','name',$spid,$fields,'');
			$securityprofile = $result->securityprofile;
			$formfields = new SimpleXMLElement($securityprofile);
			foreach ($formfields->menu as $menu)
			{
				$menu_id = sprintf('%s',$menu->menu_id);
				$iperms = sprintf('%s',$menu->controls_input);
				$eperms = sprintf('%s',$menu->controls_enquiry);
				$perms = $iperms.','.$eperms;
				$lookup = preg_split('/,/',$perms);
				$chkperms = $initperms;
				foreach($lookup as $key)
				{
					$chkperms[$key]="checked";
				}
				$sparr[$menu_id]=$chkperms;
			}
		}
	
		$this->template ->usermenu ="<ul class='treeview' id='tree'>\n";
		foreach($this->topmenu_nologin as $key=>$rec) 
		{
			$this->template ->usermenu .= $this->make_menu_selection_list($this->tree->get_children($rec->menu_id,true),$sparr);
		}
		$this->template ->usermenu .= "</ul>\n";
		if($this->render == "1"){$this->auto_render = true;} else {return $this->template->usermenu;}
	}

	function get_htmlhead()
	{	
		$head = sprintf('%s',HTML::style($this->css['site'], array('screen')))."\n"; 
		return $head;	
	}

} //End Core_Menusuper
