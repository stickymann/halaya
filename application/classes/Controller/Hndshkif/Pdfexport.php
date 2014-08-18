<?php 
//defined('SYSPATH') or die('No direct script access.');
/**
 * Sends CSV data to browser. 
 *
 * $Id: Csvexport.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Hndshkif_Pdfexport extends Controller
{
	public function __construct()
    {
       	if(!Auth::instance()->logged_in())
		{
			Controller_Core_Site::redirect_to_login();	
		}
		parent::__construct(Request::initial(),new Response);
		$this->db = new Model_SiteDB();
	}
	
	public function action_index()
	{
		//$this->request->param('opt')
		$this->export_to_pdf();
	}

	function export_to_pdf()
	{
		/*
		//many ways to print file contents, choose one
		1) file_get_contents()
		2) {
			$contents = file($file);
			$string = implode($contents);
			echo $string; 
		}
		*/
		$PDF = "/tmp/hsi846428-20140809175048.pdf";
		$filename = "846428-20140809175048.pdf";
		header('Content-type: application/pdf');
		header('Content-Disposition: inline; filename="' . $filename . '"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize($PDF));
		header('Accept-Ranges: bytes');
		@readfile($PDF);
		
		/*
		Header ( "Content-Type: application/octet-stream"); 
		Header ( "Content-Type: application/pdf"); 
		Header( "Content-Disposition: attachment; filename=$filename");
		//print $CSV;
		include($PDF); */
	}
}

