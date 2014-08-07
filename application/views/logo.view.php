<!DOCTYPE html>
<html lang="en">
<head>
<?php 
	print $title;
	print $head; 
?>		
</head>
<body bgcolor="#ffffff">
	<?php
	$TEXT=<<<_TEXT_
	<div id="banner">
	<table border="0" cellspacing=0 cellpadding=0>
		<tr>
			<td width="75%" align="left"><img src=$logo_app border=0 /></td>
   			<td align="left"><a href="login" target="_parent" title="Log Out"><img src=$img_signout /></td>
		</tr>
	</table>
	</div>
_TEXT_;
	print $TEXT;
	?>
</body>
</html>   