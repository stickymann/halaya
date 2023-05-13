<?php defined('SYSPATH') or die('No direct script access.');
/**
 * PDF controller, sets up PDF record. 
 *
 * $Id: Pdf.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Sysadmin_Pdf extends Controller_Core_Site
{
	public function __construct() 
	{
		parent::__construct("pdf");
	}

	public function action_index($opt="")
	{
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}

	function input_validation()
	{
		$this->OBJPOST['pdf_id']	= strtoupper($this->OBJPOST['pdf_id']);
		
		$post = $this->OBJPOST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('pdf_id','not_empty')
			->rule('pdf_id','min_length', array(':value', 3))->rule('pdf_id','max_length', array(':value', 30))
			->rule('pdf_id', array($this,'duplicate_altid'), array(':validation', ':field', $this->OBJPOST['id'], $this->OBJPOST['pdf_id']));

		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}

	public function insert_into_pdf_table($pdfdata)
	{
		$arr_inau['pdf_id'] = $pdfdata['pdf_id'];
		$querystr = sprintf('delete from %s where inputter = "%s" and authorizer = "%s" and record_status="HLD" and current_no="0"',$this->param['tb_inau'],$pdfdata['idname'],$pdfdata['idname']);
		if($result = $this->param['primarymodel']->execute_delete_query($querystr))
		{
			$arr['pdf_id']			= $pdfdata['pdf_id'];
			$arr['pdf_template']	= $pdfdata['pdf_template'];
			$arr['controller']		= $pdfdata['controller'];
			$arr['type']			= $pdfdata['type'];
			$arr['data']			= $pdfdata['data'];
			$arr['data_type']		= $pdfdata['datatype'];
			$arr['inputter']		= $pdfdata['idname'];
			$arr['input_date']		= date('Y-m-d H:i:s'); 
			$arr['authorizer']		= $pdfdata['idname'];
			$arr['auth_date']		= date('Y-m-d H:i:s'); 
			$arr['record_status']	= "HLD";
			$arr['current_no']		= "0";
			$this->param['primarymodel']->insert_record($this->param['tb_inau'],$arr);
		}
	}

	public function delete_from_pdf_table($pdfdata)
	{
		$arr_inau['pdf_id'] = $pdfdata['pdf_id'];
		//clean up files in tmp directory
		
		$querystr = sprintf('select pdf_id from %s where inputter = "%s" and authorizer = "%s" and record_status="HLD" and current_no="0"',$this->param['tb_inau'],$pdfdata['idname'],$pdfdata['idname']);
		if($delarr = $this->param['primarymodel']->execute_select_query($querystr))
		{	
			foreach($delarr as $index => $row)
			{
				//delete file
				$filename ="/tmp/".$row->pdf_id.".pdf";
				if(file_exists($filename)){ unlink($filename); }
			}		
			//$querystr = sprintf('delete from %s where inputter = "%s" and authorizer = "%s" and record_status="HLD" and current_no="0"',$this->param['tb_inau'],$pdfdata['idname'],$pdfdata['idname']);
/*
 * The following code is a work arounf for the bug 
 * that was found when using Chrome 
 * (speciallity version 112.0.5615.165).
 * The bug does not not occur in any versions of Firefox.
 * The bug - apparently Chrome runs doule insert two(2)
 * inoice and quptation pdf records in pdfs_is for a total
 * of four(4) records when there only be two(2). It then 
 * deletes the valis two(2) i.e. the ones linked from the 
 * enquiry page. Hence those records are not found when 
 * the link is clicked.
 */ 
$max_id = 0;
$querystr = sprintf('select max(id) as id from %s where inputter = "%s" and authorizer = "%s" and record_status="HLD" and current_no="0"',$this->param['tb_inau'],$pdfdata['idname'],$pdfdata['idname']);
if($max_r = $this->param['primarymodel']->execute_select_query($querystr))
{
    $max_r = (array) $max_r[0];
    var_dump($max_r);
    $max_id =  (int) $max_r['id'] - 4;
}
$querystr = sprintf('delete from %s where inputter = "%s" and authorizer = "%s" and record_status="HLD" and current_no="0" and id <= %s',$this->param['tb_inau'],$pdfdata['idname'],$pdfdata['idname'],$max_id);
             //$result = $this->param['primarymodel']->execute_delete_query($querystr);
            $result = $this->param['primarymodel']->execute_delete_query($querystr);
            return $result;
		}
		return false;
	}

	public function insert_into_pdf_table_no_delete($pdfdata)
	{
		$arr['pdf_id']			= $pdfdata['pdf_id'];
		$arr['pdf_template']	= $pdfdata['pdf_template'];
		$arr['controller']		= $pdfdata['controller'];
		$arr['type']			= $pdfdata['type'];
		$arr['data']			= $pdfdata['data'];
		$arr['data_type']		= $pdfdata['datatype'];
		$arr['inputter']		= $pdfdata['idname'];
		$arr['input_date']		= date('Y-m-d H:i:s'); 
		$arr['authorizer']		= $pdfdata['idname'];
		$arr['auth_date']		= date('Y-m-d H:i:s'); 
		$arr['record_status']	= "HLD";
		$arr['current_no']		= "0";
		$this->param['primarymodel']->insert_record($this->param['tb_inau'],$arr);
	}

}//End Controller_Core_Sysadmin_Pdf
