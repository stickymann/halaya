<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Create order re-open request record. 
 *
 * $Id: Orderopen.php 2013-03-20 17:11:08 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Orderopen extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('orderopen');
		$this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.orderopen.js') );
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
			->rule('orderopen_id','not_empty')
			->rule('orderopen_id','min_length', array(':value', 16))->rule('orderopen_id','max_length', array(':value', 16))
			->rule('orderopen_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['orderopen_id']));
		$validation
			->rule('order_id','not_empty')
			->rule('order_id','min_length', array(':value', 16))->rule('order_id','max_length', array(':value', 16))
			->rule('order_id', array($this,'record_status'), array(':validation', ':field', $this->OBJPOST['order_id']))
			->rule('order_id', array($this,'payment_status'), array(':validation', ':field', $this->OBJPOST['order_id']))
			->rule('order_id', array($this,'checkout_status'), array(':validation', ':field', $this->OBJPOST['order_id']));
		$validation
			->rule('pre_open_status','not_empty');
		$validation
			->rule('reason','not_empty')
			->rule('reason','min_length', array(':value', 10))->rule('reason','max_length', array(':value', 255));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function record_status(Validation $validation,$field,$altid)
	{
		$order = new Controller_Core_Sales_Order; 
		$table_live = $order->param['tb_live'];
		$table_inau = $order->param['tb_inau'];
		$status = $this->is_record_unlocked_and_exist($table_live,$table_inau,$field,$altid);
		if( $status == 0 )
		{
			$validation->error($field, 'record_is_locked');
		}
		else if( $status == -1)
		{
			$validation->error($field, 'order_id_not_exist');
		}
	}

	public function payment_status(Validation $validation,$field,$altid)
	{
		$payment = new Controller_Core_Sales_Payment; 
		$table = $payment->param['tb_live'];
		if( $this->records_exist($table,$field,$altid) )
		{
			$validation->error($field, 'payment_exist');
		}
	}

	public function checkout_status(Validation $validation,$field,$altid)
	{
		$checkout = new Controller_Core_Sales_Inventchkout; 
		$chk_table = $checkout->param['tb_live'];
		if( $this->records_exist($chk_table,$field,$altid) )
		{
			$order = new Controller_Core_Sales_Order; 
			$ot_live = $order->param['tb_live'];
			$querystr = sprintf('SELECT inventory_checkout_status FROM %s WHERE %s = "%s"',$ot_live,$field,$altid);
			$result = $this->param['primarymodel']->execute_select_query($querystr);  
			if($result)
			{
				$status = $result[0]->inventory_checkout_status;
				if( $status == "COMPLETED" || $status == "PARTIAL" )
				{
					$validation->error($field, 'checkout_exist');
				}
			}
		}
	}
	
	public function reopen_order()
	{
		$order = new Controller_Core_Sales_Order; 
		$ot_live  = $order->param['tb_live'];
		$ot_inau  = $order->param['tb_inau'];
		$order_id = $this->OBJPOST['order_id'];
		$idfield = "order_id";

		if( $this->is_record_unlocked_and_exist($ot_live,$ot_inau,$idfield,$order_id) )
		{
			$pmt = new Controller_Core_Sales_Payment; 
			$pmt_live  = $pmt->param['tb_live'];
			if( !($this->records_exist($pmt_live,$idfield,$order_id)) )
			{
				$chk = new Controller_Core_Sales_Payment; 
				$chk_live  = $chk->param['tb_live'];
				if( !($this->records_exist($chk_live,$idfield,$order_id)) )
				{
					$order->get_formless_record($this->OBJPOST['order_id']);
					$querystr = sprintf('UPDATE %s SET order_status="%s" WHERE order_id = "%s"',$ot_inau,"NEW",$order_id);
					if( $result = $this->param['primarymodel']->execute_update_query($querystr) ) { /* wait for update */ }
				}
				else
				{
					$this->append_to_status_message("<b>ORDER RE-OPEN FAILED</b> - Inventory checkout record exist");
				}
			}
			else
			{
				$this->append_to_status_message("<b>ORDER RE-OPEN FAILED</b> - Order payment exist");
			}
		}
		else
		{
			$this->append_to_status_message("<b>ORDER RE-OPEN FAILED</b> - Order record locked or does not exist");
		}
	}
	
	public function authorize_post_insert_new_record()
	{
		$this->reopen_order();
	}

} //End Controller_Core_Sales_Orderopen