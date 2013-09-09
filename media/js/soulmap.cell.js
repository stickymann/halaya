
$(document).ready(function()
{
	if($('#current_no').val() == '0')
	{
		$('#leader_id').keyup(function() {cell.CreateCellID();});
		$('#leader_id').focus(function() {cell.CreateCellID();});
		$('#leader_id').change(function() {cell.CreateCellID();});
	}
});

var cell = new function()
{
	this.getDBReqsURL = function()
	{
		url_dbreqs = siteutils.getBaseURL() + "index.php/soulmap_dbreqs?";
		return url_dbreqs; 
	}
	
	this.CreateCellID = function ()
	{
		var id = $('#id').val();
		var contact_id = $('#leader_id').val();
		var plus = "-C";
			
		qstr = "option=contactplusid&" + "id=" + id + "&contact_id=" + contact_id  + "&plus=" + plus + "&controller=cell";
		$.get(this.getDBReqsURL() + qstr, function(data) { $('#cell_id').val(data); });
	}
}
