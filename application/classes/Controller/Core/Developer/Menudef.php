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
	}

	public function action_index($opt="")
	{
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
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
			->rule('menu_id','not_empty')
			->rule('menu_id','min_length', array(':value', 1))->rule('menu_id','max_length', array(':value', 50));

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