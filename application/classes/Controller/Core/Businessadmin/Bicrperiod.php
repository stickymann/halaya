<?php defined('SYSPATH') or die('No direct script access.');
/**
 * <insert controller description>. 
 *
 * $Id: Bicrperiod.php 2013-03-29 14:44:25 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Businessadmin_Bicrperiod extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('bicrperiod');
		$this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.bicrperiod.js') );
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

	public function create_batch_invoices()
	{
		$batchrequest_id = $this->OBJPOST['batchrequest_id'];
		$vals = preg_split('/\./',$batchrequest_id);
		$batch_id = $vals[1].".".$vals[2].".".$vals[3];
		$batch = new Controller_Core_Businessadmin_Batchinvoice();
			
		//create batch details
		$startdate = $this->OBJPOST['start_date'];
		$enddate   = $this->OBJPOST['end_date'];
		$cc_id	   = $this->OBJPOST['cc_id'];
		if( $this->OBJPOST['requesttype'] == "EOMCC-ALL" )
		{
			$querystr = <<<_SQL_
SELECT order_id, id AS invoice_id,order_date,first_name,last_name,order_details,extended_total,tax_total,order_total,payment_total,balance,payment_type FROM vw_orderbalances
WHERE is_co = "Y" 
AND order_date >= "$startdate" AND order_date <= "$enddate";
_SQL_;
		}
		else if ( $this->OBJPOST['requesttype'] == "EOMCC-ONE" OR $this->OBJPOST['requesttype'] == "PERCC-ONE")
		{
			$querystr = <<<_SQL_
SELECT order_id, id AS invoice_id,order_date,first_name,last_name,order_details,extended_total,tax_total,order_total,payment_total,balance,payment_type FROM vw_orderbalances
WHERE is_co = "Y" 
AND cc_id = "$cc_id"
AND order_date >= "$startdate" AND order_date <= "$enddate";
_SQL_;
		}
		$rows = "";
		if ($result = $this->param['primarymodel']->execute_select_query($querystr) )
		{
			foreach($result as $key => $val)
			{
$rows .= sprintf('<row><id>undefined</id><batch_id>%s</batch_id><order_id>%s</order_id><invoice_id>%s</invoice_id><alt_invoice_id>%s</alt_invoice_id><order_date>%s</order_date><first_name>%s</first_name><last_name>%s</last_name><order_details>%s</order_details><extended_total>%s</extended_total><tax_total>%s</tax_total><order_total>%s</order_total><payment_total>%s</payment_total><balance>%s</balance><payment_type>%s</payment_type></row>',$batch_id,$val->order_id,$val->invoice_id,$val->invoice_id,$val->order_date,$val->first_name,$val->last_name,$val->order_details,$val->extended_total,$val->tax_total,$val->order_total,$val->payment_total,$val->balance,$val->payment_type)."\n";
			}
		}
		
		$rows = substr_replace($rows, '', -1);
		$batchdetails = <<<_XML_
<?xml version='1.0' standalone='yes'?>
<rows>
$rows
</rows>
_XML_;
		//replace any ampersand in xml string
		$batchdetails = str_replace("&","&amp;",$batchdetails);
		
		$querystr = sprintf('SELECT count(batch_id) as counter FROM %s WHERE batch_id = "%s"',$batch->param['tb_live'],$batch_id);
		$result = $this->param['primarymodel']->execute_select_query($querystr);
		$count = $result[0]->counter;
		
		if( $count < 1 ) 
		{	
			//new record created here
			$formarr = $batch->param['primarymodel']->create_blank_record($batch->param['tb_live'],$batch->param['tb_inau']);
			$arr = (array)$formarr;
			$arr['batch_id']		= $batch_id;
			$arr['batch_date']		= date('Y-m-d');
			$arr['batch_description'] = $this->OBJPOST['description'];
			$arr['batch_type']		= $this->OBJPOST['requesttype'];
			$arr['batch_details']	= $batchdetails;
			$arr['inputter']		= $this->get_idname();
			$arr['input_date']		= date('Y-m-d H:i:s'); 
			$arr['authorizer']		= $this->get_idname();
			$arr['auth_date']		= date('Y-m-d H:i:s'); 
			$arr['record_status']	= "HLD";
			$arr['current_no']		= "0";
			$batch->form			= $arr;
			//new record updated here
			if( $result = $this->param['primarymodel']->update_record($batch->param['tb_inau'],$arr) )
			{
				$batch->create_subform_records($arr);
			}
		}
		else
		{
			$batch->get_formless_record($batch_id,$batch);
			$batch->form['batch_details'] = $batchdetails;
		}
		$batch->update_formless_record($batch->form);
		$batch->authorize();
		$this->update_run_status($this->param['tb_live'],"N",$batchrequest_id);
	}
	
	public function update_run_status($table,$status,$bicr_id)
	{
		$querystr = sprintf('UPDATE %s SET run = "%s" WHERE batchrequest_id = "%s"',$table,$status,$bicr_id);
		$this->param['primarymodel']->execute_update_query($querystr);
	}

	public function authorize_post_update_existing_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->create_batch_invoices();
		}
	}

	public function authorize_post_insert_new_record()
	{
		if( $this->OBJPOST['run'] == "Y" )
		{
			$this->create_batch_invoices();
		}
	}

} //End Controller_Core_Businessadmin_Bicrperiod
