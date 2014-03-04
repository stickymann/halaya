<?php
/**
 * Database operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: dbops.php 2013-09-13 16:15:46 dnesbit $
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
	
	public function __construct($config = null)
	{
		if($config)
		{
			$this->dbserver = $config['dbserver'];
			$this->dbname = $config['dbname'];
			$this->dbuser = $config['dbuser'];
			$this->dbpasswd = $config['dbpasswd'];
			$this->connectstr = $config['connectstr'];
			$this->tb_changelogs = $config['tb_changelogs'];
			$this->connect_to_db();
		}
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
		$arr = array();
//print "[DEBUG]---> "; print($querystr); print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );
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
//print "[DEBUG]---> "; print($querystr); print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );
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
//print "<b>[DEBUG]---></b> "; print $querystr; print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		$count = $this->dbh->exec($querystr);
		return $count;
	}
	
	public function update_record($table,$arr)
	{
		$vals = '';
		foreach($arr as $key => $value)
		{
			if(!($key=='id')) {$vals .= "`".$key."`".'="'.$value.'",';}
		}
		$vals = substr($vals,0,-1);
		$querystr = sprintf('UPDATE `%s` set %s WHERE `id` = "%s"',$table,$vals,$arr['id']);
//print "[DEBUG]---> "; print($querystr); print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );
		$count = $this->execute_non_select_query($querystr);
		return $count;
	}
	
	public function insert_from_table_to_table($table_into,$table_from,$id,$current_no)
	{
		$querystr = sprintf('DELETE FROM %s WHERE id="%s" AND current_no="%s"',$table_into,$id,$current_no);       
        if( $result = $this->execute_non_select_query($querystr) ){ /*waiting for deletions of any duplicate records*/ }
		$querystr = sprintf('INSERT into %s SELECT * FROM %s WHERE id="%s"',$table_into,$table_from,$id);	
		$count = $this->execute_non_select_query($querystr);
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
	
	public function create_record_id($tb_live)
	{
		$tb_inau = $tb_live."_is";
		$querystr = sprintf('SELECT counter FROM _sys_autoids WHERE tb_inau = "%s"',$tb_inau);
		if($result = $this->execute_select_query($querystr))
		{
			$row_1 = $result[0];
			if(isset($row_1['counter']))
			{
				$counter = $row_1['counter'];
				while($this->record_exist($tb_live,"id",$counter,$counter) || $this->record_exist($tb_inau,"id",$counter,$counter))
				{
					$counter++;
				}
				$querystr = sprintf('UPDATE _sys_autoids set counter = "%s" WHERE tb_inau = "%s"',$counter,$tb_inau);
				$result = $this->execute_non_select_query($querystr);
				$querystr = sprintf('SELECT counter FROM _sys_autoids WHERE tb_inau = "%s"',$tb_inau);
				if($result = $this->execute_select_query($querystr))
				{
					$row_2 = $result[0];
					$counter = $row_2["counter"];
					return $counter;
				}
			}
		}
		return 0;
	}
	
	public function last_changelog_have_new_records($type)
	{
		$newrecs = 0;
		$querystr = sprintf('SELECT id,changelog_details from %s WHERE type = "%s" ORDER BY id DESC LIMIT 1',$this->tb_changelogs,$type);
		if( $result = $this->execute_select_query($querystr) )
		{
			$record	  = $result[0]; 
			$formfields = simplexml_load_string( $record['changelog_details'] );
			foreach ( $formfields->rows->row as $row )
			{
				if ( sprintf('%s',$row->entry) == "NEW" )
				{
					$newrecs++;
				}
			}
			if( $newrecs > 0 ) { return TRUE; }
		}
		return FALSE;
	}

} // End DbOps
