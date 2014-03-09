<!DOCTYPE html>
<html lang="en">
<head>
<?php
	print $title;
	print $head; 
?>
</head>
<body >
<div id="cmdbox">
	<table border="0" cellspacing="0" cellpadding="1">
		<tr><td><b>User Id :</b></td><td align="left"><?php print $idname; ?></td></tr>
		<tr><td><b>Signon Name :</b></td><td align="left"><?php print $username; ?></td></tr>
		<tr><td><b>App Version :</b></td><td align="left"><?php print $app_version; ?></td></tr> 
		<tr><td><b>DB Version :</b></td><td align="left"><?php print $db_version; ?></td></tr> 
		<tr><td><b>Environment :</b></td><td align="left"><?php print $environment; ?></td></tr>
	</table>
</div>
<style>
#notify-container { border: #000 solid 1px; background: #ebf2f9; margin: 1px 0px 0px 0px; padding: 5px 10px 25px 10px; }
#notify-label { 
	border: green solid 0px;  font-family: verdana, arial, helvetica, sans-serif; font-size: 1em; 
	color: #0000ad; font-size: 1em; font-weight: bold; text-align: left;
	width: 120px; float: left;
}
#notify-circle {
    border: green solid 0px; background: transparent;
    width: 35px; height: 35px;
    border-radius:50%;
    color: #fff; font-size: 2.5em; font-weight: bold; text-align: center;
    font-family: "courier new"; line-height: 1.5em;
	float: left;
}
</style>
<div id="notify-container">&nbsp
<div id="notify-label">Order Entry<br>Uploads Pending</div>
<div id="notify-circle"></div>
</div>
</body>
</html>   
