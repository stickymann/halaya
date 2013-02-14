var lastIndex = 0;
var es_fields = "product_id,product_description,TYPE,taxable,unit_price,total_price,category,sub_category";
var es_table  = "vw_estimator_products";
var es_idfield = "product_id";
var subtable = "subform_table_order_details";
var edittype = "DEFAULT";
var url_products = siteutils.getAjaxURL() + "option=jdata&controller=product&fields=product_id&prefix=&wfields=status&orderby=product_id&wvals=ACTIVE";

$(document).ready(function()	
{
	nonselect = true;
	popout.SelectorInput(es_fields,es_idfield);
	popout.NonSelectorOpenDefault(es_fields,es_table,es_idfield);
	subform_InitDataGridReadWrite(subtable);
});

function DefaultColumns(tt)
{
	//var url_cols = siteutils.getAjaxURL() + "option=jdefaultorderdetailscolumndef";
	//$.getJSON(url_cols, function(data) {
	//	var colArr = $.parseJSON(data );
	//	$('#'+tt).datagrid({columns: [colArr] });
	//});
	var colArr =new Array();
	colArr = [[
				{field:'subform_order_details_product_id',title:'<b>Product Id</b>',width:120,align:'left',editor:{type:'combobox',options:{valueField:'product_id',textField:'product_id', url:url_products,onSelect:order_GetProductData, mode:'remote',required:true}}},
				{field:'subform_order_details_description',title:'<b>Description</b>',width:200,align:'left'},
				{field:'subform_order_details_qty',title:'<b>Qty</b>',width:30,align:'center',editor:{type:'numberbox',options:{required:true}}},
				{field:'subform_order_details_unit_price',title:'<b>Unit Price</b>',width:70,align:'right'},
				{field:'subform_order_details_unit_total',title:'<b>Unit Total</b>',width:70,align:'right'},
				{field:'subform_order_details_discount_amount',title:'<b>Discount</b>',width:70,align:'right',editor:{type:'numberbox',options:{required:true,precision:2}}},
				{field:'subform_order_details_extended',title:'<b>Extended</b>',width:70,align:'right'},
				{field:'subform_order_details_tax_amount',title:'<b>Tax Amt</b>',width:50,align:'right'},
				{field:'subform_order_details_total',title:'<b>Total</b>',width:70,align:'right'},
				{field:'subform_order_details_tax_percentage',title:'<b>Tax(%)</b>',width:50,align:'right'},
				{field:'subform_order_details_taxable',title:'<b>Taxable</b>',width:50,align:'center'},
				{field:'subform_order_details_discount_type',title:'<b>DiscountType</b>',width:50,align:'left',editor:{type:'checkbox',options:{on:'DOLLAR',off:'PERCENT'}}},
				{field:'subform_order_details_description_type',title:'<b>DescriptionType</b>',width:100,align:'left',editor:{type:'checkbox',options:{on:'EXTENDED',off:'STANDARD'}}},
				{field:'subform_order_details_user_text',title:'<b>User Text</b>',width:140,align:'left',editor:{type:'textarea'}},
				{field:'subform_order_details_order_id',title:'<b>Order Id</b>',width:140,align:'left'},
				{field:'subform_order_details_id',title:'<b>Id</b>',width:50,align:'left'}
			]]
			$('#'+tt).datagrid({columns: colArr });
}

function MiscColumns(tt)
{
	var colArr = new Array();
	colArr = [[
				{field:'subform_order_details_product_id',title:'<b>Product Id</b>',width:120,align:'left'},			
				{field:'subform_order_details_qty',title:'<b>Qty</b>',width:30,align:'center',editor:{type:'numberbox',options:{required:true}}}, 
				{field:'subform_order_details_description',title:'<b>Description</b>',width:200,align:'left',editor:{type:'validatebox',options:{required:true}}},
				{field:'subform_order_details_unit_price',title:'<b>Unit Price</b>',width:70,align:'right',editor:{type:'numberbox',options:{required:true}}},
				{field:'subform_order_details_unit_total',title:'<b>Unit Total</b>',width:70,align:'right'},
				{field:'subform_order_details_discount_amount',title:'<b>Discount</b>',align:'right',width:70},
				{field:'subform_order_details_tax_percentage',title:'<b>Tax(%)</b>',width:50,align:'right'},
				{field:'subform_order_details_extended',title:'<b>Extended</b>',align:'right',width:70},
				{field:'subform_order_details_tax_amount',title:'<b>Tax Amt</b>',width:50,align:'right'},
				{field:'subform_order_details_total',title:'<b>Total</b>',align:'right',width:70},
				{field:'subform_order_details_taxable',title:'<b>Taxable</b>',width:50,align:'center',editor:{type:'checkbox',options:{on:'Y',off:'N'}}},
				{field:'subform_order_details_discount_type',title:'<b>DiscountType</b>',width:50,align:'left'},
				{field:'subform_order_details_description_type',title:'<b>DescriptionType</b>',width:100,align:'left'},
				{field:'subform_order_details_user_text',title:'<b>User Text</b>',width:140,align:'left',editor:{type:'textarea'}},
				{field:'subform_order_details_order_id',title:'<b>Order Id</b>',width:140,align:'left'},
				{field:'subform_order_details_id',title:'<b>Id</b>',width:50,align:'left'}
	]]
	$('#'+tt).datagrid({columns: colArr });
}

