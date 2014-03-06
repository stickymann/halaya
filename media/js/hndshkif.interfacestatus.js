var running = -1;
var ifpid   = "";
var timeout = 5000;

$(document).ready(function()
{
	$('#startstop').click(function(){ interfacestatus.doStartStop(); });
	interfacestatus.updateInterfaceStatus();
});

var interfacestatus = new function()
{
	this.getDBReqsURL = function()
	{
		url_dbreqs = siteutils.getBaseURL() + "index.php/hndshkif_dbreqs?";
		return url_dbreqs; 
	}
	
	this.getInterfaceStatus = function()
	{
		qstr = "option=schedulerstatus";
		$.getJSON(this.getDBReqsURL() + qstr, function(data) 
			{ 
				if( data['id'] == "scheduler" )
				{
					pid = parseInt(data['pid']);
					if( pid > 0 ) { running = 1; ifpid = pid; } 
					else { running = 0; ifpid = ""; }
				}
			});
	}
		
			
	this.displayStatusInfo = function()
	{
		if(running == 1)
		{
			$('#ifstatus').html("RUNNING");
			$('#ifpid').html(ifpid);
			$('#ifstatus').css({ 'color': 'green' });
			$('#startstop').attr('value', 'STOP ');
			$('#startstop').removeAttr('disabled');
		}
		else if (running == 0)
		{
			$('#ifstatus').html("STOPPED");
			$('#ifpid').html(ifpid);
			$('#ifstatus').css({ 'color': 'red' });
			$('#startstop').attr('value', 'START');
			$('#startstop').removeAttr('disabled');
		}
		else
		{
			$('#ifstatus').html("");
			$('#ifpid').html("");
			$('#startstop').attr('value', '       ');
			$('#startstop').attr('disabled','disabled');
		}
	}
	
	this.updateInterfaceStatus = function()
	{
		interfacestatus.getInterfaceStatus(); 
		interfacestatus.displayStatusInfo();
		setTimeout(interfacestatus.updateInterfaceStatus, timeout);
	}
		
	this.doStartStop = function()
	{
		if(running)
		{
			qstr = "option=schedulerstop";
			$.getJSON(this.getDBReqsURL() + qstr, function(data){} );
		}
		else
		{
			qstr = "option=schedulerstart";
			$.getJSON(this.getDBReqsURL() + qstr, function(data){} );
		}
		//interfacestatus.getInterfaceStatus(); 
		//interfacestatus.displayStatusInfo();
	}

}	
	
