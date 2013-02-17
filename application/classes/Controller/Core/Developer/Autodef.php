<?php
/**
 * Controller definitions builder and installer tool. 
 *
 * $Id: Autodef.php 2012-12-31 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */

define("SLEEPTIME",2);
define("INPUTTER","IMPLEMENTATION");
define("RECORDSTATUS","INAU");
define("XMLHEADER","<?xml version=\'1.0\' standalone=\'yes\'?>");	
define("FORMDIR","/controllers/");
define("MODELDIR","/models/");
define("VIEWDIR","/views/");
define("FILEDIR","/files/");
define("TABLEDEFS","/tabledefs/");
define("MENUDEFS","/menudefs/");
define("FORMDEFS","/formdefs/");
define("PARAMDEFS","/paramdefs/");
define("MVCDEFS","/mvcs/");
define("ERRMSGDIR","/errmsgs/");

class Controller_Core_Developer_Autodef extends Controller_Include
{
	public $template	= "autodef.view";
	public $TITLE		= "Auto Generator & Definitions Installer";
	public $PHP_SELF	= "";
	
	public function before()
    {
		$this->sitedb = new Model_SiteDB;
	}

	public function action_index()
    {
		if( isset($_REQUEST['ajaxoption']) )
		{
			$ajaxoption = $_REQUEST['ajaxoption'];
			switch($ajaxoption)
			{
				case "ajaxgetdefdirs":
				$this->ajax_get_def_dirs();
				break;
			}
			exit(0);
		}
		
		$this->PHP_SELF	= $_SERVER['PHP_SELF'];
		if( isset($_REQUEST['option']) ) { $this->option = $_REQUEST['option']; } else { $this->option = "defstatus"; }
		$this->template->content  = $this->html_header();
		$this->template->content .= $this->main_layout($this->option);
		$this->template->content .= $this->html_footer();
	}

	public function html_header()
	{
		$TITLE = $this->TITLE;
		$easyui_css  = sprintf('%s',HTML::style($this->css['easyui'], array('screen')));
		$autodef_css  = sprintf('%s',HTML::style($this->css['autodef'], array('screen')));
		$jquery_js = sprintf('%s',HTML::script($this->js['jquery']));
		$siteutils = sprintf('%s',HTML::script($this->js['siteutils']));
		$HTML=<<<_HTML_
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>$TITLE</title>
	$easyui_css
	$autodef_css
	$jquery_js
	$siteutils
	<style rel="stylesheet" type="text/css">
		.fail {color:red; font-size: 10pt; font-style: normal; font-weight: bold;}
		.pass {color:green; font-size: 10pt; font-style: normal; font-weight: bold;}
	</style>
 
	<script language="Javascript">
	var httpObject = null;
	var url = "";
	var checkbox_create= new Array();
	var checkbox_overwrite= new Array();

	function getHTTPObject()
	{
		if (window.ActiveXObject) 
			return new ActiveXObject("Msxml2.XMLHTTP");
		else if (window.XMLHttpRequest) 
			return new XMLHttpRequest();
		else 
		{
			alert("Your browser does not support AJAX.");
			return null;
		}
	}
		
	function runQuery(params)
	{
		httpObject = getHTTPObject();
		if (httpObject != null) 
		{
			try
				{
					httpObject.open("GET", params, true);
					httpObject.onreadystatechange = setOutput
					/*
					if (opt==1)
					{
						httpObject.onreadystatechange = setOutput1
					}
					else if(opt==2)
					{
						httpObject.onreadystatechange = setOutput2
					}*/
					httpObject.send(null);
				}
			catch(e)
			{
				alert("Error : "+ e.toString());	
			}
		}
	}
	
	function updateSelectDefs(url)
	{
		//params = url + document.getElementById("selectmod").value;
		params = url + $('#selectmod').val();
		runQuery(params);
		
		//window.siteutils.runQuery(params,"poresult","html");
	}
	
	function setOutput()
	{
		if(httpObject.readyState == 4)
		{
			str = httpObject.responseText;
			arr = str.split(",");
			HTML = '';
			HTML += '<label for="selectdef">Definition: </label><select id="selectdef" name="selectdef">';
			for(var i in arr)
			{
				HTML += '<option value="'+arr[i]+'">'+arr[i]+'</option>';
			}
			HTML += '</select>';
			$('#selectdefdiv').html(HTML);
			//document.getElementById("selectdefdiv").innerHTML = HTML;
		}
	}

	function setCheckBox(checktype,value)
	{
		if(checktype  == "create" ){checkbox_create.push(value);}
		else if(checktype == "overwrite" ){checkbox_overwrite.push(value); }
	}

	function setChecks(checktype)
	{
		if(checktype == "create" ){  _checkbox = checkbox_create; opt="_c";}
		else if(checktype == "overwrite" ){ _checkbox = checkbox_overwrite;opt="_o";}
		
		document.getElementById(checktype).innerHTML = '';
		for (key in _checkbox)
		{	
			check_id = _checkbox[key] + opt;
			if(document.getElementById(check_id).checked==true)
			{
				document.getElementById(checktype).innerHTML = document.getElementById(checktype).innerHTML + document.getElementById(check_id).value + ",";
			}
		}
		str = document.getElementById(checktype).innerHTML;
		str = str.substr(0,str.length-1);
		document.getElementById(checktype).innerHTML = str;
	}	
	
	</script>
</head>
<body>
<div id="page">
<div id="pageheader" class="window">$TITLE</div>
<div id="pagebody">
<div id="i">
_HTML_;
		return $HTML;    
	}
	
	function html_footer()
	{
	$HTML=<<<_HTML_
</div>
</div>
</div>		
</body>
</html>    
_HTML_;
		return $HTML;     
	}

	function main_layout($option)
	{
		$RESULT = "" ;
		switch($option)
		{
		
			case "setactivedef":
				$RESULT = $this->set_active_definitions();
			break;

			case "setautogendir":
				$RESULT = $this->set_auto_gen_dir();
			break;
			
			case "setdefdir":
				$RESULT = $this->set_definition_dir();
			break;

			case "setapprootdir":
				$RESULT = $this->set_approot_dir();
			break;
		
			case "addautotable":
				$RESULT = $this->add_auto_table();
			break;
		
			case "addautomenu":
				$RESULT = $this->add_auto_menu();
			break;
		
			case "createautotables":
				$RESULT = $this->create_auto_tables();
			break;
		
			case "createautomenus":
				$RESULT = $this->create_auto_menus();
			break;

			case "createautoparams":
				$RESULT = $this->create_auto_params();
			break;
		
			case "createautoforms":
				$RESULT = $this->create_auto_forms();
			break;
		
			case "createautomvcs":
				$RESULT = $this->create_auto_mvcs();
			break;

			case "defstatus":
				//$RESULT = $this->definition_status();
			break;
	
			case "definstall":
				$RESULT = $this->definition_install();
			break;
		}
		return $this->main_layout_display($RESULT);
	}

	function main_layout_display($RESULT)
	{
		$PHP_SELF = $this->PHP_SELF;
	$HTML=<<<_HTML_
	<table width=100% class="adi_table" cellpadding=2 height="50%">
		<tr align=left valign=top>
		<td class="adi_td" width='25%'>
		<h3>System Configuration</h3>
		<ul>
			<li><a href="$PHP_SELF?option=setactivedef">Set Active Module and Definition</a>
			<li><a href="$PHP_SELF?option=setautogendir">Set AutoGen Root</a>
			<li><a href="$PHP_SELF?option=setdefdir">Set Module Root</a>
			<li><a href="$PHP_SELF?option=setapprootdir">Set Application Root</a>
		</ul>
		<h3>Auto Generation</h3>
		<ul>
			<li><a href="$PHP_SELF?option=addautotable">Add Auto Table</a>
			<li><a href="$PHP_SELF?option=addautomenu">Add Auto Menu</a>
			<li><a href="$PHP_SELF?option=createautotables">Auto Generate Table Definitions</a>
			<li><a href="$PHP_SELF?option=createautomenus">Auto Generate Menu Definitions</a>
			<li><a href="$PHP_SELF?option=createautoparams">Auto Generate Param Definitions</a>
			<li><a href="$PHP_SELF?option=createautoforms">Auto Generate Form Definitions</a>
			<li><a href="$PHP_SELF?option=createautomvcs">Auto Generate MVC Definitions</a>
		</ul>
		<h3>Definition Setup</h3>
		<ul>
			<li><a href="$PHP_SELF?option=defstatus">Definition Status</a>
			<li><a href="$PHP_SELF?option=definstall">Install Definitions</a>
		</ul>
		<div id="installselect"></div>
		</td>
		<td class="adi_td" width='75%'>
		<div id="result" name="result">
		<table><tr><td>$RESULT</td></tr></table>
		</div>
		</td>
		</tr>
	</table>
_HTML_;
	return $HTML;
	}

	function ajax_get_def_dirs()
	{
		$default_mod = $_REQUEST["modname"];
		$sysarr = $this->query_system_db("getsysconfig","");
		$sysrow = (array) $sysarr[0];
		$modpath = $sysrow['defdir'];
		$RESULT = "";
		if(is_dir($modpath))
		{
			$mods = $this->get_dirs($modpath);
			$defpath = $modpath."/".$default_mod;
			if(is_dir($defpath)){$RESULT = $this->get_dirs($defpath);}
			if($default_mod == ""){$RESULT = "";}
		}
		print $RESULT;
	}

