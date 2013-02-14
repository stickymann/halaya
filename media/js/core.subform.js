var lastIndex;

function subform_InitDataGridReadOnly(tt)
{
	$('#'+tt).datagrid(
	{
		onLoadSuccess: function()
		{
			//abstract function, add to controller
			doOnLoadSuccess();
		}	
	});
}

function subform_InitDataGridReadWrite(tt)
{
	$('#'+tt).datagrid(
	{
				toolbar:[{text:'Add',iconCls:'icon-add',handler:function()
					{
						$('#'+tt).datagrid('endEdit', lastIndex);
						//abstract function, add to controller
						DefaultNewRow(tt);
						var lastIndex = $('#'+tt).datagrid('getRows').length-1;
						$('#'+tt).datagrid('selectRow', lastIndex);
						$('#'+tt).datagrid('beginEdit', lastIndex);
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
					//if (lastIndex != rowIndex)
					//{
						$('#'+tt).datagrid('endEdit', lastIndex);
						$('#'+tt).datagrid('beginEdit', rowIndex);
					//}
					lastIndex = rowIndex;
				}		
	});
}