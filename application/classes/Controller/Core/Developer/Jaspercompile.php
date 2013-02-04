<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Compiles JRXML file to Jasper report. 
 *
 * $Id: Jaspercompile.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Developer_Jaspercompile extends Controller_Core_Site
{
	public function __construct() 
	{
		parent::__construct("jaspercompile");
	}

	public function action_index($opt="")
	{
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}

	function input_validation()
	{
		$_POST['csv_id']	= strtoupper($_POST['csv_id']);
		
		$post = $_POST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('jrxml_file','not_empty')
			->rule('jrxml_file','min_length', array(':value', 3))->rule('jrxml_file','max_length', array(':value', 30))
			->rule('jrxml_file', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['jrxml_file']));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function compile_jrxml_to_report($jrxml_file)
	{
		$_POST['jasper_compiler']	= str_replace("%APP_DIR%",$_POST['app_dir'],$_POST['jasper_compiler']); 
		$_POST['jrxml_src_dir']		= str_replace("%APP_DIR%",$_POST['app_dir'],$_POST['jrxml_src_dir']); 
		$_POST['jasper_report_dir']	= str_replace("%APP_DIR%",$_POST['app_dir'],$_POST['jasper_report_dir']); 

		$cmd     = $_POST['jasper_compiler'];
		$infile	 = $_POST['jrxml_src_dir']."/".$_POST['jrxml_file'];
		$outfile = $_POST['jasper_report_dir']."/".$_POST['jasper_report_file'];
		
		//external command to execute
		$cmdstr = sprintf('%s "%s %s"',$cmd,$infile,$outfile);
print 	$cmdstr."<hr>";	
		exec ($cmdstr); 
		//$this->execInBackground($cmdstr);
	}
	
	function exec_in_background($cmd) 
	{ 
print php_uname()."<hr>";		
		if (substr(php_uname(), 0, 7) == "Windows")
		{ 
			pclose(popen("start /B ". $cmd, "r"));  
		} 
		else 
		{ 
			exec($cmd . " > /dev/null &");   
		} 
	} 

	public function authorize_post_update_existing_record()
	{
		if($_POST['compile'] = "Y" )
		{
			$this->compile_jrxml_to_report($_POST['jrxml_file']);
		}
	}

	public function authorize_post_insert_new_record()
	{
		if($_POST['compile'] = "Y" )
		{
			$this->compile_jrxml_to_report($_POST['jrxml_file']);
		}
	}

}//End Controller_Core_Developer_Jaspercompile
