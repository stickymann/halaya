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
</body>
</html>   