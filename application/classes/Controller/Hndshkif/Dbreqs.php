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

require_once('media/hsi/hsiconfig.php');
require_once('media/hsi/procops.php');
define("DELIMITER","{##}");

class Controller_Hndshkif_Dbreqs extends Controller
{
	public function before()
    {
		$cfg	  = new HSIConfig();
		$this->config = $cfg->get_config();
		$this->sitedb = new Model_SiteDB;
		$this->enqdb  = new Model_EnqDB;
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
			
			case 'dailydlbatches':
				$date = $_REQUEST['date'];
				if(isset($_REQUEST['f'])){$f = $_REQUEST['f'];} else {$f = "default";}
				$RESULT	= $this->get_daily_dl_batches($date,$f);
			break;
			
			case 'dynacombo':
				$RESULT	= $this->dynacombo();
				print $RESULT;
			break;
			
			case 'picklistprint':
				$order_id = $_REQUEST['order_id'];
				$prnopt = $_REQUEST['prnopt'];
				$RESULT	= $this->picklistprint($order_id,$prnopt);
				print $RESULT;
			break;
			
			case 'daceasyid':
				$firstname = $_REQUEST['firstname'];
				$lastname  = $_REQUEST['lastname'];
				$RESULT	= $this->get_daceasy_id($firstname,$lastname);
				print $RESULT;
			break;
		}
	}
	
	function scheduler_start()
	{
		$scheduler = new ProcOps();
		$cmd = $scheduler->scheduler_cmd." -t hsi";
		$scheduler->set_db_cmdstr($cmd);
		$status_arr = $scheduler->runcmd("scheduler",$cmd);
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
	    $dir = $this->config['current_export'];
	    $i = 0; 
		
		/* //alternative method for getting order entry files
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
		*/
		
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
    
	function get_daily_dl_batches($date,$f="default")
	{
		$table = $this->config['tb_dlorderbatchs'];
		$querystr	= sprintf('SELECT batch_id FROM %s WHERE product_batch_date="%s"',$table,$date);
		$result		= $this->sitedb->execute_select_query($querystr);
		return json_encode($result);
	}
	
	function dynacombo()
	{
		$sid  	= $_REQUEST['sid'];
		$chfunc = $_REQUEST['chfunc'];
		$table  = $_REQUEST['table'];
		$rfield = $_REQUEST['rfield'];
		$sfield = $_REQUEST['sfield'];
		$sval   = $_REQUEST['sval'];
		$querystr = sprintf('SELECT DISTINCT %s FROM %s WHERE %s="%s"',$rfield,$table,$sfield,$sval);
		$result	  = $this->sitedb->execute_select_query($querystr);
		//print_r($result);
		$count = 0;
		$HTML = sprintf('<select id="%s" name="%s" size="5" multiple>',$sid,$sid)."\n";
		foreach($result as $key => $value)
		{
			//$row = $value[;
			$HTML .= sprintf('<option value="%s">%s</option>',$value->$rfield,$value->$rfield)."\n";
			$count++;
		}
		$HTML .= "</select>"."\n";
		$HTML .= sprintf('<div style="padding: 3px 0px 0px 0px; font-weight: bold;">Total Batches: %s</div>',$count)."\n";
		$HTML .= sprintf('<script>$("#%s").change(function() { %s(); });</script>',$sid,$chfunc);
		return $HTML;
	}
		
	function picklistprint($order_id,$prnopt)
	{
		require_once('media/hsi/printerwriteops.php');
		$tb_printq = $this->config['tb_printq'];
		$picklist  = $this->config['prn_picklist'];
		$printer   = $picklist['printer'];
		
		$printerwrite = new PrinterWriteOps();
		$filename = $printerwrite->create_order_picklist($order_id,null,$prnopt,false);
		$cmd = sprintf("lpr -r -P %s %s",$printer,$filename[$prnopt]);
		exec($cmd ,$op);
		$querystr = sprintf('DELETE FROM %s WHERE filename="%s"',$tb_printq,$filename[$prnopt]);
		if( $this->sitedb->execute_delete_query($querystr) ) 
		{ 
			/* wait for deletions*/ 
		} 
	}
	
	function get_daceasy_id($firstname,$lastname)
	{
		require_once('media/hsi/customerops.php');
		$customerops = new CustomerOps();
		$table = $this->config['tb_customers'];
		$customer_id = $customerops->get_new_id($table,$firstname,$lastname);
		$arr = array('customer_id' => $customer_id);
		return json_encode($arr);
	}
	
} // End Hndshkif_Dbreqs
