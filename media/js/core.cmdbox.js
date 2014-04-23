var bgcolor = ["green","red","black"];
var msg		= "";
var timeout = 5000;

$(document).ready(function()
{
	//cmdbox.doNotification();
});

var cmdbox = new function()
{
	this.getDBReqsURL = function()
	{
		url_dbreqs = siteutils.getBaseURL() + "index.php/<application>_dbreqs?";
		return url_dbreqs; 
	}
	
	this.getNotificationInfo = function()
	{
		qstr = "option=xxxxx";
		$.getJSON(this.getDBReqsURL() + qstr, function(data) 
			{ 
				//set msg
			});
	}
				
	this.displayNotificationInfo = function()
	{
		$('#notify-circle').html(msg);
	
		if(msg == "something" )
		{
			$('#notify-circle').css({ 'background': bgcolor[1] });
			$('#notify-circle').css({ 'color': "white" });
		}
		else if(filecount == 0)
		{
			$('#notify-circle').css({ 'background': bgcolor[0] });
			$('#notify-circle').css({ 'color': "white" });
		}
	}
	
	this.doNotification = function()
	{
		cmdbox.getNotificationInfo();
		cmdbox.displayNotificationInfo();
		setTimeout(cmdbox.doNotification, timeout);
	}
}	
	
