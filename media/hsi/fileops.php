<?php
/**
 * Filesystem operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: fileops.php 2013-12-14 12:57:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */

define("INVENTORY_COMMA_COUNT",4);
define("INVENTORY_INVERT_COUNT",10); //(INVENTORY_COMMA_COUNT + 1) * 2
define("INVENTORY_HEADER",'"CODE","DESCRIPTION","AVAILUNITS","TAXABLE","UNITPRICE"');
define("CUSTOMER_COMMA_COUNT",12);
define("CUSTOMER_INVERT_COUNT",24);
define("CUSTOMER_HEADER",'"Name","Contact","eMAIL Address","Address 1","Address 2","Country","Phone Number 1","Phone Number 2","Fax Number","Terms Code","Price Group","Sales Person","Code"');
define("ERRORLOG_PREFIX","errorlog");
define("WRITE_OUT_GOOD_CUSTOMERS",false);
define("MAX_FIELD_LENGTH",39);

class FileOps 
{
	public  $config_import_files = array();
	public $config = null;
	
	public function __construct($config=null)
	{
		if( is_null($config) )
		{
			require_once(dirname(__FILE__).'/hsiconfig.php');
			$cfg = new HSIConfig();
			$this->config = $cfg->get_config();
		}
		else
		{
			$this->config = $config;
		}
	}
	
	public function get_all_filenames_in_directory($path)
	{
	    $filelist = array();
	    if ( $handle = opendir($path) )
	    {
			while (false !== ($entry = readdir($handle))) 
			{
				if ($entry != "." && $entry != "..") 
				{
					array_push ($filelist , $entry);
				}
			}
			closedir($handle);
		}
		return $filelist;
	}
	
	public function read_file_into_array($filepath)
	{
		$data = file($filepath);
		return $data;
	}
	
	public function structure_file_data($filepath)
	{
		//reads in csv file
		$dataraw = $this->read_file_into_array( $filepath );
		$header = trim($dataraw[0]);
		
		//for each line in file, value a placed in array with without enclosing inverted commas
		foreach($dataraw as $key => $value )
		{
			$value = trim($value);
			if($value != $header)
			{
				$fieldvals = explode(',', $value);
				$fixedvals = array();
				$count = 0;
				$arrlen = count($fieldvals);
				$fldval = ""; $nextval = "";
				for($i=0; $i<$arrlen; $i++ )
				{
					$fldval = $fieldvals[$i];
					if( isset($fldval[0]) )
					{
						//if a comma occurs in field value re-join field and field+1
						if($fldval[0] == '"' && $fldval[strlen($fldval) - 1] != '"') 
						{
							if( isset($fieldvals[$i+1]) )
							{
								$nextval = $fieldvals[$i+1];
								if($nextval[0] != '"' && $nextval[strlen($nextval) - 1] == '"')
								{
									$fldval = $fldval.",".$nextval;
									$i++;
								}	 
							}
						}
						//remove enclosing inverted commas from field value
						$fixedvals[$count] = trim( trim($fldval,'"') );
						$count++;
					}
				}
				$datafixed[$key] = $fixedvals;
			}
		}
		return $datafixed;
	}
	
	public function file_date_diff($file)
	{
		// file modification date - today
		$today = date('Y-m-d');
		$filecdate = date('Y-m-d',filemtime($file));
		
		// diff is in seconds 
		$diff = abs( strtotime($today) - strtotime($filecdate) );
			
		$years  = floor($diff / (365*60*60*24)); 
		$months = floor($diff / (30*60*60*24));
		$days = floor($diff / (60*60*24));
		return $days;
	}
		
	public function move_file($src,$dest)
	{
		if(file_exists($src))
		{ 
			if( $src != $dest)
			{
				rename($src,$dest);
			}
		}
	}
	
	public function delete_file($filepath)
	{
		if(file_exists($filepath)){ unlink($filepath); }
	}
	
	public function delete_files_after_days($dir,$lifedays)
	{
		if( is_dir($dir) ) 
		{
			if ($dh = opendir($dir)) 
			{
				while (($file = readdir($dh)) !== false) 
				{
					if ($file != "." && $file != "..")
					{
						if( is_dir($dir.$file) && ($file != "." || $file != ".." ) )
						{
							//delete files from sub-directories if exist
							$next_subdir = $dir.$file."/";
							$this->delete_files_after_days($next_subdir,$lifedays);
						}
						else
						{
							$current_file = $dir.$file;
							$ddiff = $this->file_date_diff($current_file);
							if($ddiff >= $lifedays)
							{
								$this->delete_file($current_file);
							}
						}
					}
				}
			}
		}
	}
	
