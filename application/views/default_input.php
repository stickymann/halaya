<div id="page">
<div id="pageheader" class="window">
<?php
	print $pageheader;
?>
</div>
<div id="pagebody">
<div id="i">
<?php
	print $pagebody;
?>
</div>
<span id="tmpval"></span>
</div>
</div>

<script>
	$(function() {
		$( "#page" ).resizable();
	});
</script>

