$(document).ready(function()
{
	if($('#current_no').val() == '0')
	{
		msg_params = "option=idname";
		siteutils.runQuery(msg_params,"sender","val");
	}
});