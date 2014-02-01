<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates the html for application's main menu. 
 *
 * $Id: Menufunc.php 2012-12-28 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license    
 */
class Controller_Core_Menufunc extends Controller_Include
{
	public function __construct()
	{
		parent::__construct();
	}
		
	function make_list_items_from_nodes($nodes)
	{
		$menutree = "";
		$clevel=1;

		foreach($nodes as $i => $row)
		{
			if ($row->nlevel > $clevel)
			{
				$menutree .= '<ul style="display: none;">'."\n";
			}
			elseif ($row->nlevel < $clevel)
			{
				for($i=$row->nlevel;$i<$clevel;$i++)
				{
					$menutree .= '</ul></li>'."\n";
				}
			}
			if($row->node_or_leaf == "L")
			{
				$baseurl = URL::base();
				if($row->label_enquiry != "")
				{
					$pos_e=strpos(trim($row->label_enquiry),"%IMG%");
					if ($pos_e > 0)
					{
						$row->label_enquiry = sprintf('<img src="%smedia/img/menu/%s" align=absbottom>',$baseurl,str_replace("%IMG%","",$row->label_enquiry));
					}
				}
				if($row->label_input != "")
				{
					$pos_i=strpos(trim($row->label_input),"%IMG%");
					if ($pos_i > 0)
					{ 
						$row->label_input = sprintf('<img src="%smedia/img/menu/%s" align=absbottom>',$baseurl,str_replace("%IMG%","",$row->label_input) );
					}
					$menutree .= sprintf('<li><a href="%s" target="input">%s</a>&nbsp<a href="%s" target="enquiry">%s</a></li>', $row->url_input, $row->label_input, $row->url_enquiry, $row->label_enquiry);
				}
				$row->url_input = $row->label_input = $row->url_enquiry = $row->label_enquiry = "";
			}

			else
			{
				$menutree .= sprintf('<li><div></div><span><strong>%s</strong></span> ',$row->label_input);
				$row->label_input = "";
			}
			$clevel = $row->nlevel;
		}
	
		for($i=1;$i<$clevel;$i++)
		{
			$menutree .= '</ul></li>'."\n";
		}
		return $menutree;
	}

	function make_security_profile($nodes)
	{
		$menutree = "";
		$menutree .= "<?xml version='1.0' standalone='yes'?>\n";
		$menutree .= "<securityprofile>\n";
		foreach($nodes as $i => $row)
		{
			$menutree .= sprintf("<menu><menu_id>%s</menu_id><controls_input>%s</controls_input><controls_enquiry>%s</controls_enquiry></menu>\n",$row->menu_id,$row->controls_input,$row->controls_enquiry);
		}
		$menutree .= "</securityprofile>\n";
		return $menutree;
	}

	function make_menu_selection_list($nodes,$sparr='')
	{
		$menutree = "";
		$clevel=1;
		
		foreach($nodes as $i => $row)
		{
			$iperm_checkboxs = '';
			if($row->controls_input != "")
			{
				$ctrl = preg_split('/,/',$row->controls_input);
				foreach($ctrl as $ctrlval)
				{
					$id = $row->menu_id.'i_'.$ctrlval;
					$checked = ""; 
					if (array_key_exists($row->menu_id, $sparr)) 
					{
						$tmpchkarr = $sparr[ $row->menu_id ];
						$checked = $tmpchkarr[$ctrlval];
					}
					$iperm_checkboxs .= '<span class="ci">'.sprintf('<input type="checkbox" id="%s" name="inp_%s" value="%s" %s onchange=window.roleadmin.UpdateSecurityProfile("%s","P") />',$id,$row->menu_id,$ctrlval,$checked,$id).Form::label($id,$ctrlval).' </span>';
				}
			}

			$eperm_checkboxs = '';
			if($row->controls_enquiry != "")
			{
				$ctrl = preg_split('/,/',$row->controls_enquiry);
				foreach($ctrl as $ctrlval)
				{
					$id = $row->menu_id.'e_'.$ctrlval;
					$checked = ""; 
					if (array_key_exists($row->menu_id, $sparr)) 
					{
						$tmpchkarr = $sparr[ $row->menu_id ];
						$checked = $tmpchkarr[$ctrlval];
					}
					$eperm_checkboxs .=  '<span class="ce">'.sprintf('<input type="checkbox" id="%s" name="enq_%s" value="%s" %s onchange=window.roleadmin.UpdateSecurityProfile("%s","P") />',$id,$row->menu_id,$ctrlval,$checked,$id).Form::label($id,$ctrlval).' </span>';
				}
			}
			
			$menuitem_checkbox = '';
			if($row->menu_id != "")
			{
				$checked = ""; 
				if (array_key_exists($row->menu_id, $sparr)) 
				{
					$checked = "checked";
				}
				$menuitem_checkbox = sprintf('<input type="checkbox" id="%s" name="menu_%s" value="%s" %s onchange=window.roleadmin.UpdateSecurityProfile("%s","M") />',$row->menu_id,$row->menu_id,$row->parent_id,$checked,$row->menu_id);
			}

			if ($row->nlevel > $clevel)
			{
				$menutree .= '<ul style="display: none;">'."\n";
			}
			elseif ($row->nlevel < $clevel)
			{
				for($i=$row->nlevel;$i<$clevel;$i++)
				{
					$menutree .= '</ul></li>'."\n";
				}
			}
			
			if($row->node_or_leaf == "L")
			{
				$menutree .= "\t".sprintf('<li id="%s_li" name="%s_li">',$row->menu_id,$row->menu_id).$menuitem_checkbox.' '.$row->label_input.' '.$iperm_checkboxs.' '.$eperm_checkboxs.'</li>'."\n";
			}
			else
			{
				$menutree .= "\t".sprintf('<li id="%s_li" name="%s_li">',$row->menu_id,$row->menu_id).$menuitem_checkbox.' '.'<span><strong>'.$row->label_input.'</strong></span> '."\n";
			}
			$clevel = $row->nlevel;
		}
	
		for($i=1;$i<$clevel;$i++)
		{
			$menutree .= '</ul></li>'."\n";
		}
		return $menutree;
	}

} //End Core_Menufunc
