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
        $this->cfg		= new HSIConfig();
		$config 		= $this->cfg->get_config();
		$this->dbops	= new DbOps($config);
		$this->tb_pidregs = $config['tb_pidregs'];
	}
    
    public function register_pid()
    {
		$this->procops = new ProcOps();
		$this->procops->runcmd("scheduler");
    }
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
		if( $options["t"] == "cli" )
		{
			$daemon = new HSIDaemon();
			$daemon->register_pid();
		}
		elseif($options["t"] == "hsi") { /* do nothing */}
		else { die($fail_message); }
	}
	
	$cmd = "php "."/shazam/www/hndshkif/media/hsi/hndshkifd.php";
	$scheduler = new Scheduler();
	$scheduler->addTask("echo \"wazaaaaa\\n\" >> somefile", "0,5,10,15,20,25,30,35,40,45,50,55 * * * *");
	$scheduler->run();

?>