	function get_dirs($path)
	{
		$dirs = "";
		if ($dir = @opendir($path)) 
		{
			while (($file = readdir($dir)) !== false) 
			{
				if ($file!="." && $file!="..")
				{
					if (is_dir("$path/$file"))
					{
						$dirs.=",".$file;
					}
				}
			}
		}
		closedir($dir);
		return $dirs;
	}

	function ucfirst_sentence($str)
	{
		return preg_replace('/\b(\w)/e', 'strtoupper("$1")', $str);
	}

	function set_active_definitions()
	{
		if( isset($_REQUEST['selectmod']) ){ $mod = $_REQUEST['selectmod']; } else { $mod ="n/a"; }
		if( isset($_REQUEST['selectdef']) ){ $def = $_REQUEST['selectdef']; } else { $mod ="n/a"; }

		$HTML = "<h3>Set Active Definition</h3>";
	
		if( isset($_REQUEST['userButtonPress']) )
		{	
			$var = array("module",$mod);
			$count = $this->query_system_db("setsysconfig",$var);
		
			$var = array("activedef",$def);
			$count = $this->query_system_db("setsysconfig",$var);

			$HTML .= sprintf('Active Definition is set to -> [module: <b>%s</b>] [definition: <b>%s</b>]',$mod,$def);
			unset($_REQUEST['userButtonPress']); unset($_REQUEST['selectmod']); unset($_REQUEST['selectdef']);
		}
		else
		{
			$res = $this->query_system_db("initialize_sysconfig","");
			$sysarr = $this->query_system_db("getsysconfig","");
			if($sysarr)
			{
				$sysrow = (array) $sysarr[0];
				$modpath = $sysrow['defdir']; $default_mod = $sysrow['module']; $default_def = $sysrow['activedef'];
			}
			else
			{
				$modpath = ""; $default_mod = ""; $default_def = "";
			}
					
			if(is_dir($modpath))
			{
				$mods = $this->get_dirs($modpath);
				$defpath = $modpath."/".$default_mod;
				if(is_dir($defpath))
				{
					$defs = $this->get_dirs($defpath);
				}
				$HTML .= $this->set_active_definitions_input_form($mods,$defs,$default_mod,$default_def);
			}
			else
			{
				$HTML .= "Invalid Definition Directory ($path)";
			}
		}
		return $HTML;
	}
	
	function set_active_definitions_input_form($mods,$defs,$default_mod,$default_def)
	{
		$PHP_SELF = $this->PHP_SELF;
		$HTML = sprintf('<form id="SetActiveDefinitions" name="SetActiveDefinitions" action="%s" method="get">',$PHP_SELF);
		$url = $PHP_SELF."?ajaxoption=ajaxgetdefdirs&modname=";
		$HTML .= sprintf('<label for="selectmod">Module: &nbsp &nbsp<label><select id="selectmod" name="selectmod" onChange=updateSelectDefs("%s");>',$url);
		$modules = preg_split('/,/',$mods);
		foreach($modules as $modname)
		{
			if($modname == $default_mod) {$selected = "selected";} else {$selected = "";} 
			$HTML .= sprintf('<option value="%s" %s>%s</option>',$modname,$selected,$modname);
		}
		$HTML .= '</select><br>';

		$HTML .= '<br><div id="selectdefdiv" name="selectdefdiv"><label for="selectdef">Definition: <label><select id="selectdef" name="selectdef">';
		$defintions = preg_split('/,/',$defs);
		foreach($defintions as $defname)
		{
			if($defname == $default_def) {$selected = "selected";} else {$selected = "";} 
			$HTML .= sprintf('<option value="%s" %s>%s</option>',$defname,$selected,$defname);
		}
		$HTML .= '</select></div>';
	
		$HTML .= '<br><br/><input class="bttn" type="submit" name="userButtonPress" value="Submit">';
		$HTML .= '<input type="hidden" name="option" value="setactivedef">';
		$HTML .= '</form>'; 
		return $HTML;
	}
	
	function set_definition_dir()
	{
		if( isset($_REQUEST['path']) ){ $path = $_REQUEST['path']; } else { $path = ""; }
		$HTML = "<h3>Set Definitions Directory</h3>";
	
		if( isset($_REQUEST['userButtonPress']) )
		{	
			$var=array("defdir",$path);
			$count = $this->query_system_db("setsysconfig",$var);
			$HTML .= sprintf('Definitions Directory is set to : <b>%s</b>',$path);
			unset($_REQUEST['userButtonPress']); unset($_REQUEST['path']);
		}
		else
		{
			$sysarr = $this->query_system_db("getsysconfig","");
			$sysrow = (array) $sysarr[0];
			$path = $sysrow['defdir'];
			$HTML .= $this->set_definition_dir_input_form($path);
		}
		return $HTML;
	}

	function set_definition_dir_input_form($path)
	{
		$PHP_SELF = $this->PHP_SELF;
		$HTML = sprintf('<form id="SetDefinitionDir" name="SetDefinitionDir" action="%s" method="get">',$PHP_SELF);
		$HTML .= sprintf('<input type="text" name="path" size="50" maxlength="1024" value="%s"/><br/>',$path);
		$HTML .= '<br/><input class="bttn" type="submit" name="userButtonPress" value="Submit">';
		$HTML .= '<input type="hidden" name="option" value="setdefdir">';
		$HTML .= '</form>'; 
		return $HTML;
	}

	function set_auto_gen_dir()
	{
		if( isset($_REQUEST['path']) ){ $path = $_REQUEST['path']; } else { $path = ""; }
		$HTML = "<h3>Set AutoGen Directory</h3>";
	
		if( isset($_REQUEST['userButtonPress']) )
		{	
			$var = array("autogendir",$path);
			$count = $this->query_system_db("setsysconfig",$var);
			$HTML .= sprintf('AutoGen Directory is set to : <b>%s</b>',$path);
			unset($_REQUEST['userButtonPress']); unset($_REQUEST['path']);
		}
		else
		{
			$sysarr = $this->query_system_db("getsysconfig","");
			$sysrow = (array) $sysarr[0];
			$path = $sysrow['autogendir'];
			$HTML .= $this->set_auto_gen_dir_input_form($path);
		}
		return $HTML;
	}

	function set_auto_gen_dir_input_form($path)
	{
		$PHP_SELF = $this->PHP_SELF;
		$HTML = sprintf('<form id="SetAutoGenDir" name="SetAutoGenDir" action="%s" method="get">',$PHP_SELF);
		$HTML .= sprintf('<input type="text" name="path" size="50" maxlength="1024" value="%s"/><br/>',$path);
		$HTML .= '<br/><input class="bttn" type="submit" name="userButtonPress" value="Submit">';
		$HTML .= '<input type="hidden" name="option" value="setautogendir">';
		$HTML .= '</form>'; 
		return $HTML;
	}
	
	function set_approot_dir()
	{
		if( isset($_REQUEST['approotpath']) ) { $path[0] = $_REQUEST['approotpath']; } else { $path[0] = ""; }
		$HTML = "<h3>Application Root Directory</h3>"; 
	
		if( isset($_REQUEST['userButtonPress']) )
		{	
			$var = array("approotdir",$path[0]);
			$count = $this->query_system_db("setsysconfig",$var);
			$HTML .= sprintf('Application Root Directory is set to : <b>%s</b><br>',$path[0]);
			unset($_REQUEST['userButtonPress']); unset($_REQUEST['approotpath']);
		}
		else
		{
			$sysarr = $this->query_system_db("getsysconfig","");
			$sysrow = (array) $sysarr[0];
			$path[0] = $sysrow['approotdir'];
			$HTML .= $this->set_approot_dir_input_form($path[0]);
		}
		return $HTML;
	}

	function set_approot_dir_input_form($path)
	{
		$PHP_SELF = $this->PHP_SELF;
		$HTML = sprintf('<form id="SetControllerDirs" name="SetControllerDirs" action="%s" method="get">',$PHP_SELF);
		$HTML .= sprintf('<input type="text" name="approotpath" size="50" maxlength="1024" value="%s"/>(Application Root Directory)<br/>',$path);
		$HTML .= '<br/><input class="bttn" type="submit" name="userButtonPress" value="Submit">';
		$HTML .= '<input type="hidden" name="option" value="setapprootdir">';
		$HTML .= '</form>'; 
		return $HTML;
	}

/// START AUTOGEN###########################################################
	function key_exist($dropdown,$idfld,$getval)
	{
		foreach($dropdown as $value)
		{
			$value = (array) $value;
			$key = array_search($getval,$value);
			if($key != "" && $key == $idfld) 
			{
				return true;
			}
		}
		return false;
	}

	function app_table_exist($tablename)
	{
		$querystr = sprintf('SHOW TABLES;');
		$result = $this->sitedb->execute_select_query($querystr);
		foreach($result as $row)
		{
			$row = (array) $row;
			if(in_array($tablename,$row))
			{
				return true;
			}
		}
		return false;
	}

