<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Main application model, non ORM.
 *
 * $Id: SiteDB.php 2012-12-29 00:00:00 dnesbit $
 *
 * @package	Halaya Core
 * @module		core
 * @author		Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright	(c) 2013
 * @license
 */
class Model_SiteDB extends Model
{
	private $errmsg;
	private $ns_total_rows;

	function __construct()
	{
		$this->db = Database::instance();
		$this->set_db_err_msg();
		$this->set_ns_totalrows($value=0);
		//set no time out for large queries
		set_time_limit(0);
	}

	public function set_db_err_msg($string='')
	{
		$this->errmsg = $string;
	}

	public function get_db_err_msg()
	{
		return $this->errmsg;
	}

	public function set_ns_totalrows($value=0)
	{
		$this->ns_total_rows = $value;
	}

	public function get_ns_totalrows()
	{
		return $this->ns_total_rows;
	}
	
	public function execute_non_select_query($querytype,$querystr)
	{
		if($result = $this->db->query($querytype,$querystr))
		{
//print "<b>[DEBUG]---></b> "; print($querystr); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
			$this->set_ns_totalrows($result);
			return TRUE;
		}
		$str = '<div class="frmmsg">An Error Occurred, Please Try Again.</div>';
		$this->set_db_err_msg($str);
//print "<b>[DEBUG]---></b> "; print($querystr); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		return FALSE;
	}
	
	public function execute_delete_query($querystr)
	{
		$result = $this->execute_non_select_query(Database::DELETE,$querystr);
		return $result;
	}
	
	public function execute_insert_query($querystr)
	{
		$result = $this->execute_non_select_query(Database::INSERT,$querystr);
		return $result;
	}

	public function execute_update_query($querystr)
	{
		$result = $this->execute_non_select_query(Database::UPDATE,$querystr);
		return $result;
	}
	
	public function execute_select_query($querystr)
	{
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
//print "<b>[DEBUG]---></b> "; print($querystr); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		$arr = array();
		$i=0;
		foreach ($result as $row)
		{
			$arr[$i] = $row;
			$i++;
		}
//print "<b>[DEBUG]---></b> "; print($querystr); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		return $arr;
	}

