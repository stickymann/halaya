<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a till transcation record. 
 *
 * $Id: Tilltransaction.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Tilltransaction extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("tilltransaction");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.tilltransaction.js') );
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
			->rule('transaction_id','not_empty')
			->rule('transaction_id','min_length', array(':value', 2))->rule('transaction_id','max_length', array(':value', 59))
			->rule('transaction_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['transaction_id']));
		$validation
			->rule('till_id','not_empty')
			->rule('till_id','min_length', array(':value', 2))->rule('till_id','max_length', array(':value', 59))
			->rule('till_id', array($this,'is_till_ok'), array(':validation', ':field'));
		$validation
			->rule('amount','not_empty')
			->rule('amount','numeric');
		$validation
			->rule('transaction_type','not_empty')
			->rule('transaction_type','in_array', array(':value', array('CASH', 'CHEQUE', 'CREDIT.CARD', 'DEBIT.CARD')));
		$validation
			->rule('transaction_date','not_empty')
			->rule('transaction_date','date');
		$validation
			->rule('movement','not_empty')
			->rule('movement','in_array', array(':value', array('IN', 'OUT')));
		$validation
			->rule('reason','not_empty')
			->rule('reason','min_length', array(':value', 2))->rule('reason','max_length', array(':value', 255));

		
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

	public function is_user_till($till_id,$idname)
	{
		$till = new Controller_Core_Sales_Tilluser();
		//if till item exist
		$datestr = date('Y-m-d');
		$timestr = date('H:i:s');
		$querystr = sprintf('SELECT COUNT(id) AS count FROM %s WHERE till_id = "%s" AND till_user = "%s" AND expiry_date >= "%s" && expiry_time > "%s" AND status="OPEN"',$till->param['tb_live'],$till_id,$idname,$datestr,$timestr);
		$result = $this->param['primarymodel']->execute_select_query($querystr);
		$recs = $result[0];
		if( $recs->count > 0 )
		{
			return true;
		}
		return false;
	}

	public function insert_into_till_transaction_table($data)
	{
		//set up new tilltransaction record and insert into tilltransaction table 
		$arr = $this->param['primarymodel']->create_blank_record($this->param['tb_live'],$this->param['tb_inau']);
		$arr = (array) $arr;
		
		$baseurl = URL::base(TRUE,'http');
		$url = sprintf('%score_ajaxtodb?option=altid&controller=tilltransaction&prefix=TLL&ctrlid=%s',$baseurl,$arr['id']);
		$transaction_id = Controller_Core_Sitehtml::get_html_from_url($url);
		
		$querystr = sprintf('DELETE FROM %s WHERE id = "%s"',$this->param['tb_inau'],$arr['id']);
		if($result = $this->param['primarymodel']->execute_delete_query($querystr))
		{
			$arr['transaction_id']		= $transaction_id;
			$arr['till_id']				= $data['till_id'];
			$arr['amount']				= $data['initial_balance'];
			$arr['transaction_type']	= "CASH";
			$arr['transaction_date']	= date('Y-m-d');
			$arr['movement']			= "IN";
			$arr['reason']				= "Initial balance for till ".$data['till_id'];
			$arr['inputter']			= $data['idname'];
			$arr['input_date']			= date('Y-m-d H:i:s'); 
			$arr['authorizer']			= 'SYSAUTH';
			$arr['auth_date']			= date('Y-m-d H:i:s'); 
			$arr['record_status']		= "LIVE";
			$arr['current_no']			= "1";
			$this->param['primarymodel']->insert_record($this->param['tb_live'],$arr);
		}
	}

} //End Controller_Core_Sales_Tilltransaction