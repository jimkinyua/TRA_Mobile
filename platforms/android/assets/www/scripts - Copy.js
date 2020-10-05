// JavaScript Document
//var serverurl = 'http://192.168.1.49:32155/simukdb/ispec_code/';
//var serverurl = 'http://192.168.1.49:32155/simukdb/';
//var serverurl = 'http://196.201.224.102:32155/simukdb/ispec_code/';
var serverurl = 'http://197.232.42.118:8081/simukdb/ispec_code/';
//var serverurl = 'http://attainsvr6:32155/simukdb/ispec_code/';
//var serverurl = 'http://localhost:/kdb/ispec_code/';

function logout()
{
	localStorage.setItem("Logged_in", 0);
	localStorage.removeItem("UserID");
	localStorage.removeItem("UserName")
	document.getElementById('content').innerHTML = fetch_page("login.html?i=1");
	document.getElementById('mymenu').innerHTML = fetch_page("menu1.html?i=1");	
	document.getElementById('notification').innerHTML = '';	
}

function verifylogin()
{	
	Logged_in = localStorage.getItem("Logged_in"); 
		if (Logged_in==1)
		{
			UserID 	 = localStorage.getItem("UserID")
			
			UserName = localStorage.getItem("UserName")
			url = "menu2.html?i=1";
			document.getElementById('mymenu').innerHTML = fetch_page(url);	
			document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
			document.getElementById('Uname').innerHTML = UserName;
			//load_users_list();	
			document.getElementById('content').innerHTML = fetch_page("home.html?i=1");		
			navigator.geolocation.getCurrentPosition(onSuccess1, onError1);
		} 
		else
		{
			document.getElementById('mymenu').innerHTML = fetch_page("menu1.html?i=1");	
			document.getElementById('content').innerHTML = fetch_page("login.html?i=1");		
		}
}

function onSuccess1()
{
	
}

function onError1()
{
}

function login(Uname, Passwd)
{
	localStorage.setItem("Logged_in", 0);
	localStorage.removeItem("UserID");
	localStorage.removeItem("UserName");
	localStorage.removeItem("CompanyID");
	
	//alert('hi');
	url = serverurl+"login.php?uname="+Uname+"&passwd="+Passwd;
	//alert(url);
	userobj = fetch_data(url);		
	//alert(userobj.jData[0].result);
	result = userobj.jData[0].result;
	
	if (result==1)
	{			
		//loadpage('home.html?i=1','content'); 
		//loadmypage('users_list.html?i=0','content','loader','users')
		UserID 	 = userobj.jData[0].UserID;
		UserName = userobj.jData[0].UserName;
		RegionStationsID = userobj.jData[0].RegionStationsID;
		
		localStorage.setItem("Logged_in", 1);
		localStorage.setItem("UserID", UserID);
		localStorage.setItem("UserName", UserName);
		localStorage.setItem("RegionStationsID", RegionStationsID);
		
		url = "menu2.html?i=1";
		document.getElementById('mymenu').innerHTML = fetch_page(url);	
		document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
		document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();
		document.getElementById('content').innerHTML = fetch_page("home.html?i=1");	
	} 
	else
	{	
		msg = result;	
		document.getElementById('msg').innerHTML = msg;
		alert(msg);
	}
	alert( url);
}

function about_us()
{
	document.getElementById('content').innerHTML = fetch_page("aboutus.html?i=1");
	document.getElementById('mymenu').innerHTML = fetch_page("menu1.html?i=1");	
	document.getElementById('notification').innerHTML = '';	
}

function about_us1()
{
	document.getElementById('content').innerHTML = fetch_page("aboutus.html?i=1");
	//document.getElementById('mymenu').innerHTML = fetch_page("menu2.html?i=1");	
	//document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//	document.getElementById('Uname').innerHTML = UserName;
}

function home()
{
	document.getElementById('content').innerHTML = fetch_page("login.html?i=1");
	document.getElementById('mymenu').innerHTML = fetch_page("menu1.html?i=1");	
	document.getElementById('notification').innerHTML = '';
}

function home_loggedin()
{
	
	document.getElementById('content').innerHTML = fetch_page("home.html?i=1");
	document.getElementById('mymenu').innerHTML = fetch_page("menu2.html?i=1");	
	document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	document.getElementById('Uname').innerHTML = UserName;
}

function help()
{
	document.getElementById('content').innerHTML = fetch_page("help.html?i=1");
	document.getElementById('notification').innerHTML = '';

}

function help1()
{
	document.getElementById('content').innerHTML = fetch_page("help.html?i=1");
	document.getElementById('notification').innerHTML = '';
	//document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	document.getElementById('Uname').innerHTML = UserName;
}

function change_password_page()
{
	//url = "menu2.html?i=1";
	//document.getElementById('mymenu').innerHTML = fetch_page(url);
	//document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//document.getElementById('Uname').innerHTML = UserName;	
	document.getElementById('content').innerHTML = fetch_page("change_password.html?i=1");
	
}

function reset_password_page()
{
	//url = "menu2.html?i=1";
	//document.getElementById('mymenu').innerHTML = fetch_page(url);
	//document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//document.getElementById('Uname').innerHTML = UserName;	
	document.getElementById('content').innerHTML = fetch_page("reset_password.html?i=1");
	
}

function change_password(OriginalPassword, NewPassword, ConfirmPassword)
{
	
	original_password = document.getElementById('original_password').value;
	//alert(original_password);
	
	new_password = document.getElementById('new_password').value;
	//alert(new_password);
	confirm_password = document.getElementById('confirm_password').value;
	//alert(confirm_password);
	
	if(new_password == confirm_password)
	{
		
			//alert('sdss');
		   UserID   = localStorage.getItem("UserID");
		   url = serverurl+"change_password.php?original_password="+original_password+"&new_password="+new_password+"&UserID="+UserID;
		   userobj = fetch_data(url);		
		
		   result = userobj.jData[0].Result;
		 
		  // alert(result);
		   //error here
		   
		   document.getElementById('message').value = result;
		   
		   document.getElementById('original_password').value = '';
		   document.getElementById('new_password').value = '';
		   document.getElementById('confirm_password').value = '';
		   
		  // document.getElementById('message').innerHTML = 'Password changed';
		   alert("Your password has been changed. Log in with your new password.");
		   
		   localStorage.setItem("Logged_in", 0);
	       localStorage.removeItem("UserID");
	       localStorage.removeItem("UserName")
	       document.getElementById('content').innerHTML = fetch_page("login.html?i=1");
	       document.getElementById('mymenu').innerHTML = fetch_page("menu1.html?i=1");	
	       document.getElementById('notification').innerHTML = '';
	}
	else
	{
		message = 'Your New Password and Confirm Password do not match.';
		
		document.getElementById('message').innerHTML = message;
	}
}

function reset_password(Email)
{

	if(Email != '')
	{
		   url = serverurl+"reset_password.php?Email="+Email;
		   userobj = fetch_data(url);		
		
		   result = userobj.jData[0].Result;
		   code = userobj.jData[0].Code;
		   
		   if (code == '0')
		   {
			   document.getElementById('content').innerHTML = fetch_page("reset_password_code.html?i=1");
			   document.getElementById('message').innerHTML = result;
			   
		   } else
		   {
			   document.getElementById('message').innerHTML = result;
		   }
	}
	else
	{
		message = 'Please enter a valid email address.';
		
		document.getElementById('message').innerHTML = message;
	}
}

function reset_password_code(Code, NewPassword, ConfirmPassword)
{
	
	code = document.getElementById('code').value;
	//alert(original_password);
	
	new_password = document.getElementById('new_password').value;
	//alert(new_password);
	confirm_password = document.getElementById('confirm_password').value;
	//alert(confirm_password);
	
	if(new_password == confirm_password)
	{
			//alert('sdss');
		   UserID   = localStorage.getItem("UserID");
		   url = serverurl+"reset_password_code.php?code="+code+"&new_password="+new_password;
		   userobj = fetch_data(url);		
		
		   result = userobj.jData[0].Result;
		 
		  // alert(result);
		   //error here
		   
		   document.getElementById('message').value = result;
		   
		   document.getElementById('code').value = '';
		   document.getElementById('new_password').value = '';
		   document.getElementById('confirm_password').value = '';
		   
		  // document.getElementById('message').innerHTML = 'Password changed';
		   alert("Your password has been changed. Log in with your new password.");
		   
		   localStorage.setItem("Logged_in", 0);
	       localStorage.removeItem("UserID");
	       localStorage.removeItem("UserName")
	       document.getElementById('content').innerHTML = fetch_page("login.html?i=1");
	       document.getElementById('mymenu').innerHTML = fetch_page("menu1.html?i=1");	
	       document.getElementById('notification').innerHTML = '';
	}
	else
	{
		message = 'Your New Password and Confirm Password do not match.';
		
		document.getElementById('message').innerHTML = message;
	}
}

