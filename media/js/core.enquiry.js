$(function(){$( "#tabs" ).tabs();});
var searchValue = $('#js_exportid').val();
var currentValue = "";
var delimiter = "{##}";

var enquiry = new function()
{
	this.OpenExportWin = function()
	{
		var csv_id = $('#js_exportid').val();
		var tmparr = csv_id.split(delimiter);
		csv_id = tmparr[0];
		var url = window.siteutils.getBaseURL() + "index.php/csvexport/index/" + csv_id;
		window.open(url,'','width=1,height=1');
	}

	this.GetResults = function()
	{
		var str = $('#controllersel').val();
		var arr = str.split(",");
		var controller = arr[0];
		if(arr[1]=="enquiry"){enqtype = "custom";} else {enqtype = "default";}
		
		if ( $('#enqexport').val() != null)
		{
			if( $('#enqexport').attr("checked")=="checked" ){enqexport = 1;} else {enqexport = 0;}
		}
		else {enqexport = 0;}
		
		if ( $('#fieldnames').val() != null)
		{
			if( $('#fieldnames').attr("checked")=="checked" ){fieldnames= 1;} else {fieldnames = 0;}
		}
		else {fieldnames = 0;}
		
		var user = $('#js_idname').val();		
		var radios = document.getElementById ('radios');
		
		if (radios) 
		{
			var inputs = radios.getElementsByTagName ('input');
			if (inputs) 
			{
				for (var i = 0; i < inputs.length; ++i) 
				{
					if (inputs[i].checked && inputs[i].type=="radio")
					{
						{ 
							tabletype=inputs[i].value; 
							if(tabletype == 'ls'){id = "resultlive"; seltab=1; } 
							else if(tabletype == 'df'){id = "resultlive"; seltab=1; } 
							else if (tabletype == 'is'){id = "resultinau"; seltab=2;} 
							else if (tabletype == 'hs'){id = "resulthist"; seltab=3;}
						}
					}
				}
			}
		}
		
		if (enqexport == 1) {seltab=0;}
		var formfilter = document.getElementById ('formfilter');
		if (formfilter)
		{
			var inputs = formfilter.getElementsByTagName ('input');
			var selects = formfilter.getElementsByTagName ('select');
			var fields='', opvals='', fieldvals='';
			if (inputs) 
			{
				for (var i = 0; i < inputs.length; ++i) 
				{
					fields = fields + inputs[i].id + "," ; 
					fieldvals = fieldvals  + inputs[i].value + ","; 
					opvals = opvals  + selects[i].value + ","; 
				}
			}
		}
		fields = fields.substring(0,fields.lastIndexOf(","));
		fieldvals = fieldvals.substring(0,fieldvals.lastIndexOf(","));
		opvals = opvals.substring(0,opvals.lastIndexOf(","));
		limit = $('#limit').val();
		if(isNaN(limit) || limit==""){ limit = '500';} else {limit = parseInt(limit);}
		var param = 'option=enquiry&controller=' + controller  + '&user=' + user + '&tabletype=' + tabletype + '&export=' + enqexport  + '&limit=' + limit + '&fieldnames=' + fieldnames + '&enqtype=' + enqtype + '&opvals=' + opvals + '&fieldvals=' + fieldvals;
		var tabs = $('#tabs').tabs('tabs');
		$('#tabs').tabs('select', tabs[seltab].panel('options').title);
		var winheight = $(window).height() - 55;
		if( winheight < 50) {winheight = 50;}
		$('#'+id).height(winheight);
		
		if(enqexport)
		{
			searchValue = $('#js_exportid').val();
			setTimeout(enquiry.checkSearchChanged,500);
			window.siteutils.runQuery(param,'js_exportid',"val");
		} 
		else {window.siteutils.runQuery(param,id,"html");}
	}
	
	this.checkSearchChanged = function() 
	{
		var currentValue = $('#js_exportid').val();
		if ((currentValue) && currentValue != searchValue && currentValue != '') 
		{
			searchValue = currentValue;
			enquiry.OpenExportWin();
		} 
		else
		{
			setTimeout(enquiry.checkSearchChanged,500);
		}
	}

	this.makeFilter = function()
	{
		var str = document.getElementById('controllersel').value;
		var user = document.getElementById('js_idname').value;
		var arr = str.split(",");
		var controller = arr[0];
		
		if(arr[1]=="enquiry"){enqtype = "custom";} else {enqtype = "default";}
		param1 = 'option=filterform&controller=' + controller +  '&user=' + user  + '&enqtype='+ enqtype + '&loadfixedvals=1&rochk=0';
		param2 = 'option=enqctrl&controller=' + controller + '&user=' + user;
		setTimeout('window.siteutils.runQuery(param1,"filterform","html")',500);
		window.siteutils.runQuery(param2,"radios","html");
	}
}

	