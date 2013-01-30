var _checkbox;
var firstload = true;

$(document).ready(function()
{
	$('#roles').focus(function() 
	{
		var ur_params = "";
		var roles  = $('#roles').val();
		siteutils.dialogWindow("chklight",600,300,"Role Selector");
		if(firstload)
		{
			ur_params = "option=userrolechkbox&roles=" + roles;
			siteutils.runQuery(ur_params,"chkresult","html");
			firstload = false;
		}
	});
});
		
var userrole = new function()
{
	this.setCheckBox = function ()	
	{
			//alert("check");
		str = $('#allroles').val();
		_checkbox= str.split(",");
	}

	this.setRoles = function()	
	{
		this.setCheckBox();
		$('#roles').val('');
		len = _checkbox.length;
		for (var key = 0; key < len; key++)
		{
			//alert (_checkbox[key]);
			if(document.getElementById(_checkbox[key]).checked==true)
			{
				roles  = $('#roles').val() + $('#'+_checkbox[key]).val() + ",";
				$('#roles').val(roles);
			}
		}
		str = $('#roles').val();
		str = str.substr(0,str.length-1);
		$('#roles').val(str);
			
	}	
}
