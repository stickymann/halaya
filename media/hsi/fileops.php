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

//require_once(dirname(__FILE__).'/config/tcpdf_config.php');
class FileOps 
{

	public function __construct()
	{
		
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

} //End FileOps
