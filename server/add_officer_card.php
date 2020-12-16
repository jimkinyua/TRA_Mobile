<?php 
require 'DB_PARAMS/connect.php';
require_once('utilities.php');
require_once('GlobalFunctions.php');

// require_once('county_details.php');
// require_once('smsgateway.php');
// require('password_compat/lib/password.php');




if (!isset($_SESSION))
{
	session_start();
}

$msg ='';
$UserID = $_SESSION['UserID'];

if (isset($_REQUEST['msg']))
{
	$msg = $_REQUEST['msg'];	
}

$ApplicationID='';
$CustomerName='';
$CustomerID="";
$ServiceName ='';
$ServiceID='';
$Charges=0;
$Notes='';
$ServiceState="";
$CurrentStatus="";
$NextStatus="";
$Customer;
$SubCountyName;
$BusinessZoneID;
$WardName;
$CustomerType="";
$RegNo="";
$PostalAddress="";
$PostalCode="";
$Pin="";
$Vat="";
$Town="";
$Country="";
$Telephone1="";
$Mobile1="";
$Telephone2="";
$Mobile2="";
$Mobile1="";
$url="";
$Email="";
$ServiceHeaderType="";
$SubSystemID=1;
$ApplicationDate='';
$today=date("d/m/Y");
//$DateLine=date('d/m/Y',strtotime('2018-03-31'));
$DateLine=$cosmasRow['SBPDateline'];
$DateLine=date('d/m/Y',strtotime($DateLine));
$BusinessIsOld=0;
$ConservancyCost=0;
$PermitYear=date("Y");
$InvoiceNo=0;
$ServiceCost=0;
$LicenceNumber = "";
$SubmisionDate = "";
$LicenceIssueDate = "";
$LicenceExpiryDate = "";
$ServiceHeaderID= "";

if (isset($_REQUEST['ApplicationID'])) 
{
    $ApplicationID = $_REQUEST['ApplicationID']; 	


}

$today=date('Y-m-d H:i:s');
$FirstDec=date(date('Y')."-12-01 00:00:00");
if($today>$FirstDec){
	$PermitYear=date("Y")+1;
}


