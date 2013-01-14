var firstload = true;

$(document).ready(function()
{
	var isloginok = 0;
});
		
var userchangepassword = new function()
{
	this.LoadForm = function()
	{
		var cp_params = "";
		siteutils.dialogWindow("chklight",350,150,"Enter New Password");
		if(firstload)
		{
			cp_params = 'option=changepasswordform';
			siteutils.runQuery(cp_params,"chkresult","html");
			//setTimeout( function(){ userchangepassword.SetChangeInputs(); },1000);
			firstload = false;
		}
	}
	
	this.UpdatePasswordChecks = function()
	{
		var pass = $('#cp_oldpasswd').val();
		var user  = $('#js_idname').val();
		cp_params = 'option=loginok&user='+ user + "&pass=" + pass;
		siteutils.runQuery(cp_params,"cp_isloginok","val");
		setTimeout( function(){ userchangepassword.CompareInputs(); },500);
	}	
		
	this.CompareInputs = function()
	{
		var logintext = "";
		var passtext = "";
		var passwd_old	 = false;
		var passwd_match = false;
		var passwd_good  = false;
		var passwd_blank = true;

		if( $('#cp_isloginok').val()=="1" )
		{
			passwd_old	 = true;
			logintext = '<span style="color:green;padding 5px;">Old Password: Correct</span>';
		} 
		else 
		{
			passwd_old	 = false;
			logintext = '<span style="color:red;padding 5px;">Old Password: Incorrect<span>';
		}
			
		if( $('#cp_newpasswd').val() == $('#cp_conpasswd').val() )
		{ 
			passwd_match = true;
			passtext = '<span style="color:green;padding 5px;">New Password: Match<span>';
		} 
		else 
		{
			passwd_match = false;
			passtext = '<span style="color:red;padding 5px;">New Password: Mis-match<span>';
		}
			
		if( $('#cp_newpasswd').val() =="" && $('#cp_conpasswd').val() ==""){ passwd_blank  = true; } else { passwd_blank  = false; }
		if( $('#cp_newpasswd').val().length > 2 )
		{ 
			passwd_good  = true;
		} 
		else 
		{ 
			passwd_good  = false;
			passtext = '<span style="color:red;padding 5px;">New Password: Too Short<span>';
		}
		$('#cp_logintext').html(logintext + " , ");
		$('#cp_passtext').html(passtext);
			
		if( passwd_old && passwd_match && passwd_good && !(passwd_blank))
		{
			$('#password').val( $('#cp_newpasswd').val() );
		}
		else
		{
			$('#password').val("");
		}
	}
	
	this.SetChangeInputs = function()
	{
		$('#cp_oldpasswd').keyup( function(){ userchangepassword.UpdatePasswordChecks(); });
		$('#cp_newpasswd').keyup(function(){ userchangepassword.CompareInputs(); });
		$('#cp_conpasswd').keyup(function(){ userchangepassword.CompareInputs(); });
	}
}
