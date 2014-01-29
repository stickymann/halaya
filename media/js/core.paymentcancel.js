
$(document).ready(function()
{
	if( $('#current_no').val() == '0' )
	{
		if( $('#payment_id').val() == '' )
		{
			paymentcancel.CreatePaymentCancelID();
		}
		$('#payment_id').focus(function() { paymentcancel.SetAmount(); });
	}
});

var paymentcancel = new function()
{
	this.CreatePaymentCancelID = function ()
	{
		ctrlid = $('#id').val();
		order_params = "option=altid&controller=paymentcancel&prefix=PCR&ctrlid=" + ctrlid;
		siteutils.runQuery(order_params,'paymentcancel_id','val');
	}

	this.SetAmount = function ()
	{
		var payment_id = $('#payment_id').val(); 
		var url = siteutils.getAjaxURL() + "option=jdata&controller=payment&fields=amount&prefix=&wfields=payment_id&wvals=" + payment_id;
		$.getJSON(url, function(data){
		$('#amount').val( data[0].amount );
		});
	}
}