	function add_auto_table()
	{
		$dropdown = $this->query_system_db("getautotables","");
		$HTML = "<h3>Add Auto Table</h3>";
	
		if( isset($_REQUEST['userButtonPress']) )
		{	
			$getval = $_REQUEST['id'];
			if($this->key_exist($dropdown,"id",$getval))
			{
				$result = $this->query_system_db("updateautotable",$_REQUEST);
				$HTML .= sprintf('Autogen Table Parameters Update [<i><b>%s</b></i>] : %s',$getval,$result); 
			}
			else
			{
				$result = $this->query_system_db("insertautotable",$_REQUEST);
				$HTML .= sprintf('Autogen Table Parameters Insert [<i><b>%s</b></i>] : %s',$getval,$result); 
			}
			unset($_REQUEST['userButtonPress']);
		}
		else if( isset($_REQUEST['editButtonPress']) )
		{	
			$var = $_REQUEST['selectautotable'];
			$result = $this->query_system_db("getautotable",$var);
			$HTML .= $this->add_auto_table_input_form($dropdown,$result);
			unset($_REQUEST['editButtonPress']);
		}
		else
		{
			$result = array();
			$HTML .= $this->add_auto_table_input_form($dropdown,$result);
		}
		return $HTML;
	}

	function add_auto_table_input_form($dropdown,$result)
	{
		$PHP_SELF = $this->PHP_SELF; 
		$value = array('id'=>"", 'module'=>"", 'tablename'=>"", 'tablefields'=>"", 'uniquefield'=>"" );
		$HTML = sprintf('<form id="AddAutoTable" name="AddAutoTable" action="%s" method="get">',$PHP_SELF);
		$HTML .= '<table>';
		$SELTABLE = '<tr><td><label for="selectautotable">Select Table To Edit: </label></td><td><select id="selectautotable" name="selectautotable">';
		$SELTABLE .= '<option value=""></option>';
		foreach($dropdown as $selval)
		{
			$selval = (array) $selval;
			$SELTABLE .= sprintf('<option value="%s">%s</option>',$selval['id'],$selval['id']);
		}
		$SELTABLE .= '</select>';
		$SELTABLE .= '<input class="bttn" type="submit" name="editButtonPress" value="Edit"></td></tr>';
		$HTML .= $SELTABLE;
		
		if( isset($result[0]) ) {$value = (array) $result[0]; }
		$HTML .= sprintf('<tr valign=top><td><label for="id">Id: </label></td><td><input type="text" id="at_id" name="id" size="50" maxlength="50" value="%s"/></td></tr>',$value['id']);
		$HTML .= sprintf('<tr valign=top><td><label for="module">Module: </label></td><td><input type="text" id="module" name="module" size="50" maxlength="50" value="%s"/></td></tr>',$value['module']);
		$HTML .= sprintf('<tr valign=top><td><label for="tablename">Table Name: </label></td><td><input type="text" id="tablename" name="tablename" size="50" maxlength="50" value="%s"/></td></tr>',$value['tablename']);
		$HTML .= sprintf('<tr valign=top><td><label for="tablefields">Table Fields: </label></td><td><textarea id="tablefields" name="tablefields" rows=5 cols=50>%s</textarea></td></tr>',$value['tablefields']);

		$HTML .= sprintf('<tr valign=top><td><label for="uniquefield">Unique Field: </label></td><td><input type="text" id="uniquefield" name="uniquefield" size="50" maxlength="50" value="%s"/></td></tr>',$value['uniquefield']);
		$HTML .= '</table>';
	
		$HTML .= '<br/><input class="bttn" type="submit" name="userButtonPress" value="Submit">';
		$HTML .= '<input type="hidden" name="option" value="addautotable">';
		$HTML .= '</form>'; 
		return $HTML;
	}
	
	function add_auto_menu()
	{
		$dropdown = $this->query_system_db("getautomenus","");
		$HTML = "<h3>Add Auto Menu</h3>";
	
		if( isset($_REQUEST['userButtonPress']) )
		{	
			$getval = $_REQUEST['id'];
			if($this->key_exist($dropdown,"id",$getval))
			{
				$result = $this->query_system_db("updateautomenu",$_REQUEST);
				$HTML .= sprintf('Autogen Menu Parameters Update [<i><b>%s</b></i>] : %s',$getval,$result); 
			}
			else
			{
				$result = $this->query_system_db("insertautomenu",$_REQUEST);
				$HTML .= sprintf('Autogen Menu Parameters Insert [<i><b>%s</b></i>] : %s',$getval,$result); 
			}
			unset($_REQUEST['userButtonPress']);
		}
		else if( isset($_REQUEST['editButtonPress']) )
		{	
			$var = $_REQUEST['selectautomenu'];
			$result = $this->query_system_db("getautomenu",$var);
			$HTML .= $this->add_auto_menu_input_form($dropdown,$result);
			unset($_REQUEST['editButtonPress']);
		}
		else
		{
			$result = array();
			$HTML .= $this->add_auto_menu_input_form($dropdown,$result);
		}
		return $HTML;
	}

	function add_auto_menu_input_form($dropdown,$result)
	{
		$PHP_SELF = $this->PHP_SELF; 
		$value = array('id'=>"", 'module'=>"", 'root_id'=>"", 'menu_layout'=>"" );
		$HTML = sprintf('<form id="AddAutoMenu" name="AddAutoMenu" action="%s" method="get">',$PHP_SELF);
		$HTML .= '<table>';
		$SELTABLE = '<tr><td><label for="selectautomenu">Select Menu To Edit: </label></td><td><select id="selectautomenu" name="selectautomenu">';
		$SELTABLE .= '<option value=""></option>';
		foreach($dropdown as $selval)
		{
			$selval = (array) $selval;
			$SELTABLE .= sprintf('<option value="%s">%s</option>',$selval['id'],$selval['id']);
		}
		$SELTABLE .= '</select>';
		$SELTABLE .= '<input class="bttn" type="submit" name="editButtonPress" value="Edit"></td></tr>';
		$HTML .= $SELTABLE;
		
		if( isset($result[0]) ) {$value = (array) $result[0]; }
		$HTML .= sprintf('<tr valign=top><td><label for="id">Id: </label></td><td><input type="text" id="at_id" name="id" size="50" maxlength="50" value="%s"/></td></tr>',$value['id']);
		$HTML .= sprintf('<tr valign=top><td><label for="module">Module: </label></td><td><input type="text" id="module" name="module" size="50" maxlength="50" value="%s"/></td></tr>',$value['module']);
		$HTML .= sprintf('<tr valign=top><td><label for="root_id">Root Id: </label></td><td><input type="text" id="root_id" name="root_id" size="50" maxlength="50" value="%s"/></td></tr>',$value['root_id']);
		$HTML .= sprintf('<tr valign=top><td><label for="menu_layout">Menu Layout: </label></td><td><textarea id="menu_layout" name="menu_layout" rows=10 cols=80>%s</textarea></td></tr>',$value['menu_layout']);
		$HTML .= '</table>';
		$HTML .= '<br/><input class="bttn" type="submit" name="userButtonPress" value="Submit">';
		$HTML .= '<input type="hidden" name="option" value="addautomenu">';
		$HTML .= '</form>'; 
		return $HTML;
	}
	
	function create_autogen_input_form($dropdown,$result,$option)
	{	
		$PHP_SELF = $this->PHP_SELF;
		$HTML = sprintf('<form id="CreateAutoGen" name="CreateAutoGen" action="%s" method="get">',$PHP_SELF)."\n";
		$HTML .= "\n".'<table class="autogen-table">'."\n";
		$HTML .= "<thead>\n";
		$HTML .= '<tr valign="center"><th class="autogen-th">Create Autogen</th><th class="autogen-th">Overwrite Existing</th>'."\n";
		$HTML .= "</thead>\n";
		$HTML .= "<tbody>\n";
		foreach($dropdown as $value)
		{
			$value = (array) $value;
			$HTML .= '<tr valign="top">';
			$html = sprintf('<td class="autogen-td"><input type="checkbox" id="%s_c" name="%s_c" value="%s" onchange=setChecks("%s")>',$value['id'],$value['id'],$value['id'],"create");
			$html .= sprintf('<label for="%s_c">%s</label>',$value['id'],$value['id']);
			$html .= '<script type="text/javascript">setCheckBox("create","'.$value['id'].'");</script></td>';
			$html .= sprintf('<td class="autogen-td"><input type="checkbox" id="%s_o" name="%s_o" value="%s" onchange=setChecks("%s")>',$value['id'],$value['id'],$value['id'],"overwrite");
			$html .= sprintf('<label for="%s_o">%s</label>',$value['id'],$value['id']);
			$html .= '<script type="text/javascript">setCheckBox("overwrite","'.$value['id'].'");</script></td>';
			$HTML .= $html;
			$HTML .= "</tr>\n";
		}
		$HTML .= "</tbody>\n";
		$HTML .= "</table>\n";
		$HTML .= "<br>\n";
		$HTML .= "<table>\n";
		$HTML .= '<tr valign="top"><td>Create The Following Definitions:<br><textarea id="create" name="create" rows=4 cols=80 readonly></textarea></td><tr>';
		$HTML .= '<tr valign="top"><td>Overwrite The Following Definitions:<br><textarea id="overwrite" name="overwrite" rows=4 cols=80 readonly></textarea></td><tr>';
		$HTML .= "</table>\n";

		$HTML .= '<br/><input class="bttn" type="submit" name="userButtonPress" value="Submit">';
		$HTML .= sprintf('<input type="hidden" id="option" name="option" value="%s">',$option);
		$HTML .= "</form>\n"; 
		return $HTML;
	}

