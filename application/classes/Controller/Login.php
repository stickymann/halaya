<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Entry point for application. 
 *
 * $Id: Login.php 2012-12-28 00:00:00 dnesbit $
 *
 * @application Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license    
 */
class Controller_Login extends Controller_Include
{
	public $template = 'login.view';

	public function before()
	{
		parent::before();
		$this->template->form = array('username' => '','password' => '');
		$this->template->isLoginOk = FALSE;
		$this->template->status = NULL;
		$this->template->head = $this->get_htmlhead($this->global_app_title);
	}
		
	public function action_index()
    {
		Auth::instance()->logout();
		
		if($this->request->post())
		{
			$myuser = $this->request->post('username');
			$mypass = $this->request->post('password');
	       	$user = ORM::factory('User')->where('username','=',$myuser)->find();
			if(!($user->username == '')) //id == 0, user not found
			{	
		        $exdate = strtotime(str_replace('/','-',$user->expiry_date)); 
				$curdate = strtotime(date("Y/m/d"));
				if($user->enabled && ($exdate > $curdate )) 
				{	
					if(Auth::instance()->login($myuser,$mypass))
					{
						$this->template->isLoginOk = TRUE;
						$this->template->status .= 'Welcome '.$user->username.'<br>';
						$this->template->status .= $user->email.'<br>';
						$this->template->status .= $user->password.'<br>';
						$this->template->status .= 'setting up session<br>';
						$this->template->status .= 'redirecting........<br>';
						HTTP::redirect('app');
					}
					else
					{
						$this->template->status .= 'Invalid password<br>';
					}
				}
				else
				{
					$this->template->status .= 'User account disabled or expired<br>';
				}
            }
			else
			{
				$this->template->status .= 'User account not found<br>';
			}
		}
	}

	function get_htmlhead($title="")
	{	
		$head = sprintf('<title>%s</title>',$title)."\n";
		$head .= sprintf('%s',HTML::style($this->css['jqeasy'], array('screen')))."\n"; 
		if(!$this->template->isLoginOk)
		{
			$head .= sprintf('%s',HTML::script($this->js['jquery']))."\n";
			$head .= sprintf('%s',HTML::script($this->js['jquery_form']))."\n";
			$head .= sprintf('%s',HTML::script($this->js['jqeasy_dropdown']));
		}
		return $head;	
	}

} //End Login
