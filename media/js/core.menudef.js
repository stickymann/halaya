
$(document).ready(function()
{
	if( $('#current_no').val() == '0' )
	{
		//menudef.getNextChildFromParent();
	}
});

var menudef = new function()
{
	this.getNextChildFromParent = function ()
	{
		parent_id = $('#parent_id').val();
		params = "option=nextmenuchild&parent_id=" + parent_id;
		siteutils.runQuery(params,'menu_id','val');
	}

}
