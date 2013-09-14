<?php
/**
 * Curl operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: CurlOps.php 2013-09-13 16:15:46 dnesbit $
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
	
	public function __construct()
	{
		$configfile = dirname(__FILE__).'/hsiconfig.xml';
		try
			{
				//check for required fields in xml file
				$xml = file_get_contents($configfile);
				$config = new SimpleXMLElement($xml);
				if($config->handshake->apikey) { $this->hs_apikey = sprintf('%s',$config->handshake->apikey); }
			}
		catch (Exception $e) 
			{
				$desc='Configuration File Error : '.$e->getMessage();
				print $desc;
			}
	}
	
	public function get_remote_data($url,&$status)
	{
		$curl = curl_init($url);
//print "<b>[DEBUG]---></b> "; print($url); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
		curl_setopt($curl, CURLOPT_USERPWD, $this->hs_apikey.':x');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

		$data = curl_exec($curl);
//print "<b>[DEBUG]---></b> "; print htmlspecialchars($xml); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		$status = curl_getinfo($curl);  
//print "<b>[DEBUG]---></b> "; print_r($status); print( sprintf('<br><b>[line %s - %s, %s]</b><hr>',__LINE__,__FUNCTION__,__FILE__) );
		curl_close($curl);
		return $data;
	}
	
	public function put_remote_data($url,$status)
	{
		//NOTES: http://developers.sugarcrm.com/wordpress/2011/11/22/howto-do-put-requests-with-php-curl-without-writing-to-a-file/
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $closeOppURL);
		curl_setopt($curl, CURLOPT_USERAGENT, 'SugarConnector/1.4');
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($update_json)));
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); 
		curl_setopt($curl, CURLOPT_POSTFIELDS,$update_json);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		
		$curlresult = curl_exec($curl);
		$status = curl_getinfo($curl);
		$curlapierr = curl_errno($curl);
		$curlerrmsg = curl_error($curl);
		curl_close($curl);
	}
	
} // End CurlOps
