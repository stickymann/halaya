<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gets list of sql file  in media/dbvwsql directories 
 * as a replacement database views and complicated SELECTs
 * for fasrer query processing and results
 * 
 * $Id: Dbvwsql.php 2023-05-12 15:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2023
 * @license    
 */
class Controller_Core_Dbvwsql extends Controller
{
    public $sqlfiles_r = array();
	private $ext_r = array("sql");
    private $sqldir = "media/dbvwsql/";
	
	public function __construct()
	{
		//parent::__construct(Request::initial(),	new Response);
        //print $this->sqldir."<br>";
        $this->sqlfiles_r = $this->filesearch($this->sqldir,$this->ext_r);
        //print_r($this->sqlfiles_r);

    }
    /*
    public function action_index()
    {
        //$this->get_sqlfiles($this->sqldir,$this->ext_r);
        //print_r($this->sqlfiles_r);
    }*/
        
    public function get_sqlfiles()
    {
       return  $this->sqlfiles_r;
    }
    
    private function filesearch($search_dir, $ext_r)
    {
        if( is_dir($search_dir) ) {
            if ($dh = opendir($search_dir)) {
                while (($file = readdir($dh)) !== false)  {
                    if ($file != "." && $file != "..") {
                        $fullpath = trim($search_dir.$file);
                        if( is_dir($fullpath) ) {
                            $next_search_dir = trim($search_dir.$file."/");
                            if( array_key_exists($next_search_dir, $this->linkable_r) AND $this->linkable_r[$next_search_dir]) {
                                $this->filesearch($next_search_dir, $ext_r);
                            }
                        } else if( is_file($fullpath) ) {
                            $ext = pathinfo($file, PATHINFO_EXTENSION);
                            $filename = pathinfo($file, PATHINFO_FILENAME);
                            if( in_array($ext, $ext_r) AND  !is_link($fullpath)) { 
                                 $this->sqlfiles_r[$filename] = $fullpath;
                            }
                        }
                    }   
                    
                }
                closedir($dh);
            }
        }
        return $this->sqlfiles_r;
    }
    
} //End Core_Dbvwsql

