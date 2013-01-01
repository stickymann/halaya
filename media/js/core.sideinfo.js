
var sideinfo = new function()
{
	this.Update = function(fields,table,idfield,retfield,format)
	{
		span_id = retfield + "_sideinfo";
		idval = $('#'+retfield).val();
		si_params =  "option=sideinfo&" + "fields=" + fields + "&" + "table=" + table + "&" + "idfield=" + idfield  + "&" + "idval=" + idval + "&" + "format=" + format;
		window.siteutils.runQuery(si_params,span_id,"html");
	}
}

var sidefunc = new function()
{
	this.Update = function(func,idfield,format)
	{
		var span_id = idfield + "_sidefunc";
		var val = $('#'+idfield).val();
		sf_params =  "option=sidefunc&" + "func=" + func + "&" + "parameter=" + val + "&" + "format=" + format;
		window.siteutils.runQuery(sf_params,span_id,"html");
	
	}
}