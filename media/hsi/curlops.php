<?php
/**
 * Curl operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: curlops.php 2013-09-13 16:15:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class CurlOps 
{
	private $hs_apikey = "";
	
	public function __construct($config = null)
	{
		if($config)
		{
			$this->hs_apikey = $config['hs_apikey'];
		}
	}
	
	public function get_remote_data($url,&$status,$use_password=true)
	{
		$curl = curl_init($url);
//print "<b>[DEBUG]---></b> "; print($url); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		curl_setopt($curl, CURLOPT_VERBOSE, 0);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
		if( $use_password ) { curl_setopt($curl, CURLOPT_USERPWD, $this->hs_apikey.':x'); }
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

		/*
		 * If you're running curl 7.35.0 and run into this error in php when trying to connect to a remote host:
		 * 35 - error:14077410:SSL routines:SSL23_GET_SERVER_HELLO:sslv3 alert handshake failure
		 * uncomment the following curl_setopt lines
		 */
		curl_setopt($curl, CURLOPT_SSLVERSION, 3);
		curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, 'SSLv3');

		$response = curl_exec($curl);
//print "<b>[DEBUG]---></b> "; print htmlspecialchars($xml); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		$status = curl_getinfo($curl);  
//print "<b>[DEBUG]---></b> "; print_r($status); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		curl_close($curl);
		return $response;
	}
	
	public function put_remote_data($url,$data_json,&$status,$password=true)
	{
		//PUT USED TO UPDATE EXISTING RECORDS
		$curl = curl_init($url);
		$fp = fopen('php://temp/maxmemory:256000', 'w');
		if (!$fp) {  die('could not open temp memory data'); }
		fwrite($fp, $data_json);
		fseek($fp, 0);
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curl, CURLOPT_INFILE, $fp); // file pointer
		curl_setopt($curl, CURLOPT_INFILESIZE, strlen($data_json)); 
		
		curl_setopt($curl, CURLOPT_PUT, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json') );
		curl_setopt($curl, CURLOPT_VERBOSE, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if( $use_password ) { curl_setopt($curl, CURLOPT_USERPWD, $this->hs_apikey.':x'); }
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSLVERSION, 3);
		curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, 'SSLv3');
		
		$response = curl_exec($curl);
		$status = curl_getinfo($curl);
//print "[DEBUG]---> "; print ("ERRNO: ".curl_errno($curl));print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );
//print "[DEBUG]---> "; print ("ERROR: ".curl_error($curl));print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );
		curl_close($curl);
		fclose($fp);
		return $response;
	}
	
	public function post_remote_data($url,$data_json,&$status,$password=true)
	{
		//POST USED TO CREATE NEW RECORDS
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POSTFIELDS,$data_json);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json') );
		curl_setopt($curl, CURLOPT_VERBOSE, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if( $use_password ) { curl_setopt($curl, CURLOPT_USERPWD, $this->hs_apikey.':x'); }
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSLVERSION, 3);
		curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, 'SSLv3');
		
		$response = curl_exec($curl);
		$status = curl_getinfo($curl);
//print "[DEBUG]---> "; print ("ERRNO: ".curl_errno($curl));print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );
//print "[DEBUG]---> "; print ("ERROR: ".curl_error($curl));print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );
		curl_close($curl);
		return $response;
	}
	
} // End CurlOps
