<?php
/**
 *  Daemon process for Handshake to DacEasy Interface automation. 
 *
 * $Id: hndshkifd.php 2014-03-04 01:15:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
require_once(dirname(__FILE__).'/hsiconfig.php');
require_once(dirname(__FILE__).'/dbops.php');
require_once(dirname(__FILE__).'/procops.php');
require_once(dirname(__FILE__).'/scheduler.php');

class HSIDaemon
{	
	public function __construct()
    {
        $cfg		= new HSIConfig();
		$this->config 	= $cfg->get_config();
		$this->dbops	= new DbOps($this->config );
		$this->tb_schedulers = $this->config ['tb_schedulers'];
		$this->tb_pidregs = $this->config ['tb_pidregs'];
	}
    
    public function register_pid($type="")
    {
		$this->procops = new ProcOps();
		$cmdstr = "php ".__FILE__." -t ".$type;
		$this->procops->set_db_cmdstr($cmdstr);
		$this->procops->runcmd("scheduler");
    }
    
} // End HSIDaemon
	
	//prevent running more than one instance
	$grep_arg = basename(__FILE__);
	if( ProcOps::process_exist($grep_arg) )
	{
		die("Process already exist, terminating now!\n");
	}
	
	$fail_message = sprintf("Run with: \n\t php %s -t %s\n\n",__FILE__,"cli");
	$opts  = "";
	$opts .= "t:";  
	$options = getopt($opts);
	
	if( !isset($options["t"]) )
	{
		die($fail_message);
		//posix_kill(getmypid(), SIGTERM);
	}
	else
	{
		$daemon = new HSIDaemon();
		if( $options["t"] == "cli" || $options["t"] == "onboot")
		{
			$daemon->register_pid($options["t"]);
		}
		elseif($options["t"] == "hsi") { /* do nothing */}
		else { die($fail_message); }
	}
	
	$querystr = sprintf('SELECT * FROM %s WHERE enabled = "Y"',$daemon->tb_schedulers);
	if( $result = $daemon->dbops->execute_select_query($querystr) )
	{ 
		$scheduler = new Scheduler();
		$logfile = sprintf('%sSCHEDULER-%s.log.txt',$daemon->config['archive_log'],date('Ymd-His'));
		$scheduler->setLogFile($logfile); 
		foreach( $result as $index => $record )
		{
			$cmd = "";
			if( $record['format'] == "SCRIPT" )
			{
				$cmd = sprintf('%s %s %s',$record['type'],$record['fullpath'],$record['args']);
			}
			else
			{
				$cmd = sprintf('%s %s',$record['fullpath'],$record['args']);
			}
//print "[DEBUG]---> "; print $cmd." | ".$record['crontab']; print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );
			
			$scheduler->addTask($cmd, $record['crontab']);
		}
		$scheduler->run();
	}
	// End hndshkifd.php
?> 
