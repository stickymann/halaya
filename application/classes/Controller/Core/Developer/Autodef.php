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

define("DATABASE","gbiz");
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
				$RESULT = addAutoMenu();
			break;
		
			case "createautotables":
				$RESULT = createAutoTables();
			break;
		
			case "createautomenus":
				$RESULT = createAutoMenus();
			break;

			case "createautoparams":
				$RESULT = createAutoParams();
			break;
		
			case "createautoforms":
				$RESULT = createAutoForms();
			break;
		
			case "createautomvcs":
				$RESULT = createAutoMVCs();
			break;

			case "defstatus":
				//$RESULT = definitionStatus();
			break;
	
			case "definstall":
				$RESULT = definitionInstall();
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
		$stmt = $this->sitedb->query(Database::SELECT,$querystr,TRUE);
		$result = $stmt->fetchAll();
		foreach($result as $row)
		{
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
				$querystr = sprintf("SELECT * FROM automenu");
				$res = selectFromSystemDB($querystr);
				return $res;
			break;

			case "getautomenu":
				$querystr = sprintf("SELECT * FROM automenu WHERE id='%s'",$var);
				$res = selectFromSystemDB($querystr);
				return $res;
			break;

			case "insertautomenu":
				$querystr = sprintf("INSERT INTO automenu(id,module,root_id,menu_layout) VALUES('%s','%s','%s','%s');",$var['id'],$var['module'],$var['root_id'],$var['menu_layout']);
				$res = insertToSystemDB($querystr);
				if($res !== FALSE && $res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
				$result_txt = sprintf('[ <span class="%s">%s</span> => Records Inserted(%s) ]',$class,$RESTXT,$res);
				return $result_txt;
			break;
		
			case "updateautomenu":
				$querystr = sprintf("UPDATE automenu SET module='%s',root_id='%s',menu_layout='%s' WHERE id='%s';",$var['module'],$var['root_id'],$var['menu_layout'],$var['id']);
				$res = insertToSystemDB($querystr);
				if($res !== FALSE && $res > 0 ) {$class = 'pass'; $RESTXT='PASS';} else { $class = 'fail'; $RESTXT='FAIL';}
				$result_txt = sprintf('[ <span class="%s">%s</span> => Records Updated(%s) ]',$class,$RESTXT,$res);
				return $result_txt;
			break;
	
			case "deletemenutree":
				$querystr = sprintf("DELETE from menutree");
				$res = insertToSystemDB($querystr);
				return $res;
			break;
		
			case "insertintomenutree":
				$res = false;
				$fldvals = preg_split('/,/',trim($var));
				if(count($fldvals) == 5)
				{
					$querystr = vsprintf("INSERT INTO menutree(parent,title,node_or_leaf,module,url_input) VALUES('%s','%s','%s','%s','%s');",$fldvals);
					$res = insertToSystemDB($querystr);
				}
				return $res;
			break;

			case "rebuildmenutree":
				list($root_title,$root_id) = preg_split('/,/',trim($var));
				rebuild_menutree($root_title,1);
				$res = define_menutree($root_title,$root_id); //add menudefs values
				return $res;
			break;

			case "getmenutree":
				$querystr = sprintf('SELECT * FROM menutree');
				$res = selectFromSystemDB($querystr);
				return $res;
			break;
		}
	}

}
?>