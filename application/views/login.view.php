<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php 
	print $title; 
	print $head; 
?> 
</head>
<body>
	<div id="container">
<?php
if(!$isLoginOk)
{
	print "\t".'<div id="signbtn"><a href="login" class="btnsignin">Sign In</a><span class="errmsg">'.$status.'</span></div>'."\n";
	print "\t".'<br><img src="'.$logo_front.'" border=0 align=middle><br>'."\n";
	print "\t".'<div id="frmsignin">'."\n";
    print "\t".Form::open('login',array('autocomplete'=>'off'))."\n";
    print "\t".'<p id="puser">'."\n";
	print "\t".Form::label('username','Username:')."\n";
	print "\t".Form::input('username',$form['username'],array('id'=>'username'))."\n";
	print "\t".'</p><p>'."\n";
	print "\t".Form::label('password','Password:')."\n"; 
	print "\t".Form::password('password',$form['password'],array('id'=>'password'))."\n";
	print "\t".'</p><p class="submit">'."\n";
	print "\t".Form::submit('submitbtn', 'Login',array('id'=>'submitbtn'))."\n";
	print "\t".Form::hidden('status',$status,array('id'=>'status'))."\n";
	print "\t".'</p>'."\n";
	print "\t".Form::close()."\n";
}
	print "\t".'<p id="msg">'.$status.'</p></div></div>'."\n"; 
?>
</body>
</html>


		