
$(document).ready(function()
{
	if( $('#current_no').val() == '0' )
	{
		if( $('#payment_id').val() == '' )
		{
			payment.CreatePaymentID();
			payment.GetUserBranch();
			payment.CreateTillID();
			payment.SetPaymentStatus();
			payment.SetPaymentDate();
		}
		
		$('#order_id').focus(function() 
		{ 
			payment.GetOrderBalance(); 
			setTimeout(payment.GetOrderBalanceInfo,1000);
		});
		$('#amount').keyup(function() { payment.GetOrderBalanceInfo(); });
	}
	payment.GetOrderBalanceInfo();
});
		
var payment = new function()
{
	this.CreateTillID = function ()
	{
		var current_date = siteutils.currentDate('Y-m-d');
		current_date = current_date.split("-").join("");
		var till_id = $('#js_idname').val() + "-" + current_date;
		$('#till_id').val(till_id);
	}

	this.CreatePaymentID = function ()
	{
		ctrlid = $('#id').val();
		order_params = "option=altid&controller=payment&prefix=PMT&ctrlid=" + ctrlid;
		siteutils.runQuery(order_params,'payment_id','val');
	}

	this.GetUserBranch = function ()
	{
		idname = $("#js_idname").val();
		order_params = "option=userbranch&idname=" + idname;
		siteutils.runQuery(order_params,'branch_id','val');
	}
	
	this.SetPaymentStatus = function ()
	{
		$("#payment_status").val('VALID');
	}
	
	this.GetOrderTotal = function ()
	{
		order_id = $("#order_id").val();
		order_params = "option=ordertotal&order_id=" + order_id;
		siteutils.runQuery(order_params,'amount','val');
	}

	this.GetOrderBalance = function ()
	{
		order_id = $("#order_id").val();
		order_params = "option=orderbalance&order_id=" + order_id;
		siteutils.runQuery(order_params,'amount','val');
	}

	this.GetOrderBalanceInfo = function ()
	{
		if( $('#payment_status').val()  =='VALID') { var amount = $('#amount').val(); } else { var amount = 0; }
		var balparam = $('#payment_id').val() + "," + $('#order_id').val() + "," + amount; 
		order_params = "option=sidefunc&func=orderbalance&parameter=" + balparam + "&format=_*";
		siteutils.runQuery(order_params,'amount_sidefunc','html');
	}

	this.SetPaymentDate = function ()
	{
		var current_date = siteutils.currentDate('Y-m-d');
		$('#payment_date').val(current_date);
	}
}
