<?php
/**
 * Command line process operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: procops.php 2013-03-03 16:15:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
require_once(dirname(__FILE__).'/hsiconfig.php');
require_once(dirname(__FILE__).'/dbops.php');

class ProcOps 
{
	private $pid;
	private $command;
	private $tb_pidregs;
	public $scheduler_cmd;
	public $db_cmdstr;
		
    public function __construct($cl=false)
    {
        $this->cfg		= new HSIConfig();
		$config 		= $this->cfg->get_config();
		$this->dbops	= new DbOps($config);
		$this->scheduler_cmd = "php ".$config['scheduler'];
		$this->tb_pidregs = $config['tb_pidregs'];
		$this->db_cmdstr = "";
	}
    
    public function set_db_cmdstr($cmdstr)
    {
		$this->db_cmdstr  = $cmdstr;
	}
    
    public function runcmd($id,$cmd=false)
    {
        if ($cmd != false)
        {
            $this->command = $cmd;
			$command = 'nohup '.$this->command.' > /dev/null 2>&1 & echo $!';
			exec($command ,$op);
			$this->pid = (int)$op[0];
		}
		else
		{
			$this->pid = getmypid();
			$this->command = __FILE__;
		}
		
		$arr['id'] = $id;
		$arr['pid'] = $this->pid;
		$arr['command'] = $this->db_cmdstr;
			
		if( $this->dbops->record_exist($this->tb_pidregs, "id", $arr['id']) )
		{ 
			$querystr = sprintf('SELECT id,pid FROM %s WHERE %s = "%s"',$this->tb_pidregs,"id",$arr['id']);
			$formdata = $this->dbops->execute_select_query($querystr);
			$record	  = $formdata[0];
			$pid	  = $record['pid'];
			if( $this->status($pid) )
			{
				//program already running, return status
				$this->stop($arr['pid']);
				return $record;
			}
			else
			{
				//pid record exist but program not running, update with new info
				$count = $this->dbops->update_record($this->tb_pidregs, $arr);
				unset($arr['command']);
				return $arr;
			}
		}
		else
		{
			//pid record does not exist, insert with new info
			$count = $this->dbops->insert_record($this->tb_pidregs, $arr);
			unset($arr['command']);
			return $arr;
		}
    }

    public function setpid($pid)
    {
        $this->pid = $pid;
    }
    
    public function setdbpid($arr)
    {
		$this->dbops->insert_record($this->tb_pidregs, $arr);
	}

    public function getpid()
    {
        return $this->pid;
    }
    
    public function getdbpid($id)
    {
        $querystr = sprintf('SELECT id,pid FROM %s WHERE %s = "%s"',$this->tb_pidregs,"id",$id);
		if( $result = $this->dbops->execute_select_query($querystr) )
		{ 
			$arr = $result[0];
			if( $this->status( $arr['pid']) )
			{
				return $arr;
			}			
			else
			{
				//clean up if any database entry exist
				$this->stop( $arr['pid'] );
				return false;
			}
		}
		return false;
    }
    
    public function status($pid)
    {
        $command = 'ps p '.$pid;
        exec($command,$op);
        if( !isset($op[1]) ) return false;
		else return true;
    }

    public function start()
    {
        if ($this->command != '')$this->runCom();
        else return true;
    }

    public function stop($pid)
    {
        $command = 'sudo kill -9 '.$pid;
        exec($command);
        if ($this->status($pid) == false)
        {
			$querystr = sprintf('DELETE FROM %s WHERE %s = "%s"',$this->tb_pidregs,"pid",$pid);
			if( $result = $this->dbops->execute_non_select_query($querystr) ){ /*waiting for deletions of any duplicate records*/ }
			return true;
		}
        else return false;
    }
    
    public static function process_exist($grep_arg)
    {
		$nohup_used = false;
		$minprocs = 1;
		$output_format = "%p;%a";
		$cmd = sprintf('ps axo "%s" | grep -i "%s"',$output_format,$grep_arg);
		exec($cmd,$output);
		
		//parse original ps output and remove grep entries
		foreach($output as $index => $value)
		{
			if( preg_match('/grep /i', $value) ) {  unset($output[$index]); }
		}
		
		//parse ps output minus grep entries
		foreach($output as $index => $value)
		{
			if( preg_match('/sh /i', $value) || preg_match('/su /i', $value) )   { $nohup_used = true; $minprocs = 2; }
		}
		
		$count = count($output);
		if($count > $minprocs )
		{
			return true;
		}
		return false;
	}

} // End ProcOps
