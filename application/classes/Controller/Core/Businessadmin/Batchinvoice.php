<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates a batchinvoice record. 
 *
 * $Id: Batchinvoice.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Businessadmin_Batchinvoice extends Controller_Core_Site
{
	public function __construct()
    {
		parent::__construct("batchinvoice");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		$js = HTML::script( $this->randomize('media/js/core.batchinvoice.js') );
		$controller = $this->param['controller'];
		$TEXT=<<<_text_
		<script type="text/javascript">
		var js_controller = "$controller";
		</script>
_text_;
		return $TEXT.$js;
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
			->rule('batch_id','not_empty')
			->rule('batch_id','min_length', array(':value', 3))->rule('batch_id','max_length', array(':value', 50))
			->rule('batch_id', array($this,'duplicate_altid'), array(':validation', ':field', $_POST['id'], $_POST['batch_id']));
		$validation
			->rule('batch_description','not_empty')
			->rule('batch_description','min_length', array(':value', 3))->rule('batch_description','max_length', array(':value', 255));
		$validation
			->rule('batch_date','not_empty')
			->rule('batch_date','date');
		$validation
			->rule('batch_details','not_empty')
			->rule('batch_details', array($this,'batch_details_exist'), array(':validation', ':field'));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

	public function batch_details_exist(Validation $validation,$field)
	{
		$count = 0;
		$rows = new SimpleXMLElement($_POST['batch_details']);
		if($rows->row) 
		{ 
			foreach ($rows->row as $row) 
			{ 
				$count++; 
			} 
		}
		if( !($count > 0) ) { $validation->error($field, 'zero_batchdetails');}
	}

}//End Controller_Core_Businessadmin_Batchinvoice
