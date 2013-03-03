<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a user till record. 
 *
 * $Id: Tilluser.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Tilluser extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("tilluser");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.tilluser.js') );
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
			->rule('till_id','not_empty')
			->rule('till_id','min_length', array(':value', 2))->rule('till_id','max_length', array(':value', 59))
			->rule('till_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['till_id']));
		$validation
			->rule('till_user','not_empty')
			->rule('till_user','min_length', array(':value', 2))->rule('till_user','max_length', array(':value', 50));
		$validation
			->rule('till_date','not_empty')
			->rule('till_date','date');
		$validation
			->rule('initial_balance','not_empty')
			->rule('initial_balance','numeric');
		$validation
			->rule('status','not_empty')
			->rule('status','in_array', array(':value', array('CLOSED', 'OPEN', 'SUSPENDED')));
		$validation
			->rule('expiry_date','not_empty')
			->rule('expiry_date','date');
		$validation
			->rule('expiry_time','not_empty');
			//->rule('expiry_time','matches',array(array(':','1','2','3','4','5','6','7','8','9','0'), ':field', ':value' ));
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

	public function insert_initial_till_transaction()
	{
		$data['till_id']			= $_POST['till_id'];
		$data['initial_balance']	= $_POST['initial_balance'];
		$data['idname']				= Auth::instance()->get_user()->idname; 
		$tilltransaction = new Controller_Core_Sales_Tilltransaction();
		$tilltransaction->insert_into_till_transaction_table($data);
	}
	
	public function close_expired_tills()
	{
		$current_no = $_POST['current_no'];
		$current_date = date('Y-m-d');
		$table = $this->param['tb_live'];
		
		if($current_no == 1)
		{
			$querystr = sprintf('UPDATE %s SET status = "CLOSED" WHERE expiry_date < "%s" AND status != "CLOSED"',$table,$current_date);
			$this->param['primarymodel']->execute_update_query($querystr);
		}
	}

	public function authorize_post_insert_new_record()
	{
		$this->insert_initial_till_transaction();
		$this->close_expired_tills();
	}

} //End  Controller_Core_Sales_Tilluser