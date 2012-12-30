<!DOCTYPE html>
<html lang="en">
<head>
<?php
	print $title;
	print $head; 
$HTML=<<<_HTML_
		<script type="text/javascript">
		$(function() {
			$("#tree").treeview({
				collapsed: true,
				animated: "low",
				control:"#sidetreecontrol",
				prerendered: false,
				persist: "menuconf"
			});
		})
		
		</script>
	</head>	
	<body style="margin: 0px 0px 0px 10px;">
	<div id="sidetree">
	<div class="treeheader">&nbsp;</div>
	<div id="sidetreecontrol"> <a href="?#">Collapse All</a> | <a href="?#">Expand All</a> </div>
_HTML_;
	print $HTML;
	print ($usermenu);	
	$HTML=<<<_HTML_
	</body >
	<html>
_HTML_;
	print $HTML;
?>