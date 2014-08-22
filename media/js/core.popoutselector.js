var po_return_id = "";
var po_lkparam =  "";
var qfldlen = 0;
var nonselect = false;
	
var popout = new function()
{
	this.Update = function(retval)
	{
		var newString = retval.split("_");
		newString = newString.join(" ");
		$('#'+po_return_id).val(newString + "\n");
		$('#'+po_return_id).focus();
		popout.SelectorClose();
	}

	this.SelectorOpen = function (fields,table,idfield,retfield)
	{
		po_return_id = retfield;
		window.siteutils.dialogWindow("light",600,250,"Popout Selector");
		popout.SelectorInput(fields,idfield);
		popout.SelectorOpenDefault(fields,table,idfield);
	}

	this.SelectorOpenDefault = function (fields,table,idfield)
	{
		var po_params = ""; 
		//po_lkparam = "";
		po_params = "option=popout&" + "fields=" + fields + "&" + "table=" + table + "&" + "idfield=" + idfield;
		po_lkparam = "option=pofilter&" + "fields=" + fields + "&" + "table=" + table + "&" + "idfield=" + idfield;
		window.siteutils.runQuery(po_params,"poresult","html");
	}

	this.NonSelectorOpenDefault = function (fields,table,idfield)
	{
		var po_params = ""; 
		//po_lkparam = "";
		po_params = "option=poplist&" + "fields=" + fields + "&" + "table=" + table + "&" + "idfield=" + idfield;
		po_lkparam = "option=polistfilter&" + "fields=" + fields + "&" + "table=" + table + "&" + "idfield=" + idfield;
		window.siteutils.runQuery(po_params,"polistresult","html");
	}
	
	this.SelectorClose = function ()
	{
		//clear divs so that no extra post data is added to controller
		$( '#pofilter' ).html( "" );
		$( '#result' ).html( "" );
		$( '#light' ).dialog( "close" );
	}
	
	this.SelectorInput = function (qfields,table)
	{
		var po_HTML = '<form id="poform" name="poform"><table>'; var po_HTML_LABEL = ""; var po_HTML_INPUT =""; var label ="";
		qfieldarr = qfields.split(",");
		len = qfieldarr.length;
		qfldlen = qfieldarr.length;
		for(var i=0; i<len; i++) 
		{	idfield = 'po_'+ qfieldarr[i]; 
			label = qfieldarr[i];
			label = window.siteutils.strtotitlecase(label.replace("_"," "));
			po_HTML_LABEL += '<td><label for="'+ qfieldarr[i] + '">'+ label +'</label><td>';
			po_HTML_INPUT += '<td><input type="text" id="'+ idfield + '" size=10 onKeyUp=popout.getInput()><td>';
		}
		po_HTML += '<tr>' + po_HTML_LABEL + '</tr><tr>' + po_HTML_INPUT + '<tr></table></form>';
		$('#pofilter').html(po_HTML);
	}
	
	this.getInput = function()
	{
		var i, po_LKFLDS = "", po_params = "";
		var tree = $("#poform input");
		tree.each(function()
		{
			type	= $(this).attr("type");
			value	= $(this).val();
			if(type == "text")
			{
				po_LKFLDS += value + ',';
			}
		});
		po_params = po_lkparam + "&lkvals=" + po_LKFLDS;
		//replace last occurrence of comma
		po_params = po_params.substring(0,po_params.lastIndexOf(","));
		if(nonselect)
		{
			window.siteutils.runQuery(po_params,"polistresult","html");
		}
		else
		{
			window.siteutils.runQuery(po_params,"poresult","html");
		}
	}
}