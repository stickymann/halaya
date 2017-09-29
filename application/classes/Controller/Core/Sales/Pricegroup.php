<?php defined('SYSPATH') or die('No direct script access.');
/**
 * <insert controller description>. 
 *
 * $Id: Pricegroup.php 2014-10-04 11:16:54 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
class Controller_Core_Sales_Pricegroup extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct('pricegroup');
		// $this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.pricegroup.js') );
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
			->rule('pricegroup_id','not_empty')
			->rule('pricegroup_id','min_length', array(':value', 16))->rule('pricegroup_id','max_length', array(':value', 16))
			->rule('pricegroup_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['pricegroup_id']));
			
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

} //End Controller_Core_Sales_Pricegroup