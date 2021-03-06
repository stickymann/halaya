<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates country record. 
 *
 * $Id: Country.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sysadmin_Country extends Controller_Core_Site
{
public function __construct()
    {
		parent::__construct("country");
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
			->rule('country_id','not_empty')
			->rule('country_id','min_length', array(':value', 2))->rule('country_id','max_length', array(':value', 2))
			->rule('country_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['country_id']));
	
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

}//End Controller_Core_Sysadmin_Country