function user_profile()
{
	   //url = "menu2.html?i=1";
	  // document.getElementById('mymenu').innerHTML = fetch_page(url);	
	   //document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	   document.getElementById('Uname').innerHTML = UserName;
	   document.getElementById('content').innerHTML = fetch_page("user_profile.html?i=1");
    
   
	   UserID 	 = localStorage.getItem("UserID")
	   
	   url = serverurl +"user_details.php?UserID="+UserID;

	   userobj = fetch_data(url);
		 
	   UserName = userobj.jData[0].UserName;
	   UserFullName = userobj.jData[0].UserFullName;
	   //alert(UserFullName);
	   UserEmail = userobj.jData[0].UserEmail;
	   RegionStationName = userobj.jData[0].RegionStationName;


		 document.getElementById('username').value = UserName;
		 document.getElementById('username').style.backgroundColor = '#D8D8D8'
		 document.getElementById('fullname').value = UserFullName;
		 document.getElementById('fullname').style.backgroundColor = '#D8D8D8'
		 document.getElementById('emailaddress').value = UserEmail;
		 document.getElementById('emailaddress').style.backgroundColor = '#D8D8D8'
		 document.getElementById('RegionStationName').value = RegionStationName;
		 document.getElementById('RegionStationName').style.backgroundColor = '#D8D8D8'

   
}

function post_data(url)
{
	xmlhttp=GetXmlHttpObject()
	if (xmlhttp==null)
 	{
 		alert ("Browser does not support HTTP Request")
 		return
 	}
	xmlhttp.open("POST",url,false);
	xmlhttp.send();
	rest = xmlhttp.responseText;
	//var obj = JSON.parse(rest);
	return rest;	
}

function fetch_data(url)
{
	//alert(url);
	xmlhttp=GetXmlHttpObject()
	if (xmlhttp==null)
 	{
 		alert ("Browser does not support HTTP Request")
 		return
 	}
	xmlhttp.open("GET",url,false);
	xmlhttp.send();
	rest = xmlhttp.responseText;
	//alert(url+' '+rest);
	var obj = JSON.parse(rest);
	
	return obj;	
}

function fetch_page(url)
{
	var rand = Math.random();
	if (url.indexOf("?")!=-1)
	{
		theurl = url+'&q='+rand;
	} else
	{
		theurl = url+'?q='+rand;
	}	
	xmlhttp=GetXmlHttpObject()
	if (xmlhttp==null)
 	{
 		alert ("Browser does not support HTTP Request")
 		return
 	}

	try 
	{
		xmlhttp.open("GET",theurl,false);
		xmlhttp.send();
		rest = xmlhttp.responseText;
	}
	catch(err) 
	{
		//document.getElementById("demo").innerHTML = err.message;
		rest = "System Error";
	}			
	return rest;	
}