	public function write_file($filepath,$filedata)
	{
		if( $handle = fopen($filepath, 'w') ) 
		{
			fwrite($handle, $filedata);
			fclose($handle);
			return true;
		}
		return false;
	}
	
	public function append_to_file($filepath,$filedata)
	{
		if( $handle = fopen($filepath, 'a') ) 
		{
			fwrite($handle, $filedata);
			fclose($handle);
			return true;
		}
		return false;
	}
	
	public function process_import_files()
	{
		$filespecs = array();
		$errorlog_prefix = ERRORLOG_PREFIX;
		$filelist = $this->get_all_filenames_in_directory( $this->config['current_import'] );
		foreach( $filelist as $index => $filename )
		{
			$errorlog_prefix = substr( $filename, 0, strlen(ERRORLOG_PREFIX) );
			if( !($errorlog_prefix == ERRORLOG_PREFIX) )
			{
				$specs = array();
				$filepath = $this->config['current_import'].$filename;
				$specs['filename'] = $filename;
				$specs['filepath'] = $filepath;
				$specs = array_merge( $specs, $this->set_filetype($filepath) );
				$filespecs[$index] = $specs;
			}
		}
		return $filespecs;
	}
	
	public function set_filetype($filepath)
	{
		$filetype = "UNKNOWN";
		$errors = array('total_min'=>0,'total_max'=>0,'linenums_min'=>"",'linenums_max'=>"",'errorlines_min'=>"",'errorlines_max'=>"");
		$linenum_min = array(); $linenum_max = array(); $good_customers = ""; $good_customers_count = 0;
		$data = file($filepath);
		foreach($data as $index => $line)
		{
			$line = trim($line);
			if($index == 0)
			{
				if( $line == INVENTORY_HEADER ) 
				{ 
					$filetype = "INVENTORY";
					$errors['errorlines_min'] = INVENTORY_HEADER."\r\n";
					$errors['errorlines_max'] = INVENTORY_HEADER."\r\n";
				} 
				else if ( $line == CUSTOMER_HEADER ) 
				{ 
					$filetype = "CUSTOMER";
					$errors['errorlines_min'] = CUSTOMER_HEADER."\r\n";
					$errors['errorlines_max'] = CUSTOMER_HEADER."\r\n";
					$good_customers = CUSTOMER_HEADER."\r\n";
				}
			}
			
			$line_comma_count = substr_count($line, ',');
			$line_invert_count = substr_count($line, '"');
			if( $filetype == "INVENTORY" && $index > 0 ) 
			{ 
				if( !($line_comma_count == INVENTORY_COMMA_COUNT && $line_invert_count == INVENTORY_INVERT_COUNT) )
				{
					$errors['total_min']++; $errors['total_max']++; 
					$linenum_min[ $errors['total_min'] ] = sprintf("%s",$index+1);
					$linenum_max[ $errors['total_max'] ] = sprintf("%s",$index+1);
					$errors['errorlines_min'] .= $line."\r\n";
					$errors['errorlines_max'] .= $line."\r\n";
				}
			} 
			else if ( $filetype == "CUSTOMER" && $index > 0 ) 
			{ 
				$warning_error = FALSE;
				$faillist_error = FALSE;
				
				$data_r = array('abbr' => "AVE", 'baseword' => "AVENUE", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "CO", 'baseword' => "COMPANY", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "COR", 'baseword' => "CORNER", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "ENT", 'baseword' => "ENTERPRISES", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
		
				$data_r = array('abbr' => "ELEC", 'baseword' => "ELECTRICAL", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "H/WARE", 'baseword' => "HARDWARE", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "HWY", 'baseword' => "HIGHWAY", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
		
				$data_r = array('abbr' => "JNCT", 'baseword' => "JUNCTION", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "LTD", 'baseword' => "LIMITED", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
				
				$data_r = array('abbr' => "RD", 'baseword' => "ROAD", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "SUPP", 'baseword' => "SUPPLIES", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "VILL", 'baseword' => "VILLAGE", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				if( preg_match('/\bST\s/i',$line) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "STR", 'baseword' => "STREET", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "EMR", 'baseword' => "EASTERN MAIN ROAD", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "E.M.R.", 'baseword' => "EASTERN MAIN ROAD", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "SMR", 'baseword' => "SOUTHERN MAIN ROAD", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "S.M.R.", 'baseword' => "SOUTHERN MAIN ROAD", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "WMR", 'baseword' => "WESTERN MAIN ROAD", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				$data_r = array('abbr' => "W.M.R.", 'baseword' => "WESTERN MAIN ROAD", 'maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_abbreviation($data_r) ) { $warning_error = TRUE; }
	
				if( preg_match('/L\.P\./i',$line) || preg_match('/LP\./i',$line) || preg_match('/LP#/i',$line)) { $warning_error = TRUE; }
	
				$data_r = array('maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_lightpole_number($data_r) ) { $warning_error = TRUE; }
	
				if( preg_match('/\bPRINCESS TOWN\b/i',$line) ) { $warning_error = TRUE; }
				
				if( preg_match('/\bMAROUGA\b/i',$line) ) { $warning_error = TRUE; }
				 
				if( preg_match('/\bSAN-FERNANDO\b/i',$line) ) { $warning_error = TRUE; }
				
				if( preg_match('/\bMIAN RD\b/i',$line) || preg_match('/\bMIAN ROAD\b/i',$line) ) { $warning_error = TRUE; }
				
				if( preg_match('/#\D/i',$line) ){ $warning_error = TRUE; }
				
				$data_r = array('maxfldlen' => MAX_FIELD_LENGTH, 'line' => trim($line) );
				if( !$this->is_valid_customer_id($data_r) ) { $faillist_error = TRUE; }
				if( !$this->is_valid_salesperson($data_r) ) { $faillist_error = TRUE; }
				if( $this->is_two_digit_salesperson($data_r) ) { $warning_error = TRUE; }
								
				if( $faillist_error || $warning_error || !($line_comma_count == CUSTOMER_COMMA_COUNT && $line_invert_count == CUSTOMER_INVERT_COUNT) )
				{
					if( $warning_error )
					{
						$errors['total_max']++;
						$linenum_max[ $errors['total_max'] ] = sprintf("%s",$index+1); 
						$errors['errorlines_max'] .= $line."\r\n";
					}
				
					if( (!($line_comma_count == CUSTOMER_COMMA_COUNT && $line_invert_count == CUSTOMER_INVERT_COUNT) || $faillist_error) && !$warning_error )
					{
						$errors['total_min']++; 
						//$errors['total_max']++; 
						$linenum_min[ $errors['total_min'] ] = sprintf("%s",$index+1);
						///$linenum_max[ $errors['total_max'] ] = sprintf("%s",$index+1);
						$errors['errorlines_min'] .= $line."\r\n";
					}
				}
				else
				{
					$good_customers_count++;
					$good_customers .= $line."\r\n";
				}
			}
		}
		
		if( $linenum_min ) { $errors['linenums_min'] = join(',',$linenum_min); }
		if( $linenum_max ) { $errors['linenums_max'] = join(',',$linenum_max); }
		
		if( WRITE_OUT_GOOD_CUSTOMERS && $good_customers_count > 0)
		{
			$this->write_out_good_customers($good_customers);
		}
		
		$arr = array();
		$arr['filetype'] = $filetype;
		$arr['errors'] = $errors;
		return $arr;
	}
	
	public function write_errorlog_import_files(&$filespecs,$delete_bad_files=false)
	{
		$datestr = date('YmdHis');
		$current_import_dir = $this->config['current_import'];
		foreach($filespecs as $index => $specs)
		{
			if( $specs['filetype'] == "UNKNOWN" )
			{
				$filepath = sprintf("%s%s_%s.%s-FORMAT[ %s ].txt",$current_import_dir,ERRORLOG_PREFIX,$datestr,$specs['filetype'],$specs['filename']);
				if( $delete_bad_files ) { $this->move_file($specs['filepath'],$filepath); }
			}
			else
			{			
				if( $specs['filetype'] == "CUSTOMER" )
				{
					$filepath = sprintf("%s%s_%s.%s.WARNINGS[ %s ].txt",$current_import_dir,ERRORLOG_PREFIX,$datestr,$specs['filetype'],$specs['filename']);
					if( $specs['errors']['total_max'] > 0 )
					{
						$this->write_file($filepath,$specs['errors']['errorlines_max']);
					}
				}
				$filepath = sprintf("%s%s_%s.%s.FAILLIST[ %s ].txt",$current_import_dir,ERRORLOG_PREFIX,$datestr,$specs['filetype'],$specs['filename']);
				if( $specs['errors']['total_min'] > 0 )
				{
					$this->write_file($filepath,$specs['errors']['errorlines_min']);
					if( $delete_bad_files ) { $this->delete_file( $specs['filepath'] ); }
					$filespecs[$index]['filetype']  = $specs['filetype']."-ERR";
				}
			}
		}
	}
		
	public function write_out_good_customers($good_customers)
	{
		$current_import_dir = $this->config['current_import'];
		$filepath = sprintf("%scustomers_with_valid_data.txt",$current_import_dir);
		$this->write_file($filepath,$good_customers);
	}
	
	private function is_valid_abbreviation($data_r)
	{
		$valid = true;
		// fix abreviaions for matching
		$abbr = str_replace(".","\.",$data_r['abbr']);
		$abbr = str_replace("/","\/",$data_r['abbr']);
		
		/*
		$abbr = str_replace("$","\$",$data_r['abbr']);
		$abbr = str_replace("^","\^",$data_r['abbr']);	
		$abbr = str_replace("*","\*",$data_r['abbr']);
		$abbr = str_replace("?","\?",$data_r['abbr']);
		$abbr = str_replace("+","\+",$data_r['abbr']);
		*/
		if( preg_match('/\b'.$abbr.'\b/i', $data_r['line']) || preg_match('/\b'.$abbr.'[;]/i',$data_r['line']) )
		{
			$BASEWORD_CHAR_COUNT = strlen( $data_r['baseword'] );
			$field_r = explode(',', $data_r['line']);
			foreach($field_r as $index => $field)
			{
				$field = trim($field,'"');
				$FIELD_LENGTH = strlen($field);
				if( $index == 0 || $index == 3 || $index == 4 || $index == 5 )
				{
					if( preg_match('/\b'.$abbr.'$/i',$field) || preg_match('/\b'.$abbr.'\s/i',$field) || preg_match('/\b'.$abbr.'[;]/i',$field) )
					{
						$fixed_field_length = $FIELD_LENGTH - strlen( $data_r['abbr'] ) + $BASEWORD_CHAR_COUNT;
						if( $fixed_field_length <= $data_r['maxfldlen'] )
						{
							$valid = FALSE;
						}
					}
					else if( preg_match('/\b'.$abbr.'[.]$/i',$field) || preg_match('/\b'.$abbr.'[.]\s/i',$field) )
					{
						$fixed_field_length = $FIELD_LENGTH - ( strlen($data_r['abbr']) + 1 ) + $BASEWORD_CHAR_COUNT;
						if( $fixed_field_length <= $data_r['maxfldlen'] )
						{
							$valid = FALSE;
						}
					}
				}
			}
		}
		return $valid;
	}

	public function is_valid_lightpole_number($data_r)
	{
		$valid = TRUE;
		if( preg_match('/\bLP\b/i', $data_r['line']) )
		{
			$field_r = explode(',', $data_r['line']);
			foreach($field_r as $index => $field)
			{
				$field = trim($field,'"');
				$FIELD_LENGTH = strlen($field);
				if( $index == 3 || $index == 4 || $index == 5 )
				{
					if( preg_match('/LP\s[#]\d+[;]/i',$field,$output_r) || preg_match('/LP\s[#]\d+-\d+[;]/i',$field,$output_r) )
					{
						$valid = TRUE;
					}
					else if( preg_match('/\bLP\b/i', $field) )
					{
						$valid = FALSE;
					}
				}
			}
		}
		return $valid;
	}
	
	public function is_valid_customer_id($data_r)
	{
		$valid = true;
		$field_r = explode(',', $data_r['line']);
		$field = trim($field_r[12],'"');

		if( preg_match('/\d{2}[A-Z]{3}\d{5}/i',$field,$output_r) || preg_match('/\A\d{1,10}\z/i',$field,$output_r) )
		{
			$valid = false;
		}
		return $valid;
	}
	
	public function is_valid_salesperson($data_r)
	{
		$valid = false;
		$field_r = explode(',', $data_r['line']);
		$field = trim($field_r[11],'"');
		$FIELD_LENGTH = strlen($field);
		
		if( (preg_match('/\d{2}[A-Z]{2}/i',$field,$output_r) || preg_match('/d{2}/',$field,$output_r)) && ($FIELD_LENGTH == 2 || $FIELD_LENGTH == 4) )
		{
			$valid = true;
		}
		return $valid;
	}
	
	public function is_two_digit_salesperson($data_r)
	{
		$valid = false;
		$field_r = explode(',', $data_r['line']);
		$field = trim($field_r[11],'"');
		$FIELD_LENGTH = strlen($field);
		
		if( preg_match('/\d{2}/',$field,$output_r) && $FIELD_LENGTH == 2 )
		{
			$valid = true;
		}
		return $valid;
	}

} //End FileOps
