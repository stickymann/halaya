var _checkbox;
var InitialValue = $('#chkresult').html();
var searchValue = $('#chkresult').html();
var firstload = true;
		
$(document).ready(function()
{
	$('#securityprofile').focus(function()
	{
		var ra_params = "";
		var securityprofile  = $('#securityprofile').val();
		siteutils.dialogWindow("chklight",940,400,"Role Permissions");
		if(firstload)
		{	
			var spid = $('#name').val();
			var current_no = $('#current_no').val();
			ra_params = "option=roleadminchkbox&spid=" + spid + "&current_no=" + current_no;
			siteutils.runQuery(ra_params,"chkresult","html");
			firstload = false;
		}
	});
});

var roleadmin = new function()
{
	this.UpdateSecurityProfile = function (menu_id,type)
	{
		if(type == "M")
		{
			if($("#"+menu_id).attr("checked")!="checked")
			{
				$("input[id^="+menu_id+"]").prop("checked",false);
				$("input[id^="+menu_id+"]").prop("disabled",true);
				$("input[id="+menu_id+"]").prop("disabled",'');
			}
			else
			{
				$("input[id^="+menu_id+"]").prop("disabled",false);
				$("input[id^="+menu_id+"]").prop("checked",true);
			}
		}
		this.CreateSecurityProfile();
	}

	this.CreateSecurityProfile = function()
	{
		var sphr = "<?xml version='1.0' standalone='yes'?>"+"\\n"+"<securityprofile>"+"\\n";
		var spft = "</securityprofile>";
		var tree = $("#tree li input[name^=menu]");
		var sptxt = "";
			
		tree.each(function()
		{
			var id = $(this).attr("id");
			var ci = "";
			var ce = "";
			if($("#"+id).attr("checked")=="checked")
			{
				var irow = $("#tree li[id="+id+"_li] input[id^="+id+"i]");
				var erow = $("#tree li[id="+id+"_li] input[id^="+id+"e]");
				irow.each(function()
				{
					if($(this).attr("checked")=="checked") {ci = ci + $(this).val() + ",";}
				});
				ci = ci.substr(0,ci.length-1);
									
				erow.each(function()
				{
					if($(this).attr("checked")=="checked") {ce = ce + $(this).val() + ",";}
				});
				ce = ce.substr(0,ce.length-1);
				sptxt = sptxt + "<menu><menu_id>"+id+"</menu_id><controls_input>"+ci+"</controls_input><controls_enquiry>"+ce+"</controls_enquiry></menu>"+"\\n"
			}
		});
		if(sptxt != "") {sptxt = sphr+sptxt+spft;}
		$('#securityprofile').html(sptxt);
	}

	this.SetDisabled = function ()
	{
		var tree = $("#tree li input");
		tree.each(function()
		{
			var id = $(this).attr("id");
			var name = $(this).attr("name");
			var m_name = "menu_" + id;
			var menu_checked = false;
			var row_id = "";

			if( name == m_name)
			{
				if($("#"+id).attr("checked")=="checked")
				{
					menu_checked = true;
					row_id = id;
				}
			}
				
			if($("#"+id).attr("checked")!="checked" && $("#"+id).val()!=0)
			{
				if(!menu_checked)
				{
					$(this).prop("disabled",true);
				}
			}
		});
	}
}
