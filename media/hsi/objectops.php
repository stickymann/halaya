<?php
/**
 * Object operations for Handshake to DacEasy Interface automation. 
 *
 * $Id: objectops.php 2014-09-14 17:52:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */

require_once(dirname(__FILE__).'/hsiconfig.php');
require_once(dirname(__FILE__).'/dbops.php');
require_once(dirname(__FILE__).'/curlops.php');

class ObjectOps
{
	public $config 	= null;
	public $dbops 	= null;
	public $curlops = null;
	
	public function __construct()
	{
		$cfg	= new HSIConfig();
		$this->config	= $cfg->get_config();
		$this->dbops	= new DbOps($this->config);
		$this->curlops 	= new CurlOps($this->config);
	}
	
	private function process_object_xml($id,$xmldata,$type="string")
	{
		$meta = array(); $total = 0; $faillist = "";
		if( $type == "file" )
		{ 
			$response = simplexml_load_file($xmldata); 
		} 
		else 
		{ 
			$response = simplexml_load_string($xmldata);
		}

		foreach ($response->objects->object as $object)
		{
			$arr = array(); 
			$arr['id']			= $id;
			$arr['hs_name']		= sprintf('%s',$object->name);
			$arr['hs_objid']	= sprintf('%s',$object->objID);
			
			if( $this->dbops->record_exist($this->config['tb_objects'], "id", $id) )
			{ 
				$count = $this->dbops->update_record($this->config['tb_objects'], $arr);
				if($count > 0) { $total = $total + $count; } else { $faillist .= $arr['id'].",";}
			}
		}
		
		$faillist = substr_replace($faillist, '', -1);

		$meta['next']			= sprintf('%s',$response->meta->next);
		$meta['total_count']	= sprintf('%s',$response->meta->total_count);
		$meta['total_inserts']	= $total;
		$meta['previous']		= sprintf('%s',$response->meta->previous);
		$meta['limit']			= sprintf('%s',$response->meta->limit);
		$meta['offset']			= sprintf('%s',$response->meta->offset);
		$meta['faillist']		= $faillist;
		return $meta;
	}
		
	public function update_object_data($mapping_id)
	{
		$RESULT = ""; $total_inserts = 0;
		$querystr = sprintf('SELECT id,mapping_id,hs_object,hs_id FROM %s WHERE mapping_id = "%s"',$this->config['tb_objects'],$mapping_id);
		if( $result = $this->dbops->execute_select_query($querystr) )
		{
			$mapping = $result[0];
			$url = sprintf('%s%s%s?id=%s',$this->config['appurl'],$this->config['hs_apiver'],$mapping['hs_object'],$mapping['hs_id']);
			$GET_REMOTE_DATA = TRUE;
			while( $GET_REMOTE_DATA )
			{
				$xml = $this->curlops->get_remote_data($url,$status);
				if( $status['http_code'] == 200 )
				{
					$meta = $this->process_object_xml($mapping['id'],$xml);
					if( $meta['next'] == "" )
					{
						$GET_REMOTE_DATA = FALSE;
					}
					else
					{
						$url = $this->config['appurl'].$meta['next'];
						$RESULT .= sprintf('Processing records up to offset : %s<br>',$meta['offset']);
						$RESULT .= sprintf('Fail list : %s<br>',$meta['faillist']);
						$RESULT .= sprintf('Records refreshed : %s<br><hr>',$meta['total_inserts']);
						$total_inserts = $total_inserts + $meta['total_inserts'];
					}
				}
				else
				{
					$GET_REMOTE_DATA = FALSE;
				}
			}
			usleep(1000000);
		}
		
		if( $meta )
		{
			$RESULT .= sprintf('Processing records up to offset : %s<br>',$meta['offset']);
			$RESULT .= sprintf('Fail list : %s<br>',$meta['faillist']);
			$RESULT .= sprintf('Records refreshed : %s<br><hr>',$meta['total_inserts']);
			$total_inserts = $total_inserts + $meta['total_inserts'];
	
			$total_count = $meta['total_count']; 
			$total_failed = $total_count - $total_inserts;
			$RESULT .= sprintf('<b>Summary</b><br>Total Download : %s',$total_count);
		}
		return $RESULT;
	} 

} // ObjectOps
