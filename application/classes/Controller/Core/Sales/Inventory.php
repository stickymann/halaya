<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a inventory item record. 
 *
 * $Id: Inventory.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Inventory extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("inventory");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.inventory.js') );
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
			->rule('inventory_id','not_empty')
			->rule('inventory_id','min_length', array(':value', 2))->rule('inventory_id','max_length', array(':value', 50))
			->rule('inventory_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['inventory_id']));
		$validation
			->rule('product_id','not_empty')
			->rule('product_id','min_length', array(':value', 2))->rule('product_id','max_length', array(':value', 50));
		$validation
			->rule('branch_id','not_empty')
			->rule('branch_id','min_length', array(':value', 2))->rule('branch_id','max_length', array(':value', 50));
		$validation
			->rule('qty_instock','not_empty')
			->rule('qty_instock','numeric');
		$validation
			->rule('reorder_level','not_empty')
			->rule('reorder_level','numeric');
		$validation
			->rule('last_update_type','not_empty')
			->rule('last_update_type','min_length', array(':value', 2))->rule('last_update_type','max_length', array(':value', 50));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);	
	}
	
}//End Controller_Core_Sales_Inventory 