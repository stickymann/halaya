<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a sales order record. 
 *
 * $Id: Ordernew.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Ordernew extends Controller_Core_Site
{
	public $BACK_ORDER_MODE = FALSE;

	public function __construct()
    {
		parent::__construct("ordernew");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.ordernew.js') );
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
			->rule('order_id','not_empty')
			->rule('order_id','min_length', array(':value', 16))->rule('order_id','max_length', array(':value', 16))
			->rule('order_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['order_id']));
		$validation
			->rule('branch_id','not_empty')
			->rule('branch_id','min_length', array(':value', 2))->rule('branch_id','max_length', array(':value', 50));
		$validation
			->rule('customer_id','not_empty')
			->rule('customer_id','min_length', array(':value', 8))->rule('customer_id','max_length', array(':value', 8));
		$validation
			->rule('order_status','not_empty')
			->rule('order_status','min_length', array(':value', 3))->rule('order_status','max_length', array(':value', 20))
			->rule('order_status', array($this,'order_status_ok'), array(':validation', ':field'));
		$validation
			->rule('order_date','date');
		$validation
			->rule('status_change_date','date');
		$validation
			->rule('quotation_date','date');
		$validation
			->rule('invoice_date','date');
		$validation
			->rule('order_details','not_empty')
			->rule('order_details', array($this,'order_details_exist'), array(':validation', ':field'))
			->rule('order_details', array($this,'stockcheck_pass'), array(':validation', ':field'));
		$validation
			->rule('inventory_checkout_type','not_empty')
			->rule('inventory_checkout_type','in_array', array(':value', array('AUTO', 'MANUAL')));
		$validation
			->rule('inventory_checkout_status','not_empty')
			->rule('inventory_checkout_status','in_array', array(':value', array('NONE', 'PARTIAL', 'COMPLETED')));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

	public function order_details_exist(Validation $validation,$field)
	{
		$count = 0; $usertext_required = false;
		$rows = new SimpleXMLElement($this->OBJPOST['order_details']);
		if($rows->row) 
		{ 
			foreach ($rows->row as $row) 
			{ 
				$user_text = sprintf('%s',$row->user_text);
				if(!($user_text == "?") && (strlen($user_text) < 3)) { $usertext_required = true; }
				$count++; 
			} 
		}
		if( !($count > 0) ) { $validation->error($field, 'zero_orderdetails');}
		if( $usertext_required ) { $validation->error($field, 'usertext_required');}
	}

	public function stockcheck_pass(Validation $validation,$field)
	{
		if( $this->BACK_ORDER_MODE ) { return; }
		$products = ""; $quantities = "";
		$rows = new SimpleXMLElement($this->OBJPOST['order_details']);
		if($rows->row) 
		{ 
			foreach ($rows->row as $row) 
			{ 
				$products	.= sprintf('%s',$row->product_id).",";
				$quantities	.= sprintf('%s',$row->qty).",";
			} 
		}
		$products   = substr_replace($products, '', -1);
		$quantities = substr_replace($quantities, '', -1);
		
		$order_statuses = array('QUOTATION','QUOTATION.EXPIRED','ORDER.CANCELLED','REOPENED');
		if( !( in_array($this->OBJPOST['order_status'],$order_statuses) ))
		{
			$chk = new Controller_Core_Sales_Inventchkout();
			$querystr = sprintf('SELECT COUNT(order_id) AS count FROM %s WHERE order_id ="%s"',$chk->param['tb_live'], $this->OBJPOST['order_id']);
			$result	  = $this->param['primarymodel']->execute_select_query($querystr);
			$count	  = $result[0]->count;
			if($count == 0 ) 
			{
				$order_id   = $this->OBJPOST['order_id'];
				$branch_id  = $this->OBJPOST['branch_id'];
				$icstat		= $this->OBJPOST['inventory_checkout_status'];
				$baseurl    = URL::base(TRUE,'http');
$url = sprintf('%sindex.php/core_ajaxtodb?option=stockcheckstatus&order=%s&icstat=%s&branch=%s&products=%s&quantities=%s',$baseurl,$order_id,$icstat,$branch_id,$products,$quantities);
				$status = Controller_Core_Sitehtml::get_html_from_url($url);
				if($status == "FAIL")
				{
					$validation->error($field, 'stock_required');
				}
			}  
		}
	}

	public function order_status_ok(Validation $validation,$field)
	{
		$status_new = false;
		if($this->OBJPOST['order_status'] == "NEW") { $status_new = true; }
		if( $status_new ) { $validation->error($field, 'msg_new');}
	}

	public function subform_summary_html($results=null,$labels=null,$color=null)
	{
		$subtotal =0; $tax_total = 0; $grandtotal = 0; $products = ""; $quantities = "";
		foreach($results as $index => $row)
		{
			$row = (array) $row;
			if($row['discount_type']=="PERCENT")
			{
				$discount_amount =  ($row['qty']*$row['unit_price']) *  ($row['discount_amount'] / 100);
			}
			else
			{
				$discount_amount =  $row['discount_amount'];
			}
			
			$subtotal		+= ($row['qty']*$row['unit_price']) - $discount_amount;
			$tax_total		+= $row['tax_amount'];
			$grandtotal		+= $row['total'];
			$products		.= $row['product_id'].",";
			$quantities		.= $row['qty'].",";
		}  
		$products   = substr_replace($products, '', -1);
		$quantities = substr_replace($quantities, '', -1);
		$order_id   = $this->form['order_id'];
		$branch_id  = $this->form['branch_id'];
		$icstat		= $this->form['inventory_checkout_status'];
		$baseurl    = URL::base(TRUE,'http');
$url = sprintf('%sindex.php/core_ajaxtodb?option=stockcheckreport&order=%s&icstat=%s&branch=%s&products=%s&quantities=%s&style=viewtbl',$baseurl,$order_id,$icstat,$branch_id,$products,$quantities);
		$loadval = Controller_Core_Sitehtml::get_html_from_url($url);
	
		$summaryhtml  = '<div id="summary_container">';
		$summaryhtml .= '<div id="total_vw" name="total_vw" class="total_vw">';
		$summaryhtml .= '<table class="viewtext" >';
		$summaryhtml .= sprintf('<tr><td style="color:%s;"><b>Sub Total :</b></td><td width="25%s" style="text-align:right; padding 5px 5px 5px 5px; color:%s;">%s</td></tr>',$color,"%",$color,number_format($subtotal, 2, '.', ''));
		$summaryhtml .= sprintf('<tr><td style="color:%s;"><b>Tax Total :</b></td><td width="25%s" style="text-align:right; padding 5px 5px 5px 5px; color:%s;">%s</td></tr>',$color,"%",$color,number_format($tax_total, 2, '.', ''));
		$summaryhtml .= sprintf('<tr><td style="color:%s;"><b>GRAND TOTAL :</b></td><td width="25%s" style="text-align:right; padding 5px 5px 5px 5px; color:%s;"><b>%s</b></td></tr>',$color,"%",$color,number_format($grandtotal, 2, '.', ''));
		$summaryhtml .= '</table>';
		$summaryhtml .= '</div>';
		$summaryhtml .= sprintf('<div id="stock_chk_inp" name="stock_chk_inp" class="stock_chk_inp">%s</div>',$loadval);
		$summaryhtml .= '</div>';
		return $summaryhtml;
	}

	public function subform_field_exclusion_list()
	{
		$list = array("order_details" => array("unit_total","tax_amount","extended"));
		return $list;
	}
	
	public function is_forced_inventory_checkout_rerun()
	{
		$order_id = $this->OBJPOST['order_id'];
		$querystr = sprintf('SELECT count(order_id) as counter FROM _sys_rrcs WHERE order_id = "%s"',$order_id);
		$result = $this->param['primarymodel']->execute_select_query($querystr);
		$count = $result[0]->counter;
		if( $count > 0 ) 
		{
			$chk = new Controller_Core_Sales_Inventchkout();
			$table = $chk->param['tb_live'];
			
			/* delete existing checkout record if any */
			$querystr = sprintf('DELETE FROM %s WHERE order_id = "%s"',$table,$order_id);
			if( $result = $this->param['primarymodel']->execute_delete_query($querystr) ) { /* wait for deletions */ }
			
			/* delete existing _sys_rrcs record */
			$querystr = sprintf('DELETE FROM _sys_rrcs WHERE order_id = "%s"',$order_id);
			if( $result = $this->param['primarymodel']->execute_delete_query($querystr) ) { /* wait for deletions */}
		}
	}
	
	public function inventory_checkout()
	{
		$chk = new Controller_Core_Sales_Inventchkout();
		$idname = Auth::instance()->get_user()->idname;
		$xmlrows = "";
		
		/* forced checkout re-run */
		$this->is_forced_inventory_checkout_rerun();
				
		if(!($this->OBJPOST['order_status'] == "NEW") && !($this->OBJPOST['order_status'] == "QUOTATION") && $this->OBJPOST['current_no'] > 0)
		{
			$param	= $this->param['primarymodel']->get_controller_params("product");
			$table	= $param['tb_live'];
			$unique_id = "product_id";
			$fields = array('product_id','type','package_items','product_description');

			$rows = new SimpleXMLElement($this->OBJPOST['order_details']);
			if($rows->row) 
			{	
				$rowcount = 0;
				foreach ($rows->row as $row) 
				{ 
					$pid = sprintf('%s',$row->product_id);
					if($pid != "MISC")
					{
						$qty = sprintf('%s',$row->qty);
						$desc = sprintf('%s',$row->description);
						$result = $this->param['primarymodel']->get_record_by_id_val($table,$unique_id ,$pid,$fields);
						if($result->type == "STOCK")
						{
$xmlrows .=sprintf('<row><product_id>%s</product_id><description>%s</description><order_qty>%s</order_qty><filled_qty>%s</filled_qty><checkout_qty>%s</checkout_qty><status>%s</status></row>',$pid,$desc,$qty,"0",$qty,"NONE")."\n";
						$rowcount++;
						}
						else if($result->type == "PACKAGE")
						{
							$packages = preg_split('/;/',$result->package_items);
							foreach($packages as $idx => $packagestr)
							{
								$arr = preg_split('/=/',$packagestr);
								$pck_pid = $arr[0];
								$pck_qty = $arr[1] * $qty;
								$pck_result = $this->param['primarymodel']->get_record_by_id_val($table,$unique_id ,$pck_pid,$fields);
								$pck_desc = $pck_result->product_description;
								if($pck_result->type == "STOCK")
								{
$xmlrows .=sprintf('<row><product_id>%s</product_id><description>%s</description><order_qty>%s</order_qty><filled_qty>%s</filled_qty><checkout_qty>%s</checkout_qty><status>%s</status></row>',$pck_pid,$pck_desc,$pck_qty,"0",$pck_qty,"NONE")."\n";
									$rowcount++;
								}
							}
						}
					}
				}	 
			}
$xmlheader = "<?xml version='1.0' standalone='yes'?>"."\n"."<formfields>"."\n";
$xmlheader .= "<header><column>Product Id</column><column>Description</column><column>Order Qty</column><column>Filled Qty</column><column>Checkout Qty</column><column>Checkout Status</column></header>"."\n";
$xmlheader .= "<rows>"."\n";
$xmlfooter = "</rows>"."\n"."</formfields>"."\n";

			$data['order_id'] = $this->OBJPOST['order_id']; 
			$data['checkout_details'] = $xmlheader.$xmlrows.$xmlfooter;
			$data['idname'] = $idname;
			
			if($rowcount > 0 )
			{
				//create inventory checkout profile
				$chkout_record = $chk->insert_into_checkout_table($data);
			}
			else if($rowcount == 0 && $this->OBJPOST['inventory_checkout_status'] == "NONE")
			{
				//update checkout status for nonstock order
				$chk->update_order_checkout_status($this->param['tb_live'],$this->OBJPOST['order_id'],"COMPLETED");
			}
				
			if( $this->OBJPOST['inventory_checkout_type'] == "AUTO"  && !($this->OBJPOST['inventory_checkout_status'] == "COMPLETED") && $rowcount > 0)
			{
				$chk->process_checkout($chkout_record);
			}
		}
	}

	public function update_order_status($table,$order_id,$status,$status_change_date)
	{
		$querystr = sprintf('UPDATE %s SET order_status = "%s", status_change_date = "%s" WHERE order_id = "%s"',$table,$status,$status_change_date,$order_id);
		$this->param['primarymodel']->execute_update_query($querystr);
	}

	public function set_zero_charge_order_status()
	{
		if(!($this->OBJPOST['order_status'] == "NEW") && !($this->OBJPOST['order_status'] == "QUOTATION") && !($this->OBJPOST['order_status'] == "ZERO.CHARGE") && $this->OBJPOST['current_no'] > 0)
		{
			$order_id = $this->OBJPOST['order_id'];
			$querystr = sprintf('SELECT order_total FROM vw_orderbalances WHERE order_id = "%s"',$order_id);
			$result = $this->param['primarymodel']->execute_select_query($querystr);
			$order_total = $result[0]->order_total;
			if($order_total == 0 && !($this->OBJPOST['order_status'] == "ORDER.CANCELLED") )
			{
				$order_status = "ZERO.CHARGE";
				$this->update_order_status($this->param['tb_live'],$order_id,$order_status,date('Y-m-d')); 
			}
		}
	}

	public function expire_quotations()
	{
		$seq_no = substr($this->OBJPOST['order_id'], -4);
		$current_no = $this->OBJPOST['current_no'];
		$current_date = date('Y-m-d');
		$table = $this->param['tb_live'];
		
		if($current_no == 1 && $seq_no == "0001")
		{
			$querystr = sprintf('UPDATE %s SET order_status = "QUOTATION.EXPIRED" WHERE quotation_date < (SELECT DATE_ADD("%s", INTERVAL -30 day)) AND order_status = "QUOTATION"',$table,$current_date);
			$this->param['primarymodel']->execute_update_query($querystr);
		}	
	}

	public function update_order_invoice_date($table,$order_id,$invoice_date)
	{
		$querystr = sprintf('update %s set invoice_date = "%s" where order_id = "%s"',$table,$invoice_date,$order_id);
		$this->param['primarymodel']->execute_update_query($querystr);
	}
	
	public function authorize_post_update_existing_record()
	{
		$this->inventory_checkout();
		$this->set_zero_charge_order_status();
	}

	public function authorize_post_insert_new_record()
	{
		$this->inventory_checkout();
		$this->set_zero_charge_order_status();
		$this->expire_quotations();
	}

}//End Controller_Core_Sales_Ordernew
