<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates user menutree based on security profile parameters.
 * See more in MenuTreeSystem.php
 *
 * $Id: MenuTreeUserProfile.php 2012-12-29 00:00:00 dnesbit $
  *
 * @package		Halaya Core
 * @module		core
 * @author		Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright	(c) 2013
 * @license		
 */
class Model_MenuTreeUserProfile extends Model
{
	function __construct($table, $inputter, $idField, $parentField, $sortField)
    {
		$this->db = Database::instance();
		$this->table = $table;
		$this->inputter = $inputter;
		$this->fields = array('id'     => $idField, 'parent' => $parentField,'sort'   => $sortField);
	}

	function _get_fields()
    {
		return array($this->fields['id'], $this->fields['parent'], $this->fields['sort'],'id','nleft', 'nright', 'nlevel',
					'node_or_leaf','module','label_input','label_enquiry','url_input','url_enquiry','controls_input','controls_enquiry',
					'inputter','input_date','authorizer','auth_date','record_status','current_no');
  	}

	function get_node($id)
	{
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = %d AND inputter = "%s"', join(',', $this->_get_fields()),$this->table,$this->fields['id'],$id,$this->inputter);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		if ($row = $result[0])
			return $row;
         return null;
  	}
  
	function get_descendants($id = 0, $includeSelf = false, $childrenOnly = false)
    {
		$idField = $this->fields['id'];
        $node = $this->get_node($id);
		if (is_null($node)) 
		{
			$nleft = 0;
			$nright = 0;
			$parent_id = 0;
		}
		else 
		{
			$nleft = $node->nleft;
			$nright = $node->nright;
			$parent_id = $node->$idField;
		}
 
		if ($childrenOnly) 
		{
			if ($includeSelf) 
			{
				$querystr = sprintf('SELECT %s FROM %s WHERE (%s = %d or %s = %d) AND inputter = "%s" ORDER BY nleft',
									join(',', $this->_get_fields()),
									$this->table,
									$this->fields['id'],
									$parent_id,
									$this->fields['parent'],
                                    $parent_id,
									$this->inputter);
			}
			else 
			{
				$querystr = sprintf('SELECT %s FROM %s WHERE %s = %d AND inputter = "%s" ORDER BY nleft',
									join(',', $this->_get_fields()),
									$this->table,
									$this->fields['parent'],
									$parent_id,
									$this->inputter);
			}	
		}
		else 
		{
			if ($nleft > 0 && $includeSelf) 
			{
				$querystr = sprintf('SELECT %s FROM %s where nleft >= %d AND nright <= %d AND inputter = "%s" ORDER BY nleft',
                                    join(',', $this->_get_fields()),
                                    $this->table,
                                    $nleft,
                                    $nright,
									$this->inputter);
			}
			else if ($nleft > 0) 
			{
				$querystr = sprintf('SELECT %s FROM %s where nleft > %d AND nright < %d AND inputter = "%s" ORDER BY nleft',
                                    join(',', $this->_get_fields()),
                                    $this->table,
                                    $nleft,
                                    $nright,
									$this->inputter);
			}
			else 
			{
				$querystr = sprintf('SELECT %s FROM %s AND inputter = "%s" ORDER BY nleft',
                                    join(',', $this->_get_fields()),
                                    $this->table,
									$this->inputter);
			}
		}
 
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		foreach ($result as $row)
		{
			$arr[ $row->$idField ] = $row;
		}
		return $arr;
	}

    public function get_children($id = 0, $includeSelf = false)
	{
		return $this->get_descendants($id, $includeSelf, false);
	}
	
	public function get_top_level_menus()
	{
		$querystr = sprintf('SELECT menu_id,module,url_input FROM %s where %s = 0 AND inputter = "%s" ORDER BY sortpos',$this->table,$this->fields['parent'],$this->inputter);
     	$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		foreach ($result as $row)
		{
			$arr[ $row->menu_id ] = $row;
		}
		return $arr;
	}

} //End Model_MenuTreeUserProfile