	public function get_params($controller)
	{
		$this->table = 'params';
		$fields = array(
			'id','param_id','controller','dflag','module','auth_mode_on','index_field_on','indexview','viewview','inputview','authorizeview',
			'deleteview','enquiryview','indexfield','indexfieldvalue','indexlabel','appheader','primarymodel','tb_live','tb_inau','tb_hist','errormsgfile'			
		);
		$querystr = sprintf('SELECT %s FROM %s WHERE controller = "%s" AND dflag = "Y"', join(',',$fields),$this->table,$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		$idField = $fields[2];
		foreach ($result as $row)
		{
			$arr[ $row->$idField ] = $row;
		}
		return $arr;
	}

	public function get_controller_params($controller)
	{
		$arrobj = $this->get_params(trim($controller));
//print "<b>[DEBUG]---></b> "; print_r($arrobj); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		$arr = (array) $arrobj[trim($controller)];
		return $arr;
	}

	public function get_formdefs($controller)
	{
		$this->table = 'params';
		$fields = array('id','param_id','controller','module','formfields');
		$querystr = sprintf('SELECT %s FROM %s WHERE controller = "%s" AND dflag = "Y"', join(',',$fields),$this->table,$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$row = $result[0];
		return $row;
	}

	public function get_all_recs_all_fields($table)
	{
		$querystr = sprintf('SELECT * FROM %s',$table);
        $idField = 'id';
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		foreach ($result as $row)
		{
			$arr[$row->$idField] = $row;
		}
		return $arr;
	}

	public function get_all_recs_by_fields($table,$fields,$prefix="",$where="",$orderby="")
	{
		if(is_array($fields))
		{
			if(isset($prefix))
			{
				foreach($fields as $key => $value)
				{
					$fields[$key] = sprintf('%s AS %s%s',$value,$prefix,$value);		
				}
			}
			$querystr = sprintf('SELECT %s FROM %s %s %s', join(',',$fields),$table,$where,$orderby);
		}
		else
		{
			if(isset($prefix))
			{
				foreach($fields as $key => $value)
				{
					$fields = sprintf('%s AS %s_%s',$value,$prefix,$value);		
				}
			}
			$querystr = sprintf('SELECT %s FROM %s %s %s', $fields,$table,$where,$orderby);
		}
		$result = $this->execute_select_query($querystr);
		return $result;
	}

	public function get_recs_by_subform($table,$fields,$idfield,$idval,$current_no,$prefix="")
	{
		if(is_array($fields))
		{
			if(isset($prefix))
			{
				foreach($fields as $key => $value)
				{
					$fieldname = $value; $asname = $value;	
					$vals = preg_split('/:/',$value);
					if(is_array($vals) && count($vals)==2)
					{
						$fieldname = $vals[0]; 
						$asname = $vals[1];
					}
					$fields[$key] = sprintf('%s AS %s%s',$fieldname,$prefix,$asname);		
				}
			}
			$query = sprintf('SELECT %s FROM %s WHERE %s="%s" AND current_no="%s"', join(',',$fields),$table,$idfield,$idval,$current_no);
		}
		else
		{
			if(isset($prefix))
			{
				foreach($fields as $key => $value)
				{
					
					$fieldname = $value; $asname = $value;	
					$vals = preg_split('/:/',$value);
					if(is_array($vals) && count($vals)==2)
					{
						$fieldname = $vals[0]; 
						$asname = $vals[1];
					}
					$fields = sprintf('%s AS %s_%s',$value,$prefix,$value);		
				}
			}
			$query = sprintf('SELECT %s FROM %s WHERE %s="%s" AND current_no="%s"',$fields,$table,$idfield,$idval,$current_no);
		}
		$result = $this->execute_select_query($querystr);
		return $result;
	}

	public function get_all_history_recs_all_fields($table)
	{
		$querystr = sprintf('SELECT * FROM %s',$table);
        $result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		foreach ($result as $row)
		{
			$arr[$row->id.",".$row->current_no] = $row;
		}
		return $arr;
	}

	public function get_record_by_id_val($table,$unique_id,$id,$fields)
	{
		$idfield = $unique_id;
		if(is_array($fields))
		{
			$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"', join(',', $fields),$table,$idfield,$id);
		}
		else
		{
			$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"',$fields,$table,$idfield,$id);
		}
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		if ($row = $result[0])
		{
			return $row;
		}
	}
	
	public function get_menu_controls($table,$field,$url)
	{
		$querystr = sprintf('SELECT * FROM %s WHERE %s ="%s"',$table,$field,$url);
        $idField = 'id';
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$row = $result[0];
		return $row;
	}
	
	public function get_user_menu_controls($table,$field,$url,$user)
	{
		$querystr = sprintf('SELECT * FROM %s WHERE %s ="%s" AND inputter = "%s" ',$table,$field,$url,$user);
		$idField = 'id';
//print "<b>[DEBUG]---></b> "; print($querystr); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
//print "<b>[DEBUG]---></b> "; print_r($result); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		$row = $result[0];
		return $row;
	}

	public function record_exist($table,$field,$id,$unique_id)
	{
		$idfield = $field;
		$querystr = sprintf('SELECT COUNT(id) AS count FROM %s WHERE id = "%s" AND %s = "%s"',$table,$id,$idfield,$unique_id);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$row = $result[0];
		if ($row['count'] > 0 )
		{
			return TRUE;
		}
		else
		{
			$idfield = 'id';
			$querystr = sprintf('SELECT COUNT(id) AS count FROM %s WHERE id = "%s" AND %s = "%s"',$table,$id,$idfield,$unique_id);
			$result = $this->db->query(Database::SELECT,$querystr,TRUE);
			$row = $result[0];
			if ($row['count'] > 0 )
			{
				return TRUE;
			}
		}
		$str = '<div class="frmmsg">Record [ '.$id.' ] does not exist.</div>';
		$this->set_db_err_msg($str);
		return FALSE;
	}
	
	public function record_exist_dual_key($table,$field1,$field2,$value1,$value2)
	{
		$querystr = sprintf('SELECT COUNT(id) AS count FROM %s WHERE %s = "%s" AND %s = "%s"',$table,$field1,$value1,$field2,$value2);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$row = $result[0];
		if ($row->count > 0 )
		{
			return TRUE;
		}
		$str = '<div class="frmmsg">Record [ '.$field1.' | '.$field2.' ] does not exist.</div>';
		$this->set_db_err_msg($str);
		return FALSE;
	}


	public function is_duplicate_unique_id($table,$field,$id,$unique_id)
	{
		$idfield = $field;
		$querystr = sprintf('SELECT id FROM %s WHERE %s = "%s"',$table,$idfield,$unique_id);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		if ($row = $result[0])
		{
			if(!($row->id == $id)){return TRUE;}
		}
		$str = '<div class="frmmsg">Record [ '.$id.' ] does not exist.</div>';
		$this->set_db_err_msg($str);
		return FALSE;
	}

	public function is_duplicate_composite_id($table,$fields,$id)
	{
		$where = "WHERE ";
		foreach($fields as $key => $val)
		{
			$where .= sprintf('%s = "%s" AND ',$key,$val);
		}
		$where  = substr_replace($WHERE, '', -5);
		
		$querystr = sprintf('SELECT id FROM %s %s',$table,$WHERE);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		if ($row = $result[0])
		{
			if(!($row->id == $id)){return TRUE;}
		}
		$str = '<div class="frmmsg">Record [ '.$id.' ] does not exist.</div>';
		$this->set_db_err_msg($str);
		return FALSE;
	}

	public function get_record_by_id($table,$unique_id,$id,$fields)
	{
		$idfield = $unique_id;
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"', join(',', $fields),$table,$idfield,$id);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		if ($row = $result[0])
		{
			if($lockedrec=$this->is_record_locked($table,$row->id))
			{
				$str = '<div class="frmmsg">Record [ '.$lockedrec->record_id.' ] is locked by user '.$lockedrec->idname.'.</div>';
				$this->set_db_err_msg($str);
				return NULL;
			}
			else
			{	
				return $row;
			}
		}
		else
		{
			$idfield = 'id';
			$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"', join(',', $fields),$table,$idfield,$id);
			$result = $this->db->query(Database::SELECT,$querystr,TRUE);
			if ($row = $result[0])
			{
				if($lockedrec=$this->is_record_locked($table,$row->id))
				{
					$str = '<div class="frmmsg">Record [ '.$lockedrec->record_id.' ] is locked by user '.$lockedrec->idname.'.</div>';
					$this->set_db_err_msg($str);
					return NULL;
				}
				else
				{	;
					return $row;
				}
			}
		}
		$str = '<div class="frmmsg">Record [ '.$id.' ] does not exist.</div>';
		$this->set_db_err_msg($str);
		return NULL;
	}

	public function get_record_by_lookup($tb_live,$tb_inau,$unique_id,$id,$fields,$formtype)
	{
		/**look in inau table first*/
		$idfield = $unique_id;
		$table = $tb_inau;
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"', join(',', $fields),$table,$idfield,$id);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		if ($row = $result[0])
		{
			if($formtype=='i' || $formtype=='w')
			{
				if($formtype=='w' && $row->current_no !=0)
				{
					//if record is not new AND in inau/ihld table, it cannot be edit with this permission
					$str = '<div class="frmmsg">Record [ '.$row->id.' ] is not a new record, current no not equal 0.</div>';
					$this->set_db_err_msg($str);
					return NULL;
				}
				
				if($lockedrec=$this->is_record_locked($table,$row->id))
				{
					$str = '<div class="frmmsg">Record [ '.$lockedrec->record_id.' ] is locked by user '.$lockedrec->idname.'.</div>';
					$this->set_db_err_msg($str);
					return NULL;
				}
			}
			return $row;
        }
		else
		{
			/*if not unique field try id*/
			$idfield = 'id';
			$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"', join(',', $fields),$table,$idfield,$id);
			$result = $this->db->query(Database::SELECT,$querystr,TRUE);
			if ($row = $result[0])
			{
	
				if($formtype=='i' || $formtype=='w')
				{
					if($formtype=='w' && $row->current_no !=0)
					{
						//if record is not new and in inau/ihld table, it cannot be edit with this permission
						$str = '<div class="frmmsg">Record [ '.$row->id.' ] is not a new record, current no not equal 0.</div>';
						$this->set_db_err_msg($str);
						return NULL;
					}
				
					if($lockedrec=$this->is_record_locked($table,$row->id))
					{
						$str = '<div class="frmmsg">Record [ '.$lockedrec->record_id.' ] is locked by user '.$lockedrec->idname.'.</div>';
						$this->set_db_err_msg($str);;
						return NULL;
					}
				}
				return $row;
			}
			else
			{
				/**look in live table now, most hits should be here*/
				$idfield = $unique_id;
				$table = $tb_live;
				$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"', join(',', $fields),$table,$idfield,$id);
				$result = $this->db->query(Database::SELECT,$querystr,TRUE);
				if ($row = $result[0])
				{
					if($formtype=='i')
					{
						//if record is coming FROM live, set status 'IHLD' and pre-status 'LIVE'
						$this->insert_from_table_to_table($tb_inau,$tb_live,$row->id);
					}
					else if ($formtype=='w')
					{
						//if record is coming FROM live, it cannot be edit with this permission
						$str = '<div class="frmmsg">Record [ '.$row->id.' ] is not a new record, current no not equal 0.</div>';
						$this->set_db_err_msg($str);
						return NULL;
					}
					return $row;
				}
				else
				{
					/*if not unquie field try id*/
					$idfield = 'id';
					$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"', join(',', $fields),$table,$idfield,$id);
					$result = $this->db->query(Database::SELECT,$querystr,TRUE);
					if ($row = $result[0])
					{
						if($formtype=='i')
						{
							//if record is coming FROM live, set status 'IHLD' and pre-status 'LIVE'
							$this->insert_from_table_to_table($tb_inau,$tb_live,$row->id);
						}
						else if ($formtype=='w')
						{
							//if record is coming FROM live, it cannot be edit with this permission
							$str = '<div class="frmmsg">Record [ '.$row->id.' ] is not a new record, current no not equal 0.</div>';
							$this->set_db_err_msg($str);
							return NULL;
						}
						return $row;
					}
				}
			}
		}	
		$str = '<div class="frmmsg">Record [ '.$id.' ] does not exist.</div>';
		$this->set_db_err_msg($str);
		return NULL;
	}
	
	public function get_hist_record_by_lookup($tb_hist,$id,$fields,$formtype)
	{
		/**look in hist table*/
		list($idfield,$cur_no) = preg_split('/;/',$id);
		$table = $tb_hist;
		$querystr = sprintf('SELECT %s FROM %s WHERE id = "%s" AND current_no = "%s"', join(',', $fields),$table,$idfield,$cur_no);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		if ($row = $result[0])
		{
			return $row;
        }
		$str = '<div class="frmmsg">Record [ '.$id.' ] does not exist.</div>';
		$this->set_db_err_msg($str);;
		return NULL;
	}

	public function get_formfields($controller,&$labelarr=NULL)
	{
		$labels = FALSE; 
		if(isset($labelarr)){ $labels = TRUE; }
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"','formfields','params','controller',$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		$row = $result[0];
		
		$formfields = new SimpleXMLElement($row->formfields);
		foreach ($formfields->field as $field)
		{
			$val = sprintf('%s',$field->name);
			$arr[$val] = $val;
			if($labels) 
			{
				$lbl = sprintf('%s',$field->name);
				$labelarr[$lbl] = sprintf('%s',$field->label);
			}
		}
		return $arr;
	}
	
	public function get_subform_fields($controller,&$labelarr=NULL)
	{
		$labels = FALSE; 
		if(isset($labelarr)){ $labels = TRUE; }
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"','formfields','params','controller',$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		$row = $result[0];
		
		$formfields = new SimpleXMLElement($row->formfields);
		foreach ($formfields->subformfields->subfield as $field)
		{
			$val = sprintf('%s',$field->subname);
			$arr[$val] = $val;
			if($labels) 
			{
				$lbl = sprintf('%s',$field->subname);
				$labelarr[$lbl] = sprintf('%s',$field->sublabel);
			}
		}
		return $arr;
	}

	public function get_subform_column_def($controller,$prefix="")
	{
		$labels = FALSE; 
		if(isset($labelarr)){ $labels = TRUE; }
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"','formfields','params','controller',$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		$row = $result[0];

		$i=0;
		$formfields = new SimpleXMLElement($row->formfields);
		foreach ($formfields->subformfields->subfield as $rowfield)
		{
			$field = sprintf('%s',$rowfield->subname);
			$vals = preg_split('/:/',$field);
			if(is_array($vals) && count($vals)==2)
			{
				$field = $vals[1];
			}	
			$field = $prefix.$field;
			$title = sprintf('%s',$rowfield->sublabel);
			$align = sprintf('%s',$rowfield->align);
			$width = sprintf('%s',$rowfield->width);
			$editor = sprintf('%s',$rowfield->editor);
			$formatter = sprintf('%s',$rowfield->formatter);
			$arr[$i] = array('field'=>$field,'title'=>$title,'width'=>$width,'align'=>$align,'formatter'=>$formatter,'editor'=>$editor);
			$i++;
		}
		return $arr;
	}

	public function get_subform_options($controller)
	{
		$labels = FALSE; 
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"','formfields','params','controller',$controller);
		$result = $this->db->query(Database::SELECT,$query);
		$arr = array();
		$row = $result[0];
		$formfields = new SimpleXMLElement($row->formfields);
		foreach ($formfields->subformfields->subfield as $subfield)
		{
			$val = sprintf('%s',$subfield->subname);
			$arr[$val]['subname'] = sprintf('%s',$subfield->subname); 
			$arr[$val]['sublabel'] = sprintf('%s',$subfield->sublabel); 
			if($subfield->formatter) { $arr[$val]['formatter'] = sprintf('%s',$subfield->formatter); }
			if($subfield->width) { $arr[$val]['width'] = sprintf('%s',$subfield->width); }
			if($subfield->align) { $arr[$val]['align'] = sprintf('%s',$subfield->align); }
			if($subfield->editor) { $arr[$val]['editor'] = sprintf('%s',$subfield->editor); }
		}
		return $arr;
	}

	public function get_form_subtable_options($controller,$fieldname)
	{
		$labels = FALSE; 
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"','formfields','params','controller',$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		$row = $result[0];
		$formfields = new SimpleXMLElement($row->formfields);
		
		//$subfield = $formfields->$field->subtable->subfield;
		foreach ($formfields->field as $field)
		{
			if(sprintf('%s',$field->name) == $fieldname)
			{
				foreach ($field->subtable->subfield as $subfield)
				{
					$val = sprintf('%s',$subfield->subname);
					$arr[$val]['subname'] = sprintf('%s',$subfield->subname); 
					$arr[$val]['sublabel'] = sprintf('%s',$subfield->sublabel); 
					if($subfield->formatter) { $arr[$val]['formatter'] = sprintf('%s',$subfield->formatter); }
					if($subfield->width) { $arr[$val]['width'] = sprintf('%s',$subfield->width); }
					if($subfield->align) { $arr[$val]['align'] = sprintf('%s',$subfield->align); }
					if($subfield->editor) { $arr[$val]['editor'] = sprintf('%s',$subfield->editor); }
				}
				break;
			}
		}
		return $arr;
	}

	public function get_subform_controller($controller)
	{
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"','formfields','params','controller',$controller);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		$row = $result[0];
		
		$formfields = new SimpleXMLElement($row->formfields);
		foreach ($formfields->field as $field)
		{
			$name = sprintf('%s',$field->name);
			if($field->subform && $field->type=="subform")
			{
				$val = sprintf('%s',$field->subform->subformcontroller);
				$arr[$name] = $val;
			}
		}
		return $arr;
	}

	public function get_xmlfield_data_by_idval($table,$idfield,$idval,$field,$prefix="")
	{
		$querystr = sprintf('SELECT %s FROM %s WHERE %s = "%s"',$field,$table,$idfield,$idval);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		$xml = $result[0]->$field;
		
		$i=0;
		$formfields = new SimpleXMLElement($xml);
		foreach ($formfields->rows->row as $row)
		{
			$rowarr = array();
				
			foreach ($row->children() as $field)
			{
				$id  = sprintf('%s',$field->getName() );
				$val = sprintf('%s',$row->$id );
				$index = $prefix.$id;
				$rowarr[$index] = $val;
			}
			$arr[$i] = $rowarr;
			$i++;
		}
		return $arr;
	}
	
	public function get_subform_view_records($controller,$idfield,$idval,$current_no,$table_type,&$lbl)
	{
		$labels = array();
		$param	= $this->get_controller_params($controller);
		$columnfield = $this->get_subform_fields($controller,$labels);

		if($table_type)
		{
			if($table_type == "live"){ $table = $param['tb_live']; }else if($table_type == "inau"){	$table = $param['tb_inau'];}else if($table_type == "hist"){ $table = $param['tb_hist'];}
			$result = $this->get_recs_by_subform($table,$columnfield,$idfield,$idval,$current_no);
		}
		else
		{	/*default action*/
			/* for subrecs in inau first*/
			$table = $param['tb_inau'];
			if(!($result = $this->get_recs_by_subform($table,$columnfield,$idfield,$idval,$current_no)))
			{
				/*if no inau records found, check live*/
				$table = $param['tb_live'];
				$result = $this->get_recs_by_subform($table,$columnfield,$idfield,$idval,$current_no);
			}
		}
		$lbl = $labels;
		return $result;
	}

	public function create_blank_record($tb_live,$tb_inau)
	{
		$str = '<div class="frmmsg">An Error Occurred, Please Try Again.</div>';
				
		$querystr = sprintf('SELECT counter FROM _sys_autoids WHERE tb_inau = "%s"',$tb_inau);
		if($result = $this->db->query(Database::SELECT,$querystr,TRUE))
		{
			$row_1 = $result[0];
			if(isset($row_1->counter))
			{
				$counter = $row_1->counter;
				while($this->record_exist($tb_live,"id",$counter,$counter) || $this->record_exist($tb_inau,"id",$counter,$counter))
				{
					$counter++;
				}
				
				$querystr = sprintf('UPDATE _sys_autoids set counter = "%s" WHERE tb_inau = "%s"',$counter,$tb_inau);
				if($result = $this->execute_non_select_query(Database::INSERT,$querystr))
				{
					$querystr = sprintf('SELECT counter FROM _sys_autoids WHERE tb_inau = "%s"',$tb_inau);
					if($result = $this->db->query(Database::SELECT,$querystr,TRUE))
					{
						$row_2 = $result[0];
						$counter = $row_2->counter;
					}
					else
					{
						$this->set_db_err_msg($str);
						return NULL;
					}
				}
				else
				{
					$this->set_db_err_msg($str);
					return NULL;
				}
				
				$querystr = sprintf('INSERT into `%s` (`id`,`current_no`) VALUES("%s","0")',$tb_inau,$counter);	
				if($result = $this->execute_non_select_query(Database::INSERT,$querystr))
				{
					$query = sprintf('SELECT * FROM `%s` WHERE id = "%s"',$tb_inau,$counter);
					$result = $this->db->query(Database::SELECT,$query);
					if($row = $result[0])
					{
						return $row;
					}
				}
			}
			else
			{
				$querystr = sprintf('INSERT into `%s` (`current_no`) VALUES("0")',$tb_inau);	
				if($result = $this->execute_non_select_query(Database::INSERT,$querystr))
				{
					$querystr = sprintf('SELECT * FROM `%s` ORDER BY id DESC LIMIT 1',$tb_inau);
					$result = $this->db->query(Database::SELECT,$querystr,TRUE);
					if($row = $result[0])
					{
						return $row;
					}
				}
			}
		}
		$this->set_db_err_msg($str);
		return NULL;
	}

	public function create_blank_record_on_db_auto_increment_table($tb_inau)
	{
		$querystr = sprintf('INSERT INTO `%s` (`current_no`) VALUES("1")',$tb_inau);	
		if($result = $this->execute_non_select_query(Database::INSERT,$querystr))
		{
			$querystr = sprintf('SELECT * FROM `%s` ORDER BY id DESC LIMIT 1',$tb_inau);
			$result = $this->db->query(Database::SELECT,$querystr,TRUE);
			if($row = $result[0])
			{
				return $row;
			}
		}
		$str = '<div class="frmmsg">An Error Occurred, Please Try Again.</div>';
		$this->set_db_err_msg($str);
		return NULL;
	}
	
	public function set_record_lock($idname,$locktable,$rec_id,$pre_status)
	{
		$querystr = sprintf('INSERT INTO `recordlocks` (`idname`,`lock_table`,`record_id`,`pre_status`) VALUES("%s","%s","%s","%s")',$idname,$locktable,$rec_id,$pre_status);	
		$result = $this->execute_non_select_query(Database::INSERT,$querystr);
		return $result;
	}

	public function set_record_lock_by_id($lock_id,$idname,$locktable,$rec_id,$pre_status)
	{
		$querystr = sprintf('INSERT INTO `recordlocks` (`id`,`idname`,`lock_table`,`record_id`,`pre_status`) VALUES("%s","%s","%s","%s","%s")',$lock_id,$idname,$locktable,$rec_id,$pre_status);	
		$result = $this->execute_non_select_query(Database::INSERT,$querystr);
		return $result;
	}
	
	public function get_record_lock($idname,$locktable,$rec_id)
	{
		$querystr = sprintf('SELECT * FROM `recordlocks` WHERE `idname`="%s" AND `lock_table`="%s" AND `record_id`="%s"',$idname,$locktable,$rec_id);	
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		if ($row = $result[0]){return $row;} else {return FALSE;} 
	}
	
	public function get_record_lock_by_id($id)
	{
		$querystr = sprintf('SELECT * FROM `recordlocks` WHERE `id`="%s"',$id);	
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		if ($row = $result[0]){return $row;} else {return FALSE;} 
	}

	public function remove_record_lock($idname,$locktable,$rec_id)
	{
		$querystr = sprintf('DELETE FROM `recordlocks` WHERE `idname`="%s" AND `lock_table`="%s" AND `record_id`="%s"',$idname,$locktable,$rec_id);	
		$result = $this->execute_non_select_query(Database::DELETE,$querystr);
		return $result;
	}
	
	public function remove_record_lock_by_id($id)
	{
		$querystr = sprintf('DELETE FROM `recordlocks` WHERE `id`="%s"',$id);	
		$result = $this->execute_non_select_query(Database::DELETE,$querystr);
		return $result;
	}

	public function delete_record_by_id($table,$id)
	{
		$querystr = sprintf('DELETE FROM `%s` WHERE `id`="%s"',$table,$id);			
		$result = $this->execute_non_select_query(Database::DELETE,$querystr);
		return $result;
	}

	public function delete_record_by_field_value($table,$field,$fieldval)
	{
		$querystr = sprintf('DELETE FROM `%s` WHERE `%s`="%s"',$table,$field,$fieldval);	
		$result = $this->execute_non_select_query(Database::DELETE,$querystr);
		return $result;
	}

	public function insert_from_table_to_table($table_into,$table_FROM,$id)
	{
		$querystr = sprintf('INSERT into %s SELECT * FROM %s WHERE id="%s"',$table_into,$table_FROM,$id);	
		$result = $this->execute_non_select_query(Database::INSERT,$querystr);
		return $result;;
	}

	public function set_record_status($table,$id,$status)
	{
 		$querystr = sprintf('UPDATE `%s` set record_status = "%s" WHERE id="%s"',$table,$status,$id);
		$result = $this->execute_non_select_query(Database::UPDATE,$querystr);
		return $result;
	}
	
	public function set_record_status_by_fieldvalue($table,$field,$fieldval,$status)
	{
 		$querystr = sprintf('UPDATE `%s` SET record_status = "%s" WHERE %s="%s"',$table,$status,$field,$fieldval);		
		$result = $this->execute_non_select_query(Database::UPDATE,$querystr);
		return $result;
	}

	function set_record_status_hist($table,$id,$current_no,$status='HIST')
	{
		$querystr = sprintf('UPDATE `%s` SET record_status = "%s" WHERE id="%s" AND current_no="%s"',$table,$status,$id,$current_no);
		$result = $this->execute_non_select_query(Database::UPDATE,$querystr);
		return $result;
	}

	public function increment_current_no($table,$id)
	{
 		$querystr = sprintf('UPDATE `%s` SET current_no+1 WHERE id="%s"',$table,$id);
		$result = $this->execute_non_select_query(Database::UPDATE,$querystr);
		return $result;
	}

	public function is_record_locked($locktable,$rec_id)
	{
		$querystr = sprintf('SELECT idname,lock_table,record_id,pre_status FROM recordlocks WHERE lock_table="%s" AND record_id="%s"',$locktable,$rec_id);	
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		if ($row = $result[0])
		{ return $row; } else { return FALSE; } 
	}
	
	public function is_record_locked_by_id($id)
	{
		$querystr = sprintf('SELECT idname,lock_table,record_id,pre_status FROM recordlocks WHERE id="%s"',$id);	
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		if ($row = $result[0]){ return $row; } else { return FALSE; } 
	}

	public function update_record($table,$arr)
	{
		$vals = '';
		foreach($arr as $key => $value)
		{
			if(!($key=='id')) {$vals .= "`".$key."`".'="'.$value.'",';}
		}
		$vals = substr($vals,0,-1);
		$querystr = sprintf('UPDATE `%s` set %s WHERE `id` = %s',$table,$vals,$arr['id']);
		$result = $this->execute_non_select_query(Database::UPDATE,$querystr);
		return $result;
	}

	public function update_record_dual_key($table,$arr,$field1,$field2,$value1,$value2)
	{
		$vals = '';
		foreach($arr as $key => $value)
		{
			if(!($key=='id') || !($key==$field1) || !($key==$$field2)) {$vals .= "`".$key."`".'="'.$value.'",';}
		}
		$vals = substr($vals,0,-1);
		$querystr = sprintf('UPDATE `%s` set %s WHERE `%s` = "%s" AND  `%s` = "%s"' ,$table,$vals,$field1,$value1,$field2,$value2);
		$result = $this->execute_non_select_query(Database::UPDATE,$querystr);
		return $result;
	}

	public function insert_record($table,$arr)
	{
		$vals = '';
		$fields = '';
		foreach($arr as $key => $value)
		{
			$fields .= "`".$key."`,";
			$vals .= '"'.$value.'",';
		}
		$vals = substr($vals,0,-1);
		$fields = substr($fields,0,-1);
		$querystr = sprintf('INSERT into `%s` (%s) VALUES(%s)',$table,$fields,$vals);			
//print "<b>[DEBUG]---></b> "; print($querystr); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		$result = $this->execute_non_select_query(Database::INSERT,$querystr);
		return $result;
	}
	
	public function get_subform_records($table,$field,$fieldval)
	{	
		$querystr = sprintf('SELECT * FROM %s WHERE %s="%s"',$table,$field,$fieldval);		
		$arr = $this->execute_select_query($querystr);
		return $arr;
	}

	public function get_messages($table,$type,$idname)
	{
	
		$querystr = sprintf('SELECT id,vw,recipient,sender,subject,input_date,auth_date,record_status,current_no FROM %s WHERE %s = "%s" order by id desc',$table,$type,$idname);
        $idField = 'id';
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$arr = array();
		foreach ($result as $row)
		{
			$arr[$row->$idField] = $row;
		}
		return $arr;
	}

	public function get_user_enquiry_tables($user)
	{
		$querystr = sprintf('SELECT url_input,module,label_input FROM menudefs_users WHERE inputter="%s" AND (url_input !="" || url_input !=NULL) AND url_input NOT LIKE "%senquiry%s" ORDER BY  url_input;',$user,"%","%");
		$arr = $this->execute_select_query($querystr);
		return $arr;
	}
	
	public function get_record_count($table,$where="")
	{
		$querystr = sprintf('SELECT COUNT(id) AS count FROM %s %s;',$table,$where);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$row = $result[0];
		return $row->count;
	}

	public function get_record_count_unread_messages($table,$idname)
	{
		$querystr = sprintf('SELECT COUNT(id) AS count FROM %s WHERE vw="N" AND recipient="%s";',$table,$idname);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$row = $result[0];
		return $row->count;
	}

	public function get_record_count_unsent_messages($table,$idname)
	{
		$querystr = sprintf('SELECT COUNT(id) AS count FROM %s WHERE sender ="%s" OR inputter="%s";',$table,$idname,$idname);
		$result = $this->db->query(Database::SELECT,$querystr,TRUE);
		$row = $result[0];
		return $row->count;
	}

} // End Site