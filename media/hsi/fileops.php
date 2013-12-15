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
		$dataraw = $this->read_file_into_array( $filepath );
		$header = trim($dataraw[0]);
		$count = 0;
		foreach($dataraw as $key => $value )
		{
			$value = trim($value);
			if($value != $header)
			{
				$fieldvals = explode(',', $value);
//print "<b>[DEBUG]---></b> "; print_r($fieldvals); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
				foreach($fieldvals as $idx => $fldval )
				{
					$fieldvals[$idx] = trim( trim($fldval,'"') );
				}
				$datafixed[$key] = $fieldvals;
			}
			//$count++;
			//if($count == 2) { break; }
		}
		return $datafixed;
	}
}