	function create_auto_tables()
	{
		$dropdown = $this->query_system_db("getautotables","");
		$HTML = "<h3>Auto Generate Tables</h3>";
		if( isset($_REQUEST['userButtonPress']) )
		{	
			$createlist = preg_split('/,/',$_REQUEST['create']);
			$overwritelist = preg_split('/,/',$_REQUEST['overwrite']);
		
			if($createlist[0] != null)
			{
				foreach($createlist as $key => $value)
				{
					$result= $this->create_autotable_def($value);
					$HTML .= sprintf('Auto TableDef Created [<i><b>%s</b></i>] :<br>%s<br>',$value,$result); 
				}
			}
			else
			{
				$HTML .= sprintf('Auto TableDef Created [ zero(0) tables to create selected]<br>'); 
			}
			$HTML .= "<hr>";
			if($overwritelist[0] != null)
			{
				foreach($overwritelist as $key => $value)
				{
					$arr = $this->query_system_db("getautotable",$value);
					$row = $arr[0];
					$result = $this->copy_autodef_to_defdir($row,TABLEDEFS,"tabledef");
					$HTML .= sprintf('TableDef Copy [<i><b>%s</b></i>] : %s<br><br>',$value,$result); 
				}
			}
			else
			{
				$HTML .= sprintf('Auto TableDef Created : [ zero(0) tables to overwrite selected]<br>'); 
			}
		}
		else
		{
			$result = array();
			$HTML .= $this->create_autogen_input_form($dropdown,$result,"createautotables");
		}
		return $HTML;
	}
	
	function create_auto_menus()
	{
		$dropdown = $this->query_system_db("getautomenus","");
		$HTML = "<h3>Auto Generate Menus</h3>";
	
		if( isset($_REQUEST['userButtonPress']) )
		{	
			$createlist = preg_split('/,/',$_REQUEST['create']);
			$overwritelist = preg_split('/,/',$_REQUEST['overwrite']);
		
			if($createlist[0] != null)
			{
				foreach($createlist as $key => $value)
				{
					$result = $this->create_automenu_def($value);
					$HTML .= sprintf('Auto MenuDef Created [<i><b>%s</b></i>] : %s<br>',$value,$result); 
				}
			}
			else
			{
				$HTML .= sprintf('Auto MenuDef Created : [ zero(0) menus to create selected]<br>'); 
			}
			$HTML .= "<hr>";
			if($overwritelist[0] != null)
			{
				foreach($overwritelist as $key => $value)
				{
					$arr = $this->query_system_db("getautomenu",$value);
					$row = $arr[0];
					$result = $this->copy_autodef_to_defdir($row,MENUDEFS,"menudef");
					$HTML .= sprintf('MenuDef Copy [<i><b>%s</b></i>] : %s<br><br>',$value,$result); 
				}
			}
			else
			{
				$HTML .= sprintf('Auto MenuDef Created : [ zero(0) menus to overwrite selected]<br>'); 
			}
		}
		else
		{
			$result = array();
			$HTML .= $this->create_autogen_input_form($dropdown,$result,"createautomenus");
		}
		return $HTML;
	}

	function create_auto_params()
	{
		$dropdown = $this->query_app_db("getmenuurls","");
		$HTML = "<h3>Auto Generate Params</h3>";
	
		if( isset($_REQUEST['userButtonPress']) )
		{	
			$createlist = preg_split('/,/',$_REQUEST['create']);
			$overwritelist = preg_split('/,/',$_REQUEST['overwrite']);
		
			if($createlist[0] != null)
			{
				foreach($createlist as $key => $value)
				{
					$result= $this->create_autoparam_def($value);
					$HTML .= sprintf('Auto ParamDef Created [<i><b>%s</b></i>] : %s<br>',$value,$result); 
				}
			}
			else
			{
				$HTML .= sprintf('Auto ParamDef Created [ zero(0) params to create selected]<br>'); 
			}
			$HTML .= "<hr>";
			if($overwritelist[0] != null)
			{
				foreach($overwritelist as $key => $value)
				{
					$arr = $this->query_app_db("getmenuurl",$value);
					$row = $arr[0];
					$result = $result = $this->copy_autodef_to_defdir($row,PARAMDEFS,"paramdef");
					$HTML .= sprintf('ParamDef Copy [<i><b>%s</b></i>] : %s<br><br>',$value,$result); 
				}
			}
			else
			{
				$HTML .= sprintf('Auto ParamDef Created : [ zero(0) params to overwrite selected]<br>'); 
			}
		}
		else
		{
			$result = array();
			$HTML .= $this->create_autogen_input_form($dropdown,$result,"createautoparams");
		}
		return $HTML;
	}

	function create_auto_forms()
	{
		$dropdown = $this->query_app_db("getmenuurls","");
		$HTML = "<h3>Auto Generate Forms</h3>";
	
		if( isset($_REQUEST['userButtonPress']) )
		{	
			$createlist = preg_split('/,/',$_REQUEST['create']);
			$overwritelist = preg_split('/,/',$_REQUEST['overwrite']);
		
			if($createlist[0] != null)
			{
				foreach($createlist as $key => $value)
				{
					$result= $this->create_autoform_def($value);
					$HTML .= sprintf('Auto FormDef Created [<i><b>%s</b></i>] : %s<br>',$value,$result); 
				}
			}
			else
			{
				$HTML .= sprintf('Auto FormDef Created [ zero(0) forms to create selected]<br>'); 
			}
			$HTML .= "<hr>";
			if($overwritelist[0] != null)
			{
				foreach($overwritelist as $key => $value)
				{
					$arr = $this->query_app_db("getmenuurl",$value);
					$row = $arr[0];
					$result = $this->copy_autodef_to_defdir($row,FORMDEFS,"formdef");
					$HTML .= sprintf('FormDef Copy [<i><b>%s</b></i>] : %s<br><br>',$value,$result); 
				}
			}
			else
			{
				$HTML .= sprintf('Auto FormDef Created : [ zero(0) forms to overwrite selected]<br>'); 
			}
		}
		else
		{
			$result = array();
			$HTML .= $this->create_autogen_input_form($dropdown,$result,"createautoforms");
		}
		return $HTML;
	}

	function create_auto_mvcs()
	{
		$dropdown = $this->query_app_db("getmenuurls","");
		$HTML = "<h3>Auto Generate MVCs</h3>";
	
		if( isset($_REQUEST['userButtonPress']) )
		{	
			$createlist = preg_split('/,/',$_REQUEST['create']);
			$overwritelist = preg_split('/,/',$_REQUEST['overwrite']);
		
			if($createlist[0] != null)
			{
				foreach($createlist as $key => $value)
				{
					$result= $this->create_automvc_def($value);
					$HTML .= sprintf('Auto MVCDef Created [<i><b>%s</b></i>] : %s<br>',$value,$result); 
				}
			}
			else
			{
				$HTML .= sprintf('Auto MVCDef Created [ zero(0) create mvcs selected]<br>'); 
			}
			$HTML .= "<hr>";
			if($overwritelist[0] != null)
			{
				foreach($overwritelist as $key => $value)
				{
					$arr = $this->query_app_db("getmenuurl",$value);
					$row = $arr[0];
					$result = $result = $this->copy_autodef_to_defdir($row,MVCDEFS,"mvcdef");
					$HTML .= sprintf('MCVDef Copy [<i><b>%s</b></i>] : %s<br><br>',$value,$result); 
				}
			}
			else
			{
				$HTML .= sprintf('Auto MVCDef Created : [ zero(0) overwrite mvcs selected]<br>'); 
			}
		}
		else
		{
			$result = array();
			$HTML .= $this->create_autogen_input_form($dropdown,$result,"createautomvcs");
		}
		return $HTML;
	}
	

/// END AUTOGEN###########################################################

	function copy_autodef_to_defdir($row,$defdir,$deftype)
	{
		$row = (array) $row;
		$controller = $row['id'];
		$MODULE = $row['module'];
	
		$sysarr = $this->query_system_db("getsysconfig","");
		$sysrow = (array) $sysarr[0];
		$SRCBASE = $sysrow['autogendir'];
		$TRGBASE = $sysrow['defdir'];
	
		$filename = $SRCBASE.$defdir.$controller.".".$deftype.".xml";
		$target = $TRGBASE."/".$MODULE."/".$controller."/".$deftype.".xml";
		$res = $this->copy_to_defs_dir($filename,$target);
		return $res;
	}

	function copy_to_defs_dir($filename,$target)
	{
		$res_c = 1; $res_b = 1;
		$date = date("YmdHis");
		$dirname = dirname($target); 
		$backupstr = "new def, no backup";
		if(!file_exists($dirname)){	mkdir($dirname,777,true);} 
		
		if(file_exists($filename))
		{
			if(is_file($target))
			{
				$backupstr = $target.".".$date.".xml";
				if(!copy($target, $backupstr)){$res_b = 0;}
			}
			if($res_b == 1) {if(!copy($filename, $target)){$res_c = 0;}}
		}
		else {$res_b = 0; $res_c = 0;}

		if($res_b > 0 && $res_c > 0) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
		$result_txt = sprintf('[ <span class="%s">%s</span> =><br>Backup Copied ( %s , %s )<br>File Copied ( %s , %s )]',$class,$RESTXT,$res_b,$backupstr,$res_c,$target);
		return $result_txt;
	}

