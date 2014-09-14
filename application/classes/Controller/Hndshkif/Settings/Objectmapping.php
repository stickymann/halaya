<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gets internal Handshake ids of essential records. 
 *
 * $Id: Objectmapping.php 2014-09-14 14:44:02 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
 
require_once('media/hsi/objectops.php');
 
class Controller_Hndshkif_Settings_Objectmapping extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('objectmapping');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/hndshkif.objectmapping.js') );
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
			->rule('mapping_id','not_empty')
			->rule('mapping_id','min_length', array(':value', 2))->rule('mapping_id','max_length', array(':value',50))
			->rule('mapping_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['mapping_id']));
		$validation
			->rule('hs_object','not_empty')
			->rule('hs_object','min_length', array(':value', 2))->rule('hs_object','max_length', array(':value',50));	
		$validation
			->rule('hs_id','not_empty')
			->rule('hs_id','min_length', array(':value', 2))->rule('hs_id','max_length', array(':value',50));
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function update_run_status($table,$status,$mapping_id)
	{
		$querystr = sprintf('UPDATE %s SET run = "%s" WHERE mapping_id = "%s"',$table,$status,$mapping_id);
		$this->param['primarymodel']->execute_update_query($querystr);
	}
	
	public function update_object()
	{
		$objectops = new ObjectOps();
		$objectops->update_object_data( $this->OBJPOST['mapping_id'] );
		$this->update_run_status($this->param['tb_live'],"N",$this->OBJPOST['mapping_id']);
	}
	
	public function authorize_post_update_existing_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->update_object();
		}
	}

	public function authorize_post_insert_new_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->update_object();
		}
	}

} //End Controller_Hndshkif_Settings_Objectmapping
