<?php
 header('Access-Control-Allow-Origin: *');
require 'DB_PARAMS/connect.php';
require('password_compat/lib/password.php');
//require_once('GlobalFunctions.php');

$uname=$_REQUEST['uname'];
$passwd=$_REQUEST['passwd'];
// exit('hapa');
echo _login($db,$uname,$passwd);

if (!isset($_SESSION))
{
	session_start();
}
$msg ='';
$UserID = $_SESSION['UserID'];

$ActiveSessionID=0;

$DefaultMenuGroupID="0";
function _login($db,$uname,$passwd)
{
	$verdict=0;
	session_defaults();
	$DbSessionID=0;

	//print_r($_SESSION);


	$Passwd=md5($passwd);

	//exit($Passwd);
	$sql   = "select u.*,ur.RoleCenterID,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName UserFullNames,ag.password,rc.DefaultMenuGroupID,rc.RoleCenterName,rc.MaximumApprovalLimit
			from users u 
			join UserRoles ur on ur.UserID=u.AgentID
			join Agents ag on u.AgentID=ag.AgentID
			join RoleCenters rc on ur.RoleCenterID=rc.RoleCenterID
			WHERE (u.Email = '$uname')";


	
	$result  = sqlsrv_query($db, $sql);

	//DisplayErrors();
	
	if ($result)
	{
		//echo $sql;

		if ($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
		{
			
			if(password_verify($passwd,$myrow['password']))
			{

				$UserName 		= $myrow["UserName"];	
				$UserID 		= $myrow["AgentID"];
				$UserStatusID 	= $myrow["UserStatusID"];			
				$UserFullNames 	= $myrow['UserFullNames'];
				$RoleCenterID	= $myrow['RoleCenterID'];
				$RegionID		= $myrow['RegionID'];
				$MaximumApprovalLimit	= $myrow['MaximumApprovalLimit'];

				$RoleCenterName = $myrow['RoleCenterName'];
				$DefaultMenuGroupID	= $myrow['DefaultMenuGroupID'];
				
				$_SESSION["DefaultMenuGroupID"] = $DefaultMenuGroupID;
				$_SESSION["RoleCenterName"] = $RoleCenterName;	
				$_SESSION["RoleCenterID"] = $RoleCenterID;	
				$_SESSION["RoleCenter"]=$RoleCenterID;		
				$_SESSION["UserFullNames"] = $UserFullNames;
				$_SESSION["MaximumApprovalLimit"]=$MaximumApprovalLimit;
				
				$_SESSION["UserStatusID"] = $UserStatusID;
				if ($UserStatusID==0)
				{   
					$_SESSION["ChangePassword"] = 1;
					$_SESSION["UserID"] = $UserID;				
					$verdict= 1;	
				}			
				else if ($UserStatusID==1)
				{



					$sql="Select SessionID,Session_Start from SessionMgr where UserID=$UserID and Active=1";
					$result=sqlsrv_query($db,$sql);
					
					while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
						$DbSessionID=$row['SessionID'];
						$SessionStart=$row['Session_Start'];
					}

					if($DbSessionID==0){
						//echo 'one';
					}else{
						//echo 'DBSID :'. $DbSessionID;
						
					}


					$ActiveSessionID=time();



					// if($_SESSION['ID']!==$DbSessionID)
					// {
					// 	$sql="Insert into SessionMgr (UserID,ActiveSessionID) Values($UserID,'$ActiveSessionID') SELECT SCOPE_IDENTITY() AS ID";
					// 	$result=sqlsrv_query($db,$sql);
					// 	if(!$result){
					// 		//DisplayErrors();
					// 	}else{
					// 		$SessionID=lastid($result);
					// 	}
					// }else{

					// }


					

					// $sql="update SessionMgr set Active=0,Session_End=getdate() 
					// where UserID=$UserID and SessionID<>$SessionID";
					
					// $result=sqlsrv_query($db,$sql);

					$_SESSION['ID']=$SessionID;
					$_SESSION["logged_in"] = 1;
					$_SESSION["ChangePassword"]=0;
					$_SESSION["UserName"] = $UserName;
						$_SESSION["UserID"] = $UserID;
					$_SESSION["RoleCenter"]=$RoleCenterID;
					$_SESSION["UserFullNames"] = $UserFullNames;



					setcookie('PROJECTMAN', $_SESSION["UserFullNames"], time() + 3600);



					// $_SESSION["fail_reason"] = "System Licence Obsolete, Contact the Authorities";
					// $_SESSION["logged_in"] = 0;
					// $verdict= 0;

					$verdict= 1;
				}
				else if ($UserStatusID==2)
				{
					$rst=SaveTransaction($db,$UserID," Tried Logging in but Account Blocked ");
					$_SESSION["logged_in"] = 0;
					$_SESSION["fail_reason"] = "Account Is Blocked, Contact the System Admin";
					$verdict= 0;
				}
			}else
			{
				$_SESSION["fail_reason"] = "Invalid UserName or Password";
				$_SESSION["logged_in"] = 0;
				$verdict= 0;
			}
		} else
		{
			$_SESSION["fail_reason"] = "Invalid UserName or Password";
			$_SESSION["logged_in"] = 0;
			$verdict= 0;
		}	
	} else
	{
		$_SESSION["fail_reason"] = "Invalid UserName or Password";
		$verdict= 0;
	}


	if($verdict==1)
	{
		
		$_SESSION['ActiveSessionID']=$ActiveSessionID;		
		$sql="Update Users Set LoginStatus=1,ActiveSessionID=$ActiveSessionID where AgentID=$UserID";
		$result=sqlsrv_query($db,$sql);
		if($result)
		{
			//$rst=SaveTransaction($db,$UserID,"Logged In Successfully ");
			
		}else
		{
			DisplayErrors();
		}
	}

	//echo $verdict;
	$channel = array();
	if($verdict==1){

		$channel[] = array('result'=>1,'UserID'=>$UserID, 'UserName'=>$UserName, 'RegionStationsID'=>$RegionID);
		
	}else{
		$msg = "Invalid UserName or Password";
		$channel[] = array(0,'result'=>$msg);
	}

	$rss = (object) array('jData'=>$channel);
	$json = json_encode($rss);
	return $json;	
}

function session_defaults() 
{
	unset($_SESSION['logged_in']);
	unset($_SESSION['UserName']);
	unset($_SESSION['UserID']);	
}

session_start();
$date = gmdate("'Y-m-d'");
?>
