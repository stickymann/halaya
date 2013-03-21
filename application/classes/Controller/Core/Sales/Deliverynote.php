<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a delivery note record. 
 *
 * $Id: Deliverynote.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Deliverynote extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("deliverynote");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.deliverynote.js') );
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
			->rule('deliverynote_id','not_empty')
			->rule('deliverynote_id','min_length', array(':value', 16))->rule('deliverynote_id','max_length', array(':value', 16))
			->rule('deliverynote_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['deliverynote_id']));
		$validation
			->rule('order_id','not_empty')
			->rule('order_id','min_length', array(':value', 16))->rule('order_id','max_length', array(':value', 16));
		$validation
			->rule('deliverynote_date','date');
		$validation
			->rule('delivery_date','date');
		$validation
			->rule('status','not_empty')
			->rule('status','min_length', array(':value', 3))->rule('status','max_length', array(':value', 50))
			->rule('status', array($this,'delivery_status'), array(':validation', ':field'));
		$validation
			->rule('delivered_by','not_empty')
			->rule('delivered_by','min_length', array(':value', 2))->rule('delivered_by','max_length', array(':value', 50));
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
	public function delivery_status(Validation $validation,$field)
	{
		if($this->OBJPOST['status'] == "NEW") { $validation->error($field, 'msg_new');}
	}
	
	public function insert_into_delivery_note_table($data)
	{
		//set up new checkout record and insert into checkout table 
		$arr = $this->param['primarymodel']->create_blank_record($this->param['tb_live'],$this->param['tb_inau']);
		$arr = (array) $arr;
		
		$baseurl = URL::base(TRUE,'http');
		$url = sprintf('%score_ajaxtodb?option=altid&controller=deliverynote&prefix=DNT&ctrlid=%s',$baseurl,$arr['id']);
		$deliverynote_id = Controller_Core_Sitehtml::get_html_from_url($url);
		
		$querystr = sprintf('DELETE FROM %s WHERE id = "%s"',$this->param['tb_inau'],$arr['id']);
		if($result = $this->param['primarymodel']->execute_delete_query($querystr))
		{
			$arr['deliverynote_id']	 = $deliverynote_id;
			$arr['order_id']		 = $data['order_id'];
			$arr['deliverynote_date']= date('Y-m-d H:i:s'); 
			$arr['details']			 = $data['details'];
			$arr['delivery_date']	 = "0000-00-00";
			$arr['returned_signed_date'] = "0000-00-00";
			$arr['status']			 = "NEW";
			$arr['inputter']		 = $data['idname'];
			$arr['input_date']		 = date('Y-m-d H:i:s'); 
			$arr['authorizer']		 = 'SYSAUTH';
			$arr['auth_date']		 = date('Y-m-d H:i:s'); 
			$arr['record_status']	 = "LIVE";
			$arr['current_no']		 = "1";
			$this->param['primarymodel']->insert_record($this->param['tb_live'],$arr);
		}
	}

} //End Controller_Core_Sales_Deliverynote