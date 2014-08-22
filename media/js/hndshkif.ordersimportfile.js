
$(document).ready(function()
{
	$('#reference_type').change(function() { orderentry.ReferenceTypePOLink(); });
	if( $('#current_no').val() == '0' )
	{
		if( $('#request_id').val() == '' )
		{
			orderentry.ImportFileRequestID();
		}
	}
});

var orderentry = new function()
{
	this.ImportFileRequestID = function ()
	{
		ctrlid = $('#id').val();
		params = "option=altid&controller=ordersimportfile&prefix=IFR&ctrlid=" + ctrlid;
		siteutils.runQuery(params,'request_id','val');
	}

	this.ReferenceTypePOLink = function ()
	{
		retfield = "reference_id";
		
		if( $('#reference_type').val() == "BATCH" )
		{
			select_field = "id,batch_id,batch_date";
			table = "hsi_dlorderbatchs";
			idfield = "batch_id";
			
		
		}
		else if( $('#reference_type').val() == "ORDER" )
		{
			select_field = "id,customer_id,tax_id,name,cdate";
			table = "hsi_orders";
			idfield = "id";
		
		} 
		url  = '<a href = "javascript:void(0)" onclick=window.popout.SelectorOpen("' + select_field + '","' + table + '","' + idfield + '","' + retfield + '") class="aimg">&nbsp '; 
		url += '<img src="' + siteutils.getBaseURL() + 'media/img/site/lubw020.png" align=absbottom> &nbsp</a>';
		$('#reference_id_popout').html(url);
	}
}
