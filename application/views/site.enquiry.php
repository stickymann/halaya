<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <?php print $htmlhead; ?>
</head>
</head>
<body>
<div id="page">
<div id="pagebody">
<div id="e">
<?php
	print $pagebody;
?>
</div>
</div>
</div>

<script>
	$(function() {
		$( "#page" ).resizable();
	});
</script>
</body>
</html>