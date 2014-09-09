
$(document).ready(function()
{
	if( $('#current_no').val() == '0' )
	{
		if( $('#request_id').val() == '' )
		{
			 pushrequest.PushRequestID();
		}
	}
});

var pushrequest = new function()
{
	this.PushRequestID = function ()
	{
		ctrlid = $('#id').val();
		params = "option=altid&controller=pushhandshakeinventory&prefix=P2H&ctrlid=" + ctrlid;
		siteutils.runQuery(params,'request_id','val');
	}

}
