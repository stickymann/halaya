
$(document).ready(function()
{
	if($('#current_no').val() == '0')
	{
		var till_date = siteutils.currentDate('Y-m-d');

		$('#till_date').val(till_date);
		$('#expiry_date').val(till_date);
		$('#expiry_time').val("23:59:00");
				
		$('#till_user').keyup(function() {till.CreateTillID();});
		$('#till_date').keyup(function() 
		{
			till.CreateTillID();
			till.SetExpiryDate();
			
		});
		
		$('#till_user').focus(function() { till.CreateTillID(); });
		$('#till_date').focus(function() 
		{
			till.CreateTillID();
			till.SetExpiryDate();
		});

		till.CreateTillID();
		till.SetExpiryDate();
	}
});
		
var till = new function()
{
	this.CreateTillID = function ()
	{
		var current_date = $('#till_date').val();
		current_date = current_date.split("-").join("");
		var till_id = $('#till_user').val() + "-" + current_date;
		$('#till_id').val(till_id);
	}

	this.SetExpiryDate = function ()
	{
		$('#expiry_date').val( $('#till_date').val() );
	}
}
