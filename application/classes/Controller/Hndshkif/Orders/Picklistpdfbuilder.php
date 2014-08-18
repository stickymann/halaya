<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Create a pdf document for picklist wioth variable width,height via tcpdf library. 
 *
 * $Id: Pickpdfbuilder.php 2014-08-17 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2014
 * @license   
 */

require_once('media/hsi/printerwriteops.php');

class Controller_Hndshkif_Orders_Picklistpdfbuilder extends Controller
{
	public function __construct()
    {
		if(!Auth::instance()->logged_in())
		{
			Controller_Core_Site::redirect_to_login();	
		}
		//$response = Request::factory('core_pdfbuilder')->execute()->response;
		parent::__construct(Request::initial(),	new Response);
	}

	public function action_index()
    {
		$order_id = $this->request->param('opt');
		$printerwrite = new PrinterWriteOps();
		$printerwrite->create_order_picklist($order_id,false);
		exit();
	}

}//End Controller_Hndshkif_Orders_Picklistpdfbuilder