function DefaultNewRow(tt)
{
	$('#'+tt).datagrid('appendRow',
	{
		subform_order_details_order_id:$('#order_id').val(),
		subform_order_details_qty:'1',
		subform_order_details_discount_amount:'0.00',
		subform_order_details_discount_type:'PERCENT',
		subform_order_details_description_type:'STANDARD',
		subform_order_details_user_text:'?'
	});
}	

function MiscNewRow(tt)
{
	$('#'+tt).datagrid('appendRow',
	{
		subform_order_details_product_id:'MISC',
		subform_order_details_qty:'1',
		subform_order_details_discount_amount:'0.00',
		subform_order_details_taxable:'N',
		subform_order_details_discount_type:'PERCENT',
		subform_order_details_description_type:'STANDARD',
		subform_order_details_user_text:'?',
		subform_order_details_order_id:$('#order_id').val()
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
	order_UpdateDetails();
}
	
function doOnLoadSuccess()
{
	order_UpdateDetails();
}	
	
function doAcceptChanges()
{
	var row = $('#'+subtable).datagrid('getSelected');
	if (row)
	{
		var index = $('#'+subtable).datagrid('getRowIndex', row);
	
		if(row.subform_order_details_discount_type=="PERCENT")
		{
			discount_amount = parseFloat(row.subform_order_details_qty*row.subform_order_details_unit_price) * parseFloat(row.subform_order_details_discount_amount / 100);
		}
		else
		{
			discount_amount =  row.subform_order_details_discount_amount;
		}
			
		if(row.subform_order_details_taxable == 'Y')
		{
			if(row.subform_order_details_product_id=="MISC")
			{
				row.subform_order_details_tax_percentage = "15.00";
			}
			row.subform_order_details_tax_amount = siteutils.formatCurrency(((row.subform_order_details_qty*row.subform_order_details_unit_price)-discount_amount) * (row.subform_order_details_tax_percentage/100)); 
		}
		else
		{
			if(row.subform_order_details_product_id=="MISC")
			{
				row.subform_order_details_tax_percentage = "0.00";
			}
			row.subform_order_details_tax_amount ="0.00";
		}
		row.subform_order_details_extended = siteutils.formatCurrency(parseFloat((row.subform_order_details_qty*row.subform_order_details_unit_price)) - parseFloat(discount_amount));
		row.subform_order_details_total = siteutils.formatCurrency(parseFloat((row.subform_order_details_qty*row.subform_order_details_unit_price)) - parseFloat(discount_amount) + parseFloat(row.subform_order_details_tax_amount));
		row.subform_order_details_discount_amount = siteutils.formatCurrency(row.subform_order_details_discount_amount);
		row.subform_order_details_unit_total = siteutils.formatCurrency(parseFloat(row.subform_order_details_qty*row.subform_order_details_unit_price));
		row.subform_order_details_unit_price = siteutils.formatCurrency(row.subform_order_details_unit_price);
	}
	$('#'+subtable).datagrid('refreshRow', index);
	order_UpdateDetails();
}
	
function order_UpdateDetails()
{
	//var xmlhr = "<?xml version='1.0' standalone='yes'?>"+"\\n"+"<rows>"+"\\n";
	var xmlhr = "<?xml version='1.0' standalone='yes'?>"+"<rows>";
	var xmlft = "</rows>";
	//var xmlrowcount = "<rowcount>0</rowcount>";
	var xmltxt = "", summaryhtml = "";
	var grandtotal = 0, subtotal = 0, tax_total = 0, discount_amount = 0; 

	var rows = $('#'+subtable).datagrid('getRows');
	rowlength = rows.length;
	for(var i=0; i<rowlength; i++)
	{  
		id				= "<id>" + getCellsValue(rows[i].subform_order_details_id) + "</id>";
		order_id		= "<order_id>" + "%ORDERID%" + "</order_id>";
		product_id		= "<product_id>" + getCellsValue(rows[i].subform_order_details_product_id) + "</product_id>";
		qty				= "<qty>" + getCellsValue(rows[i].subform_order_details_qty) + "</qty>";
		unit_price		= "<unit_price>" + getCellsValue(rows[i].subform_order_details_unit_price) + "</unit_price>";
		unit_total		= "<unit_total>" + getCellsValue(rows[i].subform_order_details_unit_total) + "</unit_total>";
		taxable			= "<taxable>" + getCellsValue(rows[i].subform_order_details_taxable) + "</taxable>";
		tax_percentage  = "<tax_percentage>" + getCellsValue(rows[i].subform_order_details_tax_percentage) + "</tax_percentage>";
		tax_amount		= "<tax_amount>" + getCellsValue(rows[i].subform_order_details_tax_amount) + "</tax_amount>";
		extended		= "<extended>" + getCellsValue(rows[i].subform_order_details_extended) + "</extended>";
		total			= "<total>" + getCellsValue(rows[i].subform_order_details_total) + "</total>";
		discount_type   = "<discount_type>" + getCellsValue(rows[i].subform_order_details_discount_type) + "</discount_type>";
		discount_amount = "<discount_amount>" + getCellsValue(rows[i].subform_order_details_discount_amount) + "</discount_amount>";
		description		= "<description>" + getCellsValue(rows[i].subform_order_details_description) + "</description>";
		user_text		= "<user_text>" + getCellsValue(rows[i].subform_order_details_user_text) + "</user_text>";
		xmltxt			+= "<row>" + id + order_id + product_id + qty + unit_price + unit_total +taxable + tax_percentage + tax_amount + extended + discount_type + discount_amount + description + user_text + "</row>";
			
		if(rows[i].subform_order_details_discount_type=="PERCENT")
		{
			discount_amount = parseFloat(rows[i].subform_order_details_qty*rows[i].subform_order_details_unit_price) * parseFloat(rows[i].subform_order_details_discount_amount / 100);
		}
		else
		{
			discount_amount =  rows[i].subform_order_details_discount_amount;
		}
			
		subtotal		+= parseFloat((rows[i].subform_order_details_qty*rows[i].subform_order_details_unit_price)) - parseFloat(discount_amount);
		tax_total		+= parseFloat(rows[i].subform_order_details_tax_amount);
		grandtotal		+= parseFloat(rows[i].subform_order_details_total);
	}  
	
	//xmlrowcount = "<rowcount>" + rowlength + "</rowcount>" + "\\n";
	xmltxt = xmlhr + xmltxt + xmlft;
	//alert(xmltxt);
	summaryhtml += '<table width="100%">';
	summaryhtml += '<tr><td style="text-align:left; padding 0px 5px 0px 0px;"><b>Sub Total :</b> ' + siteutils.formatCurrency(subtotal) + '</td>';
	summaryhtml += '<td style="text-align:left; padding 0px 5px 0px 0px;"><b>Tax Total :</b> ' + siteutils.formatCurrency(tax_total) + '</td>';
	summaryhtml += '<td  style="text-align:left; padding 0px 5px 0px 0px;"><b>GRAND TOTAL :</b> ' + siteutils.formatCurrency(grandtotal) + '</b></td></tr>';
	summaryhtml += '</table>';
	$('#estimator_summary').html(summaryhtml);
	$('#order_details').val(xmltxt);
}

function order_GetProductData()
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
					lookupval = lookupval + attrib.value + ",";
				}
			});
		});
		lookupval = lookupval.substr(0,lookupval.length-1);

		url = siteutils.getAjaxURL() + "option=jdatabyid&controller=product&fields=product_id,product_description,unit_price,taxable,tax_percentage&idfield=product_id&idval=" + lookupval;
		$.getJSON(url, function(data){
			row.subform_order_details_description = data.product_description;
			row.subform_order_details_unit_price = data.unit_price;
			row.subform_order_details_taxable = data.taxable;
			row.subform_order_details_tax_percentage = data.tax_percentage;
		});
	}
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
						{text:'Misc',iconCls:'icon-add',handler:function()
					{
						MiscColumns(tt)
						$('#'+tt).datagrid('endEdit', lastIndex);
						MiscNewRow(tt);
						edittype = "MISC";
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
						if(edittype == "MISC")
						{
							//DefaultColumns(tt);
							edittype = "DEFAULT";
						}
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
					//if (lastIndex != rowIndex)
					//{
						$('#'+tt).datagrid('endEdit', lastIndex);
						row = $('#'+tt).datagrid('getSelected');
						if(row.subform_order_details_product_id=="MISC")
						{
							MiscColumns(tt);
							edittype = "MISC";
						}
						else
						{
							DefaultColumns(tt);
							edittype = "DEFAULT";
						}
					//}
					$('#'+tt).datagrid('beginEdit', rowIndex);
					lastIndex = rowIndex;
				}		
	});
}
