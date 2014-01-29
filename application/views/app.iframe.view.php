<!DOCTYPE html>
<html lang="en">
<head>
<?php
	print $title;
	print $head; 
	$orgname = sprintf('title="%s"',$orgname);
?>
</head>
	<body class="easyui-layout">
		<div region="west" split="true" <?php print $orgname; ?> style="width:250px;overflow:hidden;">
			<div class="easyui-layout" fit="true">
				<div region="north" border="false" split="false" title="" style="height:56px;overflow:hidden;">
					<iframe src="core_logo" style="width:100%;height:100%" frameborder="0" scrolling="no"></iframe>
				</div>
				<div region="center" border="false" split="false" title=" " style="overflow:hidden;">
					<iframe src="core_menuuser" style="width:100%;height:100%" frameborder="0" scrolling="auto"></iframe>
				</div>
				<div region="south" border="true" split="true" title=" " style="height:200px;overflow:hidden;">
					<iframe src="core_cmdbox" style="width:100%;height:100%" frameborder="0" scrolling="auto"></iframe>
				</div>
			</div>
		</div>
		<div region="center" title="" style="overflow:hidden;">
			<div class="easyui-layout" fit="true">
				<div region="north" split="true" title=" " style="height:230px;overflow:hidden;">
					<iframe src="core_useraccount_message/inbox" name="enquiry" style="width:100%;height:100%" frameborder="0" scrolling="auto"></iframe>
				</div>
				<div region="center" split="true" title="" style="overflow:hidden;">
					<iframe src="core_useraccount_message" name="input" style="width:100%;height:100%;color:green" frameborder="0" scrolling="auto"></iframe>
				</div>
			</div>
		</div>	
	</body>
</html>
