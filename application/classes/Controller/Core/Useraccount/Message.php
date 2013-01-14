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
    public function __construct()
    {
		parent::__construct("message");
		$this->param['htmlhead'] .= $this->insert_head_js();
	}
		
	public function action_index()
    {
		$this->delay_load();
		$this->param['indexfieldvalue'] = strtoupper( $this->request->param('opt') );
		$this->process_index();
	}
	
	function insert_head_js()
	{
		return HTML::script( $this->randomize('media/js/core.message.js') );
	}

	function input_validation()
	{
		$post = $_POST;	
		//validation rules
		array_map('trim',$post);
		$validation = new Validation($post);
		$validation
			->rule('id','not_empty')
			->rule('id','numeric');
		$validation
			->rule('recipient','not_empty')
			->rule('recipient','min_length', array(':value', 1))->rule('recipient','max_length', array(':value', 50));
		$validation
			->rule('sender','not_empty')	
			->rule('sender','min_length', array(':value', 1))->rule('sender','max_length', array(':value', 50));
		$validation
			->rule('subject','not_empty')
			->rule('subject','min_length', array(':value', 1))->rule('subject','max_length', array(':value', 255));
		$validation
			->rule('body','not_empty')
			->rule('body','min_length', array(':value', 1))->rule('subject','max_length', array(':value', 8192));
		
		//->rule('body','regex',array(':value',[^\'\"&]))
		$this->param['isinputvalid'] = $validation->check();
		$this->param['validatedpost'] = $validation->data();
		$this->param['inputerrors'] = (array) $validation->errors($this->param['errormsgfile']);
	}
		
	function delay_load()
	{
		if(substr(getenv("HTTP_REFERER"),-3) == "app") { usleep(3000000); }
	}
	
	function action_inbox()
	{
		$this->param['url'] = $this->param['param_id'].'/inbox';
		$this->param['pageheader'] = '<div id="msgcon" >Inbox</div>';
		$arr = $this->param['primarymodel']->get_messages($this->param['tb_live'],'recipient',Auth::instance()->get_user()->idname);
		$this->process_enquiry($arr);
	}
	
	function action_sent()
	{
		$this->param['url'] = $this->param['param_id'].'/sent';
		$this->param['pageheader'] = '<div id="msgcon">Sent</div>';
		$arr = $this->param['primarymodel']->get_messages($this->param['tb_live'],'sender',Auth::instance()->get_user()->idname);
		$this->process_enquiry($arr);
	}

	function action_drafts()
	{	
		$this->param['url'] = $this->param['param_id'].'/drafts';
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
