<?php defined('SYSPATH') or die('No direct script access.');
/**
 * CSV controller, sets up CSV record. 
 *
 * $Id: Csvexport.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Csv extends Controller_Core_Site
{
	public function __construct() 
	{
		parent::__construct("csv");
	}

	public function action_index($opt="")
	{
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}

	function input_validation()
	{
		$_POST['csv_id']	= strtoupper($_POST['csv_id']);
		$post = $_POST;	

		//validation rules
		$validation = new Validation($post);
		$validation->pre_filter('trim', TRUE);
		
		$validation->add_rules('id','required','numeric');
		$validation->add_rules('csv_id','required', 'length[3,30]', 'standard_text');
		
		$validation->add_callbacks('csv_id', array($this, '_duplicate_altid'));
		
		//$validation->post_filter('strtoupper', '?????_id');
		$this->param['isinputvalid'] = $validation->validate();
		$this->param['validatedpost'] = $validation->as_array();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

	public function _duplicate_altid(Validation $validation,$field)
    {
		$id	 = $_POST['id'];
		$unique_id = $_POST['csv_id'];
		if (array_key_exists('msg_duplicate', $validation->errors()))
				return;
		
        if ($this->param['primarymodel']->is_duplicate_unique_id($this->param['tb_inau'],$field,$id,$unique_id) || $this->param['primarymodel']->is_duplicate_unique_id($this->param['tb_live'],$field,$id,$unique_id))
        {
            $validation->add_error($field, 'msg_duplicate');
        }
	}

	public function insert_into_CSV_table($csv_id,$csv_text,$controller,$idname,$type)
	{
		$csv_tmp_path = "/tmp/";
		if(!file_exists($csv_tmp_path)){mkdir($csv_tmp_path,777,true);} 
		
		$arr_inau['csv_id'] = $csv_id;
		$querystr = sprintf('select csv from %s where inputter = "%s" and authorizer = "%s" and record_status="HLD" and current_no="0"',$this->param['tb_inau'],$idname,$idname);
		$delarr = $this->param['primarymodel']->execute_select_query($querystr);
	
		$querystr = sprintf('delete from %s where inputter = "%s" and authorizer = "%s" and record_status="HLD" and current_no="0"',$this->param['tb_inau'],$idname,$idname);
		if($result = $this->param['primarymodel']->execute_delete_query($querystr))
		{
			//$arr['id']			= $result[0]->id;
			$arr['csv_id']			= $csv_id;
			$arr['controller']		= $controller;
			$arr['type']			= $type;
			if($type == "default") 
			{
				$arr['csv']	= $csv_tmp_path.$csv_id.".csv";
			}
			else
			{
				$arr['csv']	= $csv_tmp_path.$csv_id.".".$type;
			}
			$arr['inputter']		= $idname;
			$arr['input_date']		= date('Y-m-d H:i:s'); 
			$arr['authorizer']		= $idname;
			$arr['auth_date']		= date('Y-m-d H:i:s'); 
			$arr['record_status']	= "HLD";
			$arr['current_no']		= "0";
			$this->param['primarymodel']->insert_record($this->param['tb_inau'],$arr);

			if ($handle = fopen($arr['csv'], 'w')) 
			{
				fwrite($handle, $csv_text);
				fclose($handle);
				$res = 1;
			}
		}

		foreach($delarr as $row)
		{
			//delete file
			if(file_exists($row->csv)){ unlink($row->csv); }
		}
	}
}
?>