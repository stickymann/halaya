var fs_searchValue = $('#poresult').text();
var fs_currentValue = "";
		
$(document).ready(function()
{
	$('#formfields').focus(function()
	{
		fixedselection.LoadForm();
	});
});
		
var fixedselection = new function()
{
	this.LoadForm = function() 
	{
		var fs_params = "";
		var controller  = $('#fixedselection_id').val();
		var user  = $('#js_idname').val();
		var enqtype  = $('#enquiry_type').val();
		if(controller)
		{
			siteutils.dialogWindow("chklight",700,300,"Fixed Selection Chooser");
			fs_params = param1 = 'option=filterform&controller=' + controller + '&user='+ user + '&enqtype=' + enqtype + '&loadfixedvals=0&rochk=1';
			setTimeout('this.FillForm',500);
			siteutils.runQuery(fs_params,"chkresult","html");
		}
		else
		{
			alert('Error: Select a controller');
		}
	}

	this.FillForm = function() 
	{
		var fs_currentValue = $('#poresult').text();
		if ((fs_currentValue) && fs_currentValue != fs_searchValue && fs_currentValue != '') 
		{
			var xml = $('#formfields').val();
			$(xml).find('field').each(function() 
			{
				var _name		=  $(this).find('name').text();
				var _operand	=  $(this).find('operand').text();
				var _value		=  $(this).find('value').text();
				var _attr		=  $(this).find('onload').text();
				$('#'+_name).val(_value);
				$('#'+_name+'_select').val(_operand);
				if(_attr == "readonly")
				{
					$('#'+_name+'_rochk').attr('checked', true);
				}
				else
				{
					$('#'+_name+'_rochk').attr('checked', false);
				}
					//alert('id :' + _name + ', value :' + _value + ', operand :' + _operand + ', attr :' + _attr);
			});
		}	 
		else
		{
			setTimeout('this.FillForm',500);
		}
	}

	this.setFS = function()
	{
		var _value="", _name="", _operand="", _fields = "", _attr ="", input=1;
		var i = 0;
		$('#formfields').val("");
		$(':input', '#formfilter').each(function() 
		{
			if(this.type=="select-one")
			{
				_operand = "<operand>" + this.value + "</operand>";
			}
				
			if(this.type=="text")
			{
				_value = "<value>" + this.value + "</value>";
				_name = "<name>" + this.id + "</name>";
				if(_value == "<value></value>"){ input = 0;}
			}
				
			if(this.type=="checkbox")
			{
				//alert('id : ' + this.id + ', value : ' + this.value + ', type : ' + this.type);
				if(this.checked){_attr = "<onload>readonly</onload>";} else {_attr = "<onload>enabled</onload>";}
			}
			i++;
			if((i%3)==0)
			{
				//alert('id :' + this.id + ', value :' + this.value + ', type :' + this.type);
				if(input) {_fields += "<field>" +_name + _value + _operand +_attr + "</field>" + "\\n";} else {_fields += "";}
				input = 1;
			}
		});
			
		if(_fields != "")
		{
			var xml_header = "<?xml version=\\'1.0\\' standalone='yes'?>" + "\\n" + "<formfields>" +"\\n";
			var xml_footer = "</formfields>"+"\\n";
			var xml = xml_header + _fields + xml_footer;
			$('#formfields').val(xml);
		}
	}
}
