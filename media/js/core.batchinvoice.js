var lastIndex = 0;
var order_status = "";
var edittype = "DEFAULT";
var subtable = "subform_table_batch_details";
if( js_controller  == "eominvoice" )
{
	var url_orders = siteutils.getAjaxURL() + "option=jdata&dbtable=vw_eomorders_lookup&fields=order_id&prefix=&orderby=order_id";
}
else
{
	var url_orders = siteutils.getAjaxURL() + "option=jdata&dbtable=vw_batchorders_lookup&fields=order_id&prefix=&orderby=order_id";
}

$(document).ready(function()	
{
	subform_InitDataGridReadWrite(subtable); 
	if($('#current_no').val() == '0' && $('#batch_id').val() == '')
	{
		batchinvoice_UpdateDetails();
		batchinvoice_CreateID();
		$('#batch_date').val(siteutils.currentDate('Y-m-d'));
	}
});

function DefaultColumns(tt)
{
	var colArr =new Array();
	colArr = [[
				{field:'subform_batch_details_order_id',title:'<b>Order Id</b>',width:140,align:'left',editor:{type:'combobox',options:{valueField:'order_id',textField:'order_id', url:url_orders,onSelect:batchinvoice_GetOrderData, mode:'remote',required:true}}},
				{field:'subform_batch_details_invoice_id',title:'<b>Invoice Id</b>',width:80,align:'left'},
				{field:'subform_batch_details_alt_invoice_id',title:'<b>Alt Invoice Id</b>',width:80,align:'left',editor:{type:'numberbox',options:{required:true}}},
				{field:'subform_batch_details_order_date',title:'<b>Order Date</b>',width:80,align:'left'},
				{field:'subform_batch_details_first_name',title:'<b>First Name</b>',width:80,align:'left'},
				{field:'subform_batch_details_last_name',title:'<b>Last Name</b>',width:80,align:'left'},
				{field:'subform_batch_details_order_details',title:'<b>Order Details</b>',width:140,align:'left'},
				{field:'subform_batch_details_extended_total',title:'<b>Extended</b>',width:50,align:'right'},
				{field:'subform_batch_details_tax_total',title:'<b>Tax</b>',width:50,align:'right'},
				{field:'subform_batch_details_order_total',title:'<b>Total</b>',width:50,align:'right'},
				{field:'subform_batch_details_payment_total',title:'<b>Payments</b>',width:50,align:'right'},
				{field:'subform_batch_details_balance',title:'<b>Balance</b>',width:50,align:'right'},
				{field:'subform_batch_details_payment_type',title:'<b>Payment Type</b>',width:140,align:'left'},
				{field:'subform_batch_details_batch_id',title:'<b>Batch Id</b>',width:140,align:'left'},
				{field:'subform_batch_details_id',title:'<b>Id</b>',width:50,align:'left'}
			]]
			$('#'+tt).datagrid({columns: colArr });
}

function DefaultNewRow(tt)
{
	$('#'+tt).datagrid('appendRow',
	{
		subform_batch_details_batch_id:$('#batch_id').val()
	});
}	

function getCellsValue(val)
{
	if( val == "undefined"){ return "null"; }
	return val;
}

function doRemove()
{
	order_UpdateDetails();
}

function doUndo()
{
	batchinvoice_UpdateDetails();
}
	
function doOnLoadSuccess()
{
	batchinvoice_UpdateDetails();
}	
	
function doAcceptChanges()
{
	var row = $('#'+subtable).datagrid('getSelected');
	if (row)
	{
		var index = $('#'+subtable).datagrid('getRowIndex', row);
	}
	$('#'+subtable).datagrid('refreshRow', index);
	batchinvoice_UpdateDetails();
}
	
function batchinvoice_UpdateDetails()
{
	var xmlhr = "<?xml version='1.0' standalone='yes'?>"+"<rows>";
	var xmlft = "</rows>";
	var xmltxt = "";
	var rows = $('#'+subtable).datagrid('getRows');
	
	rowlength = rows.length;
	for(var i=0; i<rowlength; i++)
	{  
		id				= "<id>" + getCellsValue(rows[i].subform_batch_details_id) + "</id>";
		batch_id		= "<batch_id>" + getCellsValue(rows[i].subform_batch_details_batch_id) + "</batch_id>";
		invoice_id		= "<invoice_id>" + getCellsValue(rows[i].subform_batch_details_invoice_id) + "</invoice_id>";
		alt_invoice_id	= "<alt_invoice_id>" + getCellsValue(rows[i].subform_batch_details_alt_invoice_id) + "</alt_invoice_id>";
		order_id		= "<order_id>" + getCellsValue(rows[i].subform_batch_details_order_id) + "</order_id>";
		order_date		= "<order_date>" + getCellsValue(rows[i].subform_batch_details_order_date) + "</order_date>";
		first_name		= "<first_name>" + getCellsValue(rows[i].subform_batch_details_first_name) + "</first_name>";
		last_name		= "<last_name>" + getCellsValue(rows[i].subform_batch_details_last_name) + "</last_name>";
		order_details	= "<order_details>" + getCellsValue(rows[i].subform_batch_details_order_details) + "</order_details>";
		extended_total	= "<extended_total>" + getCellsValue(rows[i].subform_batch_details_extended_total) + "</extended_total>";
		tax_total		= "<tax_total>" + getCellsValue(rows[i].subform_batch_details_tax_total) + "</tax_total>";
		order_total		= "<order_total>" + getCellsValue(rows[i].subform_batch_details_order_total) + "</order_total>";
		payment_total	= "<payment_total>" + getCellsValue(rows[i].subform_batch_details_payment_total) + "</payment_total>";
		balance			= "<balance>" + getCellsValue(rows[i].subform_batch_details_balance) + "</balance>";
		payment_type	= "<payment_type>" + getCellsValue(rows[i].subform_batch_details_payment_type) + "</payment_type>";
		xmltxt			+= "<row>"+ id + batch_id + order_id + invoice_id + alt_invoice_id + order_date + first_name + last_name + order_details + extended_total + tax_total + order_total + payment_total + balance + payment_type + "</row>";
	}  
	xmltxt = xmlhr + xmltxt + xmlft;
	xmltxt = xmltxt.replace(/&/g,"&amp;");
	$('#batch_details').val(xmltxt);
}

