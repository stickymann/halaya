<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Merges xfdf data with pdf forms. 
 *
 * $Id: Xfdfexport.php 2013-01-13 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Xfdfexport extends Controller
{
	public function __construct()
    {
       	if(!Auth::instance()->logged_in())
		{
			Controller_Core_Site::redirect_to_login();	
		}
		$this->db = new Model_SiteDB();
	}
	
	public function action_index()
    {
		$cert_id = strtoupper( $this->request->param('opt') );
		$this->export_to_xfdf($cert_id);
	}

	function export_to_xfdf($cert_id)
	{
		$csv_id		= trim($cert_id);
		$querystr	= sprintf('select csv from csvs_is where csv_id = "%s"',$cert_id);
		$arr		= $this->db->execute_select_query($querystr);
		$template	= $arr[0]->csv;
		/*
		//many ways to print file contents, choose one
		1) file_get_contents()
		2) {
			$contents = file($file);
			$string = implode($contents);
			echo $string; 
		}
		*/
		//files
		//$template = "media/pdftemplate/vtcert_template.pdf";
		$xfdffile	= "/tmp/".$cert_id.".xfdf";
		$outfile	= "/tmp/".$cert_id.".pdf";
		$cmd		= "/opt/pdflabs/pdftk/bin/pdftk";
		
		//external command to execute
		$cmdstr = sprintf('%s %s fill_form %s output %s',$cmd,$template,$xfdffile,$outfile);
		exec($cmdstr);
		
		//pdf header
		header('Content-Type: application/pdf'); 
		//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
		//header('Pragma: public');
		//header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		//header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		//header('Content-Length: '.filesize($outfile));
		//header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: inline; filename="'.$outfile.'";');
		print file_get_contents($outfile);
		
		//clean up
		if(file_exists($outfile)){ unlink($outfile); }
		exit();
	}

} //End Controller_Core_Xfdfexport
