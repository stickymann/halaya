<?php defined('SYSPATH') or die('No direct script access.');
/**
 * System configurations. 
 *
 * $Id: Branch.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sysadmin_Sysconfig extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("sysconfig");
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
			->rule('sysconfig_id','not_empty')
			->rule('sysconfig_id','min_length', array(':value', 2))->rule('sysconfig_id','max_length', array(':value', 50))
			->rule('sysconfig_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['sysconfig_id']));
						
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

}//End Controller_Sysadmin_Sysconfig