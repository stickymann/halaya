<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a payment record. 
 *
 * $Id: Payment.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Payment extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("payment");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.payment.js') );
	}
	
	function input_validation()
	{
		$post = $_POST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('payment_id','not_empty')
			->rule('payment_id','min_length', array(':value', 16))->rule('payment_id','max_length', array(':value', 16))
			->rule('payment_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['payment_id']));
		$validation
			->rule('branch_id','not_empty')
			->rule('branch_id','min_length', array(':value', 2))->rule('branch_id','max_length', array(':value', 50));
		$validation
			->rule('till_id','not_empty')
			->rule('till_id','min_length', array(':value', 2))->rule('till_id','max_length', array(':value', 59))
			->rule('till_id', array($this,'is_till_ok'), array(':validation', ':field'));
		$validation
			->rule('order_id','not_empty')
			->rule('order_id','min_length', array(':value', 16))->rule('order_id','max_length', array(':value', 16))
			->rule('order_id', array($this,'is_orderstatus_ok'), array(':validation', ':field'));
		$validation
			->rule('amount','not_empty')
			->rule('amount','numeric');
		$validation
			->rule('payment_type','not_empty')
			->rule('payment_type','in_array', array(':value', array('CASH', 'CHEQUE', 'CREDIT.CARD', 'DEBIT.CARD')));
		$validation
			->rule('payment_date','not_empty')
			->rule('payment_date','date');
		$validation
			->rule('payment_status','not_empty')
			->rule('payment_status','in_array', array(':value', array('VALID', 'CANCELLED')));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

	public function is_till_ok(Validation $validation,$field)
	{
		$till_id = $_POST['till_id'];
		$idname  = Auth::instance()->get_user()->idname;
		if( !($this->is_user_till($till_id,$idname)) )
		{
			$validation->error($field, 'msg_till');
		}
	}

	public function is_orderstatus_ok(Validation $validation,$field)
	{
		$order_id = $_POST['order_id'];
		$order = new Controller_Core_Sales_Order();

		$querystr = sprintf('SELECT COUNT(id) AS count FROM %s WHERE order_id = "%s" AND order_status = "QUOTATION"' ,"vw_orderbalances",$order_id);
		$result = $this->param['primarymodel']->execute_select_query($querystr);
		$recs = $result[0];
		if( $recs->count > 0 )
		{
			$validation->error($field, 'msg_orderstatus');	
		}
	}
	
	public function is_user_till($till_id,$idname)
	{
		$till = new Controller_Core_Sales_Tilluser();
		//if till item exist
		$datestr = date('Y-m-d');
		$timestr = date('H:i:s');
		$querystr = sprintf('SELECT COUNT(id) AS count FROM %s WHERE till_id = "%s" AND till_user = "%s" AND expiry_date >= "%s" AND expiry_time > "%s" AND status="OPEN"',$till->param['tb_live'],$till_id,$idname,$datestr,$timestr);
		$result = $this->param['primarymodel']->execute_select_query($querystr);
		$recs = $result[0];
		if( $recs->count > 0 )
		{
			return true;
		}
		return false;
	}

	public function order_update()
	{
		$order_id = $_POST['order_id'];
		$order = new Controller_Core_Sales_Order();

		$querystr = sprintf('SELECT COUNT(id) AS count FROM %s WHERE order_id = "%s"',"vw_orderbalances",$order_id);
		$result = $this->param['primarymodel']->execute_select_query($querystr);
		$recs = $result[0];
		if( $recs->count > 0 )
		{
			$querystr = sprintf('SELECT id,order_id,invoice_date,balance FROM %s WHERE order_id = "%s"',"vw_orderbalances",$order_id);
			$result = $this->param['primarymodel']->execute_select_query($querystr);
			$orderrec = $result[0];
			
			if( $orderrec->balance > 0 ){ $order_status = "INVOICE.PART.PAID"; } else { $order_status = "INVOICE.FULL.PAID"; }
			$order->update_order_status($order->param['tb_live'],$order_id,$order_status,date('Y-m-d')); 


			if( $orderrec->invoice_date == "" ||  $orderrec->invoice_date == "0000-00-00" ||  $orderrec->invoice_date == "1901-12-14")
			{
				$order->update_order_invoice_date($order->param['tb_live'],$order_id,date('Y-m-d'));
			}
		}
	}
	
	public function authorize_post_insert_new_record()
	{
		$this->order_update();
	}

} //End Controller_Core_Sales_Payment