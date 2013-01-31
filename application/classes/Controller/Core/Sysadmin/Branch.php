<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates organisation/company branch. 
 *
 * $Id: Branch.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sysadmin_Branch extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("branch");
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
			->rule('branch_id','not_empty')
			->rule('branch_id','min_length', array(':value', 2))->rule('branch_id','max_length', array(':value', 50))
			->rule('branch_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['branch_id']));
		$validation
			->rule('description','not_empty')
			->rule('description','min_length', array(':value', 3))->rule('description','max_length', array(':value', 255));
		$validation
			->rule('location','not_empty')
			->rule('location','min_length', array(':value', 1))->rule('location','max_length', array(':value', 255));
		$validation
			->rule('region_id','not_empty')
			->rule('region_id','numeric');
		$validation
			->rule('active','not_empty')
			->rule('active','in_array', array(':value', array('Y', 'N')));
				
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

}// End Controller_Sysadmin_Branch
