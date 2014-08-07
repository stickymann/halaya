<?php defined('SYSPATH') or die('No direct script access.');
/**
 * <insert controller description>. 
 *
 * $Id: Bicrmonthly.php 2013-03-29 14:44:25 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Businessadmin_Bicrmonthly extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('bicrmonthly');
		$this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.bicrmonthly.js') );
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
			->rule('batchrequest_id','not_empty')
			->rule('batchrequest_id','min_length', array(':value', 16))->rule('batchrequest_id','max_length', array(':value', 40))
			->rule('batchrequest_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['batchrequest_id']));
		$validation
			->rule('requesttype','not_empty');
		$validation
			->rule('cc_id', array($this,'check_ccid'), array(':validation', ':field'));
		$validation
			->rule('description','not_empty')
			->rule('description','min_length', array(':value', 5))->rule('description','max_length', array(':value', 255));
		$validation
			->rule('start_date','date');
		$validation
			->rule('end_date','date')
			->rule('end_date', array($this,'enddate_ok'), array(':validation', ':field'));
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function check_ccid(Validation $validation,$field)
	{
		if( $this->OBJPOST['requesttype'] == "EOMCC-ALL" && $this->OBJPOST['cc_id'] != "" ) 
		{ 
			$validation->error($field, 'ccid_clear');
		}
		else if( $this->OBJPOST['requesttype'] != "EOMCC-ALL" && $this->OBJPOST['cc_id'] == "" )
		{
			$validation->error($field, 'ccid_required');
		}
	}

	public function enddate_ok(Validation $validation,$field)
	{
		$startdate = strtotime( $this->OBJPOST['start_date'] );
		$enddate = strtotime( $this->OBJPOST['end_date'] );
		if( $startdate > $enddate ) 
		{ 
			$validation->error($field, 'enddate_failed');
		}
	}

	public function authorize_post_update_existing_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$bicr = new Controller_Core_Businessadmin_Bicrperiod;
			$bicr->create_batch_invoices();
		}
	}

	public function authorize_post_insert_new_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$bicr = new Controller_Core_Businessadmin_Bicrperiod;
			$bicr->create_batch_invoices();
		}
	}

} //End Controller_Core_Businessadmin_Bicrmonthly