
$(document).ready(function()
{
	init_cc = $('#cc_id').val();
	init_cc_id_sideinfo = $('#cc_id_sideinfo').html();
	bicrmonthly.CreateAltID();
			
	if($('#current_no').val() == '0')
	{
		$('#requesttype').keyup(function() {bicrmonthly.CreateAltID();});
		$('#requesttype').change(function() 
		{
			bicrmonthly.CreateAltID();
			bicrmonthly.SetChargeCustomer();
		});

		$('#cc_id').keyup(function() {bicrmonthly.CreateAltID();});
		$('#cc_id').change(function() {bicrmonthly.CreateAltID();});
		$('#cc_id').focus(function() 
		{
			bicrmonthly.CreateAltID();
			bicrmonthly.SetChargeCustomerBlank();
		});
		
		$('#start_date').keyup(function() {bicrmonthly.CreateAltID();});
		$('#start_date').change(function() {bicrmonthly.CreateAltID();});
		$('#start_date').focus(function() 
		{
			var yearmonth = $('#start_date').val();
			var datearr = yearmonth.split("-");
			var month = datearr[0] + "-" + datearr[1];
			var startdate = month + "-01";
			var monthend = bicrmonthly.GetEndMonthDate(month);
			var enddate = month + "-" + monthend;
			$('#start_date').val(startdate);
			$('#end_date').val(enddate);
			bicrmonthly.CreateAltID();
		});
	}
});
		
var bicrmonthly = new function() 
{
	this.CreateAltID = function()
	{
		var requesttype = $('#requesttype').val();
		var start_date = $('#start_date').val();
		var end_date = $('#end_date').val();
		
		if(requesttype == "EOMCC-ONE")
		{
			var cc_id = $('#cc_id').val();
		}
		else
		{
			var cc_id = "EOMCC-ALL";
		}
		batchrequest_id = "BICR." + cc_id + "." + start_date + "." + end_date;
		$('#batchrequest_id').val(batchrequest_id);
		description = cc_id + " for month " + start_date + " to " + end_date;
		$('#description').val(description);
	}

	this.SetChargeCustomer = function()
	{
		if($('#is_co').val() == "EOMCC-ALL") 
		{ $('#cc_id').val(""); $('#cc_id_sideinfo').html("");  }
		else 
		{ $('#cc_id').val(init_cc); $('#cc_id_sideinfo').html(init_cc_id_sideinfo); }
	}

	this.SetChargeCustomerBlank = function()
	{
		if($('#requesttype').val() == "EOMCC-ALL") { $('#cc_id').val(""); $('#cc_id_sideinfo').html(""); } 
	}

	this.GetEndMonthDate = function(yearmonth)
	{
		var datearr = yearmonth.split("-");
		if(datearr[1] == "01") { return "31"; }
		else if (datearr[1] == "02") 
		{ 
			if( (datearr[0]%4) == 0) { return "29"; } else { return "28"; } 
		}
		else if(datearr[1] == "03") { return "31"; }
		else if(datearr[1] == "04") { return "30"; }
		else if(datearr[1] == "05") { return "31"; }
		else if(datearr[1] == "06") { return "30"; }
		else if(datearr[1] == "07") { return "31"; }
		else if(datearr[1] == "08") { return "31"; }
		else if(datearr[1] == "09") { return "30"; }
		else if(datearr[1] == "10") { return "31"; }
		else if(datearr[1] == "11") { return "30"; }
		else if(datearr[1] == "12") { return "31"; }
	}
}
