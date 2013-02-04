<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Common queries for enquiry operations.
 *
 * $Id: EnqDB.php 2012-12-29 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module		core
 * @author		Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright	(c) 2013
 * @license
 */
class Model_EnqDB extends Model 
{
	function __construct()
	{
		$this->db = Database::instance();
		//set no time out for large queries
		set_time_limit(0);
	}
	
	public function count_records($querystr)
    { 
        $querystr = $this->db->query(Database::SELECT,$querystr,TRUE);
		return $query->count();
    }

    public function browse($querystr)
	{
		return $this->db->query(Database::SELECT,$querystr,TRUE);
	}

	public function get_enq_formfields($controller,&$arr1,&$arr2,&$arr3)
	{
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s" AND dflag = "Y"','formfields','enquirydefs','controller',$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$row = $result[0];
		$formfields = new SimpleXMLElement($row->formfields);
		foreach ($formfields->field as $field)
		{
			$val1 = sprintf('%s',$field->name);
			$val2 = sprintf('%s',$field->label);
			$val3 = sprintf('%s',$field->filterfield);
			$arr1[$val1] = $val1;
			$arr2[$val1] = $val2;
			$arr3[$val1] = $val3;
		}
	}
	
	public function get_fixed_selection_params($controller,&$arr1,&$arr2,&$arr3,&$arr4)
	{
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"','formfields','fixedselections','fixedselection_id',$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		/*controller could exist in params but not in fixedSELECTions, need to return empty arrays*/
		$arr1 = array();
		$arr2 = array();
		$arr3 = array();
		$arr4 = array();
		
		if($row = $result[0])
		{
			if($row->formfields)
			{
				$formfields = new SimpleXMLElement($row->formfields);
				foreach ($formfields->field as $field)
				{
					$val1 = sprintf('%s',$field->name);
					$val2 = sprintf('%s',$field->operand);
					$val3 = sprintf('%s',$field->value);
					$val4 = sprintf('%s',$field->onload);
					$arr1[$val1] = $val1;
					$arr2[$val1] = $val2;
					$arr3[$val1] = $val3;
					$arr4[$val1] = $val4;
				}
			}
			return true;
		}
		return false;
	}

	public function get_enquiry_params($controller)
	{
		$table = 'enquirydefs';
		$fields = array('id','controller','tablename','model','view','idfield','enqheader','showfilter','printuser','printdatetime');
		$querystr = sprintf('SELECT %s FROM %s WHERE controller = "%s AND dflag = "Y"', join(',',$fields),$table,$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		$idField = $fields[1];
		foreach ($result as $row)
		{
			$arr[$row->$idField] = $row;
		}
		return $arr;
	}

} // End Enq

