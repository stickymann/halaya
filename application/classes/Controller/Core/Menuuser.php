<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates user menu based on security profile. 
 *
 * $Id: Menuuser.php 2012-12-28 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license    
 */
class Controller_Core_Menuuser extends Controller_Core_Menufunc
{
	public $template = 'menutree.view';
    
	public function before()
	{
		parent::before();
		$this->template->head = $this->get_htmlhead();
	}
		
	public function action_index()
    {
        $site = new Model_SiteDB;
			
		//delete stale menu
		$querystr = sprintf('DELETE FROM menudefs_users WHERE inputter = "%s"',Auth::instance()->get_user()->idname);
		$site->execute_non_select_query(Database::DELETE,$querystr);
		if($site->get_ns_totalrows() > -1)
		{
			//select currently assigned roles
			$querystr = sprintf('SELECT * FROM roles_users WHERE user_id = "%s"',Auth::instance()->get_user()->id);
			$_arr = $site->execute_select_query($querystr);
			if($rolearr = $site->execute_select_query($querystr))
			{
				foreach($rolearr as $key => $rec)
				{
					$role = ORM::factory('Role',$rec->role_id);
				
					//get security profle from role record, data is xml
					$xml = new SimpleXMLElement($role->securityprofile);
					foreach ($xml->menu as $menu)
					{
						$controls_i = ''; $controls_e = '';
					
						//menudef record for menu id
						$querystr = sprintf('SELECT * from menudefs WHERE menu_id = "%s"',$menu->menu_id);
						$menurec = $site->execute_select_query($querystr);
					
						if(!$menurec){ continue; }
					
						$post = (array) $menurec[0];
					
						//update audit fields, we use inputter field to identify the user
						$post['inputter']=Auth::instance()->get_user()->idname; $post['authorizer']='SYSAUTH';
						$post['input_date']=date('Y-m-d H:i:s'); $post['auth_date']=date('Y-m-d H:i:s'); $post['record_status']='LIVE';
				
						//set user input controls
						$master_input_ctrls = preg_split('/,/',$post['controls_input']); 
						$user_input_ctrls = preg_split('/,/',$menu->controls_input);
						foreach($user_input_ctrls as $key => $ctrl)
						{
							if(in_array($ctrl, $master_input_ctrls))
							{
								$controls_i = $controls_i.$ctrl.",";
							}
						}
						$post['controls_input'] = substr($controls_i,0,-1);				
				
						//set user enquiry controls
						$master_enquiry_ctrls = preg_split('/,/',$post['controls_enquiry']); 
						$user_enquiry_ctrls = preg_split('/,/',$menu->controls_enquiry);
						foreach($user_enquiry_ctrls as $key => $ctrl)
						{
							if(in_array($ctrl, $master_enquiry_ctrls))
							{
								$controls_e = $controls_e.$ctrl.",";
							}
						}
						$post['controls_enquiry'] = substr($controls_e,0,-1);				
								
						if($site->record_exist_dual_key('menudefs_users','menu_id','inputter',$post['menu_id'],$post['inputter']))
						{
							//update controls
							//this scenario never occurs because xml parser only returns 1st instance of duplicate ids.	
							$site->update_record_dual_key('menudefs_users',$post,'menu_id','inputter',$post['menu_id'],$post['inputter']);
						}
						else
						{				
							$site->insert_record('menudefs_users',$post);
						}
					}
				}
			}
		}
		
		//delay load
		if($site->get_ns_totalrows() > 0)
		{
			if(substr(getenv("HTTP_REFERER"),-3) == "app") { usleep(3000000); }
		}
				
		//get root menus for user
		$usertree = new Model_MenuTreeUserProfile('menudefs_users',Auth::instance()->get_user()->idname, 'menu_id', 'parent_id', 'sortpos');
		$topmenu = $usertree->get_top_level_menus();
		$this->template->usermenu ="<ul class='treeview' id='tree'>\n";
		foreach($topmenu as $key=>$rec) 
		{
			$this->template->usermenu .= $this->make_list_items_from_nodes($usertree->get_children($rec->menu_id,true));
		}
		$this->template->usermenu .= "</ul>\n";
	}

	function get_htmlhead()
	{	
		$head = sprintf('%s',HTML::style($this->css['treeview'], array('screen')))."\n"; 
		$head .= sprintf('%s',HTML::style($this->css['screen'], array('screen')))."\n";
		$head .= sprintf('%s',HTML::script($this->js['jquery']))."\n";
		$head .= sprintf('%s',HTML::script($this->js['cookie']))."\n";
		$head .= sprintf('%s',HTML::script($this->js['treeview']));
		return $head;	
	}

} // End Core_Menuuser
