<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User to user messaging. 
 *
 * $Id: Message.php 2012-12-31 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */
class Controller_Core_Useraccount_Message extends Controller_Core_Site
{   
	//public $template = "template";

	//public function __construct()
    public function before()
	{	
		$this->set_start_controller("message");
		$this->delay_load();
		parent::before();
		$this->param['htmlhead'] .= $this->insert_head_js();
	}	
		
	public function action_index($opt="")
    {
		$this->param['indexfieldvalue'] = strtoupper($opt);
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/message.js') );
	}

	function input_validation()
	{
		$post = $_POST;	
		//validation rules
		$validation = new Validation($post);
		$validation->pre_filter('trim', TRUE);
		$validation->add_rules('id','required','standard_text');
		//$validation->add_rules('vw','required', 'length[1]', 'standard_text');
		$validation->add_rules('recipient','required', 'length[1,50]', 'standard_text');
		$validation->add_rules('sender','required', 'length[1,50]', 'standard_text');	
		$validation->add_rules('subject','required', 'length[1,255]','standard_text');
		$validation->add_rules('body','required', 'length[1,8192]');

		$this->param['isinputvalid'] = $validation->validate();
		$this->param['validatedpost'] = $validation->as_array();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
		
	function delay_load()
	{
		if(substr(getenv("HTTP_REFERER"),-3) == "app") { usleep(3000000); }
	}
	
	function action_inbox()
	{
		$this->param['url'] = $this->param['controller'].'/inbox';
		$this->param['pageheader'] = '<div id="msgcon" >Inbox</div>';
		$arr = $this->param['primarymodel']->get_messages($this->param['tb_live'],'recipient',Auth::instance()->get_user()->idname);
		$this->process_enquiry($arr);
	}
	
	function action_sent()
	{
		$this->param['url'] = $this->param['controller'].'/sent';
		$this->param['pageheader'] = '<div id="msgcon">Sent</div>';
		$arr = $this->param['primarymodel']->get_messages($this->param['tb_live'],'sender',Auth::instance()->get_user()->idname);
		$this->process_enquiry($arr);
	}

	function action_drafts()
	{	
		$this->param['url'] = $this->param['controller'].'/drafts';
		$this->param['pageheader'] = '<div id="msgcon">Drafts</div>';
		$arr = $this->param['primarymodel']->get_messages($this->param['tb_inau'],'sender',Auth::instance()->get_user()->idname);
		$this->process_enquiry($arr);
	}

	function view_pre_open_existing_record()
	{
		$arr=$this->param['primarymodel']->get_record_by_id($this->param['tb_live'],$this->param['indexfield'],$this->param['indexfieldvalue'],$this->param['defaultlookupfields']);
		if (isset ($arr->recipient))
		{
			if($arr->recipient == Auth::instance()->get_user()->idname )
			{
				$querystr = sprintf('UPDATE %s SET vw="Y" WHERE %s = "%s"',$this->param['tb_live'],$this->param['indexfield'],$this->param['indexfieldvalue']);
				if($result = $this->param['primarymodel']->execute_update_query($querystr))
				{
					//wait for update to complete, do nothing for now			
				}
			}
		}
	}

} // End Core_Useraccount_Message
