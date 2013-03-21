<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a sale/service product record. 
 *
 * $Id: Product.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Product extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("product");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.product.js') );
	}

	function input_validation()
	{
		$this->OBJPOST['product_id']	= strtoupper($this->OBJPOST['product_id']);
		$this->OBJPOST['category']		= strtoupper($this->OBJPOST['category']);
		$this->OBJPOST['sub_category']	= strtoupper($this->OBJPOST['sub_category']);
		$this->OBJPOST['product_id']	= str_replace(" ","",$this->OBJPOST['product_id']);
		
		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('product_id','not_empty')
			->rule('product_id','min_length', array(':value', 2))->rule('product_id','max_length', array(':value', 50))
			->rule('product_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['product_id']));
		$validation
			->rule('type','not_empty')
			->rule('type','min_length', array(':value', 2))->rule('type','max_length', array(':value', 7));
		$validation
			->rule('product_description','not_empty')
			->rule('product_description','min_length', array(':value', 2))->rule('product_description','max_length', array(':value', 255));
		$validation
			->rule('category','not_empty')
			->rule('category','min_length', array(':value', 2))->rule('category','max_length', array(':value', 20));
		$validation
			->rule('sub_category','not_empty')
			->rule('sub_category','min_length', array(':value', 2))->rule('sub_category','max_length', array(':value', 7));
		$validation
			->rule('unit_price','not_empty')
			->rule('unit_price','numeric');
		$validation
			->rule('tax_percentage','not_empty')
			->rule('tax_percentage','numeric');
		$validation
			->rule('taxable','not_empty')
			->rule('taxable','in_array', array(':value', array('Y', 'N')));
		$validation
			->rule('status','not_empty')
			->rule('status','min_length', array(':value', 2))->rule('status','max_length', array(':value', 20));
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

}//End Controller_Core_Sales_Product