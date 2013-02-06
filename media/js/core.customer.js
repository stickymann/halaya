var last_gender, last_fname;

$(document).ready(function()
{
	last_fname = $('#first_name').val();
	last_gender = $('#gender').val();
			
	if( $('#region_id').val() == '0' ){ $('#region_id').val(''); }
	if( $('#phone_home').val() == '0' ){ $('#phone_home').val(''); }
	if( $('#phone_work').val() == '0' ){ $('#phone_work').val(''); }
	if( $('#phone_mobile1').val() == '0' ){ $('#phone_mobile1').val(''); }
	if( $('#phone_mobile2').val() == '0' ){ $('#phone_mobile2').val(''); }
	$('#customer_type').change(function() {customer.SetGender();});
	$('#gender').change(function() {customer.SetGenderVal();});
	$('#first_name').change(function() {last_fname = $('#first_name').val();});

	if($('#current_no').val() == '0')
	{
		$('#first_name').keyup(function() {customer.CreateAltID();});
		$('#last_name').keyup(function() {customer.CreateAltID();});
		$('#first_name').change(function() {customer.CreateAltID();});
		$('#last_name').change(function() {customer.CreateAltID();});
	}
});
		
var customer = new function() 
{
	this.CreateAltID = function ()
	{
		var id = $('#id').val();
		var fn = $('#first_name').val();
		var ln = $('#last_name').val();
			
		customer_params = "option=customerid&" + "id=" + id + "&" + "firstname=" + fn + "&" + "lastname=" + ln + "&controller=customer";;
		siteutils.runQuery(customer_params,"customer_id","val");
	}
		
	this.SetGender = function ()
	{
		if ($('#customer_type').val() == 'COMPANY')
		{
			$('#gender').val('N');
			$('#first_name').val('CO.');
			$('#first_name').attr('readonly', true);
		}
		else
		{
			if(last_gender == 'N'){last_gender = 'M';}
			$('#gender').val(last_gender);
			$('#first_name').val(last_fname);
			$('#first_name').removeAttr('readonly');
		}
	}
		
	this.SetGenderVal = function()
	{
		if($('#gender').val()=='N')
		{
			$('#customer_type').val('COMPANY');
		}
		else
		{
			$('#customer_type').val('INDIVIDUAL')
			$('#first_name').val(last_fname);
			$('#first_name').removeAttr('readonly');
		}
		last_gender = $('#gender').val();
	}
}
