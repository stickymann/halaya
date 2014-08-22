var last_package_items;
var firstload = true;
var pi_params = "";

$(document).ready(function()
{
	var p_idfield = "product_id";
	var p_table = "vw_nonpackageproducts";
	var p_fields = "product_id,type,product_description";
	var last_package_items = $('#package_items').val();
	var package_items  = $('#package_items').val();
			
	pi_params = "option=productpopoutchkbox&pitems=" + package_items + "&idfield="+ p_idfield + "&table=" + p_table + "&fields=" + p_fields;
		$('#type').change(function() { $('#package_items').val(last_package_items); product.SetPackageItems();});
		
		if( $('#type').val() == 'PACKAGE' ) 
		{ 
			$('#package_items').click( function() 
			{ 
				product.SetPackageItems();
			});
		}

});

var product = new function()
{
	this.SetPackageItems = function()
	{
		if ($('#type').val() == 'PACKAGE')
		{
			$('#package_items').removeAttr('readonly');
			$('#package_items').live("click",function() 
			{
				if(firstload)
				{
					siteutils.runQuery(pi_params,"chkresult","html");
					firstload = false;
				}
				siteutils.dialogWindow("chklight",600,300,"Package Items Selector");
			});
		}
		else
		{
			$('#package_items').die("click");
			last_package_items = $('#package_items').val();
			$('#package_items').attr('readonly', true);
			$('#package_items').val('');
			$( '#chklight' ).dialog( "close" );
		}
	}

	this.setCheckBox = function()
	{
		str = $('#selids').val();
		_checkbox= str.split(",");
	}

	this.setCheckBoxItems = function()	
	{
		this.setCheckBox();
		$('#package_items').val('');
		len = _checkbox.length;
		for(var key=0; key<len; key++)
		{
			inp = $('#'+_checkbox[key]+'_inp').val();
			if( isNaN(inp) ||   inp < 1 )
			{
				$('#'+_checkbox[key]+'_inp').val("1");
			}
				
			if( $('#'+_checkbox[key]).attr("checked") == "checked" )
			{
				pitems = $('#package_items').val() + $('#'+_checkbox[key]).val() + "=" + $('#'+_checkbox[key]+'_inp').val() + ";";				$('#package_items').val(pitems);
			}
		}
		str = $('#package_items').val();
		str = str.substr(0,str.length-1);
		$('#package_items').val(str);
	}
}
