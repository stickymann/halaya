<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Returns AJAX request to javascripts. 
 *
 * $Id: Dbreqs.php 2013-01-01 00:00:00 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license     
 */

require_once('media/hsi/procops.php');
define("DELIMITER","{##}");

class Controller_Hndshkif_Dbreqs extends Controller
{
	public function before()
    {
		$this->sitedb = new Model_SiteDB;
		$this->enqdb = new Model_EnqDB;
		$this->paramkey = $this->sitedb->get_param_keys();
	}

	public function action_index()
	{
		$limit = "limit 500";
		$option = $_REQUEST['option'];
		switch($option)
		{
			case 'schedulerstart':
				$RESULT	= $this->scheduler_start();
				print $RESULT;
			break;
			
			case 'schedulerstop':
				$RESULT	= $this->scheduler_stop();
				print $RESULT;
			break;
			
			case 'schedulerstatus':
				$RESULT	= $this->scheduler_status();
				print $RESULT;
			break;
			
			case 'uploadfilecount':
				$RESULT	= $this->upload_filecount();
				print $RESULT;
			break;
		}
	}
	
	function scheduler_start()
	{
		$scheduler = new ProcOps();
		$status_arr = $scheduler->runcmd("scheduler",$scheduler->scheduler_cmd);
		return json_encode($status_arr);
	}
	
	function scheduler_stop()
	{
		$scheduler = new ProcOps();
		$arr = $scheduler->getdbpid("scheduler");
		if( $scheduler->stop( $arr['pid'] )) { return "OK"; } else { return "FAIL"; }
	}
	
	function scheduler_status()
	{
		$scheduler = new ProcOps();
		$status_arr = $scheduler->getdbpid("scheduler");
		if( $status_arr )
		{
			return json_encode($status_arr);
		}
		else
		{
			$fail_arr = array("id"=>"scheduler","pid"=>"-1");
			return json_encode($fail_arr);
		}
	}
	
	function upload_filecount()
	{
	    $dir = '/shazam/hsi/current/export';  //default export dir
	    $i = 0; 
		
		$param = $this->sitedb->get_controller_params("interfaceconfiguration");
		$querystr	= sprintf('SELECT config_xml FROM %s WHERE config_id ="%s"',$param['tb_live'],"DEFAULT");
		if( $result	= $this->sitedb->execute_select_query($querystr) )
		{
			try
			{
				$cfg = new SimpleXMLElement( $result[0]->config_xml );
				if( $cfg->folders->current_export ) 
				{
					$dir = sprintf('%s',$cfg->folders->current_export);
				}
			}
			catch (Exception $e) { }
		}
		
		try
		{
			if( $handle = opendir($dir) ) 
			{
				while( ($file = readdir($handle) ) !== false )
				{
					if( !in_array($file, array('.', '..')) && !is_dir($dir.$file) ) 
					$i++;
				}
			}
		}
		catch (Exception $e) { $i = -1; }
		
		$count_arr = array("count"=>$i);
		return json_encode($count_arr);
    }

} // End Hndshkif_Dbreqs
