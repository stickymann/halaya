<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a customer record. 
 *
 * $Id: Customer.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Customer_Customer extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("customer");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.customer.js') );
	}
	
	function input_validation()
	{
		$this->OBJPOST['first_name']	= $this->strtotitlecase($this->OBJPOST['first_name']);
		$this->OBJPOST['last_name']		= $this->strtotitlecase($this->OBJPOST['last_name']);
		$this->OBJPOST['address1']		= $this->strtotitlecase($this->OBJPOST['address1']);
		$this->OBJPOST['address2']		=	$this->strtotitlecase($this->OBJPOST['address2']);
		$this->OBJPOST['city']			= $this->strtotitlecase($this->OBJPOST['city']);
		$this->OBJPOST['email_address']	= strtolower($this->OBJPOST['email_address']);
		$this->OBJPOST['business_type']	= strtoupper($this->OBJPOST['business_type']);
		$this->OBJPOST['business_type'] = str_replace(" ",".",$this->OBJPOST['business_type']);
		
		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('customer_id','not_empty')
			->rule('customer_id','min_length', array(':value', 8))->rule('customer_id','max_length', array(':value', 8))
			->rule('customer_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['customer_id']));
		$validation
			->rule('customer_type','not_empty')
			->rule('customer_type','min_length', array(':value', 2))->rule('customer_type','max_length', array(':value', 50));
		$validation
			->rule('business_type','not_empty')
			->rule('business_type','min_length', array(':value', 2))->rule('business_type','max_length', array(':value', 50));
		$validation
			->rule('first_name','not_empty')
			->rule('first_name','min_length', array(':value', 2))->rule('first_name','max_length', array(':value', 50));
		$validation
			->rule('last_name','not_empty')
			->rule('last_name','min_length', array(':value', 2))->rule('last_name','max_length', array(':value', 50));
		$validation
			->rule('address1','not_empty')
			->rule('address1','min_length', array(':value', 1))->rule('address1','max_length', array(':value', 50));
		$validation
			->rule('city','not_empty')
			->rule('city','min_length', array(':value', 1))->rule('city','max_length', array(':value', 50));
		$validation
			->rule('region_id','not_empty')
			->rule('region_id','numeric');
		$validation
			->rule('country_id','not_empty')
			->rule('country_id','min_length', array(':value', 2))->rule('country_id','max_length', array(':value', 2));
		$validation
			->rule('date_of_birth','date');
		$validation
			->rule('gender','not_empty')
			->rule('gender','in_array', array(':value', array('M', 'F', 'N')));
		$validation
			->rule('phone_home','min_length', array(':value', 7))->rule('phone_home','max_length', array(':value', 7));
		$validation
			->rule('phone_work','min_length', array(':value', 7))->rule('phone_work','max_length', array(':value', 7));
		$validation
			->rule('phone_bmobile','min_length', array(':value', 7))->rule('phone_bmobile','max_length', array(':value', 7));
		$validation
			->rule('phone_digicel','min_length', array(':value', 7))->rule('phone_digicel','max_length', array(':value', 7));
		$validation
			->rule('phone_mobile_other','min_length', array(':value', 7))->rule('phone_mobile_other','max_length', array(':value', 7));
		$validation
			->rule('email_address','email');
		$validation
			->rule('branch_id','not_empty')
			->rule('branch_id','min_length', array(':value', 2))->rule('branch_id','max_length', array(':value', 50));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

}//End Controller_Core_Customer_Customer
