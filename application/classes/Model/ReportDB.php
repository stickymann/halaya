<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Common queries for report operations.
 *
 * $Id: ReportDB.php 2012-12-29 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module		core
 * @author		Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright	(c) 2013
 * @license
 */
class Model_ReportDB extends Model 
{
	function __construct()
	{
		$this->db = Database::instance();
		//set no time out for large queries
		set_time_limit(0);
	}
		
	public function get_report_formdefs($controller)
	{
		$this->table = 'reportdefs';
		$fields = array('id','reportdef_id','controller','formfields');
		$querystr = sprintf('SELECT %s FROM %s WHERE controller = "%s" AND dflag = "Y"', join(',',$fields),$this->table,$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$row = $result[0];
		return $row;
	}
	
	public function get_report_params($controller)
	{
		$table = 'reportdefs';
		$fields = array('id','reportdef_id','controller','model','view','rptheader','showfilter','printuser','printdatetime');
		$querystr = sprintf('SELECT %s from %s WHERE controller = "%s" AND dflag = "Y"', join(',',$fields),$table,$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		$idField = $fields[2];
		foreach ($result as $row)
		{
			$arr[$row->$idField] = $row;
		}
		return $arr;
	}

}//End Model_ReportDB
