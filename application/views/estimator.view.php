<div id="page">
<div id="pageheader" class="window">
<?php
	print $pageheader;
?>
</div>
<div id="pagebody">
<div id="t">
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

