var last_status_change_date;

$(document).ready(function()
{
	$('#status_change_date').change(function() {creditaccount.SetChangeDate();});

	if($('#current_no').val() == '0')
	{
		$('#activation_date').val(siteutils.currentDate('Y-m-d'));
		$('#status_change_date').val(siteutils.currentDate('Y-m-d'));
	}
	last_status_change_date = $('#status_change_date').val();
});
		

var creditaccount = new function() 
{
	this.SetChangeDate = function()
	{
		if($('#active').val()=='N')
		{
			$('#status_change_date').val(last_status_change_date);
		}
		else
		{
			$('#status_change_date').val(siteutils.currentDate('Y-m-d'));
		}
	}
}
