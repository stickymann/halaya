var lastIndex;
var subtable = "subform_table_details";
var subform_table_details_dataurl = "";
var colArr = new Array();		
var delivery_date;
var returned_signed_date;
var delivered_by;
var returned_signed_by;

$(document).ready(function()	
{
	delivery_date = $('#delivery_date').val();
	returned_signed_date = $('#returned_signed_date').val();
	delivered_by = $('#delivered_by').val();
	returned_signed_by = $('#returned_signed_by').val();
	details_DefaultColumns(subtable);
	subform_InitDataGridReadWrite(subtable);
});

function subform_InitDataGridReadWrite(tt)
{
	$('#'+tt).datagrid(
	{
		url: subform_table_details_dataurl,
		columns: details_colArr
	});
}

function order_ToggleStatus()
{
	if($('#status').val() == "NEW") 
	{ 
		$('#status').val("SENT.FOR.DELIVERY"); 
		if(delivery_date == "0000-00-00" || delivery_date == ""){ $('#delivery_date').val(siteutils.currentDate('Y-m-d'));}	else { $('#delivery_date').val(delivery_date);}
		$('#delivered_by').val(delivered_by);
	} 
	else if ($('#status').val() == "SENT.FOR.DELIVERY")
	{ 
		$('#status').val("SIGNATURE.RETURNED");
		if(returned_signed_date == "0000-00-00" || returned_signed_date == ""){ $('#returned_signed_date').val(siteutils.currentDate('Y-m-d'));}	else { $('#returned_signed_date').val(returned_signed_date);}
		$('#returned_signed_by').val(returned_signed_by);
	}
	else if ($('#status').val() == "SIGNATURE.RETURNED")
	{ 
		$('#status').val("NEW"); 
		$('#delivery_date').val("0000-00-00");
		$('#returned_signed_date').val("0000-00-00");
		$('#delivered_by').val("");
		$('#returned_signed_by').val("");
	}
}
