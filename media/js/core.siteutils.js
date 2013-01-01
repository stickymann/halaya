
window.onunload = function() 
{
	//using jQuery to make this work right
	if($('#recordlockid').val() != null && $('#bttnclicked').val() == "false")
	{
		//json/cleanupRecordLocksAndOphanRecords/1480
		url = 'json/cleanupRecordLocksAndOphanRecords/' + $('#recordlockid').val();
		$.getJSON(url); //jQuery function
		alert ("WARNING: Navigating away from this page without using the page controls may cause the record to remain in IHLD status!");
	}
}

var siteutils = new function()
{
	this.getAjaxURL = function()
	{
		url_ajax = this.getBaseURL() + "index.php/ajaxtodb?";
		return url_ajax; 
	}

	this.setButtonClicked = function(bttn)
	{
		$('#bttnclicked').val(bttn);
	}

	this.getBaseURL = function() 
	{
		var url = location.href;  // entire url including querystring - also: window.location.href;
		var baseURL = url.substring(0, url.indexOf('/', 14));
	    //if (baseURL.indexOf('http://localhost') != -1) 
		//{
			// Base Url for localhost
			var url = location.href;  // window.location.href;
			var pathname = location.pathname;  // window.location.pathname;
			var index1 = url.indexOf(pathname);
			var index2 = url.indexOf("/", index1 + 1);
			var baseLocalUrl = url.substr(0, index2);
			//alert ("1:" +baseLocalUrl);
			return baseLocalUrl + "/";
		//}
		//else 
		//{
			// Root Url for domain name
		//	alert ("2:" +baseURL);
		//	return baseURL + "/";
		//}
	}
		
	this.runQuery = function(qstr,id,type)
	{
		if(type=="html")
		{
			$.get(this.getAjaxURL() + qstr, function(data) 
				{ $('#'+id).html(data); });
		}
		else if (type=="val")
		{
			$.get(this.getAjaxURL() + qstr, function(data) 
				{ $('#'+id).val(data); });
		}
		else if (type=="text")
		{
			$.get(this.getAjaxURL() + qstr, function(data) 
				{ $('#'+id).text(data); });
		}
		else if (type=="var")
		{
			$.get(this.getAjaxURL() + qstr, function(data) 
				{ $('#js_tmpvar').val(data); });
		}
	}

	this.dialogWindow = function(id,wdth,hght,titletxt)
	{
		$('#'+ id).dialog({
                title: titletxt,
                width: wdth,
                height: hght,
                modal: true,
                resizable: true
            });
    }

	this.closeDialog = function(id,clear)
	{
		if(clear) {	$('#'+ id).html(""); }
		$('#'+ id).dialog( "close" );
	}
/*	
	this.validationWindow = function()
	{
		dialogWindow("validatewin",300,200,"Validation Alert");
	}
*/	
	this.currentDate = function(a,s) 
	{
		var d = isNaN( s *= 1000 ) ? new Date() : new Date( s ), f = d.getTime();
		return ( '' + a ).replace( /a|A|d|D|F|g|G|h|H|i|I|j|l|L|m|M|n|s|S|t|T|U|w|y|Y|z|Z/g, function( a )
		{
			switch ( a )
			{	
			case 'a' : return d.getHours() > 11 ? 'pm' : 'am';
			case 'A' : return d.getHours() > 11 ? 'PM' : 'AM';
			case 'd' : return ( '0' + d.getDate() ).slice(-2);
			case 'D' : return [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ][ d.getDay() ];
			case 'F' : return [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ][ d.getMonth() ];
			case 'g' : return ( s = ( d.getHours() || 12 ) ) > 12 ? s - 12 : s;
			case 'G' : return d.getHours();
			case 'h' : return ( '0' + ( ( s = d.getHours() || 12 ) > 12 ? s - 12 : s ) ).slice(-2);
			case 'H' : return ( '0' + d.getHours() ).slice(-2);
			case 'i' : return ( '0' + d.getMinutes() ).slice(-2);
			case 'I' : return (function(){ d.setDate(1); d.setMonth(0); s = [ d.getTimezoneOffset() ]; d.setMonth(6); s[1] = d.getTimezoneOffset(); d.setTime( f ); return s[0] == s[1] ? 0 : 1; })();
			case 'j' : return d.getDate();
			case 'l' : return [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ][ d.getDay() ];
			case 'L' : return ( s = d.getFullYear() ) % 4 == 0 && ( s % 100 != 0 || s % 400 == 0 ) ? 1 : 0;
			case 'm' : return ( '0' + ( d.getMonth() + 1 ) ).slice(-2);
			case 'M' : return [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ][ d.getMonth() ];
			case 'n' : return d.getMonth() + 1;
			case 's' : return ( '0' + d.getSeconds() ).slice(-2);
			case 'S' : return [ 'th', 'st', 'nd', 'rd' ][ ( s = d.getDate() ) < 4 ? s : 0 ];
			case 't' : return (function(){ d.setDate(32); s = 32 - d.getDate(); d.setTime( f ); return s; })();
			case 'T' : return 'UTC';
			case 'U' : return ( '' + f ).slice( 0, -3 );
			case 'w' : return d.getDay();
			case 'y' : return ( '' + d.getFullYear() ).slice(-2);
			case 'Y' : return d.getFullYear();
			case 'z' : return (function(){ d.setMonth(0); return d.setTime( f - d.setDate(1) ) / 86400000; })();
			default : return -d.getTimezoneOffset() * 60;
			};
		} );
	};

	this.dateSaneValue = function(date_field,init_date)
	{
		
		if(!siteultils.dateTestISO( $( '#'+ date_field ).val() ))
		{
			$( '#'+ date_field ).val( init_date );
		}
	}

	this.dateTestISO = function(v) 
	{
		var r = false;
		if (/^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/.test(v)) 
		{
			var a = v.split('-'),
			d = parseInt(a[2], 10),
			m = parseInt(a[1], 10),
			y = parseInt(a[0], 10);
			var b = new Date(y, (m - 1),d);
			if (((b.getMonth() + 1) !== m)  || (b.getDate() !== d)  || (b.getFullYear() !== y)) 
			{r = false; } else { r = true; }
		}
		return r;
	}
	
	this.formatCurrency = function (num) 
	{
		num = isNaN(num) || num === '' || num === null ? 0.00 : num;
		return parseFloat(num).toFixed(2);
	}
	
	this.strtotitlecase = function(str)
	{
		return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	}
	
	var url_ajax = this.getAjaxURL();
	var url_media = this.getBaseURL() + "media/";
	var url_js = this.getBaseURL() + "js/";
}