if (isset($_REQUEST['save']))
    {
        // echo '<pre>';
        // print_r($_REQUEST);
        // exit; 

                // CustomerName=this.form.CustomerName.value;
                // ServiceID=this.form.ServiceID.value;
                // ContactPerson=this.form.ContactPerson.value;
                // Type=this.form.Type.value;
                // PostalAddress=this.form.PostalAddress.value;
                // PhysicalAddress=this.form.PhysicalAddress.value;
                // PlotNo=this.form.PlotNo.value;
                // PostalCode=this.form.PostalCode.value;
                // Town=this.form.Town.value;
                // Telephone1=this.form.Telephone1.value;
                // Telephone2=this.form.Telephone2.value;
                // Mobile1=this.form.Mobile1.value;
                // Mobile2=this.form.Mobile2.value;
                // Email=this.form.Email.value;
                // website=this.form.website.value;
                // PIN=this.form.PIN.value;



            $CustomerName=$_REQUEST['CustomerName'];
            $ContactPerson=$_REQUEST['ContactPerson'];
            $Type=$_REQUEST['Type'];
            $ServiceID=$_REQUEST['ServiceID'];
            $PostalAddress=$_REQUEST['PostalAddress'];
            $PhysicalAddress = $_REQUEST['PhysicalAddress'];
            $PlotNo = $_REQUEST['PlotNo'];
            $PostalCode = $_REQUEST['PostalCode'];
            $Town = $_REQUEST['Town'];
            $Telephone1 = $_REQUEST['Telephone1'];
            $Telephone2 = $_REQUEST['Telephone2'];
            $Mobile1 = $_REQUEST['Mobile1'];
            $Mobile2 = $_REQUEST['Mobile2'];
            $Email = $_REQUEST['Email'];
            $website = $_REQUEST['website'];
            $PIN = $_REQUEST['PIN'];
            $Force_inspection = $_REQUEST['Force_inspection'];


            // $IdNo = 60008726; //rand(80, );
            // $UserStatusID =1; // $_REQUEST['UserStatusID'];
            // $DateToday=date('Y-m-d H:i:s');
            // $UserID = $_SESSION['UserID'];
            // $Active =1;
            // $MobileNumber = $_REQUEST['MobileNumber'];
             //Insert Into Table Customer First
            $InsertIntoAgentsSQL="INSERT INTO Customer (CustomerName,ContactPerson,Type,ServiceID,PostalAddress,PhysicalAddress,PlotNo,PostalCode,Town,Telephone1,Telephone2,Mobile1,Mobile2,Email,website,PIN)
                Values('$CustomerName',
                '$ContactPerson','$Type',
                '$ServiceID',
                '$PostalAddress','$PlotNo','$PostalCode', 
                '$Town','$Telephone1', 
                '$Telephone2', '$Mobile1','$Mobile2','$Email','$website','$PIN','$Force_inspection') SELECT SCOPE_IDENTITY() AS ID";
            
            // echo $InsertIntoAgentsSQL; exit;
                     /* Begin the transaction. */

            if ( sqlsrv_begin_transaction( $db ) === false ) {
                die( print_r( sqlsrv_errors(), true ));
            }

            $InsertIntoAgentsResult = sqlsrv_query($db, $InsertIntoAgentsSQL);

            //Insert Now to Users

            //Get AgentNo
			$AgentNo=lastid($InsertIntoAgentsResult);
            
            $InsertIntoUsersSQL = "INSERT INTO Users([Mobile],[UserName],[Email],[agentID],IDNo,Password,CreatedBy,RegionID) 
            select [Mobile],[UserName],[Email],[agentID],IDNo,Password,".$UserID.",".$Region." from agents where AgentID=$AgentNo SELECT SCOPE_IDENTITY() AS ID" ;
            
            //    echo $InsertIntoUsersSQL; exit;

            // print_r($AgentNo); exit;
			$InsertIntoUserRolesSQL="Insert into UserRoles (UserID,ServiceID,CreatedBY)
			Values('$AgentNo',$ServiceID,$UserID)";	

            
            $InsertIntoUsersSQLResult = sqlsrv_query($db, $InsertIntoUsersSQL);
            $InsertIntoUserRolesSQLResult = sqlsrv_query($db, $InsertIntoUserRolesSQL);

            if($InsertIntoAgentsResult &&$InsertIntoUsersSQLResult && InsertIntoUserRolesSQLResult) {
                sqlsrv_commit( $db );
                // echo "Transaction committed.<br />";
                $Msg="Created the Account for ".$UserName;
                return  $Msg;

            } else {
                sqlsrv_rollback( $db );
                echo "Transaction rolled back.<br />";
                DisplayErrors();

            }

  

              
    }



//get the Arrears

// if (isset($_REQUEST['approve']))
// {	
// 	$input=array_slice($_REQUEST,2,count($input)-1);	
// 	foreach ($input AS $id => $value)
// 	{	
// 		$newID=substr($id,3,strlen($id)-3);	
			
// 		$sql="if exists(select * from FormData where FormColumnID=$newID)
// 				Update FormData set Value='$value' where FormColumnID=$newID and ServiceHeaderID=$ApplicationID
// 			  else
// 				insert into FormData (FormColumnID,ServiceHeaderID,Value)
// 			    values($newID,$ApplicationID,'$value')";
				
// 		$result=sqlsrv_query($db,$sql);
		
// 		if(!$result)
// 		{
// 			DisplayErrors();
// 			continue;
// 		}		

// 	}	
// }



?>

