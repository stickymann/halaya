<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Returns AJAX request to javascripts. 
 *
 * $Id: Dbreqs.php 2013-01-01 00:00:00 dnesbit $
 *
 * @package		Soulmap
 * @module	    lovecenter
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license     
 */

define("DELIMITER","{##}");

class Controller_Soulmap_Dbreqs extends Controller
{
	public function before()
    {
		$this->sitedb = new Model_SiteDB;
		$this->enqdb = new Model_EnqDB;
		$this->paramkey = $this->sitedb->get_param_keys();
	}

	public function action_index()
	{
		$limit = "limit 500";
		$option = $_REQUEST['option'];
		switch($option)
		{
			case 'contactplusid':
				$id 		= $_REQUEST['id'];
				$contact_id	= $_REQUEST['contact_id'];
				$plus		= $_REQUEST['plus'];
				$controller = $_REQUEST['controller'];
				$RESULT		= $this->get_contactplus_id($id,$contact_id,$plus,$controller);
				print $RESULT;
			break;
		
		}
	}
	
	function get_contactplus_id($id,$contact_id,$plus,$controller)
	{
		$contactplus_id = "";

		$querystr = sprintf('select indexfield,tb_live,tb_inau from params where controller = "%s"',$controller);
		if($result = $this->sitedb->execute_select_query($querystr))
		{
			$table = $result[0]->tb_live;
			$table_is = $result[0]->tb_inau;
			$field = $result[0]->indexfield;
		
			$C = $contact_id;
			$P = $plus;
			$N = "01";
			$contactplus_id = strtoupper($C.$P.$N);
		
			while($this->sitedb->is_duplicate_unique_id($table,$field,$id,$contactplus_id) || $this->sitedb->is_duplicate_unique_id($table_is,$field,$id,$contactplus_id))
			{
				$N++;
				$N = str_pad($N, 2, "0", STR_PAD_LEFT);
				$contactplus_id = strtoupper($C.$P.$N);
			}
		}
		return $contactplus_id;
	}

} // End Soulmap_Dbreqs
