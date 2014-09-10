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
	
	public function process_import_files()
	{
		$errorlog_prefix = ERRORLOG_PREFIX;
		$filespecs = array();
		$filelist = $this->get_all_filenames_in_directory( $this->config['current_import'] );
			
		foreach( $filelist as $index => $filename )
		{
			$errorlog_prefix = substr( $filename, 0, strlen(ERRORLOG_PREFIX) );
			if( !($errorlog_prefix == ERRORLOG_PREFIX) )
			{
				$specs = array();
				$filepath = $this->config['current_import']."/".$filename;
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
				if( !($line_comma_count == INVENTORY_COMMA_COUNT) || !($line_invert_count == INVENTORY_INVERT_COUNT) )
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
				$none_comma_error = false;
				if( 
					( preg_match('/\bAVE\b/i',$line) || preg_match('/\bAVE\.\b/i',$line) ) ||
					( preg_match('/\bLTD\b/i',$line) || preg_match('/\bLTD\.\b/i',$line) ) ||
					( preg_match('/\bLP\b/i',$line) || preg_match('/L\.P\./i',$line) || preg_match('/\bLP\.\b/i',$line) || preg_match('/\bLP#\b/i',$line)) ||
					  preg_match('/\bCO\b/i',$line) ||
					  preg_match('/\bRD\b/i',$line) ||
					( preg_match('/\bST\b/i',$line) && !(preg_match('/\bSTREET\b/i',$line)) ) ||
					  preg_match('/\bCOR\b/i',$line) ||
					  preg_match('/\bHWY\b/i',$line) ||
					( preg_match('/\bSMR\b/i',$line) || preg_match('/\bS\.M\.R\.\b/i',$line) ) ||
					( preg_match('/\bEMR\b/i',$line) || preg_match('/\bE\.M\.R\.\b/i',$line) ) ||
					( preg_match('/\bWMR\b/i',$line) || preg_match('/\bW\.M\.R\.\b/i',$line) ) ||
					  preg_match('/\bPRINCESS\b/i',$line) ||
					( preg_match('/\b#\b/i',$line) || preg_match('/[\s]#[\s]/i',$line) ) ||
					  preg_match('/\.\"/i',$line)
				)
				{
					$none_comma_error = true;
				}
				
				if( $none_comma_error )
				{
					$errors['total_max']++;
					$linenum_max[ $errors['total_max'] ] = sprintf("%s",$index+1); 
					$errors['errorlines_max'] .= $line."\r\n";
				}
				else if( !($line_comma_count == CUSTOMER_COMMA_COUNT) || !($line_invert_count == CUSTOMER_INVERT_COUNT) )
				{
					$errors['total_min']++; $errors['total_max']++; 
					$linenum_min[ $errors['total_min'] ] = sprintf("%s",$index+1);
					$linenum_max[ $errors['total_max'] ] = sprintf("%s",$index+1);
					$errors['errorlines_min'] .= $line."\r\n";
					$errors['errorlines_max'] .= $line."\r\n";
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
	
	public function write_errorlog_import_files($filespecs,$delete_bad_files=false)
	{
		$datestr = date('YmdHis');
		$current_import_dir = $this->config['current_import'];
		foreach($filespecs as $index => $specs)
		{
			if( $specs['filetype'] == "UNKNOWN" )
			{
				if( $delete_bad_files ) { $this->delete_file( $specs['filepath'] ); }
			}
			else
			{			
				if( $specs['filetype'] == "CUSTOMER" )
				{
					$filepath = sprintf("%s/%s_%s.MAX.%s[ %s ].txt",$current_import_dir,ERRORLOG_PREFIX,$datestr,$specs['filetype'],$specs['filename']);
					if( $specs['errors']['total_max'] > 0 )
					{
						$this->write_file($filepath,$specs['errors']['errorlines_max']);
						if( $delete_bad_files ) { $this->delete_file( $specs['filepath'] ); }
					}
				}
				$filepath = sprintf("%s/%s_%s.MIN.%s[ %s ].txt",$current_import_dir,ERRORLOG_PREFIX,$datestr,$specs['filetype'],$specs['filename']);
				if( $specs['errors']['total_min'] > 0 )
				{
					$this->write_file($filepath,$specs['errors']['errorlines_min']);
					if( $delete_bad_files ) { $this->delete_file( $specs['filepath'] ); }
				}
			}
		}
	}
		
	public function write_out_good_customers($good_customers)
	{
		$current_import_dir = $this->config['current_import'];
		$filepath = sprintf("%s/customers_with_valid_data.txt",$current_import_dir);
		$this->write_file($filepath,$good_customers);
	}

} //End FileOps
