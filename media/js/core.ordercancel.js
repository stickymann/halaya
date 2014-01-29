
$(document).ready(function()
{
	if( $('#current_no').val() == '0' )
	{
		if( $('#order_id').val() == '' )
		{
			ordercancel.CreateOrderCancelID();
		}
		$('#order_id').focus(function() { ordercancel.PreCancelStatus(); });
	}
});

var ordercancel = new function()
{
	this.CreateOrderCancelID = function ()
	{
		ctrlid = $('#id').val();
		order_params = "option=altid&controller=ordercancel&prefix=OCR&ctrlid=" + ctrlid;
		siteutils.runQuery(order_params,'ordercancel_id','val');
	}

	this.PreCancelStatus = function ()
	{
		var order_id = $('#order_id').val(); 
		var url = siteutils.getAjaxURL() + "option=jdata&controller=order&fields=order_status&prefix=&wfields=order_id&wvals=" + order_id;
		$.getJSON(url, function(data){
		$('#pre_cancel_status').val( data[0].order_status );
		});
	}
}