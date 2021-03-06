<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Create menu entry. 
 *
 * $Id: Menudef.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Developer_Menudef extends Controller_Core_Site
{
	public function __construct()
	{
		parent::__construct("menudef");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}

	public function action_index($opt="")
	{
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.menudef.js') );
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
			->rule('menu_id','not_empty')
			->rule('menu_id','numeric');
		$validation
			->rule('parent_id','not_empty')
			->rule('parent_id','numeric');
		$validation
			->rule('sortpos','not_empty')
			->rule('sortpos','numeric');

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
		
	function authorize_post_insert_new_record()
	{
		$menu = new Controller_Core_Menusuper();
		$menu->updatesupers();
	}

}//End Controller_Core_Developer_Menudef
