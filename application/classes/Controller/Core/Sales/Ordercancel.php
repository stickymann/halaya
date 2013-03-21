<?php defined('SYSPATH') or die('No direct script access.');
/**
 * <insert controller description>. 
 *
 * $Id: Ordercancel.php 2013-03-18 01:59:35 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Ordercancel extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('ordercancel');
		$this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.ordercancel.js') );
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
			->rule('ordercancel_id','not_empty')
			->rule('ordercancel_id','min_length', array(':value', 16))->rule('ordercancel_id','max_length', array(':value', 16))
			->rule('ordercancel_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['ordercancel_id']));
		$validation
			->rule('order_id','not_empty')
			->rule('order_id','min_length', array(':value', 16))->rule('order_id','max_length', array(':value', 16))
			->rule('order_id', array($this,'record_status'), array(':validation', ':field', $this->OBJPOST['order_id']));
		$validation
			->rule('pre_cancel_status','not_empty');
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

	public function cancel_order()
	{
		$order = new Controller_Core_Sales_Order; 
		$order->get_formless_record($this->OBJPOST['order_id']);
		$order->form['order_status'] = "ORDER.CANCELLED";
		$order->update_formless_record($order->form);
		$order->authorize();
	}
	
	public function cancel_payments()
	{
		$pmnt = new Controller_Core_Sales_Payment; 
		$querystr = sprintf('SELECT payment_id FROM %s WHERE order_id = "%s"',$pmnt->param['tb_live'],$this->OBJPOST['order_id']);
		$result = $pmnt->param['primarymodel']->execute_select_query($querystr);

		if($result)
		{
			foreach($result as $key => $value)
			{
				$pmnt->get_formless_record($value->payment_id);
				$pmnt->form['payment_status'] = "CANCELLED";
				$pmnt->update_formless_record($pmnt->form);
				$pmnt->authorize();
			}
		}
	}
	
	public function delete_chkout_record()
	{
		$invchk = new Controller_Core_Sales_Inventchkout;
		$querystr = sprintf('DELETE FROM %s WHERE order_id = "%s"',$invchk->param['tb_live'],$this->OBJPOST['order_id']);
		$result = $invchk->param['primarymodel']->execute_delete_query($querystr);
		if($result)
		{
			$total = $invchk->param['primarymodel']->get_ns_totalrows();
		}
	}

	public function delete_deliverynotes()
	{
		$dnote = new Controller_Core_Sales_Deliverynote;
		$querystr = sprintf('DELETE FROM %s WHERE order_id = "%s"',$dnote->param['tb_live'],$this->OBJPOST['order_id']);
		$result = $dnote->param['primarymodel']->execute_delete_query($querystr);
		if($result)
		{
			$total = $dnote->param['primarymodel']->get_ns_totalrows();
		}
	}
	
	public function reverse_inventory_checkout()
	{
		$slog = new Controller_Core_Sales_Inventorysalelog;
		$invt = new Controller_Core_Sales_Inventory;
		$querystr = sprintf('SELECT inventory_id,qty_instock,qty_diff FROM %s WHERE order_id = "%s"',$slog->param['tb_live'],$this->OBJPOST['order_id']);
		$result = $slog->param['primarymodel']->execute_select_query($querystr);
		if($result)
		{
			foreach($result as $key => $value)
			{
				if($value->qty_diff < 0)
				{
					$qty_diff = $value->qty_diff * -1;
					$qty_instock = $qty_diff + $value->qty_instock;
				
					$invt->get_formless_record($value->inventory_id);
					$invt->form['qty_instock']	= $qty_instock;
					$invt->form['qty_diff']		= $qty_diff;
					$invt->form['last_update_type'] = "ORDER.CANCEL.RETURN";
					$invt->update_formless_record($invt->form);
					$invt->authorize();
					
					//insert into inventory_sale_logs
					$slog->form = $invt->form;
					unset( $slog->form['id'] ); unset( $slog->form['reorder_level'] );
					$slog->form['order_id']	= $this->OBJPOST['order_id'];
					$slog->form['qty_instock']	= $qty_instock;
					$slog->form['qty_diff']		= $qty_diff;
					$slog->form['last_update_type'] = "ORDER.CANCEL.RETURN";
					$slog->form['update_date']	= date('Y-m-d');
					$slog->form['inputter']		= Auth::instance()->get_user()->idname;
					$slog->form['input_date']	= date('Y-m-d H:i:s'); 
					$slog->form['authorizer']	= 'SYSAUTH';
					$slog->form['auth_date']	= date('Y-m-d H:i:s'); 
					$slog->form['current_no']	= 0; 
					$this->param['primarymodel']->insert_record($slog->param['tb_live'],$slog->form);
				}
			}
		}
	}
	
	public function authorize_post_insert_new_record()
	{
		$order = new Controller_Core_Sales_Order; 
		$ot_live  = $order->param['tb_live'];
		$ot_inau  = $order->param['tb_inau'];
		$order_id = $this->OBJPOST['order_id'];
		$idfield = "order_id";

		if( $this->is_record_unlocked_and_exist($ot_live,$ot_inau,$idfield,$order_id) )
		{
			$this->cancel_order();
			$this->cancel_payments();
			$this->delete_chkout_record();
			$this->delete_deliverynotes();
			$this->reverse_inventory_checkout();
		}
		else
		{
			$this->append_to_status_message("<b>ORDER CANCEL FAILED</b> - Order record locked or does not exist");
		}
	}

} //End Controller_Core_Sales_Ordercancel