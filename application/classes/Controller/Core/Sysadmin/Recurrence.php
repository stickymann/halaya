<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates recurrence record. 
 *
 * $Id: Recurrence.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sysadmin_Recurrence extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("recurrence");
	}
		
	public function action_index()
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
			->rule('recurrence_id','not_empty')
			->rule('recurrence_id','min_length', array(':value', 2))->rule('recurrence_id','max_length', array(':value', 50))
			->rule('recurrence_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['recurrence_id']));
				
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

}//End Controller_Core_Sysadmin_Recurrence
