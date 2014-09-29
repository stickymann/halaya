var last_fname;

$(document).ready(function()
{
	last_fname = $('#first_name').val();
	$('#first_name').attr('readonly', true);
	$('#first_name').css({"background-color":"#ebebe0"});

	$('#customer_type').click(function() 
	{
		idgenerator.FirstNameOnOff();
		idgenerator.CreateAltID();
	});
	
	
	$('#customer_type').change(function() 
	{
		idgenerator.FirstNameOnOff();
		idgenerator.CreateAltID();
	});
	
	$('#first_name').keyup(function() {idgenerator.CreateAltID();});
	$('#last_name').keyup(function() {idgenerator.CreateAltID();});
	//$('#first_name').change(function() {idgenerator.CreateAltID();});
	//$('#last_name').change(function() {idgenerator.CreateAltID();});
	$('#customer_id').hover(function() 
	{
		$('#customer_id').val("");
		idgenerator.CreateAltID();
	});

});
		
var idgenerator = new function() 
{
	this.getDBReqsURL = function()
	{
		url_dbreqs = siteutils.getBaseURL() + "index.php/hndshkif_dbreqs?";
		return url_dbreqs; 
	}
	
	this.CreateAltID = function ()
	{
		var fn = $('#first_name').val();
		var ln = $('#last_name').val();
		fn = fn.replace(/&/g,'');
		ln = ln.replace(/&/g,'');
		params = "option=daceasyid&" + "firstname=" + fn + "&" + "lastname=" + ln;
		$.getJSON(this.getDBReqsURL() + params, function(data) 
			{ 
				customer_id = data['customer_id'];
				$('#customer_id').val(customer_id);
			});
	}
		
	this.FirstNameOnOff = function ()
	{
		if ($('#customer_type').val() == 'COMPANY')
		{
			if( $('#first_name').val() != "" )
			{
				last_fname = $('#first_name').val();
			}
			$('#first_name').val('');
			$('#first_name').attr('readonly', true);
			$('#first_name').css({"background-color":"#ebebe0"});
		}
		else
		{
			$('#first_name').val(last_fname);
			$('#first_name').removeAttr('readonly');
			$('#first_name').css({"background-color":"#ffffff"});
		}
	}

}
