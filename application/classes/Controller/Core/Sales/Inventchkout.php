<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates an inventory checkout record. 
 *
 * $Id: Inventchkout.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Inventchkout extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("inventchkout");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.inventchkout.js') );
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
			->rule('order_id','not_empty')
			->rule('order_id','min_length', array(':value', 16))->rule('order_id','max_length', array(':value', 16))
			->rule('order_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['order_id']));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function insert_into_checkout_table($data)
	{
		//if order_id does not exist
		$querystr = sprintf('SELECT count(id) AS count FROM %s WHERE order_id = "%s"',$this->param['tb_live'],$data['order_id']);
		$result = $this->param['primarymodel']->execute_select_query($querystr);
		$recs = $result[0];
		if( $recs->count == 0 )
		{
			//set up new checkout record and insert into checkout table 
			$arr = $this->param['primarymodel']->create_blank_record($this->param['tb_live'],$this->param['tb_inau']);
			$arr = (array) $arr;
			$querystr = sprintf('DELETE FROM %s WHERE id = "%s"',$this->param['tb_inau'],$arr['id']);
			if($result = $this->param['primarymodel']->execute_delete_query($querystr))
			{
				$arr['order_id']		 = $data['order_id'];
				$arr['checkout_details'] = $data['checkout_details'];
				$arr['run']				 = "Y";
				$arr['comments']		 = "";
				$arr['inputter']		 = $data['idname'];
				$arr['input_date']		 = date('Y-m-d H:i:s'); 
				$arr['authorizer']		 = 'SYSAUTH';
				$arr['auth_date']		 = date('Y-m-d H:i:s'); 
				$arr['record_status']	 = "LIVE";
				$arr['current_no']		 = "1";
				$this->param['primarymodel']->insert_record($this->param['tb_live'],$arr);
				return $arr;
			}
		}
	}

	public function process_checkout($data)
	{
		$chk_c = 0; $chk_p = 0; $chk_n = 0;  $chk_e = 0; $pre_filled_count = 0; $post_filled_count = 0;
		if( $data['run'] == "Y" )
		{
			$order = new Controller_Core_Sales_Order();
			$querystr = sprintf('select branch_id,inventory_update_type from %s where order_id = "%s"',$order->param['tb_live'],$data['order_id']);
			$result = $this->param['primarymodel']->execute_select_query($querystr);
			if($result)
			{
				$xmlrows = ""; $dnoterows = "";
				$formfields = new SimpleXMLElement($data['checkout_details']);
				if($formfields->rows) 
				{
					foreach ($formfields->rows->row as $row) 
					{ 
						$val['branch_id'] = $result[0]->branch_id;
						$val['inventory_update_type'] = $result[0]->inventory_update_type;
						$val['product_id'] = sprintf('%s',$row->product_id);
						$val['description'] = sprintf('%s',$row->description);
						$val['order_qty'] = sprintf('%s',$row->order_qty);
						$val['filled_qty'] = sprintf('%s',$row->filled_qty);
						$val['checkout_qty'] = sprintf('%s',$row->checkout_qty);
						$val['status'] = sprintf('%s',$row->status);						
						$pre_filled_count =  $pre_filled_count + $val['filled_qty'];
						
						if($val['order_qty'] > $val['filled_qty'])
						{
							$val = $this->process_row($val);
							if( $val['status'] == "PARTIAL" ) { $chk_p++;}
							else if( $val['status'] == "NONE" ) { $chk_n++;}
							else if( $val['status'] == "COMPLETED" ) { $chk_c++; }
							else if( $val['status'] == "ERROR" ) { $chk_e++;}
							$xmlrows .= $val['xmlrow'];
							if( $val['filled_qty'] > 0 )
							{
								$post_filled_count =  $post_filled_count + $val['filled_qty'];
								$dnoterows .= $val['dnoterow'];
							}
						}
						else
						{
							$xmlrows .= sprintf('<row><product_id>%s</product_id><description>%s</description><order_qty>%s</order_qty><filled_qty>%s</filled_qty><checkout_qty>%s</checkout_qty><status>%s</status></row>',$val['product_id'],$val['description'],$val['order_qty'],$val['filled_qty'],$val['checkout_qty'],$val['status'])."\n";
						}
						if($val['order_qty'] == $val['filled_qty']) { $chk_c++; }
					}
					
					$xmlrows = "<rows>\n".$xmlrows."</rows>\n";
					$replacement_xml = "<?xml version='1.0' standalone='yes'?>\n<formfields>\n";
					//$replacement_xml .= htmlspecialchars_decode( $formfields->header->asXML() );
					$replacement_xml .= $xmlrows."</formfields>\n";
					$arr['table'] = $this->param['tb_live'];
					$arr['order_id'] = $data['order_id'];
					$arr['checkout_details'] = $replacement_xml;
					$arr['run'] = "N";
					$this->update_checkout_record($arr);
					
					//create delivery note
					$dnoterows = "<rows>\n".$dnoterows."</rows>\n";
					$dnote_xml = "<?xml version='1.0' standalone='yes'?>\n<formfields>\n";
					//$dnote_xml .= "<header><column>Product Id</column><column>Description</column><column>Qty</column></header>";
					$dnote_xml .= $dnoterows."</formfields>\n";
					$dnotedata['order_id']	= $data['order_id'];
					$dnotedata['details']	= $dnote_xml;
					$dnotedata['idname']	= Auth::instance()->get_user()->idname;
					if($post_filled_count > $pre_filled_count)
					{
						$this->create_delivery_note($dnotedata);
					}
				}
			}
			if( $chk_e > 0){ $this->update_order_checkout_status($order->param['tb_live'],$data['order_id'],"ERROR"); }
			else if( $chk_p > 0 || ( $chk_c > 0 && $chk_n > 0)) { $this->update_order_checkout_status($order->param['tb_live'],$data['order_id'],"PARTIAL"); }
			else if( $chk_n > 0 && $chk_c == 0 && $chk_p == 0) { $this->update_order_checkout_status($order->param['tb_live'],$data['order_id'],"NONE"); }
			else if( $chk_c > 0 && $chk_n == 0 && $chk_p == 0) { $this->update_order_checkout_status($order->param['tb_live'],$data['order_id'],"COMPLETED"); }
		}
	}
	
	public function process_row($val)
	{
		$rows = "";
		$inventory = new Controller_Core_Sales_Inventory();
		//if inventory item exist
		$querystr = sprintf('SELECT COUNT(id) AS count FROM %s WHERE product_id = "%s" AND branch_id = "%s"',$inventory->param['tb_live'],$val['product_id'],$val['branch_id']);
		$result = $this->param['primarymodel']->execute_select_query($querystr);
		$recs = $result[0];
		if( $recs->count > 0 )
		{
			$querystr = sprintf('SELECT id,qty_instock,current_no FROM %s WHERE product_id = "%s" AND branch_id = "%s"',$inventory->param['tb_live'],$val['product_id'],$val['branch_id']);
			$result = $this->param['primarymodel']->execute_select_query($querystr);
			$qty_instock = $result[0]->qty_instock;
			
			$pre_filled = $val['filled_qty'];
			$post_filled = $val['filled_qty'];
			if($qty_instock != 0)
			{
				if($qty_instock >= $val['checkout_qty'])
				{    
					//enough stock to fill order
					$val['adjust_qty'] = $qty_instock - $val['checkout_qty'];
					$val['filled_qty'] = $val['filled_qty'] + $val['checkout_qty'];
					$val['checkout_qty'] = $val['order_qty'] - $val['filled_qty'];
				}
				else
				{
					//not enough stock to fill order
					$val['adjust_qty'] =  0;
					$val['filled_qty'] = $val['filled_qty'] + $qty_instock;
					$val['checkout_qty'] = $val['checkout_qty'] - $qty_instock;
				}
				
				if($val['order_qty'] == $val['filled_qty']) { $val['status'] = "COMPLETED"; }
				else if($val['order_qty'] > $val['filled_qty']) { $val['status'] = "PARTIAL"; }
				else  { $val['status'] = "ERROR"; }
				$post_filled =  $val['filled_qty'];

				//update inventory
				$iid = $result[0]->id;
				$current_no = $result[0]->current_no;
				if($this->param['primarymodel']->insert_from_table_to_table($inventory->param['tb_hist'],$inventory->param['tb_live'],$iid))
				{
					$arr['id'] = $iid;
					$arr['qty_instock']		 = $val['adjust_qty'];
					$arr['qty_diff']		 = $val['adjust_qty'] - $qty_instock;
					$arr['last_update_type'] = $val['inventory_update_type'];
					$arr['inputter']		 = Auth::instance()->get_user()->idname;
					$arr['input_date']		 = date('Y-m-d H:i:s'); 
					$arr['authorizer']		 = 'SYSAUTH';
					$arr['auth_date']		 = date('Y-m-d H:i:s'); 
					$arr['record_status']	 = "LIVE";
					$arr['current_no']		 = $current_no + 1;
					$this->param['primarymodel']->update_record($inventory->param['tb_live'],$arr);
				}
			}
		}
		$xmlrow = sprintf('<row><product_id>%s</product_id><description>%s</description><order_qty>%s</order_qty><filled_qty>%s</filled_qty><checkout_qty>%s</checkout_qty><status>%s</status></row>',$val['product_id'],$val['description'],$val['order_qty'],$val['filled_qty'],$val['checkout_qty'],$val['status'])."\n";
		$val['xmlrow'] = $xmlrow;
		
		$val['dnoterow'] = "";
		$delivery_qty = $post_filled - $pre_filled;
		if($delivery_qty > 0)
		{
			$dnoterow = sprintf('<row><product_id>%s</product_id><description>%s</description><filled_qty>%s</filled_qty></row>',$val['product_id'],$val['description'],$delivery_qty)."\n";
			$val['dnoterow'] = $dnoterow;
		}
		return $val;
	}
	
	public function update_order_checkout_status($table,$order_id,$status)
	{
		$querystr = sprintf('UPDATE %s SET inventory_checkout_status = "%s" WHERE order_id = "%s"',$table,$status,$order_id);
		$this->param['primarymodel']->execute_update_query($querystr);
	}

	public function update_checkout_record($arr)
	{
		$querystr = sprintf('UPDATE %s SET checkout_details = "%s", run = "%s" WHERE order_id = "%s"',$arr['table'],$arr['checkout_details'],$arr['run'],$arr['order_id']);
		$this->param['primarymodel']->execute_update_query($querystr);
	}
	
	public function create_delivery_note($data)
	{
		$dnote = new Controller_Core_Sales_Deliverynote();
		$dnote->insert_into_delivery_note_table($data);
	}

	public function authorize_post_update_existing_record()
	{
		$this->process_checkout($_POST);
	}

} //End Controller_Core_Sales_Inventchkout