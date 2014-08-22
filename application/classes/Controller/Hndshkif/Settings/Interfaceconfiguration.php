<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Interface configuration editor. 
 *
 * $Id: Interfaceconfiguration.php 2013-09-13 16:15:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Hndshkif_Settings_Interfaceconfiguration extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('interfaceconfiguration');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/hndshkif.interfaceconfiguration.js') );
	}

	function input_validation()
	{
		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('config_id','not_empty')
			->rule('config_id','min_length', array(':value', 2))->rule('config_id','max_length', array(':value', 30))
			->rule('config_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['config_id']));
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function write_configfile()
	{
		$filename = $this->OBJPOST['config_file'];
		$XML 	  = $this->OBJPOST['config_xml'];
		$XML      = str_replace("&","&amp;",$XML);
		
		try
		{
			if ($handle = fopen($filename, 'w+')) 
			{
				fwrite($handle, $XML);
				fclose($handle);
				//chmod($filename, OUTFILE_PERMISSION);
			}
		}
		catch (Exception $e) { }
	}
		
	public function authorize_post_update_existing_record()
	{
		$this->write_configfile();
	}

	public function authorize_post_insert_new_record()
	{
		$this->write_configfile();
	}

} //End Controller_Hndshkif_Settings_Interfaceconfiguration
