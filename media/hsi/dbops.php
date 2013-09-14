<?php
/**
 * Database operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: DbOps.php 2013-09-13 16:15:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class DbOps 
{
	public $dbh = null;
	private $dbserver = "";
	private $dbname = "";
	private $dbuser = "";
	private $dbpasswd = "";
	private $connectstr = "";
	
	public function __construct()
	{
		$configfile = dirname(__FILE__).'/hsiconfig.xml';
		try
			{
				//check for required fields in xml file
				$xml = file_get_contents($configfile);
				$config = new SimpleXMLElement($xml);
				if($config->database->server) { $this->dbserver = sprintf('%s',$config->database->server); }
				if($config->database->name) { $this->dbname = sprintf('%s',$config->database->name); }
				if($config->database->user) { $this->dbuser = sprintf('%s',$config->database->user); }
				if($config->database->password) { $this->dbpasswd = sprintf('%s',$config->database->password); }
				$this->connectstr = sprintf('mysql:host=%s;dbname=%s', $this->dbserver, $this->dbname);
			}
		catch (Exception $e) 
			{
				$desc='Configuration File Error : '.$e->getMessage();
				print $desc;
			}
			$this->connect_to_db();
	}
	
	public function connect_to_db()
	{
		$this->dbh = new PDO($this->connectstr, $this->dbuser, $this->dbpasswd);
		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		set_time_limit(0);
	}

	public function disconnect_from_db()
	{
		$this->dbh = null;
	}

	public function get_db_handle()
	{
		return $this->dbh;
	}
	
	public function execute_select_query($querystr)
	{
		$i = 0;
		$result = $this->dbh->query($querystr);
		$mode = $result->setFetchMode(PDO::FETCH_ASSOC);
		foreach ($result as $row)
		{
			$arr[$i] = $row;
			$i++;
		}
		return $arr;
	}

	public function execute_non_select_query($querystr)
	{
		$count = $this->dbh->exec($querystr);
		return $count;
	}
	
	public function insert_record($table,$arr)
	{
		$vals = ''; $fields = '';
		foreach($arr as $key => $value)
		{
			$fields .= "`".$key."`,";
			$vals .= '"'.$value.'",';
		}
		$vals = substr($vals,0,-1);
		$fields = substr($fields,0,-1);
		$querystr = sprintf('INSERT INTO `%s` (%s) VALUES(%s)',$table,$fields,$vals);			
//print "<b>[DEBUG]---></b> "; print($querystr); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		$count = $this->dbh->exec($querystr);
		return $count;
	}

	public function record_exist($table,$idfield,$idval)
	{
		$querystr = sprintf('SELECT COUNT(id) AS counter FROM %s WHERE %s = "%s"',$table,$idfield,$idval);
		$result = $this->execute_select_query($querystr);
		$row = $result[0];
		if ($row['counter'] > 0 )
		{
			return TRUE;
		}
	}

} // End DbOps
