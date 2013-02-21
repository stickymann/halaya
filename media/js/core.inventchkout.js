var lastIndex;
var subtable = "subform_table_checkout_details";
var subform_table_checkout_details_dataurl = "";
var checkout_details_colArr = new Array();		

$(document).ready(function()	
{
	checkout_details_DefaultColumns(subtable);
	subform_InitDataGridReadWrite(subtable);
});

function getCellsValue(val)
{
	if( val == "undefined"){ return "null"; }
	return val;
}

function doAcceptChanges()
{
	var row = $('#'+subtable).datagrid('getSelected');
	if (row)
	{
		var index = $('#'+subtable).datagrid('getRowIndex', row);
		balance = row.subform_checkout_details_order_qty - row.subform_checkout_details_filled_qty;
		if(row.subform_checkout_details_checkout_qty > balance || row.subform_checkout_details_checkout_qty < 0 )
		{
			row.subform_checkout_details_checkout_qty = balance;
		}
	$('#'+subtable).datagrid('refreshRow', index);
	}
	checkout_details_UpdateXML();
}

function checkout_details_UpdateXML()
{
	var tblrows = new Object;
	var xmltxt = "";
	var xmlhr = "<?xml version='1.0' standalone='yes'?>"+"\n"+"<formfields>"+"\n"+"<rows>"+"\n";
	var xmlft = "</rows>"+"\n"+"</formfields>"+"\n";
	//var xmlrowcount = "<rowcount>0</rowcount>";
	var rows = $('#'+subtable).datagrid('getRows');
	rowlength = rows.length;
	
	for(var i=0; i<rowlength; i++)
	{  
		product_id		= "<product_id>" + getCellsValue(rows[i].subform_checkout_details_product_id) + "</product_id>";
		description		= "<description>" + getCellsValue(rows[i].subform_checkout_details_description) + "</description>";
		order_qty		= "<order_qty>" + getCellsValue(rows[i].subform_checkout_details_order_qty) + "</order_qty>";
		filled_qty		= "<filled_qty>" + getCellsValue(rows[i].subform_checkout_details_filled_qty) + "</filled_qty>";
		checkout_qty	= "<checkout_qty>" + getCellsValue(rows[i].subform_checkout_details_checkout_qty) + "</checkout_qty>";
		status			= "<status>" + getCellsValue(rows[i].subform_checkout_details_status) + "</status>";
		xmltxt			+= "<row>" + product_id + description + order_qty + filled_qty + checkout_qty + status + "</row>"+"\n";
	}
	xmltxt = xmlhr + xmltxt + xmlft;
	$('#checkout_details').val(xmltxt);
}

function subform_InitDataGridReadWrite(tt)
{
	$('#'+tt).datagrid(
	{
		url: subform_table_checkout_details_dataurl,
		columns: checkout_details_colArr,
		toolbar:
		[
			{
				text:'Accept',	iconCls:'icon-save', handler:function()
				{
					$('#'+tt).datagrid('acceptChanges');
					//abstract function, add to controller
					doAcceptChanges();
				}
			}
		],

		onBeforeLoad: function()
		{
			$(this).datagrid('rejectChanges');
		},
				
		onLoadSuccess: function()
		{
			//abstract function, add to controller
			//doOnLoadSuccess();
		},

		onDblClickRow: function(rowIndex)
		{
			$('#'+tt).datagrid('endEdit', lastIndex);
			row = $('#'+tt).datagrid('getSelected');
			if(row.subform_checkout_details_order_qty != row.subform_checkout_details_filled_qty)
			{
				$('#'+tt).datagrid('beginEdit', rowIndex);
				lastIndex = rowIndex;
			}
		}		
	});
}
