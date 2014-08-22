<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a inventory update type record. 
 *
 * $Id: Inventupdtype.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Inventupdtype extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("inventupdtype");
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
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
			->rule('update_type_id','not_empty')
			->rule('update_type_id','min_length', array(':value', 3))->rule('update_type_id','max_length', array(':value', 50))
			->rule('update_type_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['update_type_id']));
		$validation
			->rule('description','not_empty')
			->rule('description','min_length', array(':value', 1))->rule('description','max_length', array(':value', 255));
		$validation
			->rule('stock_movement','not_empty')
			->rule('stock_movement','min_length', array(':value', 3))->rule('stock_movement','max_length', array(':value', 20));
		
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
	
}//End Controller_Core_Sales_Inventupdtype