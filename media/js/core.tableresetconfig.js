
$(document).ready(function()
{
	$('#reset_profile').focus(function()
	{
		tableresetconfig.LoadConfigForm();
	});
});
	
var tableresetconfig = new function()
{
	this.LoadConfigForm = function() 
	{
		var trc_params = "";
		var user  = $('#js_idname').val();
		siteutils.dialogWindow("chklight",600,300,"Reset Configuration");
		trc_params = 'option=trconfig';
		siteutils.runQuery(trc_params,"chkresult","html");
	}
}
		