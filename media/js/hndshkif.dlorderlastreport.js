var order_id = "";


$(document).ready(function()
{
	$('#batch_date').focus(function() { dlorderlastreport.Dynacombo_BatchId(); });
	$('#batch_date').keyup(function() { dlorderlastreport.Dynacombo_BatchId(); });
});
	
var dlorderlastreport = new function() 
{
	
	this.getDBReqsURL = function()
	{
		url_dbreqs = siteutils.getBaseURL() + "index.php/hndshkif_dbreqs?";
		return url_dbreqs; 
	}
	
	this.runQuery = function(qstr,id,type)
	{
		if(type=="html")
		{
			$.get(this.getDBReqsURL() + qstr, function(data) 
				{ $('#'+id).html(data); });
		}
		else if (type=="val")
		{
			$.get(this.getDBReqsURL() + qstr, function(data) 
				{ $('#'+id).val(data); });
		}
		else if (type=="text")
		{
			$.get(this.getDBReqsURL() + qstr, function(data) 
				{ $('#'+id).text(data); });
		}
		else if (type=="var")
		{
			$.get(this.getDBReqsURL() + qstr, function(data) 
				{ $('#js_tmpvar').val(data); });
		}
	}
		
	this.Dynacombo_BatchId = function ()
	{
		var sid   = "batches";
		var table = "hsi_dlorderbatchs";
		var rfield = "batch_id";
		var sfield = "batch_date";
		var sval   = $('#batch_date').val();
		var chfunc = "dlorderlastreport.LoadBatchReport";		
		//http://localhost/hndshkif/index.php/hndshkif_dbreqs?option=dynacombo&sid=batches&chfunc=dlorderlastreport.LoadBatchReport&table=hsi_dlorderbatchs&rfield=batch_id&sfield=batch_date&sval=2014-08-14
		params = "option=dynacombo" + "&sid=" + sid + "&chfunc=" + chfunc + "&table=" + table + "&rfield=" + rfield + "&sfield=" + sfield + "&sfield=" + sfield + "&sval=" + sval;
		dlorderlastreport.runQuery(params,"dynacombo","html");
	}
	
	this.LoadBatchReport = function ()
	{
		var batch_id   = $('#batches').val();
		console.log(batch_id);

		//load in div
		//$("#div1").load("file1.html");
		
		//load in target
		url = siteutils.getBaseURL() + "index.php/hndshkif_orders_dlorderlastreport?batch_id=" + batch_id;
		window.open(url,"input");
	}
	
	this.PrintDialogOpen = function (idval)
	{
		order_id  = idval;
		$('#chkresult').html("");
		window.siteutils.dialogWindow("chklight",300,150,"Print Picklist - Order# " + order_id );
	}
	
	this.PrintOrder = function ()
	{
		$('#chkresult').append("Picklist for order# " + order_id + " sent to printer<br>");
		params = "option=picklistprint" + "&order_id=" + order_id;
		$.get(this.getDBReqsURL() + params, function(data) {  });
	}

}