function batchinvoice_GetOrderData()
{
	var lookupval = "";
	var row = $('#'+subtable).datagrid('getSelected');

	if (row)
	{
		var index = $('#'+subtable).datagrid('getRowIndex', row);
		$('#'+subtable).datagrid('updateRow',index);
			
		$('input').each(function() 
		{
			$.each(this.attributes, function(i, attrib)
			{
				var name = attrib.name;
				var value = attrib.value;
				//alert('name :'+attrib.name+' ,value :'+attrib.value);
				if(name == "value")
				{
					if( value.length == 16)
					{
						prefix = value.substring(0,3)
						if( prefix == 'ORD' ) 
						{
							lookupval = value;
						}
					}
				}
			});
		});

		url = siteutils.getAjaxURL() + "option=jdatabyid&dbtable=vw_orderbalances&fields=id,order_date,first_name,last_name,order_details,extended_total,tax_total,order_total,payment_total,balance,payment_type&idfield=order_id&idval=" + lookupval;
		$.getJSON(url, function(data)
		{
			row.subform_batch_details_invoice_id = data.id;
			row.subform_batch_details_order_date = data.order_date;
			row.subform_batch_details_first_name = data.first_name;
			row.subform_batch_details_last_name = data.last_name;
			row.subform_batch_details_order_details = data.order_details;
			row.subform_batch_details_extended_total = data.extended_total;
			row.subform_batch_details_tax_total = data.tax_total;
			row.subform_batch_details_order_total = data.order_total;
			row.subform_batch_details_payment_total = data.payment_total;
			row.subform_batch_details_balance = data.balance;
			row.subform_batch_details_payment_type = data.payment_type;
		});
	}
}

function batchinvoice_CreateID()
{
	ctrlid = $('#id').val();
	if( js_controller == "eominvoice" ) 
	{
		order_params = "option=altid&controller=eominvoice&prefix=EOM&ctrlid=" + ctrlid;
	}
	else
	{
		order_params = "option=altid&controller=batchinvoice&prefix=BCH&ctrlid=" + ctrlid;
	}
	siteutils.runQuery(order_params,'batch_id','val');
}
		
function subform_InitDataGridReadWrite(tt)
{
	$('#'+tt).datagrid(
	{
				toolbar:[{text:'Add',iconCls:'icon-add',handler:function()
					{
						DefaultColumns(tt)
						$('#'+tt).datagrid('endEdit', lastIndex);
						//abstract function, add to controller
						DefaultNewRow(tt);
						var index = $('#'+tt).datagrid('getRows').length-1;
						$('#'+tt).datagrid('selectRow', index);
						$('#'+tt).datagrid('beginEdit', index);
					}
				},'-',
						{text:'Remove',iconCls:'icon-remove',handler:function()
					{
						var row = $('#'+tt).datagrid('getSelected');
						if(row)
						{
							var index = $('#'+tt).datagrid('getRowIndex', row);
							$('#'+tt).datagrid('deleteRow', index);
						}
						//abstract function, add to controller
						doRemove();
					}
				},'-',
						{text:'Undo',iconCls:'icon-undo',handler:function()
					{
						$('#'+tt).datagrid('rejectChanges');
						//abstract function, add to controller
						doUndo();
					}
				},'-',
						{text:'Accept',	iconCls:'icon-save', handler:function()
					{
						$('#'+tt).datagrid('acceptChanges');
						//abstract function, add to controller
						doAcceptChanges();
					}
				}],

				onBeforeLoad: function()
				{
					$(this).datagrid('rejectChanges');
				},
				
				onLoadSuccess: function()
				{
					//abstract function, add to controller
					doOnLoadSuccess();
				},

				onDblClickRow: function(rowIndex)
				{
					$('#'+tt).datagrid('endEdit', lastIndex);
					row = $('#'+tt).datagrid('getSelected');
					DefaultColumns(tt);
					$('#'+tt).datagrid('beginEdit', rowIndex);
					lastIndex = rowIndex;
				}		
	});
}
