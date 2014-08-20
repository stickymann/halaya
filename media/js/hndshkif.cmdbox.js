var bgcolor = ["green","red","black"];
var filecount = -2;
var lastfilecount = 0;
var timeout = 5000;


$(document).ready(function()
{
	$('#startstop').click(function(){ interfacestatus.doStartStop(); });
	cmdbox.updateReadyForUploadFileCount();
});

var cmdbox = new function()
{
	this.getDBReqsURL = function()
	{
		url_dbreqs = siteutils.getBaseURL() + "index.php/hndshkif_dbreqs?";
		return url_dbreqs; 
	}
	
	this.getReadyForUploadFileCount = function()
	{
		qstr = "option=uploadfilecount";
		$.getJSON(this.getDBReqsURL() + qstr, function(data) 
			{ 
				filecount = data['count'];
			});
	}
		
			
	this.displayReadyForUploadFileCountInfo = function()
	{
		$('#notify-circle').html(filecount);
		//alert("filecount : "+filecount);
		
		if(filecount > 0)
		{
			$('#notify-circle').css({ 'background': bgcolor[1] });
			$('#notify-circle').css({ 'color': "white" });
		}
		else if(filecount == 0)
		{
			$('#notify-circle').css({ 'background': bgcolor[0] });
			$('#notify-circle').css({ 'color': "white" });
		}
		else if(filecount == -1)
		{
			$('#notify-circle').html("E");
			$('#notify-circle').css({ 'background': bgcolor[2] });
			$('#notify-circle').css({ 'color': "white" });
		}
		
		if( filecount != lastfilecount && filecount > 0 )
		{
			if($('#autorefresh').attr('checked')) 
			{
				window.open("hndshkif_orders_dlorderlastreport","input");
				window.open("hndshkif_orders_dlorderlastreport/customfilter","enquiry");
				lastfilecount = filecount;
			}
		}
		
		if( filecount == 0 ) { lastfilecount = 0; }
	}
	
	this.updateReadyForUploadFileCount = function()
	{
		cmdbox.getReadyForUploadFileCount();
		cmdbox.displayReadyForUploadFileCountInfo();
		setTimeout(cmdbox.updateReadyForUploadFileCount, timeout);
	}
}	
	