	function create_autotable_def($value)
	{
		$arr = $this->query_system_db("getautotable",$value);
		$row = (array) $arr[0];
		//$MODULE = $row['module'];
	
		$sysarr = $this->query_system_db("getsysconfig","");
		$sysrow = (array) $sysarr[0];
		$BASE = $sysrow['autogendir'];

		$dirname = $BASE.TABLEDEFS;
		if(!file_exists($dirname)){mkdir($dirname,777,true);} 
		$id = $row['id']; $tablename = $row['tablename']; $tablefields = $row['tablefields']; $uniquefield = $row['uniquefield'];
	
		$date = date("YmdHis");
		$XMLHEADER = "<?xml version='1.0' standalone='yes'?>\n";
		$TEXT1 =<<<_TEXT_
<tabledef>
<id>$id (autogen $date)</id>
<tablename>$tablename</tablename>
<livecreate>yes</livecreate>
<histcreate>yes</histcreate>
<inaucreate>yes</inaucreate>
<columns>
_TEXT_;
		$lines = explode("\n", $tablefields); // string to array
		$TEXT2 = "\n";
		foreach($lines as $key => $linestr)
		{
			$value = preg_split('/;/',$linestr);
			if($value == "id")
			{
				$TEXT2.= "\t<column><colname>id</colname><coltype>int(11)</coltype><colopts>unsigned NOT NULL</colopts></column>\n";
			}
			else
			{
				$TEXT2.= sprintf("\t<column><colname>%s</colname><coltype>%s</coltype><colopts>%s</colopts></column>\n",$value[0],$value[1],$value[2]);
			}
		}
$TEXT3 =<<<_TEXT_
</columns>
<primarykey>id</primarykey>
<uniquekeys>
	<uniquekey><ukeyname>uniq_$uniquefield</ukeyname><ukeycol>$uniquefield</ukeycol></uniquekey>
</uniquekeys>
<engine>ENGINE=InnoDB DEFAULT CHARSET=utf8</engine>
</tabledef>
_TEXT_;
		$XML = $XMLHEADER.$TEXT1.$TEXT2.$TEXT3;
	
		$res = 0;
		$filename = $dirname.$row['id'].".tabledef.xml";
		if ($handle = fopen($filename, 'w')) 
		{
			fwrite($handle, $XML);
			fclose($handle);
			$res = 1;
		}
		if($res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
		$result_txt = sprintf('[ <span class="%s">%s</span> => File Created ( %s ) ]',$class,$RESTXT,$filename);
		return $result_txt;
	}

	function create_automenu_def($value)
	{
		$res = $this->query_system_db("deletemenutree","");
		if($res >= 0)
		{
			$arr = $this->query_system_db("getautomenu",$value);
			$row = (array) $arr[0];
			//$MODULE = $row['module'];
			
			$lines = explode("\n", $row['menu_layout']); // string to array
			foreach ($lines as $line)
			{
				$res = $this->query_system_db("insertintomenutree",$line);
				if($res === false)
				{
					$class = 'fail'; $RESTXT='FAIL';
					$result_txt = sprintf('[ <span class="%s">%s</span> => insert into menutree (%s) ]',$class,$RESTXT,$line);
					return $result_txt;
				}
			}
			$res = $this->query_system_db("rebuildmenutree",$row['root_id']);
			$class = 'pass'; $RESTXT='PASS';
			$menu_txt = sprintf('[ <span class="%s">%s</span> => <br> %s]',$class,$RESTXT,$res);
		}
		else
		{
			$class = 'fail'; $RESTXT='FAIL';
			$result_txt = sprintf('[ <span class="%s">%s</span> => could not clear menutree ]',$class,$RESTXT);
			return $result_txt;
		}
		$sysarr = $this->query_system_db("getsysconfig","");
		$sysrow = (array) $sysarr[0];
		$BASE = $sysrow['autogendir'];

		$dirname = $BASE.MENUDEFS;
		if(!file_exists($dirname)){mkdir($dirname,777,true);} 
		$id = $row['id'];
	
		$date = date("YmdHis");
		$XMLHEADER = "<?xml version='1.0' standalone='yes'?>\n";
		$TEXT1 =<<<_TEXT_
<menudef>
<id>$id (autogen $date)</id>
<menus>	
_TEXT_;
		$menutree = $this->query_system_db("getmenutree","");
		$TEXT2 = "\n";
		foreach($menutree as $f)
		{
			$f = (array) $f;
			if($f['node_or_leaf']=="N")
			{
				if($f['id_opt'] > 0)
				{
$TEXT2.= sprintf("\t<menu><id>%s</id><menu_id>%s</menu_id><parent_id>%s</parent_id><sortpos>%s</sortpos><node_or_leaf>%s</node_or_leaf><module>%s</module><label_input>%s</label_input><label_enquiry></label_enquiry><url_input></url_input><url_enquiry></url_enquiry><controls_input></controls_input><controls_enquiry></controls_enquiry></menu>\n",$f['id_opt'],$f['menu_id'],$f['parent_id'],$f['sortpos'],$f['node_or_leaf'],$f['module'],$f['title']);
				}
				else if($f['id_opt'] == 0)
				{
$TEXT2.= sprintf("\t<menu><menu_id>%s</menu_id><parent_id>%s</parent_id><sortpos>%s</sortpos><node_or_leaf>%s</node_or_leaf><module>%s</module><label_input>%s</label_input><label_enquiry></label_enquiry><url_input></url_input><url_enquiry></url_enquiry><controls_input></controls_input><controls_enquiry></controls_enquiry></menu>\n",$f['menu_id'],$f['parent_id'],$f['sortpos'],$f['node_or_leaf'],$f['module'],$f['title']);
				}
			}
			else
			{		
				if($f['id_opt'] > 0)
				{
$TEXT2.= sprintf("\t<menu><id>%s</id><menu_id>%s</menu_id><parent_id>%s</parent_id><sortpos>%s</sortpos><node_or_leaf>%s</node_or_leaf><module>%s</module><label_input>%s</label_input><label_enquiry>magicon016.png%s</label_enquiry><url_input>%s</url_input><url_enquiry>%s\\/enquirydefault</url_enquiry><controls_input>%s</controls_input><controls_enquiry>%s</controls_enquiry></menu>\n",$f['id_opt'],$f['menu_id'],$f['parent_id'],$f['sortpos'],$f['node_or_leaf'],$f['module'],$f['title'],"%IMG%",$f['url_input'],$f['url_input'],$f['control_input'],$f['control_enquiry']);
				}
				else if($f['id_opt'] == 0)
				{
$TEXT2.= sprintf("\t<menu><menu_id>%s</menu_id><parent_id>%s</parent_id><sortpos>%s</sortpos><node_or_leaf>%s</node_or_leaf><module>%s</module><label_input>%s</label_input><label_enquiry>magicon016.png%s</label_enquiry><url_input>%s</url_input><url_enquiry>%s\\/enquirydefault</url_enquiry><controls_input>%s</controls_input><controls_enquiry>%s</controls_enquiry></menu>\n",$f['menu_id'],$f['parent_id'],$f['sortpos'],$f['node_or_leaf'],$f['module'],$f['title'],"%IMG%",$f['url_input'],$f['url_input'],$f['control_input'],$f['control_enquiry']);
				}
			}
		}
		$TEXT3 =<<<_TEXT_
</menus>
</menudef>
_TEXT_;
		$XML = $XMLHEADER.$TEXT1.$TEXT2.$TEXT3;
	
		$res = 0;
		$filename = $dirname.$row['id'].".menudef.xml";
		if ($handle = fopen($filename, 'w')) 
		{
			fwrite($handle, $XML);
			fclose($handle);
			$res = 1;
		}
		if($res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
		$result_txt = sprintf('[ <span class="%s">%s</span> => File Created ( %s ) ]',$class,$RESTXT,$filename);
		return $menu_txt."<br>".$result_txt."<br>";
	}
	
	function create_autoparam_def($value)
	{
		$arr = $this->query_app_db("getmenuurl",$value);
		$row = $arr[0];
		$row = (array) $row;
		$param_id = $row['id'];
		$module = $row['module'];
		$controller = $row['id'];
		if (preg_match('/_/', $row['id']))
		{
			$ctrlarr = preg_split('/_/',$row['id']);
			$controller = $ctrlarr[count($ctrlarr)-1];
		}

		$sysarr = $this->query_system_db("getsysconfig","");
		$sysrow = $sysarr[0];
		$sysrow = (array) $sysrow;
		$BASE = $sysrow['autogendir'];
		$dirname = $BASE.PARAMDEFS;
		if(!file_exists($dirname)){mkdir($dirname,777,true);} 
		
		$indexfield = $controller."_id";
		$indexlabel = $this->ucfirst_sentence(str_replace("_"," ",sprintf("%s %s",$controller,"Id")));
		$errormsgfile = $controller."_error";
		$tab = $controller."s"; $tab_is = $controller."s_is"; $tab_hs = $controller."s_hs";
		$appheader = $this->ucfirst_sentence($controller);
		$date = date("YmdHis");
	
		$XMLHEADER = "<?xml version='1.0' standalone='yes'?>\n";
		$TEXT1 =<<<_TEXT_
<paramdef>
<id>$controller (autogen $date)</id>
<columns>
	<column><field>param_id</field><value>$param_id</value></column>
	<column><field>controller</field><value>$controller</value></column>
 	<column><field>dflag</field><value>Y</value></column>
	<column><field>module</field><value>$module</value></column>
  	<column><field>auth_mode_on</field><value>1</value></column>
  	<column><field>index_field_on</field><value>1</value></column>
  	<column><field>indexview</field><value>default_index</value></column>
  	<column><field>viewview</field><value>default_view</value></column>
  	<column><field>inputview</field><value>default_input</value></column>
  	<column><field>authorizeview</field><value>default_authorize</value></column>
  	<column><field>deleteview</field><value>default_delete</value></column>
  	<column><field>enquiryview</field><value>default_enquiry</value></column>
  	<column><field>indexfield</field><value>$indexfield</value></column>
	<column><field>indexfieldvalue</field><value></value></column>
  	<column><field>indexlabel</field><value>$indexlabel</value></column>
  	<column><field>appheader</field><value>$appheader</value></column>
  	<column><field>primarymodel</field><value>Model_SiteDB</value></column>
  	<column><field>tb_live</field><value>$tab</value></column>
  	<column><field>tb_inau</field><value>$tab_is</value></column>
  	<column><field>tb_hist</field><value>$tab_hs</value></column>
  	<column><field>errormsgfile</field><value>$errormsgfile</value></column>
</columns>
</paramdef>
_TEXT_;
	$XML = $XMLHEADER.$TEXT1;
	
		$res = 0;
		$filename = $dirname.$param_id.".paramdef.xml";
		if ($handle = fopen($filename, 'w')) 
		{
			fwrite($handle, $XML);
			fclose($handle);
			$res = 1;
		}
		if($res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
		$result_txt = sprintf('[ <span class="%s">%s</span> => File Created ( %s ) ]',$class,$RESTXT,$filename);
		return $result_txt;
	}

	function create_autoform_def($value)
	{
		$TEXT1=""; $TEXT2 = "";
		$arr = $this->query_app_db("getmenuurl",$value);
		$row = (array) $arr[0];
		$param_id = $row['id'];
		$module = $row['module'];
		$controller = $row['id'];
		if (preg_match('/_/', $row['id']))
		{
			$ctrlarr = preg_split('/_/',$row['id']);
			$controller = $ctrlarr[count($ctrlarr)-1];
		}
	
		$sysarr = $this->query_system_db("getsysconfig","");
		$sysrow = (array) $sysarr[0];
		$BASE = $sysrow['autogendir'];
		$dirname = $BASE.FORMDEFS;
		if(!file_exists($dirname)){mkdir($dirname,777,true);} 

		$indexlabel = $this->ucfirst_sentence(str_replace("_"," ",sprintf("%s %s",$controller,"Id")));
		$errormsgfile = $controller."_error";
		$tab = $controller."s"; $tab_is = $controller."s_is"; $tab_hs = $controller."s_hs";
		$appheader = $this->ucfirst_sentence($controller);
		$date = date("YmdHis");
	
		$XMLHEADER = "<?xml version='1.0' standalone='yes'?>\n";
		$TEXT1 =<<<_TEXT_
<formdef>
<id>$param_id (autogen $date)</id>
<controller>$controller</controller>
<module>$module</module>
<formfields>
_TEXT_;
		$tablename = $controller."s";
		$res = 0;
		if ($this->app_table_exist($tablename))
		{
			$querystr = sprintf('describe %s',$tablename);
			$result = $this->sitedb->execute_select_query($querystr);
			foreach($result as $row) 
			{
				$row = (array) $row;
				if($row['Field'] == 'id')
				{
					$TEXT2 .="\n\t<field><name>id</name><label>Id</label><type>hidden</type><value></value><options></options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n";
				}
				else if($row['Field'] == 'comments')
				{
					$TEXT2 .="\t<field><name>comments</name><label>Comments</label><type>textarea</type><value></value><options>rows=2 cols=50</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n";
				}
				else if($row['Field'] == 'inputter')
				{
					break;
				}
				else
				{
					$label = str_replace("_"," ",$row['Field']);
					$label = $this->ucfirst_sentence($label);
					$type = "input"; $option = "size=50";
					if (preg_match('/date/i', $row['Field']))
					{
						$type = "date";
						$option = "size=12 maxlength=10 onFocus=sidefunc.Update(%FUNC%,%PARAMFLD%,%SFFORMAT%) onKeyUp=sidefunc.Update(%FUNC%,%PARAMFLD%,%SFFORMAT%)";
					}
					$TEXT2 .= sprintf("\t<field><name>%s</name><label>%s</label><type>%s</type><value></value><options>%s</options><onnew>enabled</onnew><onedit>enabled</onedit></field>\n",$row['Field'],$label,$type,$option);
				}
			}
			$TEXT2 .= "</formfields>\n</formdef>\n";
			$XML = $XMLHEADER.$TEXT1.$TEXT2;
		
			$filename = $dirname.$param_id.".formdef.xml";
			if ($handle = fopen($filename, 'w')) 
			{
				fwrite($handle, $XML);
				fclose($handle);
				$res = 1;
			}
		}
		if($res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
		$result_txt = sprintf('[ <span class="%s">%s</span> => Formdef Created ( %s ) ]',$class,$RESTXT,$res);
		return $result_txt;
	}
	
	function create_automvc_def($value)
	{
		$arr = $this->query_app_db("getmenuurl",$value);
		$row = (array) $arr[0];
		$param_id = $row['id'];
		$module = $row['module'];
		$target	= str_replace("/ ","/",ucwords(str_replace("_","/ ",$param_id)));

		$sysarr = $this->query_system_db("getsysconfig","");
		$sysrow = (array) $sysarr[0];
		$BASE = $sysrow['autogendir'];
		$dirname = $BASE.MVCDEFS;
		$ctrldir = $BASE.FORMDIR;
		if(!file_exists($dirname)){mkdir($dirname,777,true);} 

		$ctrl_srcfile= $ctrldir.$param_id.".controller.php";
		$date = date("YmdHis");
		$XMLHEADER = "<?xml version='1.0' standalone='yes'?>\n";
		$TEXT1 =<<<_TEXT_
<mvc>
<id>$param_id (autogen $date)</id>
<controllers>
	<controller><src>$ctrl_srcfile</src><target>application/classes/Controller/$target.php</target></controller>
</controllers>
<models>
	<model><src></src><target></target></model>
</models>
<views>
	<view><src></src><target></target></view>
</views>
<files>
	<file><src></src><target></target></file>
</files>
</mvc>
_TEXT_;
		$XML = $XMLHEADER.$TEXT1;
	
		$res = 0;
		$filename = $dirname.$param_id.".mvcdef.xml";
		if ($handle = fopen($filename, 'w')) 
		{
			fwrite($handle, $XML);
			fclose($handle);
			$res = 1;
		}
		if($res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
		$result_txt = sprintf('[ <span class="%s">%s</span> => MVCdef Created ( %s ) ]',$class,$RESTXT,$res);
		$ctrl_txt = $this->create_controller($value);
		return $result_txt.$ctrl_txt;
	}

	function create_controller($value)
	{
		$arr = $this->query_app_db("getmenuurl",$value);
		$row = (array) $arr[0];
		$param_id = $row['id'];
		$module = $row['module'];
		$controller = $row['id'];
		$js_file	= $row['id'];
		if (preg_match('/_/', $row['id']))
		{
			$ctrlarr = preg_split('/_/',$row['id']);
			$controller = $ctrlarr[count($ctrlarr)-1];
			$js_file = $ctrlarr[0].".".$ctrlarr[count($ctrlarr)-1];
		}
		$classname	= "Controller_".str_replace("_ ","_",ucwords(str_replace("_","_ ",$param_id)));
		$controller_file = ucfirst($controller).".php";
		$alt_id		= $controller."_id";	
		$timestamp	= date("Y-m-d H:i:s");
		$year		= date("Y");
		
		$sysarr	= $this->query_system_db("getsysconfig","");
		$sysrow	= (array) $sysarr[0];
		$BASE = $sysrow['autogendir'];
		$dirname = $BASE.FORMDIR;
		if(!file_exists($dirname)){mkdir($dirname,777,true);} 
		
		$TEXT =<<<_TEXT_
<?php defined('SYSPATH') or die('No direct script access.');
/**
 * <insert controller description>. 
 *
 * \$Id: $controller_file $timestamp dnesbit \$
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) $year
 * @license      
 */
class $classname extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('$controller');
		// \$this->param['htmlhead'] .= \$this->insert_head_js();
	}	
		
	public function action_index()
    {
		\$this->param['indexfieldvalue'] = strtoupper( \$this->request->param('opt') );
		\$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( \$this->randomize('media/js/$js_file.js') );
	}

	function input_validation()
	{
		\$post = \$_POST;	
		//validation rules
		array_map('trim',\$post);
		\$validation = new Validation(\$post);
		\$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		\$validation
			->rule('$alt_id','not_empty')
			->rule('$alt_id','min_length', array(':value', 16))->rule('$alt_id','max_length', array(':value', 16))
			->rule('$alt_id', array(\$this,'duplicate_altid'), array(':validation', ':field', \$_POST['id'], \$_POST['$alt_id']));
			
		\$this->param['isinputvalid'] = \$validation->check();
		\$this->param['validatedpost'] = \$validation->data();
		\$this->param['inputerrors'] = (array) \$validation->errors(\$this->param['errormsgfile']);
	}
} //End $classname
_TEXT_;

		$res = 0;
		$filename = $dirname.$param_id.".controller.php";
		if ($handle = fopen($filename, 'w')) 
		{
			fwrite($handle, $TEXT);
			fclose($handle);
			$res = 1;
		}
		if($res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
		$result_txt = sprintf('[ <span class="%s">%s</span> => Controller Created ( %s ) ]',$class,$RESTXT,$res);
		return $result_txt;
	}

	function rebuild_menutree($parent,$left)
	{
		// the right value of this node is the left value + 1 
		$right = $left + 1;
		$querystr = sprintf('SELECT title FROM _adi_menutrees WHERE parent="%s";',$parent);
		$result = $this->sitedb->execute_select_query($querystr);
		foreach ($result as $key => $row) 
		{   
			// recursive execution of this function for each child of this node   
			// $right is the current right value, which is incremented by the rebuild_tree function   
			$row = (array) $row;
			$right = $this->rebuild_menutree($row['title'], $right);   
		}
		// we've got the left value, and now that we've processed the children of this node we also know the right value   
		$querystr = sprintf('UPDATE _adi_menutrees SET lft=%s, rgt=%s WHERE title="%s";',$left,$right,$parent);
		$result = $this->sitedb->execute_update_query($querystr);
 		// return the right value of this node + 1   
		return $right + 1; 
	}
	function define_menutree($root_title,$root_id) 
	{  
		//traverses the menutree and creates the values required by menudefs 
		$res =""; $node_incr=1; $node_gap=0; $leaf_incr=1; $leaf_gap=50; $level_multiplier = 100;
		  
		// retrieve the left and right value of the $root node  
		$querystr = sprintf('SELECT lft, rgt FROM _adi_menutrees WHERE title="%s";',$root_title);
		$result = $this->sitedb->execute_select_query($querystr); 
		$row = (array)$result[0]; 
		// start with an empty $right stack  
		$right = array();  
 
		// now, retrieve all descendants of the $root node  
		//$querystr = sprintf('SELECT * FROM _adi_menutrees WHERE lft BETWEEN %s AND %s ORDER BY lft ASC;',$row['lft'],$row['rgt']);  
		$querystr = sprintf('SELECT * FROM _adi_menutrees WHERE lft >= %s AND lft <= %s ORDER BY lft ASC;',$row['lft'],$row['rgt']);  

		// display each row  
		$result = $this->sitedb->execute_select_query($querystr); 
		$count = 0;
		foreach ($result as $key => $row) 
		{   
			$row = (array) $row;
			// only check stack if there is one  
			
			if (count($right) > 0)
			{
				// check if we should remove a node from the stack  
				while($right[count($right)-1] < $row['rgt'])
				{  
					array_pop($right);  
				}  
			}
			
			// display indented node title  
			$tmpstr = sprintf('SELECT menu_id FROM _adi_menutrees WHERE title="%s"',$row['parent']);  
		
			$tmpres = $this->sitedb->execute_select_query($tmpstr);
			if($tmpres != null) { $tmprow = (array) $tmpres[0]; }
			if($row['node_or_leaf']=='N')
			{
				$res .= str_repeat(' &nbsp &nbsp ',count($right))."&oplus; <b>".$row['title']."</b><br>"; 
				if($tmpres == null) { $menu_id = 0; }
				else { $menu_id = ($tmprow['menu_id'] * $level_multiplier) + $node_incr + $node_gap - 1; }
				while($this->id_exist($menu_id))
				{
					$menu_id += $node_incr;
				}
			}
			else if($row['node_or_leaf']=='L')
			{
				$res .= str_repeat(' &nbsp &nbsp ',count($right))."&ndash; ".$row['title']."<br>"; 
				if($tmpres == null) { $menu_id = 0; }
				else { $menu_id = ($tmprow['menu_id'] * $level_multiplier) + $leaf_incr + $leaf_gap - 1; }
				while($this->id_exist($menu_id))
				{
					$menu_id += $leaf_incr;
				}
			}
		
			$sortpos = $menu_id / $level_multiplier;
			if($count == 0)
			{	
				$sortpos = $root_id / $level_multiplier;
				$updstr = sprintf('UPDATE _adi_menutrees SET parent_id="%s", menu_id="%s", sortpos="%s" WHERE title="%s";',0,$root_id,$sortpos,$root_title);
			}
			else
			{
				$updstr = sprintf('UPDATE _adi_menutrees SET parent_id="%s", menu_id="%s", sortpos="%s" WHERE title="%s";',$tmprow['menu_id'],$menu_id,$sortpos,$row['title']);
			}
			$bool = $this->sitedb->execute_update_query($updstr);
			$count++;	
			
			// add this node to the stack  
			$right[] = $row['rgt'];
			
		}
		return $res;
	}  

	function id_exist($id)
	{
		$querystr = sprintf('SELECT COUNT(menu_id) as counter FROM _adi_menutrees WHERE menu_id="%s";',$id);  
		$result = $this->sitedb->execute_select_query($querystr); 
		$row = (array) $result[0];
		if($row['counter'] > 0) {return true;} else {return false;};
	}

	/// START DATABASE FUNCTIONS###########################################################
	function query_system_db($queryopt,$var)
	{
		//global $sysdb;
		//$sysdb = new PDO('sqlite:definstaller.sqlite'); 

		switch($queryopt)
		{
			case "initialize_sysconfig":
				
				//sync module directories
				$querystr = sprintf('SELECT DISTINCT module FROM params');
				$res = $this->sitedb->execute_select_query($querystr);
				if($res)
				{
					foreach($res as $key => $value)
					{
						$value = (array)$value;
						$pathname = $_SERVER['DOCUMENT_ROOT'].'/halaya/autodefs/defs/'.$value['module'];
						if( !is_dir($pathname) ) { mkdir($pathname,0777,true); }
					}
				}
				
				if( $this->sitedb->record_exist("_adi_configs","id","system","system") )
				{
					//record exist
					return;
				}
				else
				{
					//set some default values
					$arr['id']			= '"'.'system'.'"';
					$arr['autogendir']	= '"'.$_SERVER['DOCUMENT_ROOT'].'/halaya/autodefs/autogen'.'"';
					$arr['defdir']		= '"'.$_SERVER['DOCUMENT_ROOT'].'/halaya/autodefs/defs'.'"';
					$arr['approotdir']	= '"'.$_SERVER['DOCUMENT_ROOT'].'/halaya'.'"';
					$arr['module']		= '"'.'useraccount'.'"';
					$arr['activedef']	= '"'.'core_useraccount_message'.'"';
					
					$fields		= array('id','autogendir','defdir','approotdir','module','activedef');
					$querystr	= sprintf('INSERT INTO _adi_configs(%s) VALUES(%s);',join(',',$fields),join(',',$arr));
					$res = $this->sitedb->execute_insert_query($querystr);
				}
			break;
						
			case "getsysconfig":
				$querystr = sprintf('SELECT * FROM _adi_configs WHERE id ="system"');
				$res = $this->sitedb->execute_select_query($querystr);
				return $res;
			break;
		
			case "setsysconfig":
				$querystr = sprintf('UPDATE _adi_configs SET %s="%s" WHERE id = "system"',$var[0],$var[1]);
				$res = $this->sitedb->execute_insert_query($querystr);
				return $res;
			break;

			case "gettabledef":
				$querystr = sprintf("SELECT * FROM tabledef");
				$res = selectFromSystemDB($querystr);
				return $res;
			break;
				
			case "settabledef":
				$arr=array_keys($var);
				foreach($arr as $key)
				{
					$id=$key; $fields = $var[$key];
					$querystr = sprintf("UPDATE tabledef SET tab_create='%s',sql_drop='%s',sql_create='%s',sql_show='%s' WHERE id = '%s'",$fields['tab_create'],$fields['sql_drop'],$fields['sql_create'],$fields['sql_show'],$id);
					$res = insertToSystemDB($querystr);
				}
				return $res;
			break;
		
			case "getparamdef":
				$querystr = sprintf("SELECT * FROM paramdef");
				$res = selectFromSystemDB($querystr);
				return $res;
			break;
				
			case "setparamdef":
				$arr=array_keys($var);
				foreach($arr as $key)
				{
					$id=$key; $fields = $var[$key];
					$querystr = sprintf("UPDATE paramdef SET sql_code='%s' WHERE id = '%s'",$fields['sql_code'],$id);
					$res = insertToSystemDB($querystr);
				}
				return $res;
			break;
			
			case "getformdef":
				$querystr = sprintf("SELECT * FROM formdef");
				$res = selectFromSystemDB($querystr);
				return $res;
			break;
				
			case "setformdef":
				$arr=array_keys($var);
				foreach($arr as $key)
				{
					$id=$key; $fields = $var[$key];
					$querystr = sprintf("UPDATE formdef SET controller='%s',module='%s',sql_code='%s' WHERE id = '%s'",$fields['controller'],$fields['module'],$fields['sql_code'],$id);
					$res = insertToSystemDB($querystr);
				}
				return $res;
			break;

			case "getmenudef":
				$querystr = sprintf("SELECT * FROM menudef");
				$res = selectFromSystemDB($querystr);
				return $res;
			break;
				
			case "setmenudef":
				$arr=array_keys($var);
				foreach($arr as $key)
				{
					$id=$key; $fields = $var[$key];
					$querystr = sprintf("UPDATE menudef SET sql_code='%s' WHERE id = '%s'",$fields['sql_code'],$id);
					$res = insertToSystemDB($querystr);
				}
				return $res;
			break;
			
			case "getupdatedef":
				$querystr = sprintf("SELECT * FROM updatedef");
				$res = selectFromSystemDB($querystr);
				return $res;
			break;
			
			case "setupdatedef":
				$arr=array_keys($var);
				foreach($arr as $key)
				{
					$id=$key; $fields = $var[$key];
					$querystr = sprintf("UPDATE updatedef SET sql_code='%s' WHERE id = '%s'",$fields['sql_code'],$id);
					$res = insertToSystemDB($querystr);
				}
				return $res;
			break;
	
			case "getmvcdef":
				$querystr = sprintf("SELECT * FROM mvcdef");
				$res = selectFromSystemDB($querystr);
				return $res;
			break;
		
			case "delmvcdef":
				$querystr = sprintf("DELETE from mvcdef;");
				$res = insertToSystemDB($querystr);
				return $res;
			break;
		
			case "setmvcdef":
				$arr=array_keys($var);
				foreach($arr as $key)
				{
					$id=$key; $fields = $var[$key];
					$querystr = sprintf("INSERT INTO mvcdef(id,src,target) VALUES ('%s','%s','%s');",$fields['id'],$fields['src'],$fields['target']);
					$res = insertToSystemDB($querystr);
				}
				return $res;
			break;

			case "getautotables":
				$querystr = sprintf('SELECT * FROM _adi_autotables');
				$res = $this->sitedb->execute_select_query($querystr);
				return $res;
			break;

			case "getautotable":
				$querystr = sprintf('SELECT * FROM _adi_autotables WHERE id="%s"',$var);
				$res = $this->sitedb->execute_select_query($querystr);
				return $res;
			break;

			case "insertautotable":
				$querystr = sprintf('INSERT INTO _adi_autotables(id,module,tablename,tablefields,uniquefield) VALUES("%s","%s","%s","%s","%s");',$var['id'],$var['module'],$var['tablename'],$var['tablefields'],$var['uniquefield']);
				$res = $this->sitedb->execute_insert_query($querystr);
				if($res !== FALSE && $res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
				$result_txt = sprintf('[ <span class="%s">%s</span> => Records Inserted(%s) ]',$class,$RESTXT,$res);
				return $result_txt;
			break;
		
			case "updateautotable":
				$querystr = sprintf('UPDATE _adi_autotables SET module="%s",tablename="%s",tablefields=\'%s\',uniquefield="%s" WHERE id="%s";',$var['module'],$var['tablename'],$var['tablefields'],$var['uniquefield'],$var['id']);
				$res = $this->sitedb->execute_update_query($querystr);
				if($res !== FALSE && $res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
				$result_txt = sprintf('[ <span class="%s">%s</span> => Records Updated(%s) ]',$class,$RESTXT,$res);
				return $result_txt;
			break;
	
			case "getautomenus":
				$querystr = sprintf("SELECT * FROM _adi_automenus");
				$res = $this->sitedb->execute_select_query($querystr);
				return $res;
			break;

			case "getautomenu":
				$querystr = sprintf("SELECT * FROM _adi_automenus WHERE id='%s'",$var);
				$res = $this->sitedb->execute_select_query($querystr);
				return $res;
			break;

			case "insertautomenu":
				$querystr = sprintf('INSERT INTO _adi_automenus(id,module,root_id,menu_layout) VALUES("%s","%s","%s","%s");',$var['id'],$var['module'],$var['root_id'],$var['menu_layout']);
				$res = $this->sitedb->execute_insert_query($querystr);
				if($res !== FALSE && $res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
				$result_txt = sprintf('[ <span class="%s">%s</span> => Records Inserted(%s) ]',$class,$RESTXT,$res);
				return $result_txt;
			break;
		
			case "updateautomenu":
				$querystr = sprintf('UPDATE _adi_automenus SET module="%s",root_id="%s",menu_layout="%s" WHERE id="%s";',$var['module'],$var['root_id'],$var['menu_layout'],$var['id']);
				$res = $this->sitedb->execute_insert_query($querystr);
				if($res !== FALSE && $res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
				$result_txt = sprintf('[ <span class="%s">%s</span> => Records Updated(%s) ]',$class,$RESTXT,$res);
				return $result_txt;
			break;
	
			case "deletemenutree":
				$querystr = sprintf('DELETE FROM _adi_menutrees');
				$res = $this->sitedb->execute_delete_query($querystr);
				return $res;
			break;
		
			case "insertintomenutree":
				$res = false;
				$fldvals = preg_split('/;/',trim($var));
//print "<b>[DEBUG]---></b> "; print_r($fldvals); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
				
				if(count($fldvals) == 8)
				{
					$querystr = vsprintf('INSERT INTO _adi_menutrees(parent,title,node_or_leaf,module,url_input,control_input,control_enquiry,id_opt) VALUES("%s","%s","%s","%s","%s","%s","%s","%s");',$fldvals);
//print "<b>[DEBUG]---></b> "; print($querystr); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
				
					$res = $this->sitedb->execute_insert_query($querystr);
				}
				return $res;
			break;

			case "rebuildmenutree":
				list($root_title,$root_id) = preg_split('/,/',trim($var));
				$this->rebuild_menutree($root_title,1);
//print "<b>[DEBUG]---></b> "; print($root_title."; ".$root_id); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
				$res = $this->define_menutree($root_title,$root_id); //add menudefs values
				return $res;
			break;

			case "getmenutree":
				$querystr = sprintf('SELECT * FROM _adi_menutrees');
				$res = $this->sitedb->execute_select_query($querystr);
				return $res;
			break;
		}
	}

	function query_app_db($queryopt,$var,&$showquery="")
	{
		$querystr = "";

		switch($queryopt)
		{
			case "users":
				$querystr = sprintf('SELECT * FROM users');
				$res = $this->sitedb->execute_select_query($querystr);
				return $res;
			break;
		
			case "tabledef":
				$tablecount = 0; $i=0;
				foreach ($var as $row)
				{
					if($i == 0){$showquery = $row['sql_show'];}
					$tab_create = $row['tab_create'];
					if (stristr('yes', $tab_create) || $tab_create == 1) 
					{
						$querystr .= $row['sql_drop'].$row['sql_create'];
						$tablecount++;
					}
					$i++;
				}
				$res = $this->sitedb->execute_insert_query($querystr);
				sleep(SLEEPTIME);
				if($res !== FALSE)
				{
					return $tablecount;
				}
			break;

			case "tableshow":
				$querystr = $showquery;
				$tablecount = $var;
				$arr = $this->show_from_app_db($querystr,$rescount);
				if($tablecount == $rescount && $tablecount > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
				$format="";
				if($rescount == 3){$format = '%s, %s, %s';}else if($rescount == 2){$format = '%s, %s';}else if($rescount == 1){$format = '%s';}
				$tables = vsprintf($format,$arr);	
				$result_txt = sprintf('[ <span class="%s">%s</span> => Count(%s), Success Installed(%s), Tables(%s) ]',$class,$RESTXT,$tablecount,$rescount,$tables);
				return $result_txt;
			break;

			case "paramdef":
				foreach ($var as $row)
				{
					$querystr .= $row['sql_code'];
				}	
				$reccount = $this->sitedb->execute_insert_query($querystr);
				if($reccount !== FALSE && $reccount > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
				$result_txt = sprintf('[ <span class="%s">%s</span> => Records Uploaded(%s) ]',$class,$RESTXT,$reccount);
				return $result_txt;
			break;

			case "formdef":
				foreach ($var as $row)
				{
					$querystr .= $row['sql_code'];
				}	
				$querystr = str_replace("%XMLHEADER%",XMLHEADER, $querystr); 
				$reccount = $this->sitedb->execute_insert_query($querystr);
				if($reccount !== FALSE && $reccount > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
				$result_txt = sprintf('[ <span class="%s">%s</span> => Records Uploaded(%s) ]',$class,$RESTXT,$reccount);
				return $result_txt;
			break;
			
			case "menudef":
				foreach ($var as $row)
				{
					$querystr .= $row['sql_code'];
				}	
				$reccount = $this->sitedb->execute_insert_query($querystr);
				if($reccount !== FALSE && $reccount > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
				$result_txt = sprintf('[ <span class="%s">%s</span> => Records Uploaded(%s) ]',$class,$RESTXT,$reccount);
				return $result_txt;
			break;

			case "updatedef":
				foreach ($var as $row)
				{
					$querystr .= $row['sql_code'];
				}	
				$reccount = $this->sitedb->execute_insert_query($querystr);
				if($reccount !== FALSE && $reccount > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
				$result_txt = sprintf('[ <span class="%s">%s</span> => Records Uploaded(%s) ]',$class,$RESTXT,$reccount);
				return $result_txt;
			break;
			
			case "getmenuurls":
				$querystr = sprintf('SELECT url_input as id,module FROM menudefs where node_or_leaf="L" AND (url_input !="" || url_input !=NULL) ORDER BY url_input;');
				$res = $this->sitedb->execute_select_query($querystr);
				return $res;	
			break;

			case "getmenuurl":
				$querystr = sprintf("SELECT url_input as id,module FROM menudefs WHERE url_input='%s'",$var);
				$res = $this->sitedb->execute_select_query($querystr);
				return $res;
			break;
		}
	}
}
?>