
$(document).ready(function()
{
	if( $('#current_no').val() == '0' )
	{
		if( $('#order_id').val() == '' )
		{
			orderopen.CreateOrderOpenID();
		}
		$('#order_id').focus(function() { orderopen.PreOpenStatus(); });
	}
});

var orderopen = new function()
{
	this.CreateOrderOpenID = function ()
	{
		ctrlid = $('#id').val();
		order_params = "option=altid&controller=orderopen&prefix=ORR&ctrlid=" + ctrlid;
		siteutils.runQuery(order_params,'orderopen_id','val');
	}

	this.PreOpenStatus = function ()
	{
		var order_id = $('#order_id').val(); 
		var url = siteutils.getAjaxURL() + "option=jdata&controller=order&fields=order_status&prefix=&wfields=order_id&wvals=" + order_id;
		$.getJSON(url, function(data){
		$('#pre_open_status').val( data[0].order_status );
		});
	}
}