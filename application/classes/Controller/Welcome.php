<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller 
{

	public function action_index()
	{
		$this->response->body('<h1>Welcome To Halaya</h1>');
	}

} // End Welcome