<div class="example">
   <legend>Forced Inspection</legend>
   <form>
      <fieldset>
          <table width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
                <td colspan="2" align="center" style="color:#F00"><?php echo $msg; ?></td>
            </tr>
              <tr>
                 <td width="50%">
					                     
                    <label>Customer Name</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="CustomerName" type="text" id="CustomerName" placeholder="" >
                    </div>

                   <label>Contact Person</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="ContactPerson" type="text" id="ContactPerson" placeholder="" >
                        
                    </div>
                    
                    <label>Business Type</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Type" type="text" id="Type" placeholder="" >
                    </div>
                    <label>Postal Address</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="PostalAddress" type="text" id="PostalAddress" placeholder="" >
                        
                    </div>
                    
                    <label>Physical Address</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="PhysicalAddress" type="text" id="PhysicalAddress" placeholder="" >
                        
                    </div>
                    
                    <label>Plot No</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="PlotNo" type="text" id="PlotNo" placeholder="" >
                        
                    </div>
                    <label>Post Code</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="PostalCode" type="text" id="PostalCode" placeholder="" >
                    </div>
                    
                    <label>Town</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Town" type="text" id="Town" placeholder="" >
                        
                    </div>
                              <label>Role</label>
                  <div class="input-control select" data-role="input-control">
                    <select name="ServiceID"  id="ServiceID">
                            <option value="0" selected="selected"></option>

                             <?php 
                                $s_sql = "SELECT * FROM Services ORDER BY ServiceID";
                                
                                $s_result = sqlsrv_query($db, $s_sql);
                                if ($s_result) 
                                { //connection succesful 
                                    while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
                                    {
                                        $s_id = $row["ServiceID"];
                                        $s_name = $row["ServiceName"];
                                        if ($ServiceID==$s_id) 
                                        {
                                            $selected = 'selected="selected"';
                                        } else
                                        {
                                            $selected = '';
                                        }                       
                                    ?>

                                <option value="<?php echo $s_id; ?>" <?php echo $selected; ?>><?php echo $s_name; ?></option>
                                <?php 
                                    }
                                }
                                ?>
                    </select>
                  
                 </div>              	
                  </td>   



                   <td width="50%" valign="top"><div id="info" style="padding-left:20px">
    
                    <label>Telephone 1</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Telephone1" type="text" id="Telephone1" placeholder="" >
                        
                    </div>
                    
                    <label>Telephone 2</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Telephone2" type="text" id="Telephone2" placeholder="" >
                        
                    </div>

                    <label>Mobile 1</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Mobile1" type="text" id="Mobile1" placeholder="" >
                        
                    </div>
                     <label>Mobile 2</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Mobile2" type="text" id="Mobile2" placeholder="" >
                        
                    </div>
                    
                    <label>Email</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Email" type="text" id="Email" placeholder="" >
                        
                    </div>
                     <label>Website</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="website" type="text" id="website" placeholder="" >
                        
                    </div>
                    
                     <label>PIN</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="PIN" type="text" id="PIN" placeholder="" 
                    </div>

                    <input type="hidden" name="Force_inspection" id="Force_inspection" value="1" />
                    
                   
    </div>             
    </td>   

              </tr>
			 



             

             

              
              



 
              


<tr>



                     
            		
          </table> 

		  <input name="Button" type="button" onClick="
                CustomerName=this.form.CustomerName.value;
                ServiceID=this.form.ServiceID.value;
                ContactPerson=this.form.ContactPerson.value;
                Type=this.form.Type.value;
                PostalAddress=this.form.PostalAddress.value;
                PhysicalAddress=this.form.PhysicalAddress.value;
                PlotNo=this.form.PlotNo.value;
                PostalCode=this.form.PostalCode.value;
                Town=this.form.Town.value;
                Telephone1=this.form.Telephone1.value;
                Telephone2=this.form.Telephone2.value;
                Mobile1=this.form.Mobile1.value;
                Mobile2=this.form.Mobile2.value;
                Email=this.form.Email.value;
                website=this.form.website.value;
                PIN=this.form.PIN.value;
                Force_inspection=this.form.Force_inspection.value;


		  	    loadpage('add_officer_card.php?save=1&CustomerName='+this.form.CustomerName.value+'&Type='+this.form.Type.value+'&ServiceID='+this.form.ServiceID.value+'&PostalAddress='+this.form.PostalAddress.value+'&ContactPerson='+this.form.ContactPerson.value+'&PhysicalAddress='+this.form.PhysicalAddress.value+'&PlotNo='+this.form.PlotNo.value+'&PostalCode='+this.form.PostalCode.value+'&Town='+this.form.Town.value+'&Email='+this.form.Email.value+'&Telephone1='+this.form.Telephone1.value+'&Telephone2='+this.form.Telephone2.value+'&Mobile1='+this.form.Mobile1.value+'&Mobile2='+this.form.Mobile2.value+'&website='+this.form.website.value+'&PIN='+this.form.PIN.value+'&Force_inspection='+this.form.Force_inspection.value+'',
                  
                  'content')
		  

		    " value="Add User">

          <div style="margin-top: 20px">
  </div>


      </fieldset>
  </form>                  
