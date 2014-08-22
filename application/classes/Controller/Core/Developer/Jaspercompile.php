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
		$this->OBJPOST['csv_id']	= strtoupper($this->OBJPOST['csv_id']);
		
		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('jrxml_file','not_empty')
			->rule('jrxml_file','min_length', array(':value', 3))->rule('jrxml_file','max_length', array(':value', 30))
			->rule('jrxml_file', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['jrxml_file']));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function compile_jrxml_to_report($jrxml_file)
	{
		$this->OBJPOST['jasper_compiler']	= str_replace("%APP_DIR%",$this->OBJPOST['app_dir'],$this->OBJPOST['jasper_compiler']); 
		$this->OBJPOST['jrxml_src_dir']		= str_replace("%APP_DIR%",$this->OBJPOST['app_dir'],$this->OBJPOST['jrxml_src_dir']); 
		$this->OBJPOST['jasper_report_dir']	= str_replace("%APP_DIR%",$this->OBJPOST['app_dir'],$this->OBJPOST['jasper_report_dir']); 

		$cmd     = $this->OBJPOST['jasper_compiler'];
		$infile	 = $this->OBJPOST['jrxml_src_dir']."/".$this->OBJPOST['jrxml_file'];
		$outfile = $this->OBJPOST['jasper_report_dir']."/".$this->OBJPOST['jasper_report_file'];
		
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
		if($this->OBJPOST['compile'] = "Y" )
		{
			$this->compile_jrxml_to_report($this->OBJPOST['jrxml_file']);
		}
	}

	public function authorize_post_insert_new_record()
	{
		if($this->OBJPOST['compile'] = "Y" )
		{
			$this->compile_jrxml_to_report($this->OBJPOST['jrxml_file']);
		}
	}

}//End Controller_Core_Developer_Jaspercompile
