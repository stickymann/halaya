<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Keeps log of all inventory levels updated by sale orders. 
 *
 * $Id: Inventorysalelog.php 2013-02-25 14:01:21 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sales_Inventorysalelog extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('inventorysalelog');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.inventorysalelog.js') );
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
			->rule('inventorysalelog_id','not_empty')
			->rule('inventorysalelog_id','min_length', array(':value', 255))->rule('inventorysalelog_id','max_length', array(':value', 255));
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Core_Sales_Inventorysalelog