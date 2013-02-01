<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates weekday or period definition of an occurence or event. 
 *
 * $Id: Weekday.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Sysadmin_Weekday extends Controller_Core_Site
{
public function __construct()
    {
		parent::__construct("weekday");
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
			->rule('weekday_id','not_empty')
			->rule('weekday_id','min_length', array(':value', 2))->rule('weekday_id','max_length', array(':value', 21))
			->rule('weekday_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['weekday_id']));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

}//End Controller_Core_Sysadmin_Weekday
