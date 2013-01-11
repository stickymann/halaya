<?php defined('SYSPATH') or die('No direct script access.');
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
class Controller_Core_Csvexport extends Controller
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
		$this->export_to_xl( $this->request->param('opt') );
	}

	function export_to_xl($csv_id)
	{
		$csv_id = trim($csv_id);
		$querystr = sprintf('select csv from csvs_is where csv_id = "%s"',$csv_id);
		$arr = $this->db->execute_select_query($querystr);
		$CSV = $arr[0]->csv;
		/*
		//many ways to print file contents, choose one
		1) file_get_contents()
		2) {
			$contents = file($file);
			$string = implode($contents);
			echo $string; 
		}
		*/
		$filename = $csv_id.".csv";
		Header ( "Content-Type: application/octet-stream"); 
		Header ( "Content-Type: application/text"); 
		Header( "Content-Disposition: attachment; filename=$filename");
		//print $CSV;
		include($CSV);
	}
}

