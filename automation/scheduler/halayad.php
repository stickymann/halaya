<?php
/*
 *  Daemon process for task automation / scheduler. 
 *
 * $Id: halayad.php 2014-03-04 01:15:46 dnesbit $
 *
 * @package		Halaya
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license      
 */
require_once(dirname(__FILE__).'/schedconfig.php');
require_once(dirname(__FILE__).'/dbops.php');
require_once(dirname(__FILE__).'/procops.php');
require_once(dirname(__FILE__).'/scheduler.php');

class SchedDaemon
{	
	public function __construct()
    {
        $this->cfg		= new SchedConfig();
		$config 		= $this->cfg->get_config();
		$this->dbops	= new DbOps($config);
		$this->tb_schedulers = $config['tb_schedulers'];
	}
    
    public function register_pid()
    {
		$this->procops = new ProcOps();
		$this->procops->runcmd("scheduler");
    }
} // End SchedDaemon
	
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
		$daemon = new SchedDaemon();
		if( $options["t"] == "cli" )
		{
			$daemon->register_pid();
		}
		elseif($options["t"] == "web") { /* do nothing */}
		else { die($fail_message); }
	}
	
	$querystr = sprintf('SELECT * FROM %s WHERE enabled = "Y"',$daemon->tb_schedulers);
	if( $result = $daemon->dbops->execute_select_query($querystr) )
	{ 
		$scheduler = new Scheduler();
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
	// End halayad.php
?> 
