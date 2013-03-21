<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a payment cancellation record. 
 *
 * $Id: Paymentcancel.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Paymentcancel extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("paymentcancel");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.paymentcancel.js') );
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
			->rule('paymentcancel_id','not_empty')
			->rule('paymentcancel_id','min_length', array(':value', 16))->rule('paymentcancel_id','max_length', array(':value', 16))
			->rule('paymentcancel_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['paymentcancel_id']));
		$validation
			->rule('payment_id','not_empty')
			->rule('payment_id','min_length', array(':value', 16))->rule('payment_id','max_length', array(':value', 16))
			->rule('payment_id', array($this,'record_status'), array(':validation', ':field', $this->OBJPOST['payment_id']));
		$validation
			->rule('amount','not_empty')
			->rule('amount','numeric');
		$validation
			->rule('reason','not_empty')
			->rule('reason','min_length', array(':value', 10))->rule('reason','max_length', array(':value', 255));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function record_status(Validation $validation,$field,$altid)
	{
		$pmnt = new Controller_Core_Sales_Payment; 
		$table_live = $pmnt->param['tb_live'];
		$table_inau = $pmnt->param['tb_inau'];
		$status = $this->is_record_unlocked_and_exist($table_live,$table_inau,$field,$altid);
		if( $status == 0 )
		{
			$validation->error($field, 'record_is_locked');
		}
		else if( $status == -1)
		{
			$validation->error($field, 'payment_id_not_exist');
		}
	}

	public function authorize_post_insert_new_record()
	{
		$pmnt = new Controller_Core_Sales_Payment; 
		$pmnt_live  = $pmnt->param['tb_live'];
		$pmnt_inau  = $pmnt->param['tb_inau'];
		$pmnt_id = $this->OBJPOST['payment_id'];
		$idfield = "payment_id";

		if( $this->is_record_unlocked_and_exist($pmnt_live,$pmnt_inau,$idfield,$pmnt_id) )
		{
			$pmnt->get_formless_record($this->OBJPOST['payment_id']);
			$pmnt->form['payment_status'] = "CANCELLED";
			$pmnt->update_formless_record($pmnt->form);
			$pmnt->authorize();
		}
		else
		{
			$this->append_to_status_message("<b>PAYMENT FAILED</b> - payment record locked or does not exist");
		}
	}

} //End Controller_Core_Sales_Paymentcancel