function GetXmlHttpObject()
{
 var xmlHttp=null;
 try
  {
   // Firefox, Opera 8.0+, Safari
   xmlHttp=new XMLHttpRequest();
  }
 catch (e)
  {
   //Internet Explorer
   try
    {
     xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
   catch (e)
    {
     xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
  }
 return xmlHttp;
}

function load_applicants()
{
	 UserID = localStorage.getItem("UserID");
	 RegionStationsID = localStorage.getItem("RegionStationsID");
	 //alert(UserID);
	 //url = "menu2.html?i=1";
	 //document.getElementById('mymenu').innerHTML = fetch_page(url);
	document.getElementById('content').innerHTML = fetch_page("list_of_applicants.html?i=1&UserID="+UserID+'&RegionStationsID='+RegionStationsID);
	// document.getElementById('Uname').innerHTML = UserName;

		
      $(function(){
           $('#dataTables-1').dataTable( {
                   "bProcessing": true,
                   "autoWidth": false,
                   "sAjaxSource": serverurl+"load_applicants.php?UserID="+UserID+'&RegionStationsID='+RegionStationsID,
					iDisplayLength: 100
						
                                           
      } );
       }); 
	   
}

//For Distribution Points
function load_distribution_points()
{
	 
	  LicenceApplicationID   = localStorage.getItem("LicenceApplicationID");
	 //alert(LicenceApplicationID);
	// url = "menu2.html?i=1";
	// document.getElementById('mymenu').innerHTML = fetch_page(url);
	 document.getElementById('content').innerHTML = fetch_page("list_of_distribution_points.html?i=1&LicenceApplicationID="+LicenceApplicationID);
	// document.getElementById('Uname').innerHTML = UserName;

		
      $(function(){
           $('#dataTables-D').dataTable( {
                   "bProcessing": true,
                   "autoWidth": false,
                   "sAjaxSource": serverurl+"load_distribution_points.php?LicenceApplicationID="+LicenceApplicationID,
					iDisplayLength: 100
						
                                           
      } );
       }); 
	   
	 AuditID   = localStorage.getItem("AuditID");
	 document.getElementById('AuditID').value = AuditID;
	   
}

//For Scheduled Applications
function load_scheduled()
{
	 UserID   = localStorage.getItem("UserID");
	 
	 //url = "menu2.html?i=1";
	 //document.getElementById('mymenu').innerHTML = fetch_page(url);
	 document.getElementById('content').innerHTML = fetch_page("list_of_scheduled.html?i=1&UserID="+UserID);
	 //document.getElementById('Uname').innerHTML = UserName;

		
      $(function(){
           $('#dataTables-11').dataTable( {
                   "bProcessing": true,
                   "autoWidth": false,
                   "sAjaxSource": serverurl+"scheduled_inspections.php?UserID="+UserID,
					iDisplayLength: 100
						
                                           
      } );
       });	   
}

function load_unscheduled()
{
	 UserID = localStorage.getItem("UserID");	 
	 document.getElementById('content').innerHTML = fetch_page("list_of_unscheduled.html?i=1&UserID="+UserID);
		
      $(function(){
           $('#dataTables-11').dataTable( {
                   "bProcessing": true,
                   "autoWidth": false,
                   "sAjaxSource": serverurl+"unscheduled_inspections.php?UserID="+UserID,
					iDisplayLength: 100
						
                                           
      } );
       });	   
}

function load_upcoming_scheduled()
{
	 UserID   = localStorage.getItem("UserID");	
	 document.getElementById('content').innerHTML = fetch_page("list_of_scheduled.html?i=1&UserID="+UserID);
		
      $(function(){
           $('#dataTables-11').dataTable( {
                   "bProcessing": true,
                   "autoWidth": false,
                   "sAjaxSource": serverurl+"upcoming_scheduled_inspections.php?UserID="+UserID,
					iDisplayLength: 100
						
                                           
      } );
       });	   
}

function setDatePicker(dateToBeShown)
{
	$("#datepicker").datepicker({
        date: dateToBeShown, // set init date
        //format: "Y-m-d", // set output format
        effect: "fade", // none, slide, fade
        position: "bottom", // top or bottom,
        locale: 'en', // 'ru' or 'en', default is $.Metro.currentLocale
    });
}

function save_scheduled_inspections_edit()
{
	UserID = localStorage.getItem("UserID");
	AuditID = localStorage.getItem("AuditID");
	AuditDate = document.getElementsByName('AuditDate')[0].value;	
	
	url = serverurl+"unscheduled_inspections_details_edit.php?UserID="+UserID +"&AuditID="+AuditID +"&AuditDate=" +AuditDate;
	userobj = fetch_data(url);
	 
	result = userobj.aaData[0].Result;
	if (result == 1) {
		load_scheduled();
	} else {
		alert('Your data was not saved!');
	}	
}

function save_unscheduled_inspections_edit()
{
	UserID = localStorage.getItem("UserID");
	AuditID = localStorage.getItem("AuditID");
	AuditDate = document.getElementsByName('AuditDate')[0].value;	
	
	url = serverurl+"unscheduled_inspections_details_edit.php?UserID="+UserID +"&AuditID="+AuditID +"&AuditDate=" +AuditDate;
	userobj = fetch_data(url);
	 
	result = userobj.aaData[0].Result;
	if (result == 1) {
		load_unscheduled();
	} else {
		alert('Your data was not saved!');
	}	
}

function scheduled_inspections_edit(AuditID, LicenceApplicationID)
{
	localStorage.setItem("AuditID", AuditID);
	localStorage.setItem("LicenceApplicationID", LicenceApplicationID);
	UserID = localStorage.getItem("UserID");
	
	document.getElementById('content').innerHTML = fetch_page("scheduled_inspection_edit.html?i=1");
	
	url = serverurl +"unscheduled_inspections_details.php?AuditID="+AuditID;
	userobj = fetch_data(url);
	 
	AuditID = userobj.jData[0].AuditID;
	localStorage.setItem("AuditID", AuditID);
	AuditID   = localStorage.getItem("AuditID");
	
	AuditDate = userobj.jData[0].AuditDate;
	setDatePicker(AuditDate);
}

function unscheduled_inspections_edit(AuditID, LicenceApplicationID)
{
	localStorage.setItem("AuditID", AuditID);
	localStorage.setItem("LicenceApplicationID", LicenceApplicationID);
	UserID = localStorage.getItem("UserID");
	
	document.getElementById('content').innerHTML = fetch_page("unscheduled_inspection_edit.html?i=1");
	
	url = serverurl +"unscheduled_inspections_details.php?AuditID="+AuditID;
	userobj = fetch_data(url);
	 
	AuditID = userobj.jData[0].AuditID;
	localStorage.setItem("AuditID", AuditID);
	AuditID   = localStorage.getItem("AuditID");
	
	AuditDate = userobj.jData[0].AuditDate;
	setDatePicker(AuditDate);
}

function applicant_details(AuditID, LicenceApplicationID, LicenceType)
{
 
    localStorage.setItem("AuditID", AuditID);	
	localStorage.setItem("LicenceType", LicenceType);
	localStorage.setItem("LicenceApplicationID", LicenceApplicationID);
	
	//alert(LicenceType);	
	//url = "menu2.html?i=1";
	//document.getElementById('mymenu').innerHTML = fetch_page(url);	
	//document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	
	document.getElementById('Uname').innerHTML = UserName;
	document.getElementById('content').innerHTML = fetch_page("applicant_detail.html?i=1"); 
	url = serverurl +"applicant_details.php?AuditID="+AuditID;
   
   //alert(LicenceApplicationID);	 
	// AuditID   = localStorage.getItem("AuditID");	 
	//alert(AuditID);	
	//localStorage.setItem("LicenceApplicationID", LicenceApplicationID);
	//alert(LicenceApplicationID);
	 
	 userobj = fetch_data(url);
	 
	 No = userobj.jData[0].No;
	 localStorage.setItem("No", No);
	 No   = localStorage.getItem("No");
	 CustomerName = userobj.jData[0].Name;
	 Address = userobj.jData[0].Address;
	 Address2 = userobj.jData[0].Address2;
	 Address3 = userobj.jData[0].Address3;
	 City = userobj.jData[0].City;
	 PhoneNo = userobj.jData[0].PhoneNo;
	 Mobile1 = userobj.jData[0].Mobile1;
	 Mobile2 = userobj.jData[0].Mobile2;
	 CountryRegionCode = userobj.jData[0].CountryRegionCode;
	 PostCode = userobj.jData[0].PostCode;
	 Email1 = userobj.jData[0].Email1;
	 Email = userobj.jData[0].Email;
	 HomePage = userobj.jData[0].HomePage;
	 RegisteredOffice = userobj.jData[0].RegisteredOffice;
	 PhoneNo1 = userobj.jData[0].PhoneNo1;
	 LicenceApplicationID = userobj.jData[0].LicenceApplicationID;
	 
	 //RegionName = userobj.jData[0].RegionName;
	// SubCountyName = userobj.jData[0].SubCountyName;
	// RegionStationName = userobj.jData[0].RegionStationName;
	 //alert(CustomerName);
	//url = "menu3.html?i=1";
	//document.getElementById('mymenu').innerHTML = fetch_page(url);			
	//localStorage.setItem("LicenceApplicationID", LicenceApplicationID);
	 //alert(LicenceApplicationID);
	
	 document.getElementById('CustomerName').value = CustomerName;
	 document.getElementById('CustomerName').style.backgroundColor = '#D8D8D8'
	 document.getElementById('Address').value = Address;
	 document.getElementById('Address').style.backgroundColor = '#D8D8D8'
	 document.getElementById('Address2').value = Address2;
	 document.getElementById('Address2').style.backgroundColor = '#D8D8D8'
	 document.getElementById('address3').value = Address3;
	 document.getElementById('address3').style.backgroundColor = '#D8D8D8'
	 document.getElementById('city').value = City;  
	 document.getElementById('city').style.backgroundColor = '#D8D8D8'
	 document.getElementById('countryregioncode').value = CountryRegionCode;
	 document.getElementById('countryregioncode').style.backgroundColor = '#D8D8D8'
	 document.getElementById('postcode').value = PostCode;
	 document.getElementById('postcode').style.backgroundColor = '#D8D8D8'
	 document.getElementById('registeredoffice').value = RegisteredOffice;
	 document.getElementById('registeredoffice').style.backgroundColor = '#D8D8D8'
	 document.getElementById('phonenumber').value = PhoneNo;
	 document.getElementById('phonenumber').style.backgroundColor = '#D8D8D8'
	 document.getElementById('phonenumber1').value = PhoneNo1;  
	 document.getElementById('phonenumber1').style.backgroundColor = '#D8D8D8'
	 document.getElementById('mobilenumber').value = Mobile1;
	 document.getElementById('mobilenumber').style.backgroundColor = '#D8D8D8'
	 document.getElementById('mobilenumber1').value = Mobile2;
	 document.getElementById('mobilenumber1').style.backgroundColor = '#D8D8D8'
	 document.getElementById('emailaddress').value = Email;
	 document.getElementById('emailaddress').style.backgroundColor = '#D8D8D8'
	 document.getElementById('emailaddress1').value = Email1;
	 document.getElementById('emailaddress1').style.backgroundColor = '#D8D8D8'
	 document.getElementById('homepage').value = HomePage;  
	 document.getElementById('homepage').style.backgroundColor = '#D8D8D8'
	 
	 //document.getElementById('option1').textContent = RegionName;  
	 //document.getElementById('option2').textContent = SubCountyName;  
	 //document.getElementById('option3').textContent = RegionStationName;
}

function reschedule()
{
	UserID = localStorage.getItem("UserID");
    LicenceType = localStorage.getItem("LicenceType");
	AuditID = localStorage.getItem("AuditID");	
	document.getElementById('content').innerHTML = fetch_page("reschedule.html");
	
}

function reschedule_audit(form)
{
	var NewDate = form.NewDate.value;
	var Reasons = form.Reasons.value;
	UserID = localStorage.getItem("UserID");
    LicenceType = localStorage.getItem("LicenceType");
	AuditID = localStorage.getItem("AuditID");
	if (NewDate != '' && Reasons != '')
	{
		   url = serverurl+"reschedule.php?NewDate="+NewDate+'&Reasons='+Reasons+'&AuditID='+AuditID+'&UserID='+UserID;
		   userobj = fetch_data(url);		
		
		   result = userobj.jData[0].Result;
		   code = userobj.jData[0].Code;
		   
		   if (code == '0')
		   {
			  load_applicants();
			   
		   } else
		   {
			   document.getElementById('message').innerHTML = result;
		   }
	}
	else
	{
		message = 'Please enter all required Values';
		
		document.getElementById('message').innerHTML = message;
	}
	
}

function reschedule_cancel()
{
	UserID = localStorage.getItem("UserID");
    LicenceType = localStorage.getItem("LicenceType");
	AuditID = localStorage.getItem("AuditID");
	LicenceApplicationID = localStorage.getItem("LicenceApplicationID");
	
	applicant_details(AuditID,LicenceApplicationID,LicenceType);
}

function load_checklist()
{
	 UserID = localStorage.getItem("UserID");
     LicenceType = localStorage.getItem("LicenceType");
	 AuditID = localStorage.getItem("AuditID");
	
	//url = "menu2.html?i=1";
	//document.getElementById('mymenu').innerHTML = fetch_page(url);	
	//document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//	document.getElementById('Uname').innerHTML = UserName;
    document.getElementById('content').innerHTML = fetch_page("checklist.html?AuditID="+AuditID+"&UserID="+UserID);
    //document.getElementById('mymenu').innerHTML = fetch_page(url);
	
	 //UserID   = localStorage.getItem("UserID");
	// LicenceApplicationID   = localStorage.getItem("LicenceApplicationID");
	 
		
	 //alert(LicenceApplicationID);
	 /*url = serverurl+"auditdetails.php?UserID="+UserID +"&ApplicationID="+LicenceApplicationID ;
	 userobj = fetch_data(url);
	 
	 AuditChecklistID = userobj.jData[0].AuditChecklistID;
	 localStorage.setItem("AuditChecklistID", AuditChecklistID);
	 AuditChecklistID   = localStorage.getItem("AuditChecklistID");
	 */
	 //alert(AuditChecklistID);
	
	 //AuditID = userobj.jData[0].AuditID;
	 //localStorage.setItem("AuditID", AuditID);
	 //AuditID   = localStorage.getItem("AuditID");
	 
	 // AuditReportId   = localStorage.getItem("AuditReportId");
	  // alert(AuditID);

 
      $(function(){
           $('#dataTables-2').dataTable( {
                   "bProcessing": true,
                   "autoWidth": false,
                   "sAjaxSource": serverurl+"checklist1.php?AuditID="+AuditID+"&LicenceType="+LicenceType+"&serverurl="+serverurl,						 
                   iDisplayLength: 100                                           
      } );
       });
	 //LicenceApplicationID   = localStorage.getItem("LicenceApplicationID");
	 //document.getElementById('ApplicationID').value = LicenceApplicationID;  
	// document.getElementById('AuditID').value = AuditID;  
	   
}

function load_auditreportprevious()
{
	document.getElementById('content').innerHTML = fetch_page("auditreport.html?i=1");
			  
	UserID   = localStorage.getItem("UserID");
	AuditID   = localStorage.getItem("AuditID");
	AuditReportID  = localStorage.getItem("AuditReportID");
				  
	url = serverurl+"get_findings_and_conclusions.php?UserID="+UserID +"&AuditID="+AuditID+"&AuditReportID="+AuditReportID;
				
	userobj = fetch_data(url);
				
	AuditFindingsSummary = userobj.jData[0].AuditFindingsSummary;
	AuditConclusion = userobj.jData[0].AuditConclusion;

	document.getElementById('AuditFindingsSummary').value = AuditFindingsSummary;
	// document.getElementById('AuditConclusion').value = AuditConclusion;
}

function load_auditreport22()
{
	UserID   = localStorage.getItem("UserID");
 	AuditID   = localStorage.getItem("AuditID");
	LicenceTypeID = localStorage.getItem("LicenceType");
	//alert(LicenceType);
	//LicenceTypeID   = 2;
	/*localStorage.getItem("LicenceTypeID");*/
	
	getfindings = '';
	getconclusions = '';
	
 	url_1 = serverurl+"validate_reportdetails.php?UserID="+UserID +"&AuditID="+AuditID +"&LicenceTypeID=" +LicenceTypeID;
	userobj_1 = fetch_data(url_1); 
 	result_1 = userobj_1.aaData[0].Result;
	if(result_1 == 1)
	{
		url = serverurl+"reportdetails.php?UserID="+UserID +"&AuditID="+AuditID +"&AuditFindingsSummary=" +getfindings 
					+ "&AuditConclusion=" +getconclusions + "&AuditPositiveFindings=" + '' + "&AuditReportStatusID=" + '' 
					+ "&AuditRiskImplications=" + '';
		userobj = fetch_data(url);
	 
		result = userobj.aaData[0].Result;
		
		if(result == 1)
		{
			UserID   = localStorage.getItem("UserID");
			AuditID   = localStorage.getItem("AuditID");
		
			url = serverurl+"get_auditreport.php?UserID="+UserID +"&AuditID="+AuditID;
			
			userobj = fetch_data(url);
			
			//Where to get the AuditReportID
			AuditReportID = userobj.jData[0].AuditReportID;
			localStorage.setItem("AuditReportID", AuditReportID);
			
			//alert(AuditReportID);
			auditresult = userobj.jData[0].AuditResult;
			
			urlstr = '';
			remstr = '';
		
			if(auditresult == 1)
			{	
				for (var i = 0; i < myform.elements.length; i++ ) 
				{
					if (myform.elements[i].type == 'checkbox') 
					{
						if (myform.elements[i].checked == true) 
						{
							urlstr += '&'+myform.elements[i].name;
						} else
						{
							ename = myform.elements[i].name;
							enamearray = ename.split("_");
							if (enamearray[2]!='')
							{
								remstr += '&'+myform.elements[i].name+"=R";		
							}
						}
					}
				}
				AuditReportID  = localStorage.getItem("AuditReportID");
				localStorage.setItem("LicenceApplicationID", LicenceApplicationID);
				AuditID   = localStorage.getItem("AuditID");
			
				url = serverurl+"audit_checklist.php?UserID="+UserID+"&AuditID="+AuditID+urlstr+remstr+"&LicenceApplicationID="+LicenceApplicationID;
			
				rest = post_data(url);
				//alert(rest);
				if (rest=='1')
				{ 
					 // url = "menu2.html?i=1";
					 // document.getElementById('mymenu').innerHTML = fetch_page(url);	
					//  document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
					//  document.getElementById('Uname').innerHTML = UserName;
					// document.getElementById('mymenu').innerHTML = fetch_page(url);
					  document.getElementById('content').innerHTML = fetch_page("auditreport.html?i=1");					 
					  
					  UserID = localStorage.getItem("UserID");
					  AuditID = localStorage.getItem("AuditID");
					  AuditReportID = localStorage.getItem("AuditReportID");
					  
					  url = serverurl+"get_findings_and_conclusions.php?UserID="+UserID +"&AuditID="+AuditID+"&AuditReportID="+AuditReportID;					
					  userobj = fetch_data(url);
					
					  AuditFindingsSummary = userobj.jData[0].AuditFindingsSummary;
					  AuditConclusion = userobj.jData[0].AuditConclusion;					  
					  document.getElementById('AuditFindingsSummary').value = AuditFindingsSummary;
					  
					 //document.getElementById('AuditConclusion').value = AuditConclusion;
					  
				}
			}		
		}
	}
	else
	{
		alert('Please Fill the required data');
	}
}

function load_auditreport()
{
	getfindings = '';
	getconclusions = '';
	
	UserID = localStorage.getItem("UserID");
 	AuditID = localStorage.getItem("AuditID");
	LicenceTypeID = localStorage.getItem("LicenceType");
	
	url = serverurl+"reportdetails.php?UserID="+UserID +"&AuditID="+AuditID +"&AuditFindingsSummary=" +getfindings 
					+ "&AuditConclusion=" +getconclusions + "&AuditPositiveFindings=" + '' + "&AuditReportStatusID=" + '' 
					+ "&AuditRiskImplications=" + '';
	userobj = fetch_data(url); 
	result = userobj.aaData[0].Result;
	if(result == 1)
	{
		urlstr = '';
		remstr = '';
		
		url = serverurl+"get_auditreport.php?UserID="+UserID +"&AuditID="+AuditID;			
		userobj = fetch_data(url);
		
		//Where to get the AuditReportID
		AuditReportID = userobj.jData[0].AuditReportID;
		localStorage.setItem("AuditReportID", AuditReportID);
		auditresult = userobj.jData[0].AuditResult;
		
		if(auditresult == 1)
		{
			for (var i = 0; i < myform.elements.length; i++ ) 
			{
				if (myform.elements[i].type == 'checkbox') 
				{
					if (myform.elements[i].checked == true) 
					{
						urlstr += '&'+myform.elements[i].name;
					} else
					{
						ename = myform.elements[i].name;
						enamearray = ename.split("_");
						if (enamearray[2]!='')
						{
							remstr += '&'+myform.elements[i].name+"=R";		
						}
					}
				}
			}
			
			AuditReportID  = localStorage.getItem("AuditReportID");
			localStorage.setItem("LicenceApplicationID", LicenceApplicationID);
			AuditID   = localStorage.getItem("AuditID");
		
			url = serverurl+"audit_checklist.php?UserID="+UserID+"&AuditID="+AuditID+urlstr+remstr+"&LicenceApplicationID="+LicenceApplicationID;		
			rest = post_data(url)
			if (rest=='1')
			{
				url_1 = serverurl+"validate_reportdetails.php?UserID="+UserID +"&AuditID="+AuditID +"&LicenceTypeID=" +LicenceTypeID;
				userobj_1 = fetch_data(url_1); 
				result_1 = userobj_1.aaData[0].Result;
				if (result_1 == '1')
				{
					document.getElementById('content').innerHTML = fetch_page("auditreport.html?i=1");
					UserID   = localStorage.getItem("UserID");
					AuditID   = localStorage.getItem("AuditID");
					AuditReportID  = localStorage.getItem("AuditReportID");
					
					url = serverurl+"get_findings_and_conclusions.php?UserID="+UserID +"&AuditID="+AuditID+"&AuditReportID="+AuditReportID;					
					userobj = fetch_data(url);					
					AuditFindingsSummary = userobj.jData[0].AuditFindingsSummary;
					AuditConclusion = userobj.jData[0].AuditConclusion;					  
					document.getElementById('AuditFindingsSummary').value = AuditFindingsSummary;
				}
				else
				{
					alert('Please fill the required data!');
				}
			}
		}
	}
}

function save_auditreport()
{
	
	//$("#message").progressbar();
	
	//Get the values from the two textareas
	getfindings = document.getElementById('AuditFindingsSummary').value;
	
	//getconclusions = document.getElementById('AuditConclusion').value;
	getconclusions = "";
	
	//call the php file
	UserID   = localStorage.getItem("UserID");
	AuditID   = localStorage.getItem("AuditID");
	 //alert(AuditID);
	url = serverurl+"editreportdetails.php?UserID="+UserID +"&AuditID="+AuditID +"&AuditFindingsSummary=" +getfindings + "&AuditConclusion=" +getconclusions + "&AuditPositiveFindings=" + '' + "&AuditReportStatusID=" + '' + "&AuditRiskImplications=" + '';
	userobj = fetch_data(url);
	 
	result = userobj.aaData[0].Result;
	 
	//AuditReportId = userobj.aaData[0].AuditReportId;
	//localStorage.setItem("AuditReportId", AuditReportId);
	//AuditReportId   = localStorage.getItem("AuditReportId");
	AuditReportID  = localStorage.getItem("AuditReportID");
	//alert(AuditReportID);
	 
	 //alert(AuditReportId);
	 //result = 1;
	if (result == 1) {
	
     	navigator.geolocation.getCurrentPosition(onSuccess, onError);
		
		//onSuccess(1); 
    

			// onSuccess Geolocation
			//
			
		  //AuditReportId = userobj.jData[0].AuditReportId;
		 // localStorage.setItem("AuditReportId", AuditReportId);
		 // AuditReportId   = localStorage.getItem("AuditReportId");
     /*   url = "menu2.html?i=1";
		document.getElementById('mymenu').innerHTML = fetch_page(url);	
		document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
		document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();	
		document.getElementById('content').innerHTML = fetch_page("inspection_photo.html?i=1");	
        document.getElementById('message').innerHTML = 'Audit Report has been sent.'
		
		
		
		url = "menu2.html?i=1";
		document.getElementById('mymenu').innerHTML = fetch_page(url);	
		document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
		document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();	
		document.getElementById('content').innerHTML = fetch_page("inspection_photo.html?i=1");
		
		No   = localStorage.getItem("No");
		AuditReportID  = localStorage.getItem("AuditReportID");
		UserID   = localStorage.getItem("UserID");
		//alert(No);
		
		  $(function(){
           $('#dataTables-3').dataTable( {
                   "bProcessing": true,
                   "autoWidth": false,
                   "sAjaxSource": serverurl+"load_requirements.php?CustomerID="+No+"&AuditReportID="+AuditReportID+"&UserID="+UserID,
				   iDisplayLength: 100
                                           
      } );
       }); 
		*/
  
  //alert('Your data is saved');
	}
	else{
	   
	   alert('Your data was not saved.')
	}

 
 
 	
 //document.getElementById('content').innerHTML = fetch_page("auditreport.html?i=1");
 //document.getElementById('mymenu').innerHTML = fetch_page(url);
 
}

function load_photos_documents()
{
	document.getElementById('content').innerHTML = fetch_page("inspection_photo.html?i=1");	
			
		No   = localStorage.getItem("No");
		AuditID  = localStorage.getItem("AuditID");
		UserID   = localStorage.getItem("UserID");
		//alert(No);
		
		$(function(){
           $('#dataTables-3').dataTable( {
                   "bProcessing": true,
                   "autoWidth": false,
                   "sAjaxSource": serverurl+"load_requirements.php?CustomerID="+No+"&AuditID="+AuditID+"&UserID="+UserID,
				   iDisplayLength: 100
                                           
			} );
		}); 
	
}

function onSuccess(position) 
{
	// var element = document.getElementById('geolocation');
	var longitude = position.coords.longitude;
	//alert(longitude);
	var latitude = position.coords.latitude;
	//alert(latitude);
	  
	AuditID  = localStorage.getItem("AuditID");
	url = serverurl+"gpstracker.php?AuditID="+AuditID +"&XLocation="+latitude +"&YLocation=" +longitude;
	  
	//alert(url);
	userobj = fetch_data(url);
	
	Result = userobj.aaData[0].Result;
	//alert(Result);
	//Result =1;
	if(Result == 1)
	{
		//alert('Successful');
		// onSuccess Geolocation

		//AuditReportId = userobj.jData[0].AuditReportId;
		// localStorage.setItem("AuditReportId", AuditReportId);
		// AuditReportId   = localStorage.getItem("AuditReportId");
        //url = "menu2.html?i=1";
	//	document.getElementById('mymenu').innerHTML = fetch_page(url);	
	//	document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//	document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();	
		document.getElementById('content').innerHTML = fetch_page("inspection_photo.html?i=1");	
        document.getElementById('message').innerHTML = 'Audit Report has been sent.'
				
		//url = "menu2.html?i=1";
		//document.getElementById('mymenu').innerHTML = fetch_page(url);	
	//	document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//	document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();	
		document.getElementById('content').innerHTML = fetch_page("inspection_photo.html?i=1");
		
		No   = localStorage.getItem("No");
		AuditID  = localStorage.getItem("AuditID");
		UserID   = localStorage.getItem("UserID");
		//alert(No);
		
		$(function(){
           $('#dataTables-3').dataTable( {
                   "bProcessing": true,
                   "autoWidth": false,
                   "sAjaxSource": serverurl+"load_requirements.php?CustomerID="+No+"&AuditID="+AuditID+"&UserID="+UserID,
				   iDisplayLength: 100
                                           
			} );
		}); 
	   
	} else
	{
	   alert('Turn on your GPS');
	   document.getElementById('message').innerHTML = 'You will not be able to proceed without turning on Location on your phone.';
	   //load_auditreport();
	}
}

    // onError Callback receives a PositionError object
    //
    function onError(error) {
      //  alert('code: '    + error.code    + '\n' +  'message: ' + error.message + '\n');
				alert('Turn on your GPS');
				document.getElementById('message').innerHTML = 'You will not be able to proceed without turning on Location on your phone.';
				//load_auditreport();
    }
	
	
function editApplicantDetails()
{
	
	//populate_regionnames_dropdown();
	//populate_subcountynames_dropdown();
	//populate_regionstation_dropdown();
	
	document.getElementById('CustomerName').removeAttribute('readonly');
	document.getElementById('CustomerName').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('Address').removeAttribute('readonly');
	document.getElementById('Address').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('Address2').removeAttribute('readonly');
	document.getElementById('Address2').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('address3').removeAttribute('readonly');
	document.getElementById('address3').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('city').removeAttribute('readonly');
	document.getElementById('city').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('countryregioncode').removeAttribute('readonly');
	document.getElementById('countryregioncode').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('postcode').removeAttribute('readonly');
	document.getElementById('postcode').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('registeredoffice').removeAttribute('readonly');
	document.getElementById('registeredoffice').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('phonenumber').removeAttribute('readonly');
	document.getElementById('phonenumber').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('phonenumber1').removeAttribute('readonly');
	document.getElementById('phonenumber1').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('mobilenumber').removeAttribute('readonly');
	document.getElementById('mobilenumber').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('mobilenumber1').removeAttribute('readonly');
	document.getElementById('mobilenumber1').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('emailaddress').removeAttribute('readonly');
	document.getElementById('emailaddress').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('emailaddress1').removeAttribute('readonly');
	document.getElementById('emailaddress1').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('homepage').removeAttribute('readonly');
	document.getElementById('homepage').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('regionname').removeAttribute('readonly');
	document.getElementById('regionname').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('subcountyname').removeAttribute('readonly');
	document.getElementById('subcountyname').style.backgroundColor = '#D8D8D8'
	
	document.getElementById('regionstationname').removeAttribute('readonly');
	document.getElementById('regionstationname').style.backgroundColor = '#D8D8D8'
}

function updateApplicantDetails()
{
	CustomerNameText = document.getElementById('CustomerName').value;
	
	AddressText = document.getElementById('Address').value;
	Address2Text = document.getElementById('Address2').value;
	address3Text = document.getElementById('address3').value;
	cityText = document.getElementById('city').value;
	countryregioncodeText = document.getElementById('countryregioncode').value;
	postcodeText = document.getElementById('postcode').value;
	registeredofficeText = document.getElementById('registeredoffice').value;
	phonenumberText = document.getElementById('phonenumber').value;
	phonenumber1Text = document.getElementById('phonenumber1').value;
	mobilenumberText = document.getElementById('mobilenumber').value;
	mobilenumber1Text = document.getElementById('mobilenumber1').value;
	emailaddressText = document.getElementById('emailaddress').value;
	emailaddress1Text = document.getElementById('emailaddress1').value;
	homepageText = document.getElementById('homepage').value;
	regionnameText = document.getElementById('regionname').value;
	subcountynameText = document.getElementById('subcountyname').value;
	regionstationnameText = document.getElementById('regionstationname').value;
	
	//alert(regionnameText);
	//alert(subcountynameText);
	//alert(regionstationnameText);
	//exit;
	
	No   = localStorage.getItem("No");
	
	
	//To Continue
	
	url = serverurl+"edit_applicant.php?CustomerNameText="+CustomerNameText+"&AddressText="+AddressText+"&Address2Text="+Address2Text+"&address3Text="+address3Text+"&cityText="+cityText+"&countryregioncodeText="+countryregioncodeText+"&postcodeText="+postcodeText+"&registeredofficeText="+registeredofficeText+"&phonenumberText="+phonenumberText+"&phonenumber1Text="+phonenumber1Text+"&mobilenumberText="+mobilenumberText+"&mobilenumber1Text="+mobilenumber1Text+"&emailaddressText="+emailaddressText+"&emailaddress1Text="+emailaddress1Text+"&homepageText="+homepageText+"&No="+No + "&regionnameText=" +regionnameText + "&subcountynameText=" +subcountynameText+"&regionstationnameText="+regionstationnameText;
    
	userobj = fetch_data(url);
 
  result = userobj.jData[0].result;
 // alert(result);
 //alert(result);
 //result = 1;
if (result == 1) {
	//alert("Data updated");
	
		//url = "menu2.html?i=1";
		//document.getElementById('mymenu').innerHTML = fetch_page(url);	
		//document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
		//document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();
		document.getElementById('content').innerHTML = fetch_page("home.html?i=1");	
		document.getElementById('message').innerHTML = 'The data has been updated.'
		
}
else
{
	//alert("Data not updated");
	//url = "menu2.html?i=1";
	//	document.getElementById('mymenu').innerHTML = fetch_page(url);	
	//	document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//	document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();
		document.getElementById('content').innerHTML = fetch_page("home.html?i=1");	
		document.getElementById('message').innerHTML = 'The data has not been updated.'
}
	
}

function inspection_images()
{
	
	 //   url = "menu6.html?i=1";
	//	document.getElementById('mymenu').innerHTML = fetch_page(url);	
		//document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//	document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();
		document.getElementById('content').innerHTML = fetch_page("photohome.html?i=1");	
		
}

function populate_regionnames_dropdown()
{
	//get region names
	url = serverurl+"region_names.php" ;
    userobj = fetch_data(url);
	
	var regionname = document.getElementById("regionname"); 
	
	//alert(userobj.jData[1].RegionName);
	//alert(userobj.jData.length);
	
	for(var i=0; i<userobj.jData.length; i++)
	{
		  var opt = userobj.jData[i].RegionName;
		  //alert(opt);
		  var opt1 = userobj.jData[i].RegionID;
		  //alert(opt1);
		  var el = document.createElement("option");
		  el.textContent = opt;
		  el.value = opt1;
		  regionname.appendChild(el);
	}



//var options = ["1", "2", "3", "4", "5"]; 

/*for(var i=0; i<userobj.jData.length; i++) {
    var opt = userobj.jData[i].RegionName;
	alert(opt);
	//var opt1 = userobj.jData[i].RegionID;
    //var el = document.createElement("option");
    //el.textContent = opt;
    //el.value = opt1;
    //regionname.appendChild(el);
}​*/
	
	//var data = userobj.jData;
 
 //var select = document.getElementById("regionname"); 

//$.each(data, function(i, item) {
//    alert(data[i].RegionName);
//});​
}

function populate_subcountynames_dropdown()
{
	//get region names
	url = serverurl+"sub_county_names.php" ;
    userobj = fetch_data(url);
	
	var subcountyname = document.getElementById("subcountyname"); 
	
	//alert(userobj.jData[1].RegionName);
	//alert(userobj.jData.length);
	
	for(var i=0; i<userobj.jData.length - 1; i++)
	{
		  var opt = userobj.jData[i].SubCountyName;
		  //alert(opt);
		  var opt1 = userobj.jData[i].SubRegionStationID;
		  //alert(opt1);
		  var el = document.createElement("option");
		  el.textContent = opt;
		  el.value = opt1;
		  subcountyname.appendChild(el);
	}
}

function populate_subcountynames_dropdown()
{
	//get region names
		url = serverurl+"sub_county_names.php" ;
		userobj = fetch_data(url);
		
		var subcountyname = document.getElementById("subcountyname"); 
		
		//alert(userobj.jData[1].RegionName);
		//alert(userobj.jData.length);
	
	for(var i=0; i<userobj.jData.length ; i++)
    {
		  var opt = userobj.jData[i].SubCountyName;
		  //alert(opt);
		  var opt1 = userobj.jData[i].SubRegionStationID;
		  //alert(opt1);
		  var el = document.createElement("option");
		  el.textContent = opt;
		  el.value = opt1;
		  subcountyname.appendChild(el);
    }
}

function populate_regionstation_dropdown()
{
	//get region names
	url = serverurl+"region_station.php" ;
    userobj = fetch_data(url);
	
	var regionstationname = document.getElementById("regionstationname"); 
	
	//alert(userobj.jData[1].RegionName);
	//alert(userobj.jData.length);
	
	for(var i=0; i<userobj.jData.length; i++)
    {
		  var opt = userobj.jData[i].RegionStationName;
		  //alert(opt);
		  var opt1 = userobj.jData[i].RegionStationID;
		  //alert(opt1);
		  var el = document.createElement("option");
		  el.textContent = opt;
		  el.value = opt1;
		  regionstationname.appendChild(el);
    }
}

function selectphoto()
{
	
	
	 ////   url = "menu2.html?i=1";
	//	document.getElementById('mymenu').innerHTML = fetch_page(url);	
	//	document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//	document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();	
		document.getElementById('content').innerHTML = fetch_page("selectPhoto.html?i=1");
		
		No   = localStorage.getItem("No");
		AuditReportID  = localStorage.getItem("AuditReportID");
		UserID   = localStorage.getItem("UserID");
		
		  $(function(){
           $('#dataTables-3').dataTable( {
                   "bProcessing": true,
                   "autoWidth": false,
                   "sAjaxSource": serverurl+"load_requirements.php?CustomerID="+No+"&AuditReportID="+AuditReportID+"&UserID="+UserID,
                                           
      } );
       }); 
}

function view_photos()
{
	   // url = "menu2.html?i=1";
	//	document.getElementById('mymenu').innerHTML = fetch_page(url);	
	//	document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//	document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();	
		document.getElementById('content').innerHTML = fetch_page("inspection_photo.html?i=1");
		
		No   = localStorage.getItem("No");
		AuditReportID  = localStorage.getItem("AuditReportID");
		UserID   = localStorage.getItem("UserID");
		AuditID   = localStorage.getItem("AuditID");
		
		  $(function(){
           $('#dataTables-3').dataTable( {
                   "bProcessing": true,
                   "autoWidth": false,
                   "sAjaxSource": serverurl+"load_requirements.php?CustomerID="+No+"&AuditID="+AuditID+"&UserID="+UserID,
                                           
      } );
       }); 
		
}

function takephoto(AuditChecklistResultID, AuditChecklistParameterID, LicenceApplicationID, AuditID)
{
	
	   // url = "menu2.html?i=1";
	///	document.getElementById('mymenu').innerHTML = fetch_page(url);	
	//	document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//	document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();	
		document.getElementById('content').innerHTML = fetch_page("takePhoto.html?i=1");
		
		UserID   = localStorage.getItem("UserID");
		No   = localStorage.getItem("No");
		reqDocid   = localStorage.getItem("reqDocid");
		
		document.getElementById('AuditChecklistResultID').value = AuditChecklistResultID;
		document.getElementById('AuditChecklistParameterID').value =  AuditChecklistParameterID;
		document.getElementById('LicenceApplicationID').value =  LicenceApplicationID;
		document.getElementById('AuditID').value =  AuditID;
		
		
		//AuditReportId   = localStorage.getItem("AuditReportID");
		
		//AuditReportId   = localStorage.getItem("AuditReportId");
		AuditReportID  = localStorage.getItem("AuditReportID");
		//alert(AuditReportId);
		
		userobj = get_function_values('RequiredDocumentID');
		RequiredDocumentID = userobj.jData[0].RequiredDocumentID;
		//alert(RequiredDocumentID);
		//RequiredDocumentName = userobj.jData[0].RequiredDocumentName;
		//alert('dfgfg');
		userobj = get_function_values('SystemSetup');
		MaxFileUploadSize = userobj.jData[0].MaxFileUploadSize;
		//alert(MaxFileUploadSize);

		
	    document.getElementById('No').value =  No ;
		//alert(No);
		document.getElementById('UserID').value =  UserID ;
		document.getElementById('reqDocid').value =  RequiredDocumentID ;
		document.getElementById('AuditReportId').value =  AuditReportID ;
		document.getElementById('MaxFileUploadSize').value =  MaxFileUploadSize ;
		
}

function delete_file()
{
	
}

function get_function_values(function_name)
{
	fname = function_name;
	
	url = serverurl+"php_functions.php?function_name="+fname;
    userobj = fetch_data(url);
 	return  userobj;
}

function new_file()
{
	   // url = "menu2.html?i=1";
	//	document.getElementById('mymenu').innerHTML = fetch_page(url);	
	//	document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//	document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();	
		document.getElementById('content').innerHTML = fetch_page("newPhoto.html?i=1");
		
		UserID   = localStorage.getItem("UserID");
		No   = localStorage.getItem("No");
		reqDocid   = localStorage.getItem("reqDocid");
		//AuditReportId   = localStorage.getItem("AuditReportID");
		
		//AuditReportId   = localStorage.getItem("AuditReportId");
		AuditID  = localStorage.getItem("AuditID");
		//alert(AuditReportID);
		
		userobj = get_function_values('RequiredDocumentID');
		RequiredDocumentID = userobj.jData[0].RequiredDocumentID;
		//alert(RequiredDocumentID);
		//RequiredDocumentName = userobj.jData[0].RequiredDocumentName;
		//alert('dfgfg');
		userobj = get_function_values('SystemSetup');
		MaxFileUploadSize = userobj.jData[0].MaxFileUploadSize;
		//alert(MaxFileUploadSize);

		
	    document.getElementById('No').value =  No ;
		//alert(No);
		document.getElementById('UserID').value =  UserID ;
		document.getElementById('reqDocid').value =  RequiredDocumentID ;
		document.getElementById('AuditID').value =  AuditID ;
		document.getElementById('MaxFileUploadSize').value =  MaxFileUploadSize ;
		document.getElementById('CustomerID').value =  No ;
		
}

function add_file(AuditChecklistParameterID)
{
	//alert(CustomerID);
		//load_users_list();	
		document.getElementById('content').innerHTML = fetch_page("addPhoto.html?i=1");
		
		UserID   = localStorage.getItem("UserID");
		No   = localStorage.getItem("No");
		reqDocid   = localStorage.getItem("reqDocid");
		//AuditReportId   = localStorage.getItem("AuditReportID");
		
		//AuditReportId   = localStorage.getItem("AuditReportId");
		AuditID  = localStorage.getItem("AuditID");
		//alert(AuditReportID);
		
		userobj = get_function_values('RequiredDocumentID');
		RequiredDocumentID = userobj.jData[0].RequiredDocumentID;
		//alert(RequiredDocumentID);
		//RequiredDocumentName = userobj.jData[0].RequiredDocumentName;
		//alert('dfgfg');
		userobj = get_function_values('SystemSetup');
		MaxFileUploadSize = userobj.jData[0].MaxFileUploadSize;
		//alert(MaxFileUploadSize);
		
	    document.getElementById('No').value =  No ;
		//alert(No);
		document.getElementById('UserID').value =  UserID ;
		document.getElementById('reqDocid').value =  RequiredDocumentID ;
		document.getElementById('AuditID').value =  AuditID ;
		document.getElementById('MaxFileUploadSize').value =  MaxFileUploadSize ;
		document.getElementById('CustomerID').value = No;
		document.getElementById('AuditChecklistParameterID').value = AuditChecklistParameterID;
		
}

function submitfile1()
{
	var form = document.getElementById('file-form');
	var fileSelect = document.getElementById('myfile');
	var uploadButton = document.getElementById('upload-button');
	//var SessionID = document.getElementById('SessionID').value;
	var description = document.getElementById('description').value;
	
	// Create a new FormData object.
	var formData = new FormData(form);
	//formData.append('myfile', fileSelect.files, fileSelect.name);

	// Set up the request.
	var xhr = new XMLHttpRequest();
	// Open the connection.
	xhr.open('POST', serverurl+"upload_docs.php", true);
	// Set up a handler for when the request finishes.
	xhr.onload = function () 
	{
		if (xhr.status === 200) 
	  	{
			// File(s) uploaded.
			rest = xhr.responseText;
			if (rest == 1)
			{
				load_checklist();	
			} else
			{
				alert(rest);
			}
			//document.getElementById('register_docs').innerHTML = fetch_page('register_docs.php?SessionID='+SessionID+'&DocumentName='+DocumentName);
			//DocumentName.value = '';
			//fileSelect.value ='';
			//uploadButton.innerHTML = 'Upload';
	  	} else 
		{
			alert('An error occurred!');
	  	}
	};	
	// Send the Data.
	xhr.send(formData);		
}

function uploadSelectedPhoto(uploadFile)
{
	Description = document.getElementById('description').value;
	
	//alert(Description);
	
	url = serverurl+"upload.php?uploadFile="+uploadFile;
	userobj = fetch_data(url);		
	
	result = userobj.jData[0].result;
	
}

function _(elementID)
{
 return document.getElementById(elementID);
}

function GetFileSize(fileid) {
 try {
	 var fileSize = 0;
	 //for IE
	 if ($.browser.msie) {
		 //before making an object of ActiveXObject, 
		 //please make sure ActiveX is enabled in your IE browser
		 var objFSO = new ActiveXObject("Scripting.FileSystemObject"); var filePath = $("#" + fileid)[0].value;
		 var objFile = objFSO.getFile(filePath);
		 var fileSize = objFile.size; //size in kb
		 fileSize = fileSize / 1048576; //size in mb 
	 }
	 		//for FF, Safari, Opeara and Others
	 else {
		 fileSize = $("#" + fileid)[0].files[0].size //size in kb
		 fileSize = fileSize / 1048576; //size in mb 
	 }
	 alert("Uploaded File Size is" + fileSize + "MB");
	 }
 catch (e) {
 	alert("Error is :" + e);
 }
}
 function myProgressHandler(event)
{
	 document.getElementById(p_progressbar).style.display = 'block';
		_(loaded_n_total).innerHTML = "Uploaded "+event.loaded+" bytes of "+event.total;
			var percent = (event.loaded / event.total) * 100;
		_(p_progressbar).value = Math.round(percent);
	 _(s_status).innerHTML = Math.round(percent)+"% uploading...please wait";
	 document.getElementById(p_progressbar).style.display = 'none';
}

function myCompleteHandler(event)
{
    _(s_status).innerHTML = event.target.responseText;
    _(p_progressbar).value = 0;
}

function myErrorHandler(event)
{
    _(s_status).innerHTML = "Upload Failed";
}
function myAbortHandler(event)
{
    _(s_status).innerHTML = "Upload Aborted";
}

function submitfile2()
{
	var form = document.getElementById('file-form');
	var fileSelect = document.getElementById('myfile');
	var uploadButton = document.getElementById('upload-button');
	//var SessionID = document.getElementById('SessionID').value;
	var description = document.getElementById('description').value;
	
	// Create a new FormData object.
	var formData = new FormData(form);
	//formData.append('myfile', fileSelect.files, fileSelect.name);

	// Set up the request.
	var xhr = new XMLHttpRequest();
	// Open the connection.
	xhr.open('POST', serverurl+"file_upload_parser.php", true);
	// Set up a handler for when the request finishes.
	xhr.onload = function () 
	{
		if (xhr.status === 200) 
	  	{
			// File(s) uploaded.
			rest = xhr.responseText;
			if (rest == 1)
			{
				view_photos();	
			} else
			{
				alert(rest);
			}
			//document.getElementById('register_docs').innerHTML = fetch_page('register_docs.php?SessionID='+SessionID+'&DocumentName='+DocumentName);
			//DocumentName.value = '';
			//fileSelect.value ='';
			//uploadButton.innerHTML = 'Upload';
	  	} else 
		{
			alert('An error occurred!');
	  	}
	};	
	// Send the Data.
	xhr.send(formData);		
}

function uploadFile(name,id,progressbar,status,loaded_n_totalId,maxAllowableSix,RequiredDocumentID,RefNumber, Description)
{
	 UserID   = localStorage.getItem("UserID");
	 No   = localStorage.getItem("No");
	 reqDocid = RequiredDocumentID;
	 refno = RefNumber;
	 //alert(refno);
	 
	 localStorage.setItem("reqDocid", reqDocid);
	 reqDocid   = localStorage.getItem("reqDocid");
	 
	 desc = Description;
	 if(desc=='')
	 {
	 	alert('Please enter the image description');
	 }
	 else
	 {
	 var docid = '#'+id
	 var filesize = $(docid)[0].files[0].size; //get file size
		var ftype = $(docid)[0].files[0].type; // get file type 
	 var f = ((maxAllowableSix * 1)/1048576);
	 
	 f = Math.round(f); 
 	if(filesize > maxAllowableSix){
  		alert('SORRY: File size is too big. The maximum allowable size is '+f+' MB');
 	} else{
	  if(ftype == 'application/pdf' || ftype == 'application/jpg' || ftype == 'application/gif' || ftype == 'application/jpeg' 
	  								|| ftype == 'image/gif' || ftype == 'image/jpeg' || ftype == 'image/pjpeg' || ftype == 'image/png') 
		{
	  loaded_n_total = loaded_n_totalId;
	  p_progressbar = progressbar;
	  s_status = status;
	  maxSize = maxAllowableSix;
	  var file = _(id).files[0];
		  if(typeof FormData == "undefined"){
			   var data = [];       
			   //data.push("files[]", $conv(this)[0]);
		  }else{
		   		var data = new FormData();
		  } 
  
  data.append(name, file);
  
  var ajax = new XMLHttpRequest({mozSystem: true});
  //new XMLHttpRequest();
  ajax.upload.addEventListener("progress", myProgressHandler, false);
  ajax.addEventListener("load", myCompleteHandler, false);
  ajax.addEventListener("error", myErrorHandler, false);
  ajax.addEventListener("abort", myAbortHandler, false);
  //alert(name);
  ajax.open("POST", serverurl+"file_upload_parser.php?imagename="+name+"&imageid="+id+"&CustomerID="+No+"&uid="+UserID+"&RequiredDocumentID="+reqDocid+"&RefNumber="+refno+"&Description="+desc); ajax.send(data);
  } else {
   alert('SORRY: The file format is not supported. You can only upload (PDF) file formats');
  }
 }
}
}

function uploadFile2(name,id,progressbar,status,loaded_n_totalId)
{
	 UserID   = localStorage.getItem("UserID");
	 No   = localStorage.getItem("No"); 
	 localStorage.setItem("reqDocid", reqDocid);
	 reqDocid   = localStorage.getItem("reqDocid");
	 
	 var docid = '#'+id
	 alert($(docid)[0].files[0].size);
	 var filesize = $(docid)[0].files[0].size; //get file size
		var ftype = $(docid)[0].files[0].type; // get file type 
	 var f = ((maxAllowableSix * 1)/1048576);
	 
	 f = Math.round(f); 
 	if(filesize > maxAllowableSix){
  		alert('SORRY: File size is too big. The maximum allowable size is '+f+' MB');
 	} else{
	  if(ftype == 'application/pdf' || ftype == 'application/jpg' || ftype == 'application/gif' || ftype == 'application/jpeg' 
	  								|| ftype == 'image/gif' || ftype == 'image/jpeg' || ftype == 'image/pjpeg' || ftype == 'image/png') 
		{
	  loaded_n_total = loaded_n_totalId;
	  p_progressbar = progressbar;
	  s_status = status;
	  maxSize = maxAllowableSix;
	  var file = _(id).files[0];
		  if(typeof FormData == "undefined"){
			   var data = [];       
			   //data.push("files[]", $conv(this)[0]);
		  }else{
		   		var data = new FormData();
		  } 
  
  data.append(name, file);
  
  var ajax = new XMLHttpRequest({mozSystem: true});
  //new XMLHttpRequest();
  ajax.upload.addEventListener("progress", myProgressHandler, false);
  ajax.addEventListener("load", myCompleteHandler, false);
  ajax.addEventListener("error", myErrorHandler, false);
  ajax.addEventListener("abort", myAbortHandler, false);
  //alert(name);
  ajax.open("POST", serverurl+"file_upload_parser.php?imagename="+name+"&imageid="+id+"&CustomerID="+No+"&uid="+UserID+"&RequiredDocumentID="+reqDocid+"&RefNumber="+refno+"&Description="+desc); ajax.send(data);
  } else {
   alert('SORRY: The file format is not supported. You can only upload (PDF) file formats');
  }

}
}
//This will be done tomorrow. 'Okay'? Joy asked. Alwanga said, 'Okay'.
function autosave_checklist()
{
//get the details
	No   = localStorage.getItem("No");
	UserID   = localStorage.getItem("UserID");
	AuditReportId   = localStorage.getItem("AuditReportId");
	//AuditChecklistParameterID
	//AuditChecklistParameterOptions
	
	//To Continue
	
	url = serverurl+"audit_checklist.php?UserID="+UserID+"&AuditReportID="+AuditReportID+"&AuditChecklistParameterID="+Address2Text;
    
	userobj = fetch_data(url);
 
    result = userobj.jData[0].result;
	
	if (result==1)
	{
	alert('successful');
	}
	else
	{
	alert('unsuccessful');
	}
 
	
}

//Submit the inspection report to Head Office; No, it should be to the Head Office
function submit_inspection()
{
	AuditID = localStorage.getItem("AuditID");
	UserID  = localStorage.getItem("UserID");
	LicenceApplicationID = localStorage.getItem("LicenceApplicationID");
	 
	//alert(LicenceApplicationID);
	
	//url = serverurl+"submit_inspection_report.php?LicenceApplicationID="+LicenceApplicationID;
    url = serverurl+"submit_inspection_report.php?AuditID="+AuditID+"&UserID="+UserID+"&LicenceApplicationID="+LicenceApplicationID;
	
	userobj = fetch_data(url);
 
    result = userobj.jData[0].result;
	//LicenceID = userobj.jData[0].LicenceID;
	
	
	if (result == 1)
	{
		
		No   = localStorage.getItem("No");
			
		//alert('successful');
		//url = "menu2.html?i=1";
	//	document.getElementById('mymenu').innerHTML = fetch_page(url);	
	//	document.getElementById('notification').innerHTML = fetch_page("notification.html?i=1");
	//	document.getElementById('Uname').innerHTML = UserName;
		//load_users_list();	
		document.getElementById('content').innerHTML = fetch_page("submission.html?i=1");
		
		//alert(LicenceID + "Me");
		
		document.getElementById('message').innerHTML = "The Inspection Report for Customer: " + No + " with Licence Application ID: " +LicenceApplicationID + " has been submitted to the Station Manager " ;
		document.getElementById('message1').innerHTML = "Thank you." ;
	} else {
		//alert('unsuccessful');
		document.getElementById('message').innerHTML = "The Inspection Report for Customer: " + No + " with Licence Application ID: " +LicenceApplicationID + " could not be submitted. Please contact the Administrator." ;
		document.getElementById('message1').innerHTML = "Try Again later." ;
	}
}
/*
function capturePhoto()
{
	navigator.camera.getPicture(onCameraSuccess, onCameraFail, {
                quality: 100,
                targetWidth: 400,
                targetHeight: 400,
                destinationType: Camera.DestinationType.DATA_URL,
                correctOrientation: true
             });	
}
*/
function capturePhoto() {
    var options = {
        quality: 75,
        destinationType: Camera.DestinationType.FILE_URI,
        sourceType: Camera.PictureSourceType.CAMERA,
        mediaType: Camera.MediaType.CAMERA,
        encodingType: Camera.EncodingType.JPEG,
        targetWidth: 200,
        targetHeight: 200,
        saveToPhotoAlbum: true
    };
    navigator.camera.getPicture(onCameraSuccess, onCameraFail, options);
}

function writeimage()
{
    var c = document.getElementById("photocanvas");
    var ctx = c.getContext("2d");
    var img = document.getElementById("doc_file");
    ctx.drawImage(img, 10, 10);
}

function onCameraSuccess(imageData) 
{/*
	var image = document.getElementById('doc_file');
	image.src = "data:image/jpeg;base64," + imageData;
	image.style.margin = "10px";
	image.style.display = "block";
*/
	document.getElementById("doc_file").src = imageData;
/*
	writeimage();
	
	var photoimage = document.getElementById('photoimage');
	photoimage.value = photocanvas.toDataURL(imageData);
	*/
	var myCanvas = document.getElementById('photocanvas');
	var ctx = myCanvas.getContext('2d');
	var img = new Image;
	img.onload = function(){
	ctx.drawImage(img,0,0); // Or at whatever offset you like
	};
	img.src = imageData;
	
	var photoimage = document.getElementById('photoimage');
	photoimage.value = photocanvas.toDataURL("image/jpeg", 1.0);
	
	var form = document.getElementById('myForm');
	//savePhoto(form);
	
	// Create a new FormData object.
	var formData = new FormData(form);
	var xhr = new XMLHttpRequest();
	// Open the connection.
	
	
	AuditReportID  = localStorage.getItem("AuditReportID");
	document.getElementById('AuditReportId').value =  AuditReportID;
	
	url = serverurl+"savephoto.php?i=1";
	xhr.open('POST', url, true);
	// Set up a handler for when the request finishes.
	xhr.onload = function () 
	{
		if (xhr.status === 200) 
	  	{
			// File(s) uploaded.
			rest = xhr.responseText;
			//alert(rest);
			
		} else 
		{
			alert('An error occurred!');
	  	}			
	};	
	// Send the Data.
	xhr.send(formData);
				
}

function savePhoto(form)
{
	jQuery.ajax( {
			url: serverurl+'savephoto.php?i=1',
			type: 'POST',
			dataType: "json",
			data: { 
					'UserID' : form.UserID.value,
					'reqDocid' : form.reqDocid.value,
					'AuditReportID' : form.AuditReportID.value,
					'AuditID'	: form.AuditID.value,
					'photoimage'	: form.photoimage.value,
					'AuditChecklistResultID' 	: form.AuditChecklistResultID.value,
					'AuditChecklistParameterID' 	: form.AuditChecklistParameterID.value,
					'LicenceApplicationID' 	: form.LicenceApplicationID.value,
				},				
			beforeSend : function( xhr ) {
				xhr.setRequestHeader( "Authorization", access_token );
			},
			success: function( response ) 
			{
				// response
			
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) 
			{ 
				//document.getElementById('msg').innerHTML = 'Failed to Save Record';
			} 
		} );
}

function win(r) 
{
    console.log("Code = " + r.responseCode);
    console.log("Response = " + r.response);
    console.log("Sent = " + r.bytesSent);
}

function fail(error) 
{
    alert("An error has occurred: Code = " + error.code);
    console.log("upload error source " + error.source);
    console.log("upload error target " + error.target);
}

function onCameraFail(message) {
   //console.log("Picture failure: " + message);
   alert("Picture failure: " + message);
}

function submitphoto(form)
{
	var image = document.getElementById('doc_file');
	image.src = "data:image/jpeg;base64," + imageData;
	image.style.margin = "10px";
	image.style.display = "block";
	var d = new Date();
	var n = d.getTime();
	var filename = n+'.jpg';
	
	var base64image = $('#doc_file').attr('src');
	var p = document.createElement("input");
 
    // Add the new element to our form. 
    form.appendChild(p);
    p.name = "p";
    p.type = "hidden";
	p.value = base64image;
	form.submit();
	return;
	
	// Create a new FormData object.
	var formData = new FormData(form);

	// Set up the request.
	var xhr = new XMLHttpRequest();
	// Open the connection.
	url = "http://localhost/test/photo.php";
	xhr.open('POST', url, true);
	// Set up a handler for when the request finishes.
	xhr.onload = function () 
	{
		if (xhr.status === 200) 
	  	{
			alert('Successful');
	  	} else 
		{
			alert('An error occurred!');
	  	}
	};	
	// Send the Data.
	xhr.send(formData);		
}
