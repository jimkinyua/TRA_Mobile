<?php
//.................................................
//. Date: 11-Nov-2019                                    .
//. Developer: Cosmas K Ngeno           .
//. Gets User Data for all List pages			  .
//.................................................
require_once 'DB_PARAMS/connect.php';
//require_once 'utilities.php';
$CurrentUser=$_REQUEST['UserID'];


//$exParam='';
$channel = array();
if (isset($_REQUEST['OptionValue'])) { $OptionValue = $_REQUEST['OptionValue']; }
if (isset($_REQUEST['UserID'])) { $UserID = $_REQUEST['UserID']; }
if (isset($_REQUEST['param1'])) { $param1 = $_REQUEST['param1']; }
if (isset($_REQUEST['param2'])) { $param2 = $_REQUEST['param2']; }
if (isset($_REQUEST['param3'])) { $param3 = $_REQUEST['param3']; }
if (isset($_REQUEST['exParam'])){ $exParam =$_REQUEST['exParam'];}else{	$exParam='None';}

if ($OptionValue == 'session')
{
	checkSession($db,$exParam);

		$channel[] = array(
					$_SESSION['Expired']
		);
		
}
else if ($OptionValue == 'PrintPermits')
{
	PrintPermits($db);		
}

else if ($OptionValue == 'users')
{
	$sql = "select u.*,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName Names,ag.AgentID,us.UserStatusName,rc.RoleCenterName  
	from Users u 
	join agents ag on u.AgentID=ag.AgentID
	join UserStatus us on u.UserStatusID=us.UserStatusID 
	left join UserRoles ur on ur.UserID=ag.AgentID 
	left join RoleCenters rc on ur.RoleCenterID=rc.RoleCenterID";
	// echo $sql;
	// exit;

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$Date 	= date('d/m/Y',strtotime($CreatedDate));
		
		$PageID=5;
		$myRights=getrights($db,$CurrentUser,$PageID);
		if ($myRights)
		{
			$View=$myRights['View'];
			$Edit=$myRights['Edit'];
			$Add=$myRights['Add'];
			$Delete=$myRights['Delete'];
		}
		
		$ResetBtn='<a href="#" onClick="loadpage(\'password_reset.php?edit=1&UserID='.$AgentID.'&UserNames='.$Names.'\',\'content\',\'\',\''.$myRights['Edit'].'\')">Reset Password</a>';
		
		if($myRights['Edit']==1){
			$EditBtn='<a href="#" onClick="loadpage(\'users.php?edit=1&AgentID='.$AgentID.'\',\'content\',\'\',\''.$myRights['Edit'].'\')">Edit</a>';
		}else{
			$EditBtn='';
		}
		if($myRights['Delete']==1){
			$DeleteBtn='<a href="#" onClick="deleteConfirm2(\'Are you sure you wish to delete this record\',\'users_list.php?delete=1&UserID='.$UserID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$myRights['Delete'].'\')">Delete</a>';
		}else
		{
			$DeleteBtn='';
		}

		$actions='['.$ResetBtn.'|'.$EditBtn.']';

		$channel[] = array(
					$AgentID,
					$Names,
					$UserName,
					$Date,					
					$UserStatusName,
					$RoleCenterName,
					$actions
		);
	}	
}
else if ($OptionValue == 'UserRoles')
{
	$sql = "select ur.UserID,ur.UserRoleID,u.FirstName+' '+u.MiddleName+' '+u.LastName UserFullNames,u.UserName,
			rc.RoleCenterName 
			from UserRoles ur
			inner join Agents u on ur.UserID=u.AgentID 
			inner join RoleCenters rc on ur.RoleCenterID=rc.RoleCenterID";
	//echo $sql;	
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);	
		$EditBtn = '<a href="#" onClick="loadpage(\'user_role.php?edit=1&UserRoleID='.$UserRoleID.'\',\'content\')">Edit</a>';			
		$DeleteBtn = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'user_roles_list.php?delete=1&UserRoleID='.$UserRoleID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		
		$channel[] = array(
					 $UserFullNames
					,$UserName
					,$RoleCenterName
					,$EditBtn
					,$DeleteBtn
					);
	}
}
else if ($OptionValue == 'UserLogs')
{
	$AgentID='';
	$fromDate='';
	$toDate='';
	$filter=' where 1=1 ';
	$orderBy;

	if (strlen($exParam)>0)
	{

		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$AgentID=$str3[1];
		
		$str3=explode('=',$details[1]);
		$fromDate=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$toDate=$str3[1]; 	

				
		if(!$AgentID=='')
		{
			$filter .= " and ag.AgentID='$AgentID'";	
		}

		if(!$fromDate=='')
		{
			$filter .= " and convert(date,l.CreatedDate)>=convert(date,'$fromDate')";	
		}

		if(!$toDate=='')
		{
			$filter .= " and convert(date,l.CreatedDate)<=convert(date,'$toDate')";	
		}
		
	}else
	{
		$sql="";
	}

	$sql = "set dateformat dmy select top 500 ag.FirstName + ' ' + ag.MiddleName+' '+ag.LastName Names,
			l.Description,l.MacAddress,l.CreatedDate 
			from agents ag 
			join logs l on l.userid=ag.AgentID 
			".$filter."
			order by ag.AgentID,l.CreatedDate desc";
	//echo $sql;	
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);	
		
		$channel[] = array(
					 $Names
					,$Description
					,$MacAddress
					,$CreatedDate
					);
	}
}
else if ($OptionValue == 'Departments')
{
	$sql = "SELECT * FROM Departments";
	//echo $sql;
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);		
		$CreatedDate = date('Y-m-d',strtotime($CreatedDate));
		
		$EditBtn = '<a href="#" onClick="loadpage(\'departments_edit.php?edit=1&DepartmentID='.$DepartmentID.'&destform=1\',\'defaultpage\',\'progressbar\')">Edit</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm(\'Are you sure you want to Delete?\',\'departments_list.php?delete=1&DepartmentID='.$DepartmentID.'\',\'defaultpage\',\'progressbar\')">Delete</a>';
		
		$channel[] = array(
					 $DepartmentName
					,$Description
					,$CreatedBy
					,$CreatedDate
					,$EditBtn
					,$DeleteBtn
					);
	}
}
else if ($OptionValue == 'ServiceGroups')
{
	$sql = "select ServiceGroupID,ServiceGroupName,Label from ServiceGroup";
			
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);		
		$CreatedDate = date('Y-m-d',strtotime($CreatedDate));
		
		
		$EditBtn = '<a href="#" onClick="loadpage(\'servicegroup.php?edit=1&ServiceGroupID='.$ServiceGroupID.'\',\'content\')">Edit</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'servicegroup_list.php?delete=1&ServiceGroupID='.$ServiceGroupID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		
		$channel[] = array(
					 $ServiceGroupID
					,$ServiceGroupName
					,$EditBtn
					,$DeleteBtn
					);
	}
}
else if ($OptionValue == 'RevenueStreams')
{
	$sql = "Select rs.RevenueStreamID,rs.RevenueStreamCode,rs.RevenueStreamName,isnull(rb.Amount,0)Amount,rb.FinancialYearID,Rc.RevenueCategoryName 
			from RevenueStreams rs 
			left join RevenueCategories rc on rc.RevenueCategoryID=rs.RevenueCategoryID
			left join RevenueBudget rb on rb.RevenueStreamID=rs.RevenueStreamID 
			left join FinancialYear fy on 
			rb.FinancialYearID=fy.FinancialYearID
			where (fy.isCurrentYear=1 or fy.isCurrentYear is null)";
			
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);		
		$CreatedDate = date('Y-m-d',strtotime($CreatedDate));
		
		
		$EditBtn = '<a href="#" onClick="loadpage(\'revenue_stream.php?edit=1&RevenueStreamID='.$RevenueStreamID.'\',\'content\')">Edit</a>';
		$BudgetBtn = '<a href="#" onClick="loadpage(\'revenue_stream_budget.php?budget=1&RevenueStreamID='.$RevenueStreamID.'\',\'content\')">Budget</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'revenue_stream_list.php?delete=1&RevenueStreamID='.$RevenueStreamID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		
		$channel[] = array(
					$RevenueStreamCode
					,$RevenueStreamName	
					,$RevenueCategoryName
					,$Amount
					,$BudgetBtn
					,$EditBtn
					,$DeleteBtn
					);
	}
}
else if ($OptionValue == 'ServiceCategories')
{
	$sql = "SELECT sc.ServiceCategoryID,sc.ServiceCode,sc.CategoryName,sg.ServiceGroupName	 
			FROM ServiceCategory sc 
			inner join ServiceGroup sg on sc.ServiceGroupID=sg.ServiceGroupID
			left join Departments dp on sg.DepartmentID=dp.DepartmentID order by 1 desc";
			
	#sql="select 1,2,3,4,5,6";
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);		
		$CreatedDate = date('Y-m-d',strtotime($CreatedDate));
		
		
		$EditBtn = '<a href="#" onClick="loadpage(\'servicecategory.php?edit=1&ServiceCategoryID='.$ServiceCategoryID.'\',\'content\')">Edit</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'servicecategory_list.php?delete=1&ServiceCategoryID='.$ServiceCategoryID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$WorkFlow = '<a href="#" onClick="loadmypage(\'approval_steps_list.php?edit=1&ServiceCategoryID='.$ServiceCategoryID.'\',\'content\',\'loader\',\'listpages\',\'\',\'AprovalSteps\','.$ServiceCategoryID.')">WorkFlow</a>';
		$Documents = '<a href="#" onClick="loadmypage(\'required_documents_list.php?ServiceCategoryID='.$ServiceCategoryID.'\',\'content\',\'loader\',\'listpages\',\'\',\'RequiredDocuments\','.$ServiceCategoryID.')">Documents</a>';
										  
		$ActionBtn='['.$EditBtn.'|'.$WorkFlow.'|'.$Documents.']';
		
		$channel[] = array(
					 $ServiceCode
					,$CategoryName
					,$ServiceGroupName
					,$ActionBtn
					);
	}
}

else if ($OptionValue == 'services')
{
	$sql = "Select Services.ServiceID,Services.ServiceName,Services.ServiceCode, CategoryName, ServiceGroupName,rs.RevenueStreamName
	From Services	
	JOIN ServiceCategory ON ServiceCategory.ServiceCategoryID = Services.ServiceCategoryID
	JOIN ServiceGroup ON ServiceGroup.ServiceGroupID = ServiceCategory.ServiceGroupID
	left join RevenueStreams rs on Services.RevenueStreamID=rs.RevenueStreamID
	ORDER BY ServiceGroup.ServiceGroupName,ServiceCategory.CategoryName";
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);
		$historyString=urlencode("loadmypage('services_list.php?i=1','content','loader','listpages','','services')");
		$CreatedDate = date('Y-m-d',strtotime($CreatedDate));
		$ServicePBtn = '<a href="#" onClick="loadmypage(\'serviceplus_list.php?A_ServiceID='.$ServiceID.'\',\'content\',\'loader\',\'listpages\',\'\',\'ServicePlus\',\''.$ServiceID.'\')">FEES</a>';
		$EditBtn = '<a href="#" onClick="loadpage(\'services_r.php?edit=1&ServiceID='.$ServiceID.'\',\'content\')">Edit</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'services_list.php?delete=1&ServiceID='.$ServiceID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$ChargesBtn = '<a href="#" onClick="loadmypage(\'service_charges_list.php?ServiceID='.$ServiceID.'\',\'content\',\'loader\',\'listpages\',\'\',\'service_charges\',\''.$ServiceID.'\')">Charges</a>';
		$actions='['.$ServicePBtn.'|'.$ChargesBtn.'|'.$EditBtn.']';
		
		$channel[] = array(
					 $ServiceID
					 ,$ServiceCode
					,$ServiceName
					,$RevenueStreamName
					,$actions
					);
	}
}
else if($OptionValue=='invoices-a')
{
	$fromDate;
	$toDate;
	$filter;
	$ServiceHeaderID='';
	$CustomerName='';
	$InvoiceHeaderID='';

	$PageID=25;
	$View=0;
	$Edit=0;
	$Add=0;
	$Delete=0;
	$myRights=getrights($db,$CurrentUser,$PageID);
	if ($myRights)
	{
		$View=$myRights['View'];
		$Edit=$myRights['Edit'];
		$Add=$myRights['Add'];
		$Delete=$myRights['Delete'];
	}

	//location
	$wards='';
	$Subcounties='';
	$locationcondition='';
	$role='None';
	//check whether the person is a clerk or Officer
	$locsql="select iif (exists(select 1 from ClerkWard where UserID=$CurrentUser and status=1),'Clerk',
			iif (exists(select 1 from ApproverSetup where UserID=$CurrentUser and status=1),'Officer','None')) Role";

	$result=sqlsrv_query($db,$locsql);
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) 
	{
		$role=$row['Role'];
	}

	if($role=='Clerk')
	{
		$sql="select WardID From ClerkWard where UserID=$CurrentUser and Status=1";

		$result=sqlsrv_query($db,$sql);
		$i=0;

		while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
			if ($i==0){
				$wards='('.$row['WardID'];
			}else{
				$wards.=','.$row['WardID'];
			}
			$i+=1;
		}

		$wards.=')';

		$locationcondition=" and (select value from fnFormData(sh.ServiceHeaderID) WHERE FormColumnID=11204) in $wards ";

	}else if ($role=='Officer'){
		$sql="select SubCountyID From ApproverSetup where UserID=$CurrentUser and Status=1";

		$result=sqlsrv_query($db,$sql);
		$i=0;
		while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
			if ($i==0){
				$subcounties='('.$row['SubCountyID'];
			}else{
				$subcounties.=','.$row['SubCountyID'];
			}
			$i+=1;
		}

		$subcounties.=')';

		$locationcondition=" and (select value from fnFormData(sh.ServiceHeaderID) WHERE FormColumnID=11203) in $subcounties ";
	}

	if (strlen($exParam)>0)
	{
		$details=explode(':',$exParam);
		
		if(strlen($exParam)>2)
		{		
			$str3=explode('=',$details[0]);
			$fromDate=$str3[1];		
			
			$str3=explode('=',$details[1]);
			$toDate=$str3[1];

			$str3=explode('=',$details[2]);
			$InvoiceHeaderID=$str3[1];

			$str3=explode('=',$details[3]);
			$ServiceHeaderID=$str3[1];

			$str3=explode('=',$details[4]);
			$CustomerName=$str3[1];

			if(!$InvoiceHeaderID==''){
				$filter=" and il.InvoiceHeaderID=$InvoiceHeaderID";
			}

			if(!$ServiceHeaderID==''){
				$filter=" and il.ServiceHeaderID=$ServiceHeaderID";
			}

			if(!$CustomerName==''){
				$filter.=" and isnull(misc.CustomerName,c.CustomerName) like '%$CustomerName%'";
			}

			if(!$fromDate==''){
				$filter.=" and convert(date,il.CreatedDate)>=convert(date,'$fromDate')";
			}

			if(!$toDate==''){
				$filter.=" and convert(date,il.CreatedDate)<=convert(date,'$toDate')";
			}
			
			if($filter==''){
				$filter=" and DATEDIFF(day,il.CreatedDate,getdate())<3 ";
			}

			

		}else{
			$filter=" and DATEDIFF(day,il.CreatedDate,getdate())<3 ";
		}
		
		$sql = "set dateformat dmy select  distinct top 10 sh.ServiceHeaderID,ih.InvoiceHeaderID, sh.CustomerID,ih.InvoiceDate [INV DATE]
		,isnull(misc.CustomerName,c.CustomerName) CustomerName,s.ServiceName +'('+isnull(ih.[Description],'')+')' 	ServiceName,sum(il.Amount) Amount,isnull(rl.Amount,0) Paid,sh.ServiceID
			from InvoiceHeader ih
			inner join InvoiceLines il on il.InvoiceHeaderID=ih.InvoiceHeaderID
			left join (select InvoiceHeaderid,sum(amount) Amount from ReceiptLines group by InvoiceHeaderID)rl on ih.InvoiceHeaderID=rl.InvoiceHeaderID
			inner join ServiceHeader sh on ih.ServiceHeaderID=sh.ServiceHeaderID
			left join Miscellaneous misc on misc.ServiceHeaderID=sh.ServiceHeaderID
			inner join Customer c on sh.CustomerID=c.CustomerID	
			inner join Services s on sh.ServiceID=s.ServiceID 
			where year(il.CreatedDate)=year(getdate()) ".$filter."
			group by sh.ServiceHeaderID,misc.CustomerName, sh.CustomerID,ih.InvoiceDate,c.CustomerName,s.ServiceName,ih.InvoiceHeaderID,sh.ServiceID,sh.ServiceHeaderID,ih.[Description],isnull(rl.Amount,0) 
			Order By ih.InvoiceHeaderID";
	}
	
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{			
		extract($row);
		$Balance=$Amount-$Paid;

		$CustomerName =  '<a href="#" onClick="loadoptionalpage2('.$ServiceID.','.$InvoiceHeaderID.','.$ServiceHeaderID.',\'content\',\'loader\',\'listpages\',\'\',\'invoices_lines\',\''.$InvoiceHeaderID.'\')">'.$CustomerName.'</a>';

		$ViewBtn  = '<a href="reports.php?rptType=Invoice&ServiceHeaderID='.$ServiceHeaderID.'&InvoiceHeaderID='.$InvoiceHeaderID.'" target="_blank">View</a>'; 

		$ReceiptBtn = '<a href="#" onClick="loadmypage(\'receipt.php?add=1&InvoiceHeaderID='.$InvoiceHeaderID.'&InvoiceAmount='.$Amount.'&Balance='.$Balance.'\',\'content\',\'\',\'\',\'\',\''.$_SESSION['UserID'].'\')">Receipt</a>';

		
		if($Add==0){
			$actions='['.$ViewBtn.']';		
		}else{
			$actions='['.$ViewBtn.'|'.$ReceiptBtn.']';
		}
		
		$Date 	= date('d/m/Y',strtotime($CreatedDate));
		$channel[] = array(
					$InvoiceHeaderID,
					$ServiceHeaderID,
					$CustomerName,
					$ServiceName,
					$Amount,
					$Paid,
					$actions
		);
	}  	
}
else if($OptionValue=='invoices_lines')
{
	$sql = "select distinct s.ServiceID,s.ServiceName,il.Amount,il.InvoiceLineID,il.InvoiceheaderID 
			from InvoiceLines il
			join Services s on il.ServiceID =s.ServiceID
			where InvoiceHeaderID=$exParam";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{			
		extract($row);

		$PageID=2;
		$myRights=getrights($db,$CurrentUser,$PageID);
		if ($myRights)
		{
			$View=$myRights['View'];
			$Edit=$myRights['Edit'];
			$Add=$myRights['Add'];
			$Delete=$myRights['Delete'];
		}

		if($myRights['Delete']==1)
		{
			$RemoveBtn = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Remove The Service?\',\'invoice_lines.php?remove=1&InvoiceLineID='.$InvoiceLineID.'&InvoiceHeaderID='.$InvoiceheaderID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Remove</a>';
		}else
		{
			$RemoveBtn='';
		}

		

		// $CustomerName =  '<a href="#" onClick="loadoptionalpage2('.$ServiceID.','.$InvoiceHeaderID.','.$ServiceHeaderID.',\'content\',\'loader\',\'listpages\',\'\',\'invoices_lines\',\''.$InvoiceHeaderID.'\')">'.$CustomerName.'</a>';


		// mypage='invoice_lines.php?InvoiceHeaderID='+inv_hdr+'&ServiceHeaderID='+service_hdr;
		// opv='invoices_lines';
		
		
		$channel[] = array(
			$ServiceID,
			$ServiceName,
			$Amount,
			$RemoveBtn
		);
	}  	
}

else if($OptionValue=='invoices-b')
{
	$ServiceID='';
	$AgentID='';
	$fromDate='';
	$toDate='';
	$filter='';
	$orderBy;
	if (strlen($exParam)>0)
	{

		$details=explode(':',$exParam);	
		
		$str3=explode('=',$details[0]);
		$AgentID=$str3[1];
		
		$str3=explode('=',$details[1]);
		$fromDate=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$toDate=$str3[1];

		$str3=explode('=',$details[3]);
		$ServiceID=$str3[1];

		//$params=$AgentID.'space'.$fromDate.'space'.$toDate.'space'.$ServiceID;
		
		$orderBy=" order by sh.ServiceHeaderID desc";
		
		$sql="set dateformat dmy select il.PosReceiptID ReceiptNo,convert(date,left(il.posreceiptid,6)) ReceiptDate,il.CreatedDate,il.ServiceHeaderID,il.invoicelineId,il.amount,
		ag.AgentID,Ag.FirstName+' '+ag.MiddleName+' '+ag.LastName [Agent],
		mk.MarketName,s.ServiceName + ' '+ isnull(p.RegNo,'') ServiceName,il.ServiceID

		from InvoiceLines il 
		join Agents ag on il.CreatedBy=ag.AgentID
		join (select * from UserDevices where DeviceUserStatusID=1) ud on ud.DeviceUserID=ag.AgentID
		left join Markets mk on ud.MarketID=mk.MarketID
		join ServiceHeader sh on il.ServiceHeaderID=sh.ServiceHeaderID
		join Services s on sh.ServiceID=s.ServiceID		
		left join vwParking p on p.ServiceHeaderID=sh.ServiceHeaderID 		
		where il.PosReceiptID is not null";		

				
		if($AgentID=='' and $fromDate=='' and $toDate=='')//everythng is empty
		{
			$filter = " and convert(date,left(il.posreceiptid,6))>=convert(date,getDate())";			
		}else if($AgentID!=='' and $fromDate=='' and $toDate=='')
		{
			$filter = " and ag.AgentID='$AgentID'";			
		}else if($AgentID!=='' and $fromDate=='' and $toDate!=='')
		{
			$filter = " and ag.AgentID='$AgentID' and convert(date,left(il.posreceiptid,6))<='$toDate'";
		}else if($AgentID!=='' and $fromDate!=='' and $toDate=='')
		{
			$filter = " and ag.AgentID='$AgentID' and convert(date,left(il.posreceiptid,6))>='$fromDate'";
		}else if($AgentID!=='' and $fromDate!=='' and $toDate!=='')
		{
			$filter = " and ag.AgentID='$AgentID' and convert(date,left(il.posreceiptid,6))>='$fromDate' and convert(date,left(il.posreceiptid,6))<='$toDate'";
		}else if($fromDate!=='' and $toDate!=='')
		{
			$filter = " and convert(date,left(il.posreceiptid,6))>='$fromDate' and convert(date,left(il.posreceiptid,6))<='$toDate'";
		}else{
			$filter = " and convert(date,left(il.posreceiptid,6))=convert(date,getDate())";
		}

			//$filter = " and convert(date,il.CreatedDate)>='$fromDate' and convert(date,il.CreatedDate)<='$toDate'";
		
		if($ServiceID!==''){
			$filter.= " and il.ServiceID=$ServiceID ";
		}
		
		if($filter!==''){
			$sql.=$filter;
		}
		
	}else
	{
		//$sql = "select top 10  p.LocalAuthorityID,p.laifomsUPN UPN,p.LRN,p.PlotNo,p.LaifomsOwner [Owner],RatesPayable,p.Balance Balance from land p";
	}
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{		
		
		
		extract($row);
		$amount=(double)$amount;
		$CustomerName =  '<a href="#" onClick="loadmypage(\'receipts.php?approve=1&InvoiceHeaderID='.$InvoiceHeaderID.'\',\'content\',\'loader\',\'receipts\')">'.$CustomerName.'</a>';	
		
		$Date 	= date('d/m/Y',strtotime($CreatedDate));
		$channel[] = array(
					$CreatedDate,
					$ReceiptNo,					
					$amount,
					$ServiceName,
					$Agent,
					$MarketName
		);
	}  	
}
 else if($OptionValue=='invoices-c')
{	
	$AgentID='';
	$fromDate='';
	$toDate='';
	$filter='';
	$orderBy;
	if (strlen($exParam)>0)
	{

		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$AgentID=$str3[1];
		
		$str3=explode('=',$details[1]);
		$fromDate=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$toDate=$str3[1]; 
		
		$orderBy=" order by mk.MarketName,Ag.FirstName+' '+ag.MiddleName+' '+ag.LastName";
		$groupBy=" group by ag.AgentID,Ag.FirstName+' '+ag.MiddleName+' '+ag.LastName ,mk.MarketName,convert(date,il.CreatedDate),il.InvoiceHeaderID,R.Amount";
		
		$sql="set dateformat dmy 		
			select ag.AgentID,convert(date,il.CreatedDate)[Date],Ag.FirstName+' '+ag.MiddleName+' '+ag.LastName [Agent],mk.MarketName,il.InvoiceHeaderID,
			isnull(R.Amount,0) Paid,sum(il.amount) InvoiceAmount
			from InvoiceLines il 

			join Agents ag on il.CreatedBy=ag.AgentID
			join (select * from UserDevices where DeviceUserStatusID=1) ud on ud.DeviceUserID=ag.AgentID
			join Markets mk on ud.MarketID=mk.MarketID
			join ServiceHeader sh on il.ServiceHeaderID=sh.ServiceHeaderID
			join Services s on sh.ServiceID=s.ServiceID 
			left join (Select InvoiceHeaderID, sum(Amount) Amount from ReceiptLines group by InvoiceHeaderID)R on R.InvoiceHeaderID=il.InvoiceHeaderID		
			where il.PosReceiptID is not null";		

				
		if($AgentID=='' and $fromDate=='' and $toDate=='')
		{
			$filter = " and convert(date,left(il.posreceiptid,6))>=convert(date,getDate())";			
		}else if($AgentID!=='' and $fromDate=='' and $toDate=='')
		{
			$filter = " and ag.AgentID='$AgentID'";			
		}else if($AgentID!=='' and $fromDate=='' and $toDate!=='')
		{
			$filter = " and ag.AgentID='$AgentID' and convert(date,left(il.posreceiptid,6))<='$toDate'";
		}else if($AgentID!=='' and $fromDate!=='' and $toDate=='')
		{
			$filter = " and ag.AgentID='$AgentID' and convert(date,left(il.posreceiptid,6))>='$fromDate'";
		}else if($AgentID!=='' and $fromDate!=='' and $toDate!=='')
		{
			$filter = " and ag.AgentID='$AgentID' and convert(date,left(il.posreceiptid,6))>='$fromDate' and convert(date,left(il.posreceiptid,6))<='$toDate'";
		}  
		
		if($filter!==''){
			$sql.=$filter;
		}
		
		$sql.=$groupBy.$orderBy;
		
	}else
	{
		$sql="";
	}
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{		
		
		
		extract($row);
		$Balance=(double)$InvoiceAmount-(double)$Paid;
		$CustomerName =  '<a href="#" onClick="loadmypage(\'receipts.php?approve=1&InvoiceHeaderID='.$InvoiceHeaderID.'\',\'content\',\'loader\',\'receipts\')">'.$CustomerName.'</a>';	
		$ReceiptBtn = '<a href="#" onClick="loadmypage(\'receipt32.php?edit=1&InvoiceHeaderID='.$InvoiceHeaderID.'&InvoiceAmount='.$InvoiceAmount.'&Balance='.$Balance.'\',\'content\')">Receipt</a>';

		// $ReceiptBtn = '<a href="#" onClick="loadpage(\'receipt.php?edit=1&InvoiceHeaderID='.$InvoiceHeaderID.'&InvoiceAmount='.$InvoiceAmount.'&Balance='.$Balance.'\',\'content\')">Receipt</a>';
		
		$channel[] = array(
					$Date,
					$Agent,					
					$MarketName,
					$InvoiceHeaderID,
					$InvoiceAmount,
					$Paid,
					$ReceiptBtn
		);
	}  	
}
else if($OptionValue=='invoices-d')
{	
	$MarketID='';
	$fromDate='';
	$toDate='';
	$filter='';
	$orderBy;
	if (strlen($exParam)>0)
	{

		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$MarketID=$str3[1];
		
		$str3=explode('=',$details[1]);
		$fromDate=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$toDate=$str3[1]; 
		
		$orderBy=" order by mk.MarketName,fd.Value";	
		
		$sql=" set dateformat dmy 
				SELECT fd.ServiceHeaderID, fd.FormColumnID,convert(date,left(il.posreceiptid,6)) [Receipt Date], fd.CreatedDate Date, fd.Value AS RegNo, il.PosReceiptID, mk.MarketName,il.Amount
				FROM dbo.FormData AS fd INNER JOIN
				dbo.InvoiceLines AS il ON fd.ServiceHeaderID = il.ServiceHeaderID INNER JOIN
				dbo.Markets AS mk ON il.MarketID = mk.MarketID
				WHERE (fd.FormColumnID = '163')";		
				
		if($MarketID!=='')		{
			$filter .= " and mk.MarketID=$MarketID ";			
		}
		if($fromDate!=='')		{
			$filter .= " and convert(date,left(il.posreceiptid,6))>='$fromDate' ";			
		}
		
		if($toDate!=='')		{
			$filter .= " and convert(date,left(il.posreceiptid,6))<='$toDate' ";			
		}

		if($fromDate=='' and $toDate=='')
		{
			$filter .= " and convert(date,left(il.posreceiptid,6))=convert(date,getDate()) ";			
		}
		
		if($filter!==''){
			$sql.=$filter;
		}
		
		$sql.=$orderBy;
		
	}
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{		
		
		
		extract($row);
		$amount=(double)$amount;
		$CustomerName =  '<a href="#" onClick="loadmypage(\'receipts.php?approve=1&InvoiceHeaderID='.$InvoiceHeaderID.'\',\'content\',\'loader\',\'receipts\')">'.$CustomerName.'</a>';	
		
		//$Date 	= date('d/m/Y',strtotime($CreatedDate));
		$channel[] = array(
					$Date,
					$RegNo,					
					$PosReceiptID,
					$MarketName,
					$Amount
		);
	}  	
} 
else if($OptionValue=='invoices-e')
{	
	$AgentID='';
	$fromDate='';
	$toDate='';
	$filter='';
	$orderBy;
	if (strlen($exParam)>0)
	{

		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$AgentID=$str3[1];
		
		$str3=explode('=',$details[1]);
		$fromDate=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$toDate=$str3[1]; 
		
		$filter=' Where 1=1 ';
		$orderBy=" order by il.InvoiceHeaderID";
		$groupBy=" group by il.InvoiceHeaderID,s.ServiceName,rs.RevenueStreamName,convert(date,il.CreatedDate,103),il.CreatedBy,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName";
		
		$sql=" set dateformat dmy select top 5 convert(date,il.CreatedDate,103) DateReceived,il.InvoiceHeaderID,rs.RevenueStreamName,s.ServiceName,sum(il.Amount) Amount,il.CreatedBy,
		ag.FirstName+' '+ag.MiddleName+' '+ag.LastName Names
		from InvoiceLines il
		join Agents ag on il.CreatedBy=ag.AgentID
		left join services s on il.ServiceID=s.ServiceID
		left join RevenueStreams rs on s.RevenueStreamID=rs.RevenueStreamID";	
				
		if($AgentID!=='')
		{
			$filter .= " and ag.AgentID='$AgentID'";			
		}
		if($fromDate!=='')
		{
			$filter .= " and convert(date,il.CreatedDate)>='$fromDate'";
		} 
		if($toDate!=='')
		{
			$filter .= " and convert(date,il.CreatedDate)<='$toDate'";
		}
		
		$sql.=$filter.$groupBy.$orderBy; 
		
	}else
	{
		$sql="set dateformat dmy 		
			select convert(date,il.CreatedDate,103) DateReceived,il.InvoiceHeaderID,rs.RevenueStreamName,s.ServiceName,sum(il.Amount) Amount,il.CreatedBy,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName AgentNames from InvoiceLines il
			join Agents ag on il.CreatedBy=ag.AgentID
			left join services s on il.ServiceID=s.ServiceID
			left join RevenueStreams rs on s.RevenueStreamID=rs.RevenueStreamID
			where convert(date,il.CreatedDate,103)=convert(date,getDate(),103)
			group by il.InvoiceHeaderID,s.ServiceName,rs.RevenueStreamName,convert(date,il.CreatedDate,103),il.CreatedBy,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName
			order by il.InvoiceHeaderID";
	}
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{	
		extract($row);		
		$channel[] = array(
			$DateReceived,					
			$InvoiceHeaderID,
			$RevenueStreamName,
			$ServiceName,
			$Amount
		);
	}  	
}
else if($OptionValue=='devices')
{
	$sql = "select d.DeviceID,d.DeviceSerialNo,d.Description,d.DeviceID,d.MacAddress,d.Status,dt.DeviceTypeName DeviceType from devices d
			inner join DeviceType dt on d.DeviceTypeID=dt.DeviceTypeID";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		
		
		
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'device.php?edit=1&SerialNo='.$DeviceSerialNo.'\',\'content\')">Edit</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'device_list.php?delete=1&DeviceID='.$DeviceID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';


		$channel[] = array(
					$DeviceSerialNo,
					$Description,
					$MacAddress,
					$DeviceType,
					$Status,
					$EditBtn,
					$DeleteBtn
		);
	}  	
}

else if($OptionValue=='user_devices')
{
	$sql = "select  ud.*,du.firstName+' '+du.MiddleName+' '+du.LastName DeviceUser,iu.firstName+' '+iu.MiddleName+' '+iu.LastName IssueingUser,dt.DeviceTypeName,m.MarketName,dus.DeviceUserStatusDescription [Status]  from UserDevices ud
			inner join Agents du on ud.deviceuserId=du.AgentID
			inner join Devices d on ud.deviceserialno=d.DeviceSerialNo
			inner join DeviceType dt on d.DeviceTypeID=dt.DeviceTypeID
			inner join Agents iu on ud.CreatedBy=iu.AgentID
			left join DeviceUserStatus dus on ud.DeviceUserStatusID=dus.DeviceUserStatusID
			left join Markets m on ud.MarketID=m.MarketID
			order by du.firstName+' '+du.MiddleName+' '+du.LastName";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{	
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'user_devices.php?edit=1&UserDeviceID='.$UserDeviceID.'\',\'content\')">Edit</a>';
		$ReturnBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Return this device?\',\'user_devices_list.php?return=1&UserDeviceID='.$UserDeviceID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Return</a>';
		if($DeviceUserStatusID==3){
			$BlockBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Block this device?\',\'user_devices_list.php?unblock=1&UserDeviceID='.$UserDeviceID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Unblock</a>';
		}else
		{
			$BlockBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Block this device?\',\'user_devices_list.php?block=1&UserDeviceID='.$UserDeviceID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Block</a>';
		}
		
		$actions='['.$EditBtn.'|'.$ReturnBtn.'|'.$BlockBtn.']';
		
		$DateIssued = date('Y-m-d',strtotime($CreatedDate));
		
		$channel[] = array(
					$DeviceSerialNo,
					$DeviceTypeName,
					$DeviceUser,
					$Status,
					$DateIssued,
					$MarketName,
					$actions
		);
	}  	
}
else if($OptionValue=='MeterStatement')
{
	$sql = "select * from meterstatement 
	where MeterNo=$exParam 
	order by statementid";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{	
		extract($row);
		
		// $EditBtn = '<a href="#" onClick="loadpage(\'user_devices.php?edit=1&CustomerID='.$CustomerID.'\',\'content\')">Edit</a>';
		// $ReadBtn = '<a href="#" onClick="loadpage(\'meter_reading.php?edit=1&CustomerID='.$CustomerID.'\',\'content\')">New Reading</a>';
		
		
		$actions='['.$EditBtn.'|'.$ReadBtn.']';
		
		$DateIssued = date('d-m-Y',strtotime($DateIssued));
		
		$channel[] = array(
					$RecordDate,					
					$Description,
					$LastReading,
					$UnitsUsed,					
					$UnitPrice,
					$Amount,
					$Balance,
		);
	}  	
}
else if($OptionValue=='CustomerMeters')
{
	$sql = "select  ud.DeviceSerialNo,c.CustomerID,c.CustomerName Customer,ud.CreatedDate DateIssued,lm.LastReading,lm.Balance  
		from UserDevices ud
		join Devices d on d.DeviceSerialNo=ud.DeviceSerialNo
		join DeviceType dt on dt.DeviceTypeID=d.DeviceTypeID
		inner join Customer c on ud.CustomerID=c.CustomerID
		cross apply fnLastMeterRecord(ud.DeviceSerialNo)lm 
		where d.DeviceTypeID=3 and c.CustomerID=$exParam";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{	
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'user_devices.php?edit=1&CustomerID='.$CustomerID.'\',\'content\')">Edit</a>';
		$ReadBtn = '<a href="#" onClick="loadmypage(\'meter_reading.php?edit=1&CustomerID='.$CustomerID.'\',\'content\')">New Reading</a>';
		$Statement   = '<a href="#" onClick="loadmypage(\'meter_statement.php?SerialNo='.$DeviceSerialNo.'\',\'content\',\'loader\',\'listpages\',\'\',\'MeterStatement\','.$DeviceSerialNo.')">Statement</a>';
		
		
		$actions='['.$EditBtn.'|'.$ReadBtn.'|'.$Statement.']';
		
		$DateIssued = date('d-m-Y',strtotime($DateIssued));
		
		$channel[] = array(
					$DeviceSerialNo,					
					$Customer,
					$DateIssued,
					$LastReading,
					$Balance,
					$actions,					
		);
	}  	
}

else if($OptionValue=='service_charges')
{
	$sql = "select sc.*, ss.SubSystemName,ct.ChargeTypeName,ls.LinkedServiceName,fy.FinancialYearName from ServiceCharges sc
			inner join SubSystems ss on sc.SubSystemID=ss.SubSystemID
			left join ChargeType ct on sc.ChargeTypeID=ct.ChargeTypeID
			left join LinkedService ls on sc.LinkedServiceID=ls.LinkedServiceID
			inner join FinancialYear fy on sc.FinancialYearID=fy.FinancialYearID
			where ServiceID=$exParam and fy.isCurrentYear=1 
			order by sc.ServiceID";



	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
				
		extract($row);
		
		$EditBtn ='';// '<a href="#" onClick="loadpage(\'service_charges.php?edit=1&ServiceID='.$exParam.'&SubSystemID='.$SubSystemId.'&FinancialYearID='.$FinancialYearId.'&ServiceAmount='.$Amount.'\',\'content\')">Edit</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'service_charges_list.php?delete=1&ServiceID='.$exParam.'&SubSystemID='.$SubSystemId.'&FinancialYearID='.$FinancialYearId.'&LinkServiceID='.$LinkServiceID.'&ChargeTypeID='.$ChargeTypeID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$exParam.'\')">Delete</a>';

		
		$DateIssued = date('Y-m-d',strtotime($CreatedDate));
		
		$channel[] = array
		(
			$FinancialYearName,
			$SubSystemName,
			$Amount,
			$EditBtn,
			$DeleteBtn					
		);
	}  	
}
else if($OptionValue=='application_charges')
{
	$sql = "select 
		pas.SetupID,apt.ApplicationTypeID,apt.ApplicationTypeName,apc.ApplicationCategoryID,apc.ApplicationCategoryName
		,iif(pas.UnitOfCharge=1,'Fixed',iif(pas.UnitOfCharge=2,'Square Meters',iif(pas.UnitOfCharge=3,'No Of Floors','Portions'))) UnitOfCharge,
		pas.StoreyedAmount,pas.NonStoreyedAmount,s.ServiceName 
		from PlanApprovalSetup pas 
		join ApplicationTypes apt on pas.ApplicationTypeID=apt.ApplicationTypeID
		join ApplicationCategories apc on pas.ApplicationCategoryID=apc.ApplicationCategoryID
		join services s on pas.ServiceID=s.ServiceID";

		$result = sqlsrv_query($db, $sql);	
		while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
		{
					
			extract($row);
			
			$EditBtn ='<a href="#" onClick="loadpage(\'application_charge.php?edit=1&SetupID='.$SetupID.'\',\'content\')">Edit</a>';
			$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'application_charges_list.php?delete=1&SetupID='.$SetupID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$exParam.'\')">Delete</a>';

			$actions='['.$EditBtn.'|'.$DeleteBtn.']';	

			
			$DateIssued = date('Y-m-d',strtotime($CreatedDate));
			
			$channel[] = array
			(
				$ApplicationTypeName,
				$ApplicationCategoryName,
				$ServiceName,
				$UnitOfCharge,
				$NonStoreyedAmount,
				$StoreyedAmount,
				$actions					
			);
		}  	
}
else if($OptionValue=='ConservancyCharges')
{
	$sql = "select c.*,ss.SubSystemName 
			from Conservancy c join SubSystems ss on c.SubSystemid=ss.SubSystemID
			order by c.[From],c.SubSystemID";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
				
		extract($row);
		
		$EditBtn ='<a href="#" onClick="loadpage(\'conservancy_charges.php?edit=1&ChargeID='.$exParam.'\',\'content\')">Edit</a>';
		// $DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'service_charges_list.php?delete=1&ServiceID='.$exParam.'&SubSystemID='.$SubSystemId.'&FinancialYearID='.$FinancialYearId.'&LinkServiceID='.$LinkServiceID.'&ChargeTypeID='.$ChargeTypeID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$exParam.'\')">Delete</a>';

		
		//$DateIssued = date('Y-m-d',strtotime($CreatedDate));
		
		$channel[] = array
		(
			$From,
			$To,
			$SubSystemName,
			$Amount,
			$EditBtn					
		);
	}  	
}
else if($OptionValue=='requisitions')
{
	$sql = "select r.*,dp.DepartmentName,ras.Name Status,(select sum(amount) from requisitionlines 
			where RequisitionHeaderID=r.requisitionheaderid)Amount
			from requisitionheader r
			inner join Departments dp on r.departmentid=dp.DepartmentID
			inner join RequisitionApprovalStatus ras on r.ApprovalStatusID=ras.RequisitionApprovalStatusID";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
				
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'service_charges.php?edit=1&RequisitionHeaderID='.$RequisitionHeaderID.'\',\'content\')">Edit</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'requisition_list.php?delete=1&RequisitionHeaderID='.$RequisitionHeaderID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$ApproveBtn = '<a href="#" onClick="loadpage(\'requisition_approval.php?edit=1&RequisitionHeaderID='.$RequisitionHeaderID.'\',\'content\')">Approve</a>';

		
		$DateIssued = date('Y-m-d',strtotime($CreatedDate));
		
		$channel[] = array(
					$DepartmentName,
					$RequisitionDate,
					$Notes,
					$Amount,
					$Status,
					$EditBtn,
					$DeleteBtn,
					$ApproveBtn					
		);
	}  	
}

else if($OptionValue=='receipts')
{
	if($exParam!=='')
	{	
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$fromDate=$str3[1];
		
		$str3=explode('=',$details[1]);
		$toDate=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$RefNo=$str3[1]; 
		
		$str3=explode('=',$details[3]);
		$InvoiceNo=$str3[1];

		$str3=explode('=',$details[4]);
		$CustomerName=$str3[1];

		$str3=explode('=',$details[5]);
		$ReceiptID=$str3[1];

	}else{
		$fromDate=date('d/m/Y');
		$toDate=date('d/m/Y');
	}
	
	$filter=" Where rec.Status<2 ";
	
	if($fromDate!=''){
		$filter.=" and rec.ReceiptDate >= '$fromDate'";
	}
	 if($toDate!=''){
		$filter.=" and rec.ReceiptDate <= '$toDate'";
	}
	if($RefNo!=''){
		$filter=" where rec.ReferenceNumber = '$RefNo'";
	}
	if($InvoiceNo!=''){
		$filter=" where rl.InvoiceHeaderID = '$InvoiceNo'";
	}

	if($CustomerName!='')
	{
		$filter=" where c.CustomerName like '%$CustomerName%'";
	}

	if($ReceiptID!='')
	{
		$filter=" where rec.ReceiptID = '$ReceiptID'";
	}


	$PageID=25;
	$myRights=getrights($db,$CurrentUser,$PageID);
	if ($myRights)
	{
		$View=$myRights['View'];
		$Edit=$myRights['Edit'];
		$Add=$myRights['Add'];
		$Delete=$myRights['Delete'];
	}

	
	
	
	$sql = "set dateformat dmy select distinct  top 100  rec.ReferenceNumber ReceiptNo,rec.ReceiptID, rec.CreatedDate ReceiptDate,rl.InvoiceHeaderID,
		rl.Amount,c.CustomerName, b.BankName ReceiptMethodName,rec.ReceiptStatusID,ih.ServiceHeaderID,iif(rec.Status=2,'Reversed','Active') Status
		from Receipts rec
		left join Banks b on rec.BankID=b.BankID
		left join (select rel.ReceiptID, rel.InvoiceheaderID,sum(rel.amount)Amount from receiptlines rel group by rel.Invoiceheaderid,rel.ReceiptID)rl on rl.ReceiptID=rec.ReceiptID
		left join invoiceheader ih on rl.InvoiceHeaderID=ih.InvoiceHeaderID
		left join ServiceHeader sh on sh.ServiceHeaderID=ih.ServiceHeaderID
		left join Customer c on sh.CustomerID=c.CustomerID
		left join ReceiptMethod rm on rec.ReceiptMethodID=rm.ReceiptMethodID
		".$filter." 
		order by rec.CreatedDate desc";
	
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{				
		extract($row);
		
		if($myRights['Delete']==1)
		{
			$btnDelete  = '|<a href="#" onClick="loadpage(\'receipt_to_invoice.php?PageID=25&Action=4&ReceiptID='.$ReceiptID.'&refno='.$ReceiptNo.'&InvoiceHeaderID='.$InvoiceHeaderID.'&amount='.$Amount.'\',\'content\')">Reverse</a>';
		}else{
			$btnDelete='';
		}

		if($myRights['Add']==1)
		{
			$Effect  = '|<a href="#" onClick="deleteConfirm2(\'Effect this Payment?\',\'receipts_list.php?effect=1&ReceiptID='.$ReceiptID.'&ReferenceNumber='.$ReceiptNo.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\'\')">Effect</a>';
		}else{
			$Effect='';
		}
		$DateIssued = date('Y-m-d',strtotime($CreatedDate));
		// $ViewBtn  = '<a href="reports.php?rptType=Receipt&ApplicationID='.$ServiceHeaderID.'&ReceiptID='.$ReceiptID.'" target="_blank">View</a>';

		if($myRights['View']==1)
		{
			$ViewBtn  = '<a href="reports.php?rptType=Receipt&InvoiceHeaderID='.$InvoiceHeaderID.'&ReceiptID='.$ReceiptID.'" target="_blank">View</a>';
		}else{
			$ViewBtn='';
		}

		
		$actions='['.$ViewBtn.$Effect.$btnDelete.']';
		
		$channel[] = array(
					$ReceiptNo,
					$ReceiptDate,
					$InvoiceHeaderID,
					$Amount,	
					$CustomerName,
					$ReceiptMethodName,
					$Status,
					$actions
		);
	}  	
}
else if($OptionValue=='missingReceipts')
{
	if($exParam!=='')
	{	
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$fromDate=$str3[1];
		
		$str3=explode('=',$details[1]);
		$toDate=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$RefNo=$str3[1]; 
		
		$str3=explode('=',$details[3]);
		$InvoiceNo=$str3[1];
	}else{
		$fromDate=date('d/m/Y');
		$toDate=date('d/m/Y');
	}
	
	$filter=" Where rec.Status<2 ";
	
	if($fromDate!=''){
		$filter.=" and rec.ReceiptDate >= '$fromDate'";
	}
	 if($toDate!=''){
		$filter.=" and rec.ReceiptDate <= '$toDate'";
	}
	if($RefNo!=''){
		$filter=" where rec.ReferenceNumber = '$RefNo'";
	}
	if($InvoiceNo!=''){
		$filter=" where rl.InvoiceHeaderID = '$InvoiceNo'";
	}
	
	
	$sql = "select distinct c.CustomerName,mplp.*,il.Description,r.CreatedDate from MPlotPayments mplp 
			join ServiceHeader sh on sh.ServiceHeaderID=mplp.ServiceHeaderID
			join InvoiceLines il on il.InvoiceHeaderID =mplp.InvoiceHeaderid
			join Customer c on c.CustomerID=sh.CustomerID
			join receipts r on r.ReferenceNumber=mplp.DocumentNo where matched=0
			order by mplp.InvoiceHeaderID";
	
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{				
		extract($row);

		$Effect  = '<a href="#" onClick="deleteConfirm2(\'Effect this Payment?\',\'match_receipt.php?effect=1&DocumentNo='.$DocumentNo.'&Amount='.$Amount.'&lrn='.$Lrn.'&plotno='.$PlotNo.'&receiptDate='.$CreatedDate.'&InvoiceHeaderID='.$InvoiceHeaderID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\'\')">Match</a>';		
		
		
		$channel[] = array(
					$CustomerName,
					$Lrn,
					$PlotNo,
					$InvoiceHeaderID,
					$DocumentNo,
					$Amount,					
					$Effect
		);
	}  	
}
else if($OptionValue=='Mpesa')
{
	if($exParam!=='')
	{	
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$fromDate=$str3[1];
		
		$str3=explode('=',$details[1]);
		$toDate=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$RefNo=$str3[1]; 
		
		$str3=explode('=',$details[3]);
		$PhoneNo=$str3[1];
		
		$str3=explode('=',$details[4]);
		$Sender=$str3[1];
	}else{
		$fromDate=date('d/m/Y');
		$toDate=date('d/m/Y');
	}
	
	$filter=" Where 1=1";
	
	if($fromDate!=''){
		$filter.=" and convert(date,tstamp) >= '$fromDate'";
	}
	 if($toDate!=''){
		$filter.=" and convert(date,tstamp) <= '$toDate'";
	}
	if($RefNo!=''){
		$filter.=" and mpesa_code = '$RefNo'";
	}
	if($PhoneNo!=''){
		$filter.=" and rl.mpesa_msisdn like '%$PhoneNo%'";
	}
	if($Sender!=''){
		$filter.=" and mpesa_sender like '%$Sender%'";
	}
	
	$orderBy=" Order by RecordID Desc";
	
	$filter2=$filter.$orderBy;
	
	$sql = "set dateformat dmy select distinct top 100 RecordID,tstamp [Date],mpesa_code,mpesa_acc,mpesa_amt,mpesa_sender from mpesa $filter2 ";	
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{				
		extract($row);
		
		$DateIssued = date('Y-m-d',strtotime($CreatedDate));

		$PageID=51;
		$myRights=getrights($db,$CurrentUser,$PageID);
		if ($myRights)
		{
			$View=$myRights['View'];
			$Edit=$myRights['Edit'];
			$Add=$myRights['Add'];
			$Delete=$myRights['Delete'];
		}
		if($myRights['Edit']==1)
		{
			$btnLink  = '<a href="#" onClick="loadpage(\'mpesa_to_invoice.php?link=1&mpesa_code='.$mpesa_code.'&mpesa_amt='.$mpesa_amt.'&mpesa_sender='.$mpesa_sender.'\',\'content\')">Link</a>';
		}else{
			$btnLink='';
		}
		
		$channel[] = array(
					$Date,
					$mpesa_code,
					$mpesa_acc,
					$mpesa_amt,
					$mpesa_sender,
					$btnLink
		);
	}  	
}
else if($OptionValue=='AgentDetails')
{
	if($exParam!=='')
	{	
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$SearchBy=$str3[1];
		
		$str3=explode('=',$details[1]);
		$SearchValue=$str3[1];
		
	}

	if ($SearchBy==1){
		$filter=" where IDNO='$SearchValue'";
	}else if($SearchBy==2){
		$filter=" where Email='$SearchValue'";
	}else if($SearchBy==3){
		$filter=" where FirstName +' '+ MiddleName+' '+ LastName like '%$SearchValue%'";
	}
	
	
	$sql = "select top 10 * from agents $filter";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{				
		extract($row);

		$ImportBtn  = '<a href="#" onClick="deleteConfirm2(\'Import this Agent As a User?\',\'users.php?save=1&AgentID='.$AgentID.'\',\'content\',\'loader\',\'listpages\',\'\',\'users\')">Import</a>';
		
		$channel[] = array(
					$IDNO,
					$FirstName.' '.$MiddleName.' '.$LastName,
					$Email,
					$ImportBtn
		);
	}  	
}

else if($OptionValue=='applications')
{
	$fromDate=date('d/m/Y');
	$toDate=date('d/m/Y');
	$filter=" and DATEDIFF(day,sh.CreatedDate,getdate())<4 ";
	$role_center=1;
	$ServiceHeaderID='';

	$UserID=$CurrentUser;
	$wards='';
	$Subcounties='';
	$locationcondition='';
	$role='None';
	//check whether the person is a clerk or Officer
	$sql="select iif (exists(select 1 from ClerkWard where UserID=$UserID and status=1),'Clerk',
			iif (exists(select 1 from ApproverSetup where UserID=$UserID and status=1),'Officer','None')) Role";

	$result=sqlsrv_query($db,$sql);
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) 
	{
		$role=$row['Role'];
	}

	if($role=='Clerk')
	{
		$sql="select WardID From ClerkWard where UserID=$UserID and Status=1";

		$result=sqlsrv_query($db,$sql);
		$i=0;

		while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
			if ($i==0){
				$wards='('.$row['WardID'];
			}else{
				$wards.=','.$row['WardID'];
			}
			$i+=1;
		}

		$wards.=')';

		$locationcondition=" and (select value from fnFormData(sh.ServiceHeaderID) WHERE FormColumnID=11204) in $wards and sh.ServiceStatusID<=3";

	}else if ($role=='Officer'){
		$sql="select SubCountyID From ApproverSetup where UserID=$UserID and Status=1";

		$result=sqlsrv_query($db,$sql);
		$i=0;
		while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
			if ($i==0){
				$subcounties='('.$row['SubCountyID'];
			}else{
				$subcounties.=','.$row['SubCountyID'];
			}
			$i+=1;
		}

		$subcounties.=')';

		$locationcondition=" and (select value from fnFormData(sh.ServiceHeaderID) WHERE FormColumnID=11203) in $subcounties and sh.ServiceStatusID>3 and sh.ServiceStatusID<7";
	}else{
		$locationcondition=" and 1=1"; //just to make sure that a person who is neither a cler nor officer cannot view anything
	}

	if (strlen($exParam)>0)
	{
		$details=explode(':',$exParam);
		
		if(strlen($exParam)>2)
		{
			$str3=explode('=',$details[0]);
			$role_center=1;//$str3[1];

			if (strpos($exParam,'fromDate')==true)
			{
				$str3=explode('=',$details[1]);
				$fromDate=$str3[1];
			}else
			{
				$fromDate='date';//date('d/m/Y');
			}

			if (strpos($exParam,'toDate')==true)
			{
				$str3=explode('=',$details[2]);
				$toDate=$str3[1];
			}else
			{
				$toDate=date('d/m/Y');
			}

			if (strpos($exParam,'ServiceHeaderID')==true)
			{
				$str3=explode('=',$details[3]);
				$ServiceHeaderID=$str3[1];
			}
			
			if(!$ServiceHeaderID=='')
			{
				$filter=" and sh.ServiceHeaderID='$ServiceHeaderID'";
			}else{
				$filter=" and convert(date,sh.CreatedDate)>='$fromDate' and convert(date,sh.CreatedDate)<='$toDate'";
			}
			
		}else{
			$role_center=1;//$exParam;
			$filter=" and DATEDIFF(day,sh.CreatedDate,getdate())<5 ";
		}
	}
		
		
	$msql = "set dateformat dmy SELECT top 100 sh.ServiceHeaderID AS ApplicationID,sh.ServiceStatusID,ss.ServiceStatusName, s.ServiceName , 
c.CustomerID, c.CustomerName, sh.SubmissionDate,sh.SetDate,s.ServiceID,ins.InspectionID,ins.InspectionStatusID FROM ServiceHeader AS sh 
INNER JOIN Services AS s ON sh.ServiceID = s.ServiceID INNER JOIN Customer AS c ON sh.CustomerID = c.CustomerID 
INNER JOIN ServiceStatus ss ON sh.ServiceStatusID=ss.ServiceStatusID INNER JOIN Inspections ins on 
ins.ServiceHeaderID=sh.ServiceHeaderID 
where sh.ServiceStatusID=2 and ins.UserID = $UserID and ins.InspectionStatusID = 0 order by sh.SubmissionDate desc";
	// echo $msql;


	//".$filter.$locationcondition."

	$result = sqlsrv_query($db, $msql);	
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);

		$sql="select fn.Value, w.RegionName Name
			from fnFormData($ApplicationID) fn 
			join Regions w on fn.Value=w.RegionID
			where fn.formcolumnid=12237
		";
		$res=sqlsrv_query($db,$sql);
		while($row=sqlsrv_fetch_array($res,SQLSRV_FETCH_ASSOC))
		{
			$RegionName=$row['Name'];
		}
		
		$SetDate1 = '';
		$d_sql="select SetDate from ServiceHeader where ServiceHeaderID = $ApplicationID";
		$dres=sqlsrv_query($db,$d_sql);
		while($row=sqlsrv_fetch_array($res,SQLSRV_FETCH_ASSOC))
		{
			$SetDate1=$row['SetDate'];
		}

		$UserID=$CurrentUser;
		$actions='';
		$Tarehe = date("Y-m-d");

		if ($Tarehe > $SetDate1){

		$CustomerName = ' <a href="#" onClick="applicant_details('.$ApplicationID.')">'.$CustomerName.'</a>';
		}else{
			$CustomerName = $CustomerName;
		}
		$channel[] = array(			
				$ApplicationID,
				$CustomerName,				
				$ServiceName,
				$SubmissionDate,
				$SetDate,
				$RegionName,
				$actions	
		);			
	}  	
	
}
else if($OptionValue=='applicant')
{
	$sql = "select c.CustomerID,
			CustomerName,PostalAddress,PhysicalAddress,Telephone1,PIN,IDNO,Town,Mobile1,Email,rg.RegionID,rg.RegionName 
			from ServiceHeader sh
			left join Customer c on c.CustomerID=sh.CustomerID
			left join (
			select fd.ServiceHeaderID,r.SubSystemID RegionID,r.SubSystemName RegionName from
			(select ServiceHeaderID,[Value] RegionID 
			from formdata where FormColumnID=12237) fd
			left join SubSystems r on r.SubSystemID=fd.RegionID
			) rg on rg.ServiceHeaderID=sh.ServiceHeaderID
			where sh.ServiceHeaderID=$param1";


	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);		
		
		$channel[] = array(
		'LicenceApplicationID'=>$param1,	
		'No'=>$CustomerID,
		'Name'=>$CustomerName,
		'Address'=>$PostalAddress,
		'City'=>$Town,
		'Mobile'=>$Mobile1,
		'Email'=>$Email,
		'LicenceApplicationID'=>$param1,
		'RegionID'=>$RegionID,
		'RegionName'=>$RegionName,
		'PhysicalLocation'=>$PhysicalAddress,
		'Telephone'=>$Telephone1,
		
  		);		
	}  	
}
else if($OptionValue=='checklist')
{
	$LicenceApplicationID=$param1;
	$UserID=$CurrentUser;


	$ServiceCategoryID = '';
	$ServiceID = '';

	$ssql= "set dateformat dmy SELECT s.ServiceCategoryID,s.ServiceID 
		from ServiceHeader AS sh 
		INNER JOIN Services AS s ON sh.ServiceID = s.ServiceID 
		INNER JOIN Customer AS c ON sh.CustomerID = c.CustomerID 
		INNER JOIN ServiceStatus ss ON sh.ServiceStatusID=ss.ServiceStatusID 
		INNER JOIN Inspections ins on ins.ServiceHeaderID=sh.ServiceHeaderID 
		where sh.ServiceStatusID=2 
		and ins.InspectionStatusID = 0 and sh.ServiceHeaderID = $LicenceApplicationID";
// echo $ssql;
		$sresult = sqlsrv_query($db, $ssql);
		while($row=sqlsrv_fetch_array($sresult, SQLSRV_FETCH_ASSOC)){

			$ServiceCategoryID = $row['ServiceCategoryID'];
			$ServiceID = $row['ServiceID'];
		}
		// echo $ServiceCategoryID; 
//set up for classification of town hotels
if($ServiceCategoryID == 2033 && $ServiceID == 2075){
	$sql = "select cat.ParameterCategoryID, cat.ParameterCategoryName,cat.ParameterCategoryDescription 
	from ChecklistParameterCategories cat where cat.ChecklistTypeID = 2";
			// echo $sql;
// echo $LicenceApplicationID;
	$result = sqlsrv_query($db, $sql);	
	$i=1;
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	

		$sql="select ParameterName ComplianceItem, ParameterScore, ParameterID from ChecklistParameters where ParameterCategoryID=$ParameterCategoryID and ChecklistTypeID = 2";
		// echo $sql;
		$result2 = sqlsrv_query($db, $sql);
// echo $sql;
		$table='<table class="bordered" width="100%"><tbody>';
		$tr='';
		while ($cp = sqlsrv_fetch_array( $result2, SQLSRV_FETCH_ASSOC)) 
		{
			extract($cp);
			$tr.='<tr>';
			$td='<td>'.$ComplianceItem.'</td>';
			$td.='<td>
					<div class="input-control select">
						<select id="verdict" name="'.$ParameterID.'_v1"><option value="1">Yes</option><option value="0" selected>No</option>
						</select>
					</div>
				</td>';
			$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v2" id="recommendation"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v3" id="quantity" rows="2" cols="5"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control numberTest">
						Max score '.$ParameterScore.'
					</div>
					</td>';
					
			$tr.=$td.'</tr>';
		}
		$table.=$tr.'</tbody></table>';


		$CompulsoryText = '';
		if ($Compulsory == 1)
		{
			$CompulsoryText = '<span style="color:#F00">*</span>';
		}

		$ItemName = "K_".$ParameterID;	
		$checkedstring = 'checked="checked"';
		$channel[] = array
		(
			'<div>'.$i.'</div>',
			'<div>'.
				'<div style="font-weight:bold">'.$ParameterCategoryName.'</div>'.
				'<div>Parameter ID: '.$ParameterCategoryDescription.'</div>'.						
			'</div>',
			'<div>'.$table.'</div>'
		);
		$i+=1;	
	}
	//set up for classification of restaurants
}elseif($ServiceCategoryID == 2033 && $ServiceID == 2073){
	$sql = "select cat.ParameterCategoryID, cat.ParameterCategoryName,cat.ParameterCategoryDescription 
	from ChecklistParameterCategories cat where cat.ChecklistTypeID = 8";
			// echo $sql;
// echo $LicenceApplicationID;
	$result = sqlsrv_query($db, $sql);	
	$i=1;
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	

		$sql="select ParameterName ComplianceItem, ParameterScore, ParameterID from ChecklistParameters where ParameterCategoryID=$ParameterCategoryID and ChecklistTypeID = 8";
		// echo $sql;
		$result2 = sqlsrv_query($db, $sql);
// echo $sql;
		$table='<table class="bordered" width="100%"><tbody>';
		$tr='';
		while ($cp = sqlsrv_fetch_array( $result2, SQLSRV_FETCH_ASSOC)) 
		{
			extract($cp);
			$tr.='<tr>';
			$td='<td>'.$ComplianceItem.'</td>';
			$td.='<td>
					<div class="input-control select">
						<select id="verdict" name="'.$ParameterID.'_v1"><option value="1">Yes</option><option value="0" selected>No</option>
						</select>
					</div>
				</td>';
			$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v2" id="recommendation"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v3" id="quantity" rows="2" cols="5"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control numberTest">
						Max score '.$ParameterScore.'
					</div>
					</td>';
					
			$tr.=$td.'</tr>';
		}
		$table.=$tr.'</tbody></table>';


		$CompulsoryText = '';
		if ($Compulsory == 1)
		{
			$CompulsoryText = '<span style="color:#F00">*</span>';
		}

		$ItemName = "K_".$ParameterID;	
		$checkedstring = 'checked="checked"';
		$channel[] = array
		(
			'<div>'.$i.'</div>',
			'<div>'.
				'<div style="font-weight:bold">'.$ParameterCategoryName.'</div>'.
				'<div>Parameter ID: '.$ParameterCategoryDescription.'</div>'.						
			'</div>',
			'<div>'.$table.'</div>'
		);
		$i+=1;	
	}
	//set up for classification of vacation hotels
}elseif($ServiceCategoryID == 2033 && $ServiceID == 2076){
	$sql = "select cat.ParameterCategoryID, cat.ParameterCategoryName,cat.ParameterCategoryDescription 
	from ChecklistParameterCategories cat where cat.ChecklistTypeID = 3";
			// echo $sql;
// echo $LicenceApplicationID;
	$result = sqlsrv_query($db, $sql);	
	$i=1;
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	

		$sql="select ParameterName ComplianceItem, ParameterScore, ParameterID from ChecklistParameters where ParameterCategoryID=$ParameterCategoryID and ChecklistTypeID = 3";
		// echo $sql;
		$result2 = sqlsrv_query($db, $sql);
// echo $sql;
		$table='<table class="bordered" width="100%"><tbody>';
		$tr='';
		while ($cp = sqlsrv_fetch_array( $result2, SQLSRV_FETCH_ASSOC)) 
		{
			extract($cp);
			$tr.='<tr>';
			$td='<td>'.$ComplianceItem.'</td>';
			$td.='<td>
					<div class="input-control select">
						<select id="verdict" name="'.$ParameterID.'_v1"><option value="1">Yes</option><option value="0" selected>No</option>
						</select>
					</div>
				</td>';
			$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v2" id="recommendation"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v3" id="quantity" rows="2" cols="5"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control numberTest">
						Max score '.$ParameterScore.'
					</div>
					</td>';
					
			$tr.=$td.'</tr>';
		}
		$table.=$tr.'</tbody></table>';


		$CompulsoryText = '';
		if ($Compulsory == 1)
		{
			$CompulsoryText = '<span style="color:#F00">*</span>';
		}

		$ItemName = "K_".$ParameterID;	
		$checkedstring = 'checked="checked"';
		$channel[] = array
		(
			'<div>'.$i.'</div>',
			'<div>'.
				'<div style="font-weight:bold">'.$ParameterCategoryName.'</div>'.
				'<div>Parameter ID: '.$ParameterCategoryDescription.'</div>'.						
			'</div>',
			'<div>'.$table.'</div>'
		);
		$i+=1;	
	}
	//set up for the classification of lodges 
}elseif($ServiceCategoryID == 2033 && $ServiceID == 2078){
	$sql = "select cat.ParameterCategoryID, cat.ParameterCategoryName,cat.ParameterCategoryDescription 
	from ChecklistParameterCategories cat where cat.ChecklistTypeID = 4";
			// echo $sql;
// echo $LicenceApplicationID;
	$result = sqlsrv_query($db, $sql);	
	$i=1;
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	

		$sql="select ParameterName ComplianceItem, ParameterScore, ParameterID from ChecklistParameters where ParameterCategoryID=$ParameterCategoryID and ChecklistTypeID = 4";
		// echo $sql;
		$result2 = sqlsrv_query($db, $sql);
// echo $sql;
		$table='<table class="bordered" width="100%"><tbody>';
		$tr='';
		while ($cp = sqlsrv_fetch_array( $result2, SQLSRV_FETCH_ASSOC)) 
		{
			extract($cp);
			$tr.='<tr>';
			$td='<td>'.$ComplianceItem.'</td>';
			$td.='<td>
					<div class="input-control select">
						<select id="verdict" name="'.$ParameterID.'_v1"><option value="1">Yes</option><option value="0" selected>No</option>
						</select>
					</div>
				</td>';
			$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v2" id="recommendation"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v3" id="quantity" rows="2" cols="5"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control numberTest">
						Max score '.$ParameterScore.'
					</div>
					</td>';
					
			$tr.=$td.'</tr>';
		}
		$table.=$tr.'</tbody></table>';


		$CompulsoryText = '';
		if ($Compulsory == 1)
		{
			$CompulsoryText = '<span style="color:#F00">*</span>';
		}

		$ItemName = "K_".$ParameterID;	
		$checkedstring = 'checked="checked"';
		$channel[] = array
		(
			'<div>'.$i.'</div>',
			'<div>'.
				'<div style="font-weight:bold">'.$ParameterCategoryName.'</div>'.
				'<div>Parameter ID: '.$ParameterCategoryDescription.'</div>'.						
			'</div>',
			'<div>'.$table.'</div>'
		);
		$i+=1;	
	}
	//set up for classification of villas
}elseif($ServiceCategoryID == 2033 && $ServiceID == 2079){
	$sql = "select cat.ParameterCategoryID, cat.ParameterCategoryName,cat.ParameterCategoryDescription 
	from ChecklistParameterCategories cat where cat.ChecklistTypeID = 6";
			// echo $sql;
// echo $LicenceApplicationID;
	$result = sqlsrv_query($db, $sql);	
	$i=1;
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	

		$sql="select ParameterName ComplianceItem, ParameterScore, ParameterID from ChecklistParameters where ParameterCategoryID=$ParameterCategoryID and ChecklistTypeID = 6";
		// echo $sql;
		$result2 = sqlsrv_query($db, $sql);
// echo $sql;
		$table='<table class="bordered" width="100%"><tbody>';
		$tr='';
		while ($cp = sqlsrv_fetch_array( $result2, SQLSRV_FETCH_ASSOC)) 
		{
			extract($cp);
			$tr.='<tr>';
			$td='<td>'.$ComplianceItem.'</td>';
			$td.='<td>
					<div class="input-control select">
						<select id="verdict" name="'.$ParameterID.'_v1"><option value="1">Yes</option><option value="0" selected>No</option>
						</select>
					</div>
				</td>';
			$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v2" id="recommendation"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v3" id="quantity" rows="2" cols="5"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control numberTest">
						Max score '.$ParameterScore.'
					</div>
					</td>';
					
			$tr.=$td.'</tr>';
		}
		$table.=$tr.'</tbody></table>';


		$CompulsoryText = '';
		if ($Compulsory == 1)
		{
			$CompulsoryText = '<span style="color:#F00">*</span>';
		}

		$ItemName = "K_".$ParameterID;	
		$checkedstring = 'checked="checked"';
		$channel[] = array
		(
			'<div>'.$i.'</div>',
			'<div>'.
				'<div style="font-weight:bold">'.$ParameterCategoryName.'</div>'.
				'<div>Parameter ID: '.$ParameterCategoryDescription.'</div>'.						
			'</div>',
			'<div>'.$table.'</div>'
		);
		$i+=1;	
	}
	//set up for classification of tented camps
}elseif($ServiceCategoryID == 2033 && $ServiceID == 2080){
	$sql = "select cat.ParameterCategoryID, cat.ParameterCategoryName,cat.ParameterCategoryDescription 
	from ChecklistParameterCategories cat where cat.ChecklistTypeID = 5";
			// echo $sql;
// echo $LicenceApplicationID;
	$result = sqlsrv_query($db, $sql);	
	$i=1;
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	

		$sql="select ParameterName ComplianceItem, ParameterScore, ParameterID from ChecklistParameters where ParameterCategoryID=$ParameterCategoryID and ChecklistTypeID = 5";
		// echo $sql;
		$result2 = sqlsrv_query($db, $sql);
// echo $sql;
		$table='<table class="bordered" width="100%"><tbody>';
		$tr='';
		while ($cp = sqlsrv_fetch_array( $result2, SQLSRV_FETCH_ASSOC)) 
		{
			extract($cp);
			$tr.='<tr>';
			$td='<td>'.$ComplianceItem.'</td>';
			$td.='<td>
					<div class="input-control select">
						<select id="verdict" name="'.$ParameterID.'_v1"><option value="1">Yes</option><option value="0" selected>No</option>
						</select>
					</div>
				</td>';
			$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v2" id="recommendation"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v3" id="quantity" rows="2" cols="5"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control numberTest">
						Max score '.$ParameterScore.'
					</div>
					</td>';
					
			$tr.=$td.'</tr>';
		}
		$table.=$tr.'</tbody></table>';


		$CompulsoryText = '';
		if ($Compulsory == 1)
		{
			$CompulsoryText = '<span style="color:#F00">*</span>';
		}

		$ItemName = "K_".$ParameterID;	
		$checkedstring = 'checked="checked"';
		$channel[] = array
		(
			'<div>'.$i.'</div>',
			'<div>'.
				'<div style="font-weight:bold">'.$ParameterCategoryName.'</div>'.
				'<div>Parameter ID: '.$ParameterCategoryDescription.'</div>'.						
			'</div>',
			'<div>'.$table.'</div>'
		);
		$i+=1;	
	}
	//set up for classification of motels
}elseif($ServiceCategoryID == 2033 && $ServiceID == 2081){
	$sql = "select cat.ParameterCategoryID, cat.ParameterCategoryName,cat.ParameterCategoryDescription 
	from ChecklistParameterCategories cat where cat.ChecklistTypeID = 7";
			// echo $sql;
// echo $LicenceApplicationID;
	$result = sqlsrv_query($db, $sql);	
	$i=1;
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	

		$sql="select ParameterName ComplianceItem, ParameterScore, ParameterID from ChecklistParameters where ParameterCategoryID=$ParameterCategoryID and ChecklistTypeID = 7";
		// echo $sql;
		$result2 = sqlsrv_query($db, $sql);
// echo $sql;
		$table='<table class="bordered" width="100%"><tbody>';
		$tr='';
		while ($cp = sqlsrv_fetch_array( $result2, SQLSRV_FETCH_ASSOC)) 
		{
			extract($cp);
			$tr.='<tr>';
			$td='<td>'.$ComplianceItem.'</td>';
			$td.='<td>
					<div class="input-control select">
						<select id="verdict" name="'.$ParameterID.'_v1"><option value="1">Yes</option><option value="0" selected>No</option>
						</select>
					</div>
				</td>';
			$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v2" id="recommendation"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v3" id="quantity" rows="2" cols="5"></textarea>
					</div>
					</td>';

					$td.='<td>
					<div class="input-control numberTest">
						Max score '.$ParameterScore.'
					</div>
					</td>';
					
			$tr.=$td.'</tr>';
		}
		$table.=$tr.'</tbody></table>';


		$CompulsoryText = '';
		if ($Compulsory == 1)
		{
			$CompulsoryText = '<span style="color:#F00">*</span>';
		}

		$ItemName = "K_".$ParameterID;	
		$checkedstring = 'checked="checked"';
		$channel[] = array
		(
			'<div>'.$i.'</div>',
			'<div>'.
				'<div style="font-weight:bold">'.$ParameterCategoryName.'</div>'.
				'<div>Parameter ID: '.$ParameterCategoryDescription.'</div>'.						
			'</div>',
			'<div>'.$table.'</div>'
		);
		$i+=1;	
	}
	}else{
		$sql = "select cat.ParameterCategoryID, cat.ParameterCategoryName,cat.ParameterCategoryDescription
			from ChecklistParameterCategories cat";
// echo $LicenceApplicationID;
	$result = sqlsrv_query($db, $sql);	
	$i=1;
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	

		$sql="select ParameterName ComplianceItem, ParameterScore, ParameterID from ChecklistParameters where ParameterCategoryID=$ParameterCategoryID";
		// echo $sql;
		$result2 = sqlsrv_query($db, $sql);
// echo $sql;
		$table='<table class="bordered" width="100%"><tbody>';
		$tr='';
		while ($cp = sqlsrv_fetch_array( $result2, SQLSRV_FETCH_ASSOC)) 
		{
			extract($cp);
			$tr.='<tr>';
			$td='<td>'.$ComplianceItem.'</td>';
			$td.='<td>
					<div class="input-control select">
						<select id="verdict" name="'.$ParameterID.'_v1"><option value="1">Yes</option><option value="0" selected>No</option>
						</select>
					</div>
				</td>';
			$td.='<td>
					<div class="input-control textarea">
						<textarea name="'.$ParameterID.'_v2" id="recommendation"></textarea>
					</div>
					</td>';

					// $td.='<td>
					// <div class="input-control textarea">
					// 	<textarea name="'.$ParameterID.'_v3" id="quantity" rows="2" cols="5"></textarea>
					// </div>
					// </td>';

					// $td.='<td>
					// <div class="input-control numberTest">
					// 	Max score '.$ParameterScore.'
					// </div>
					// </td>';
					
			$tr.=$td.'</tr>';
		}
		$table.=$tr.'</tbody></table>';


		$CompulsoryText = '';
		if ($Compulsory == 1)
		{
			$CompulsoryText = '<span style="color:#F00">*</span>';
		}

		$ItemName = "K_".$ParameterID;	
		$checkedstring = 'checked="checked"';
		$channel[] = array
		(
			'<div>'.$i.'</div>',
			'<div>'.
				'<div style="font-weight:bold">'.$ParameterCategoryName.'</div>'.
				'<div>Parameter ID: '.$ParameterCategoryDescription.'</div>'.						
			'</div>',
			'<div>'.$table.'</div>'
		);
		$i+=1;	
	}
	}  	
}

else if($OptionValue=='SaveReport')
{
	$LicenceApplicationID=$param1;
	$verdict=$param2;
	$comments=$param3;	

	$sql="update Inspections set InspectionStatusID='$verdict',UserComment='$comments' where ServiceHeaderID=$LicenceApplicationID and UserID=$UserID";
	// echo $sql;exit;
	$results=sqlsrv_query($db,$sql);

	if($results){
		$channel[] = array(			
				'Result'=>'1'
		);
	}
	
}
else if($OptionValue=='SaveChecklist')
{
	$vs=$param2;
	$LicenceApplicationID=$param1;

	$P_ParameterID=0;
	$valuearray=explode(":", $vs);
	// echo '<pre>';
	// print_r($valuearray);

	$sql="Select InspectionID from Inspections where ServiceHeaderID=$LicenceApplicationID and UserID = $UserID";
	// echo $sql;exit;
	$results=sqlsrv_query($db,$sql);

	while ($rw=sqlsrv_fetch_array($results,SQLSRV_FETCH_ASSOC)){
		$InspectionID=$rw["InspectionID"];
	}

	for($i=1;$i<sizeof($valuearray);$i++)
	{
		$values=explode("_",$valuearray[$i]);
		//echo 'Param '.$values[0].','.$values[1].'='.$values[2].'='.$values[3]'<br>';
		// echo '<pre>';
		// print_r($values);

		$ParameterID=$values[0];
		
		if($values[1]=='v1'){

			$sql="insert into ChecklistResults(InspectionID,ParameterID,ResultOptionID,CreatedBy) 
				values('$InspectionID',$values[0],$values[2],$UserID) SELECT SCOPE_IDENTITY() AS ID";
				 //echo $sql;

			$results=sqlsrv_query($db,$sql);
		}elseif($values[1]=='v2')
		{	
			if($values[1]!='') 
			{
				$sql="Update ChecklistResults set ResultComment='$values[2]' where InspectionID=$InspectionID and ParameterID=$ParameterID and CreatedBy = $UserID";
				 //echo $sql;
				$results=sqlsrv_query($db,$sql);

			}						
		}elseif($values[1]=='v3')
		{	
			if($values[1]!='') 
			{
				$sql="Update ChecklistResults set ParameterScore=$values[2] where InspectionID=$InspectionID and ParameterID=$ParameterID and CreatedBy = $UserID";
				 //echo $sql;
				$results=sqlsrv_query($db,$sql);

			}						
		}

	}
	// // date('Y-m-d H:i:s')
	// $TodayDate = date("Y-m-d H:i:s");
	// $date = 31; $month =12; $year = date("Y"); //Licences Expire on 31ST Dec EveryYear
	// $ExpiryDate="$date.$month.$year";
    // $local=new datetime($ExpiryDate);
    // $sqlExpiryDate = $local->format('Y-m-d H:i:s');
	
	
	$sql="Update ServiceHeader set ServiceStatusID=5, where ServiceHeaderID='$LicenceApplicationID'";
	// exit($sql);
	$results=sqlsrv_query($db,$sql);
	// DisplayErrors();
	$channel[] = array(			
				'Result'=>'1'
		);
}
else if($OptionValue=='applications_all')
{
	$fromDate=date('d/m/Y');
	$toDate=date('d/m/Y');
	$filter=" and DATEDIFF(day,sh.CreatedDate,getdate())<3 ";
	$role_center=1;
	$ServiceHeaderID='';

	$UserID=$CurrentUser;
	$wards='';
	$Subcounties='';
	$locationcondition='';
	$role='None';
	

	if (strlen($exParam)>0)
	{
		$details=explode(':',$exParam);
		
		if(strlen($exParam)>2)
		{
			$str3=explode('=',$details[0]);
			$role_center=1;//$str3[1];

			if (strpos($exParam,'fromDate')==true)
			{
				$str3=explode('=',$details[1]);
				$fromDate=$str3[1];
			}else{
				$fromDate='date';//date('d/m/Y');
			}

			if (strpos($exParam,'toDate')==true)
			{
				$str3=explode('=',$details[2]);
				$toDate=$str3[1];
			}else{
				$toDate=date('d/m/Y');
			}

			if (strpos($exParam,'ServiceHeaderID')==true)
			{
				$str3=explode('=',$details[3]);
				$ServiceHeaderID=$str3[1];
			}

			if (strpos($exParam,'CustomerName')==true)
			{
				$str3=explode('=',$details[4]);
				$CustomerName=$str3[1];
			}

			if($CustomerName!==''){
				$filter= " and c.CustomerName like '%$CustomerName%'";
			}
			
			if(!$ServiceHeaderID=='')
			{
				$filter=" and sh.ServiceHeaderID='$ServiceHeaderID'";
			}else{
				if(!($fromDate=='' || $toDate=='')){
					$filter=" and convert(date,sh.CreatedDate)>='$fromDate' and convert(date,sh.CreatedDate)<='$toDate'";
				}
			}
			
		}else{
			$role_center=1;//$exParam;
			$filter=" and DATEDIFF(day,sh.CreatedDate,getdate())<5 ";
		}
	}
		
		
	$msql = "set dateformat dmy SELECT top 100 sh.ServiceHeaderID AS ApplicationID,sh.ServiceStatusID,ss.ServiceStatusName, 
	s.ServiceName ,c.CustomerID, c.CustomerName, sh.SubmissionDate,s.ServiceID,f.ServiceHeaderType ApplicationType,s.ServiceCategoryID,s.ServiceCategoryID

	FROM dbo.ServiceHeader AS sh INNER JOIN 
	dbo.Services AS s ON sh.ServiceID = s.ServiceID INNER JOIN
	dbo.Customer AS c ON sh.CustomerID = c.CustomerID INNER JOIN 
	dbo.ServiceStatus ss ON sh.ServiceStatusID=ss.ServiceStatusID INNER JOIN
	DBO.ServiceCategory sc on s.ServiceCategoryID=sc.ServiceCategoryID INNER JOIN
	dbo.Forms f on sh.FormID=f.FormID 	 
	where s.ServiceCategoryID!=1  
	and (sc.InvoiceStage<>sc.LastStage or sh.ServiceStatusID<>sc.LastStage)
	and sh.ServiceID not in (select ServiceID from ServiceTrees) 
	and sh.ServiceID<>1603 ".$filter."
	order by sh.SubmissionDate desc";

		
	//".$filter.$locationcondition."

	$result = sqlsrv_query($db, $msql);	
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$app_type=$ApplicationType;
		if($ServiceCategoryID=="81"){
			$sql="select distinct Value CustomerName from fnFormData ($ApplicationID) where formcolumnid=137";
			$rslt=sqlsrv_query($db,$sql);
			while($rw=sqlsrv_fetch_array($rslt,SQLSRV_FETCH_ASSOC)){
				$CustomerName=$rw['CustomerName'];
			}	
			
		}

		$sql="select fn.Value, w.WardName from fnFormData($ApplicationID) fn 
		join Wards w on fn.Value=w.WardID
		where fn.formcolumnid=11204
		";
		$res=sqlsrv_query($db,$sql);
		while($row=sqlsrv_fetch_array($res,SQLSRV_FETCH_ASSOC))
		{
			$WardName=$row['WardName'];
		}

		$sql="select fn.Value, w.SubCountyName from fnFormData($ApplicationID) fn 
		join SubCounty w on fn.Value=w.SubCountyID
		where fn.formcolumnid=11203
		";
		$res=sqlsrv_query($db,$sql);
		while($row=sqlsrv_fetch_array($res,SQLSRV_FETCH_ASSOC))
		{
			$SubCountyName=$row['SubCountyName'];
		}

		$EditBtn = '<a href="#" onClick="loadpage(\'application_change_all.php?edit=1&ApplicationID='.$ApplicationID.'\',\'content\')">Edit</a>';
			
		$channel[] = array(			
				$ApplicationID,
				$CustomerName,				
				$ServiceName,
				$SubmissionDate,
				$SubCountyName,
				$WardName,
				$EditBtn	
		);			
	}  	
	
}
else if($OptionValue=='applications_land')
{
	$fromDate;
	$toDate;
	$lrn;
	$plotno;
	$filter;
	if (strlen($exParam)>0)
	{
		$details=explode(':',$exParam);
		
		if(strlen($exParam)>2)
		{
			$str3=explode('=',$details[0]);
			$role_center=1;//$str3[1];
			
			$str3=explode('=',$details[1]);
			$fromDate=$str3[1];		
			
			$str3=explode('=',$details[2]);
			$toDate=$str3[1];

			$str3=explode('=',$details[3]);
			$lrn=$str3[1];

			$str3=explode('=',$details[4]);
			$plotno=$str3[1];

			//$filter=" and convert(date,sh.CreatedDate)>='$fromDate' and convert(date,sh.CreatedDate)<='$toDate' ";
		}else{
			$role_center=1;//$exParam;
			$filter=" and DATEDIFF(day,sh.CreatedDate,getdate())<3 ";
		}


		if(!$fromDate=='')
		{
			$filter.=" and sh.CreatedDate>='$fromDate'";
		}

		if(!$toDate=='')
		{
			$filter.=" and sh.CreatedDate<='$toDate'";
		}
				
		if(!$lrn==0)
		{
			$filter.=" and la.lrn='$lrn'";
		}


		if(!$plotno==0)
		{
			$filter.=" and la.plotno='$plotno'";
		}

		
		$sql1 = "set dateformat dmy  SELECT TOP 100 sh.ServiceHeaderID AS ApplicationID,sh.ServiceStatusID,ss.ServiceStatusName, 
		s.ServiceName ,c.CustomerID, c.CustomerName, sh.SubmissionDate,s.ServiceID,f.ServiceHeaderType ApplicationType,
		s.ServiceCategoryID,s.ServiceCategoryID,la.LRN,la.PlotNo
		FROM dbo.ServiceHeader AS sh INNER JOIN 
		dbo.Services AS s ON sh.ServiceID = s.ServiceID INNER JOIN
		dbo.ServiceStatus ss ON sh.ServiceStatusID=ss.ServiceStatusID INNER JOIN
		DBO.ServiceCategory sc on s.ServiceCategoryID=sc.ServiceCategoryID INNER JOIN
		dbo.Forms f on sh.FormID=f.FormID 
		join LandApplication la on la.ServiceHeaderID=sh.ServiceHeaderID
		left join Land l on la.lrn=l.lrn AND LA.plotno=l.plotno 
		left join LandOwner lo on la.lrn=lo.lrn AND LA.plotno=lo.plotno
		join Customer c on isnull(lo.CustomerID,sh.CustomerID)=c.CustomerID 
		where s.ServiceCategoryID!=1 and sh.ServiceStatusID NOT IN (0,7) and sh.ServiceStatusID in 
		(select ServiceStatusID from RoleCenterApproval where RoleCenterID=$role_center)
		
		and sh.ServiceID not in (select ServiceID from ServiceTrees) 
		and sh.ServiceID=1603  ".$filter."
		 order by sh.SubmissionDate desc";

		//and (sc.InvoiceStage<>sc.LastStage or sh.ServiceStatusID<>sc.LastStage)
		
	}else{
		
	}

	$result = sqlsrv_query($db, $sql1);	
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$app_type=$ApplicationType;
		// if($ServiceCategoryID=="81"){
		// 	$sql="select distinct Value CustomerName from fnFormData ($ApplicationID) where formcolumnid=137";
		// 	$rslt=sqlsrv_query($db,$sql);
		// 	while($rw=sqlsrv_fetch_array($rslt,SQLSRV_FETCH_ASSOC)){
		// 		$CustomerName=$rw['CustomerName'];
		// 	}	
			
		// }
		
		
		$CustomerName =  '<a href="#" onClick="loadoptionalpage('.$ApplicationID.','.$app_type.','.$ServiceStatusID.',\'content\',\'loader\',\'listpages\',\'\',\''.$ApplicationID.'\')">'.$CustomerName.'</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'clients_list.php?delete=1&ApplicationID='.$ApplicationID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$channel[] = array(			
				$ApplicationID,
				$CustomerName,				
				$ServiceName,
				$SubmissionDate,
				$ServiceStatusName
		);			
	}  	
	//print_r($channel);
}
else if($OptionValue=='subcounties')
{
	$sql = "select * from SubCounty order by 1";//and (sh.ServiceHeaderID=96 or sh.ServiceHeaderID=95)
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);		
		
		$SubCountyName =  '<a href="#" onClick="loadpage(\'subcounty.php?edit=1&SubCountyID='.$SubCountyID.'\',\'content\')">'.$SubCountyName.'</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'subcounty_list.php?delete=1&SubCountyID='.$SubCountyID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
	
		$channel[] = array(	
			$SubCountyID,		
			$SubCountyName,
			$DeleteBtn
		);
		
	}  	
}
else if($OptionValue=='Banks')
{
	$sql = "select * from Banks order by 1";//and (sh.ServiceHeaderID=96 or sh.ServiceHeaderID=95)
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);		
		
		$BankName =  '<a href="#" onClick="loadpage(\'bank.php?edit=1&BankID='.$BankID.'\',\'content\')">'.$BankName.'</a>';
		$DeleteBtn   ='';// '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'banks_list.php?delete=1&BankID='.$BankID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
	
		$channel[] = array(	
			$BankName,		
			$AccountNumber,
			$Branch,
			$DeleteBtn
		);
		
	}  	
}
else if($OptionValue=='wards')
{
	$sql = "SELECT w.WardID,w.WardName,sb.SubCountyName
  			FROM Wards w join SubCounty sb on w.SubCountyID=sb.SubCountyID order by w.SubCountyID";//and (sh.ServiceHeaderID=96 or sh.ServiceHeaderID=95)
	//echo $sql;
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$WardName =  '<a href="#" onClick="loadpage(\'ward.php?edit=1&WardID='.$WardID.'\',\'content\')">'.$WardName.'</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'wards_list.php?delete=1&WardID='.$WardID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$channel[] = array(			
					$WardID,
					$WardName,
					$SubCountyName,
					$DeleteBtn
		);
		
	}  	
}
else if($OptionValue=='MatatuSaccos')
{
	$sql = "select c.CustomerID,c.customername CompanyName,count(cv.regno) Vehicles
		from Customer c 
		join (select * from customerVehicles where status=1) cv on cv.CustomerID=c.CustomerID
		join BusParks bp on cv.BusParkID=bp.ParkID 
		join MatatuRoutes mr on cv.[Route]=mr.RouteID
		where c.CustomerTypeID='4'
		group by c.customerid,c.customername";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		//$viewBtn =  '<a href="#" onClick="loadpage(\'vehicles_list.php?CustomerID='.$CustomerID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$CustomerID.'\')">Vehicles</a>';
		$viewBtn  = '<a href="reports.php?rptType=vehicles&CustomerID='.$CustomerID.'&noofvehicles='.$Vehicles.'&CustomerName='.$CompanyName.'" target="_blank">View</a>';
		$invoice =  '<a href="#" onClick="loadpage(\'buspark_invoicing.php?CustomerID='.$CustomerID.'&noofvehicles='.$Vehicles.'&CustomerName='.$CompanyName.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$CustomerID.'\')">Invoice</a>';
		$btnVehicles =  '<a href="#" onClick="loadmypage(\'sacco_vehicles_list.php?CustomerID='.$CustomerID.'\',\'content\',\'loader\',\'listpages\',\'\',\'CustomerVehicles\',\''.$CustomerID.'\')">Vehicles</a>';

		$actions='['.$viewBtn.'|'.$invoice.'|'.$btnVehicles.']';

		$channel[] = array(			
					$CustomerID,
					$CompanyName,
					$Vehicles,
					$actions
		);
		
	}  	
}
else if($OptionValue=='TimedParking')
{
	$sql = "Select *, iif(Status=1,'Parked','Left') ParkingStatus from Parking order by Checkin_Time desc";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		//$viewBtn =  '<a href="#" onClick="loadpage(\'vehicles_list.php?CustomerID='.$CustomerID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$CustomerID.'\')">Vehicles</a>';
		$viewBtn  = '<a href="reports.php?rptType=vehicles&CustomerID='.$CustomerID.'&noofvehicles='.$Vehicles.'&CustomerName='.$CompanyName.'" target="_blank">View</a>';
		$invoice =  '<a href="#" onClick="loadpage(\'buspark_invoicing.php?CustomerID='.$CustomerID.'&noofvehicles='.$Vehicles.'&CustomerName='.$CompanyName.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$CustomerID.'\')">Invoice</a>';
		$btnVehicles =  '<a href="#" onClick="loadmypage(\'sacco_vehicles_list.php?CustomerID='.$CustomerID.'\',\'content\',\'loader\',\'listpages\',\'\',\'CustomerVehicles\',\''.$CustomerID.'\')">Vehicles</a>';

		$actions='['.$viewBtn.'|'.$invoice.'|'.$btnVehicles.']';

		$channel[] = array(			
					$RegNo,
					$VehicleType,
					$Checkin_Time,
					$Checkout_Time,
					$ParkingStatus,
					$Cost
		);
		
	}  	
}
else if($OptionValue=='DailyParking')
{
	$sql = "Select *, iif(Status=1,'Parked','Left') ParkingStatus from Parking order by Checkin_Time desc";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		//$viewBtn =  '<a href="#" onClick="loadpage(\'vehicles_list.php?CustomerID='.$CustomerID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$CustomerID.'\')">Vehicles</a>';
		$viewBtn  = '<a href="reports.php?rptType=vehicles&CustomerID='.$CustomerID.'&noofvehicles='.$Vehicles.'&CustomerName='.$CompanyName.'" target="_blank">View</a>';
		$invoice =  '<a href="#" onClick="loadpage(\'buspark_invoicing.php?CustomerID='.$CustomerID.'&noofvehicles='.$Vehicles.'&CustomerName='.$CompanyName.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$CustomerID.'\')">Invoice</a>';
		$btnVehicles =  '<a href="#" onClick="loadmypage(\'sacco_vehicles_list.php?CustomerID='.$CustomerID.'\',\'content\',\'loader\',\'listpages\',\'\',\'CustomerVehicles\',\''.$CustomerID.'\')">Vehicles</a>';

		$actions='['.$viewBtn.'|'.$invoice.'|'.$btnVehicles.']';

		$channel[] = array(			
					$RegNo,
					$VehicleType,										
					$Street,
					$Checkin_Time,
					$Cost
		);
		
	}  	
}

else if($OptionValue=='CustomerVehicles')
{
	$sql = "select cv.RegNo,sc.SittingCapacity,bp.ParkName,mr.RouteName,bp.ParkID,cv.VehicleID,cv.CustomerID 
			from CustomerVehicles cv
			left join BusParks bp on cv.BusParkID=bp.ParkID 
			left join MatatuRoutes mr on cv.[Route]=mr.RouteID
			left join SittingCapacity sc on cv.SittingCapacity=sc.ID
			where cv.CustomerID=$exParam";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);

		$EditBtn = '<a href="#" onClick="loadpage(\'sacco_vehicle.php?edit=1&VehicleID='.$VehicleID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'sacco_vehicles_list.php?delete=1&VehicleID='.$VehicleID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$CustomerID.'\')">Delete</a>';

		$actions='['.$EditBtn.'|'.$DeleteBtn.']';


		$channel[] = array(			
					$RegNo,
					$SittingCapacity,
					$ParkName,					
					$RouteName,
					$actions
		);
		
	}  	
}
else if($OptionValue=='zones')
{
	$sql = "SELECT bz.ZoneID, bz.ZoneName,w.wardname,sb.SubCountyName
			FROM BusinessZones bz
			join Wards w on bz.wardid=w.wardid
			join SubCounty sb on w.SubCountyID=w.SubCountyID";//and (sh.ServiceHeaderID=96 or sh.ServiceHeaderID=95)
	//echo $sql;
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
				
		$ZoneName =  '<a href="#" onClick="loadpage(\'zones.php?edit=1&ZoneID='.$ZoneID.'\',\'content\')">'.$ZoneName.'</a>';
		
	
		$channel[] = array(			
					$ZoneName,
					$wardname,
					$SubCountyName,
					'',
					''
						
		);
		
	}  	
}
else if($OptionValue=='ServiceStatus')
{
	$sql = "select ServiceStatusID,ServiceStatusName,ServiceStatusDisplay from servicestatus";//and (sh.ServiceHeaderID=96 or sh.ServiceHeaderID=95)
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		
		$EditBtn = '<a href="#" onClick="loadpage(\'service_status.php?edit=1&ServiceStatusID='.$ServiceStatusID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'service_status_list.php?delete=1&ServiceStatusID='.$ServiceStatusID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';	
	
		$channel[] = array(			
					$ServiceStatusID,
					$ServiceStatusName,
					$ServiceStatusDisplay,
					$actions					
		);
		
	}  	
}
else if($OptionValue=='markets')
{
	$sql = "select m.*,w.WardName Ward from markets m inner join Wards w on m.WardID=w.WardID";//and (sh.ServiceHeaderID=96 or sh.ServiceHeaderID=95)
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);		
		
		$Services = '<a href="#"    onClick="loadpage(\'market_service_assignment.php?MarketID='.$MarketID.'&MarketName='.$MarketName.'\',\'content\')">Services</a>';
		$MarketName =  '<a href="#" onClick="loadpage(\'markets.php?edit=1&MarketID='.$MarketID.'\',\'content\')">'.$MarketName.'</a>';		
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'market_list.php?delete=1&MarketID='.$MarketID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		
		$actions='['.$Services.'|'.$DeleteBtn.']';

	
		$channel[] = array(	
					$MarketID,		
					$MarketName,
					$Ward,
					$actions
		);
		
	}  	
}
else if ($OptionValue=='marketservices')
{
	$sql = "SELECT ms.MarketServiceID,mk.MarketID,mk.MarketName,s.ServiceID,s.ServiceName
	  FROM [COUNTYREVENUE].[dbo].[MarketServices] ms
	  inner join markets mk on ms.MarketID=mk.MarketID
	  inner join Services s on ms.ServiceID=s.ServiceID";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
				
		$EditBtn = '<a href="#" onClick="loadpage(\'marketservice.php?edit=1&MarketServiceID='.$MarketServiceID.'\',\'content\')">Edit</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'marketservice_list.php?delete=1&MarketServiceID='.$MarketServiceID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';

	
		$channel[] = array(	
					$MarketName,		
					$ServiceName,
					$EditBtn,
					$DeleteBtn
		);
		
	} 
	
}
else if ($OptionValue=='Pages')
{
	$sql = "select p.*,mg.MenuGroupName,fa.RoleCenterName ApproverOne,sa.RoleCenterName ApproverTwo,ta.RoleCenterName ApproverThree 
			from Pages p 
			left join MenuGroups mg on p.MenuGroupID=mg.MenuGroupID
			left join RoleCenters fa on fa.RoleCenterID=p.ApproverOne
			left join RoleCenters sa on sa.RoleCenterID=p.ApproverTwo
			left join RoleCenters ta on ta.RoleCenterID=p.ApproverThree";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
				
		$PageName = '<a href="#" onClick="loadpage(\'pages.php?edit=1&PageID='.$PageID.'\',\'content\')">'.$PageName.'</a>';
		$DeleteBtn   ='';

	
		$channel[] = array(	
					$PageGroupID,		
					$PageName,
					$MenuGroupName,
					$ApproverOne,
					$ApproverTwo,
					$ApproverThree,
					$DeleteBtn
		);
		
	} 
	
}
else if ($OptionValue=='MenuGroups')
{
	$sql = "select * from MenuGroups";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
				
		$MenuGroupName = '<a href="#" onClick="loadpage(\'menu_group.php?edit=1&MenuGroupID='.$MenuGroupID.'\',\'content\')">'.$MenuGroupName.'</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'menu_groups_list.php?delete=1&MenuGroupID='.$MenuGroupID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';

	
		$channel[] = array(	
					$MenuGroupID,		
					$MenuGroupName,
					$DeleteBtn
		);
		
	} 
	
}
else if ($OptionValue=='RoleCenters')
{
	$sql = "select * from RoleCenters";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$CenterName=$RoleCenterName;		
		$RoleCenterName = '<a href="#" onClick="loadpage(\'role_center.php?edit=1&RoleCenterID='.$RoleCenterID.'\',\'content\')">'.$RoleCenterName.'</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'role_centers_list.php?delete=1&RoleCenterID='.$RoleCenterID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$RoleCenter   = '<a href="#" onClick="loadmypage(\'role_center_roles.php?RoleCenterName='.$CenterName.'&RoleCenterID='.$RoleCenterID.'\',\'content\',\'loader\',\'listpages\',\'\',\'RoleCenterRoles\','.$RoleCenterID.')">ROLE CENTER</a>';
		$ServiceApprovals   = '<a href="#" onClick="loadpage(\'role_center_approval.php?RoleCenterName='.$CenterName.'&RoleCenterID='.$RoleCenterID.'\',\'content\')">Approvals</a>';
		
		$channel[] = array(		
					$RoleCenterName,
					$DeleteBtn,
					$RoleCenter,
					$ServiceApprovals
		);
		
	} 
	
}
else if ($OptionValue=='Roles')
{
	$sql = "select * from Roles";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'userroles_list.php?delete=1&RoleCenterID='.$RoleCenterID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';	
		$channel[] = array(	
					$UserRoleID,		
					$UserID,
					$RoleCenterID,
					$DeleteBtn
		);
		
	} 
	
}
else if ($OptionValue=='ServiceTrees')
{
	$sql = "SELECT st.*,st2.Description Parent,s.ServiceName
			FROM [ServiceTrees] st
			left join Services s on st.ServiceID=s.ServiceID
			left join ServiceTrees st2 on st.ParentID=st2.ServiceTreeID order by st2.ParentID";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$fieldname='chkService';
		$IsItService='';
		if ($IsService == 1) {$IsItService = 'checked="checked"';}
		$EditBtn = '<a href="#" onClick="loadpage(\'service_tree.php?edit=1&ServiceTreeID='.$ServiceTreeID.'\',\'content\')">Edit</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'service_trees_list.php?delete=1&ServiceTreeID='.$ServiceTreeID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$IsService='<input id="'.$fieldname.'" name="'.$fieldname.'" type="checkbox" '. $IsItService.'/>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$Description,		
					$Parent,
					$IsService,
					$ServiceName,
					$actions
		);
		
	} 
	
}
else if ($OptionValue=='RoleCenterRoles')
{
	$sql = "select pg.*,mg.MenuGroupName FROM
		(select pages.*, isnull(RoleID,0)RoleID, isnull([View],0)[View], isnull([Edit],0)[Edit], isnull([Add],0)[Add], isnull([Delete],0) [Delete],roles.RoleCenterID
		from roles
		RIGHT JOIN pages ON pages.PageID = roles.PageID
		AND RoleCenterID =$exParam) pg JOIN MenuGroups MG ON pg.MenuGroupID=MG.MenuGroupID 
		order by mg.MenuGroupID,pg.PageName";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'userroles_list.php?delete=1&RoleCenterID='.$RoleCenterID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';

		$fieldname1 = 'V_'.$PageID.'_'.$RoleID;
		$fieldname2 = 'E_'.$PageID.'_'.$RoleID;
		$fieldname3 = 'A_'.$PageID.'_'.$RoleID;
		$fieldname4 = 'D_'.$PageID.'_'.$RoleID;
		
		$link .= "+'&".$fieldname1."='+this.form.".$fieldname1.'.checked';
		$link .= "+'&".$fieldname2."='+this.form.".$fieldname2.'.checked';
		$link .= "+'&".$fieldname3."='+this.form.".$fieldname3.'.checked';
		$link .= "+'&".$fieldname3."='+this.form.".$fieldname4.'./////////////////////.................................................';
		
		$Viewvalue = '';
		$Editvalue = '';
		$Addvalue = '';
		$Deletevalue = '';
		
		if ($View == 1) {$Viewvalue = 'checked="checked"';}
		if ($Edit == 1) {$Editvalue = 'checked="checked"';}
		if ($Delete == 1) {$Deletevalue = 'checked="checked"';}
		if ($Add == 1) {$Addvalue = 'checked="checked"';}

		$EditBtn = '<a href="#" onClick="loadmypage(\'role_center_rights.php?PageName='.$PageName.'&PageID='.$PageID.'&RoleCenterID='.$exParam.'\',\'content\',\'loader\',\'listpages\',\'\',\'RoleCenterRights\',\''.$exParam.':'.$PageID.'\')">Rights</a>';
		
		$channel[] = array(	
					$PageName,
					$MenuGroupName,
					'<input disabled="disabled" id="'.$fieldname1.'" name="'.$fieldname1.'" type="checkbox" '. $Viewvalue.'/>',
					'<input disabled="disabled" id="'.$fieldname2.'" name="'.$fieldname2.'" type="checkbox" '. $Editvalue.'/>',
					'<input disabled="disabled" id="'.$fieldname3.'" name="'.$fieldname3.'" type="checkbox" '. $Addvalue.'/>',
					'<input disabled="disabled" id="'.$fieldname4.'" name="'.$fieldname4.'" type="checkbox" '. $Deletevalue.'/>',
					$EditBtn,

		);
		
	} 
	
}
else if ($OptionValue=='RoleCenterRights')
{
	$details=explode(':',$exParam);
		
	$str3=explode(':',$details[0]);
	$RoleCenterID=$str3[1];

	$str3=explode(':',$details[1]);
	$PageID=$str3[1];


	$sql = "select * from Roles where RoleCenterID=$RoleCenterID and PageID=$PageID";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$ColumnBtn ='<a href="#" onClick="loadmypage(\'form_columns_list.php?FormID='.$FormID.'&FormName='.$FormName.'\',\'content\',\'loader\',\'listpages\',\'\',\'FormColumns\','.$FormID.')">Columns</a>';
		$SectionBtn ='<a href="#" onClick="loadmypage(\'form_sections_list.php?FormID='.$FormID.'&FormName='.$FormName.'\',\'content\',\'loader\',\'listpages\',\'\',\'FormSections\','.$FormID.')">Sections</a>';
		$EditBtn = '<a href="#" onClick="loadpage(\'form.php?edit=1&FormID='.$FormID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'forms_list.php?delete=1&FormID='.$FormID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$ColumnBtn.'|'.$SectionBtn.'|'.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$FormID,		
					$FormName,
					$actions
		);
		
	} 
	
}
else if ($OptionValue=='Forms')
{
	$sql = "select * from Forms";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$ColumnBtn ='<a href="#" onClick="loadmypage(\'form_columns_list.php?FormID='.$FormID.'&FormName='.$FormName.'\',\'content\',\'loader\',\'listpages\',\'\',\'FormColumns\','.$FormID.')">Columns</a>';
		$SectionBtn ='<a href="#" onClick="loadmypage(\'form_sections_list.php?FormID='.$FormID.'&FormName='.$FormName.'\',\'content\',\'loader\',\'listpages\',\'\',\'FormSections\','.$FormID.')">Sections</a>';
		$EditBtn = '<a href="#" onClick="loadpage(\'form.php?edit=1&FormID='.$FormID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'forms_list.php?delete=1&FormID='.$FormID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$ColumnBtn.'|'.$SectionBtn.'|'.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$FormID,		
					$FormName,
					$actions
		);
		
	} 
	
}
else if ($OptionValue=='FormSections')
{
	$sql = "select fs.*,f.FormName from FormSections fs inner join Forms f on fs.FormID=f.FormID where f.FormID=$exParam";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'form_sections.php?edit=1&FormSectionID='.$FormSectionID.'&FormName='.$FormName.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'form_sections_list.php?delete=1&FormSectionID='.$FormSectionID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delet7e</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$FormSectionName,
					$actions
		);
		
	}	
}
else if ($OptionValue=='FormColumns')
{
	$sql = "select fc.*,f.FormName,fs.FormSectionName,fc.ColumnSize,cdt.ColumnDataTypeName from FormColumns fc 
		inner join Forms f on fc.FormID=f.FormID
		left join ColumnDataType cdt on fc.ColumnDataTypeID=cdt.ColumnDataTypeID
		left join FormSections fs on fc.FormSectionID=fs.FormSectionID
		where f.FormID=$exParam";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'form_column.php?edit=1&FormColumnID='.$FormColumnID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'form_columns_list.php?delete=1&FormColumnID='.$FormColumnID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(
					$FormColumnID,
					$FormColumnName,
					$FormSectionName,
					$ColumnDataTypeName,
					$ColumnSize,
					$actions
		);
		
	} 
	
}
else if ($OptionValue=='RoleCenterApprovals')
{
	$sql = "select rca.RoleCenterApprovalID,isnull(rca.RoleCenterID,'')RoleCenterID,iif(rca.ServiceStatusID is null,'0','1') Accesses, 
			ss.ServiceStatusName, ss.ServiceStatusID 
			from RoleCenterApproval rca
			right join ServiceStatus ss 
			on ss.ServiceStatusID=rca.ServiceStatusID AND RoleCenterID = $exParam
			order by ss.ServiceStatusID,rca.RoleCenterID ";
			//echo $sql;
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		//$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'userroles_list.php?delete=1&RoleCenterID='.$RoleCenterID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';

		$fieldname1 = $ServiceStatusID;

		
		$link = $fieldname1."='+this.form.".$fieldname1.'.checked';

		
		$AccesValue = '';

		
		if ($Accesses == 1) {$AccesValue = 'checked="checked"';}

		
		$channel[] = array(			
					'<input id="'.$fieldname1.'" name="'.$fieldname1.'" type="checkbox" '. $AccesValue.'>'.$ServiceStatusName.'</input>'
		);
		
	} 
	
}
else if ($OptionValue=='AprovalSteps')
{
	$sql = "select sas.*,ss.ServiceStatusName,sc.CategoryName from ServiceApprovalSteps sas
	join ServiceStatus ss on sas.ServiceStatusID=ss.ServiceStatusID
	join ServiceCategory sc on sas.ServiceCategoryID=sc.ServiceCategoryID
	where sc.ServiceCategoryID=$exParam";
			//echo $sql;
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'approval_step.php?edit=1&ServiceApprovalStepID='.$ServiceApprovalStepID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'approval_steps_list.php?delete=1&ServiceApprovalStepID='.$ServiceApprovalStepID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$step,
					$ServiceStatusName,
					$actions
		);

		
	} 
	
}
else if ($OptionValue=='GLAccounts')
{
	$sql = "select * from GlAccounts";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'gl_accounts_setup.php?edit=1&GlAccountID='.$GlAccountID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'gl_accounts_list.php?delete=1&GlAccountID='.$GlAccountID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$GlAccountNo,
					$GlAccountName,
					$actions
		);

		
	} 
	
}
else if ($OptionValue=='LAIFOMS_LAND')
{
		
	$sql = "SELECT p.PlotNumber LRN,p.BlockLRNumber PlotNo,cs.CustomerSupplierName [Owner],cs.LocationDescription Location,cs.Town
			FROM PROPERTY p 
			join CustomerSupplier cs on p.CustomerSupplierID=cs.CustomerSupplierID
			join LandApplication la on la.LRN=p.BlockLRNumber and la.PlotNo=p.PlotNumber 
			where la.ServiceHeaderID=$exParam

			order by p.BlockLRNumber,p.PlotNumber";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'gl_accounts_setup.php?edit=1&GlAccountID='.$GlAccountID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'gl_accounts_list.php?delete=1&GlAccountID='.$GlAccountID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$Owner,
					$Town,
					$Location
		);		
	} 
	
}
else if ($OptionValue=='LAIFOMS_LAND_LIST')
{
	$upn='';
	$plotno='';
	$lrn='';
	$owner='';
	$authority='';
	$DBase='LAIFOMS-M';
	$filter='';
	$ssql='';
	if (strlen($exParam)>0)
	{

		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$upn=$str3[1];
		
		$str3=explode('=',$details[1]);
		$plotno=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$lrn=$str3[1]; 
		
		$str3=explode('=',$details[3]);
		$owner=$str3[1];

		$str3=explode('=',$details[4]);
		$authority=$str3[1];	
		
		if($authority==856){
			$DBase='LAIFOMS-W';			 
		}
		
		$sql = "SELECT top 20  p.LocalAuthorityID,p.UPN, p.BlockLRNumber LRN,p.PlotNumber PlotNo,cs.CustomerSupplierName [Owner],P.LandRates RatesPayable,p.CurrentBalance Balance
					FROM [".$DBase."].dbo.[Property]  p 
					join [".$DBase."].dbo.CustomerSupplier cs on p.CustomerSupplierID=cs.CustomerSupplierID ";
				
		$filter=" where 1=1 ";
			
		if(!$upn==0)
		{
			$filter.=" and p.UPN='$upn'";
		}
		
		if(!$owner=='')
		{
			$filter.=" and cs.CustomerSupplierName like '%$owner%'";
		}
		if(!$lrn=='')
		{
			$filter.=" and p.BlockLRNumber='$lrn'";
		}
		if(!$plotno=='')
		{
			$filter.=" and p.PlotNumber='$plotno'";
		}
		if(!$FirmID=='')
		{
			$filter.=" and p.MarketCentreID='$FirmID'";
		} 
	
		$sql.=$filter;
		//$ssql=$sql.$filter;
	}else
	{
		//$sql = "select top 10  p.LocalAuthorityID,p.laifomsUPN UPN,p.LRN,p.PlotNo,p.LaifomsOwner [Owner],RatesPayable,p.Balance Balance from land p";
	}
	
	//$sql = "select  p.laifomsUPN UPN,p.LRN,p.PlotNo,p.LaifomsOwner [Owner],RatesPayable,p.Balance Balance from land p";
	
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		//$ImportBtn = '<a href="#" onClick="loadpage(\'gl_accounts_setup.php?edit=1&GlAccountID='.$GlAccountID.'\',\'content\')">Import</a>';
		$ImportBtn  = '<a href="#" onClick="deleteConfirm2(\'Import this Property?\',\'land_from_laifoms.php?import=1&upn='.$UPN.'&Authority='.$LocalAuthorityID.'&lrn='.$LRN.'&plotno='.$PlotNo.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Import</a>';
		//$StmtBtn  = '<a href="statement.php?lrn='.$LRN.'&plotno='.$PlotNo.'&upn='.$UPN.'&authority='.$LocalAuthorityID.'" target="_blank">Statement</a>';
		$actions='['.$ImportBtn.']';
	
		$channel[] = array(
					$UPN,
					$LRN,
					$PlotNo,
					$RatesPayable,
					$Balance,
					$Owner,
					$actions
					
		);		
	}
}
else if ($OptionValue=='IMPORTED_LAND_LIST')
{
	$upn='';
	$plotno='';
	$lrn='';
	$owner='';
	$FirmID='';
	if (strlen($exParam)>0)
	{
		//$sql = "select top 100 p.LocalAuthorityID,p.laifomsUPN,p.upn,p.LRN,p.PlotNo,p.LaifomsOwner [Owner],RatesPayable,p.Balance Balance from land p ";
		 
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$upn=$str3[1];
		
		$str3=explode('=',$details[1]);
		$plotno=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$lrn=$str3[1]; 
		
		$str3=explode('=',$details[3]);
		$owner=$str3[1];
		
		$str3=explode('=',$details[4]);
		$FirmID=$str3[1];

		$str3=explode('=',$details[5]);
		$idno=$str3[1];

		$sql = "select top 100 p.LocalAuthorityID,isnull(p.laifomsUPN,upn) laifomsUPN,p.upn,p.LRN,p.PlotNo,p.LaifomsOwner [Owner],RatesPayable,(select Balance from dbo.fnlastplotrecord(p.upn)) Balance ,fn.FirmName,p.AreaInHa 
				from land p 
				join LandFirms fn on p.FirmID=fn.FirmID
				left join Customer c on c.CustomerID=p.CustomerID";
		
		$filter=" where 1=1 ";
		
		if(!$upn==0)
		{
			if (strpos($upn, '-') !== false) 
			{		  		
		  		$filter.=" and p.laifomsUPN='$upn' ";
			}else{				
				$filter.=" and p.upn='$upn' ";
			}			
		}
		
		if(!$owner=='')
		{
			$ownername=explode(' ', $owner);
			for($i=0;$i<count($ownername);$i++){
				$filter.=" and laifomsOwner like '%$ownername[$i]%'";
			}
			
		}
		if(!$lrn=='')
		{
			$filter.=" and p.lrn='$lrn'";
		}
		if(!$plotno=='')
		{
			$filter.=" and p.plotno='$plotno'";
		}
		if(!$FirmID=='')
		{
			$filter.=" and p.FirmID='$FirmID'";
		}
		if(!$idno=='')
		{
			$filter.=" and (c.idno='$idno' or c.PIN='$idno')";
		}
		$sql.=$filter;
	}else
	{
		$sql = "select top 100  p.LocalAuthorityID,isnull(p.laifomsUPN,upn) laifomsUPN,p.upn,p.LRN,p.PlotNo,p.LaifomsOwner [Owner],RatesPayable,p.Balance Balance ,fn.FirmName,p.AreaInHa 
		from land p join LandFirms fn on isnull(p.FirmID,0)=fn.FirmID";
	} 
	
	//$sql = "select  p.laifomsUPN UPN,p.LRN,p.PlotNo,p.LaifomsOwner [Owner],RatesPayable,p.Balance Balance from land p";
	
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		
		$PageID=62;
		$myRights=getrights($db,$CurrentUser,$PageID);
		if ($myRights)
		{
			$View=$myRights['View'];
			$Edit=$myRights['Edit'];
			$Add=$myRights['Add'];
			$Delete=$myRights['Delete'];
		}
		if($myRights['View']==1)
		{
			$StmtBtn  = '<a href="statement.php?lrn='.$LRN.'&plotno='.$PlotNo.'&upn='.$upn.'&authority='.$LocalAuthorityID.'" target="_blank">Statement</a>';
		}else{
			$StmtBtn='';
		}

		$PageID=60;
		$myRights=getrights($db,$CurrentUser,$PageID);
		if ($myRights)
		{
			$View=$myRights['View'];
			$Edit=$myRights['Edit'];
			$Add=$myRights['Add'];
			$Delete=$myRights['Delete'];
		}
		if($myRights['View']==1)
		{
			$DNoticeBtn  = '<BR>|<a href="DemandNotices.php?create2=1&ID='.$upn.'&PerWhat=3&LocalAuthorityID='.$LocalAuthorityID.'" target="_blank">Demand Notice</a>';
		}else{
			$DNoticeBtn='';
		}

		$EditBtn = '<BR>|<a href="#" onClick="loadpage(\'plots.php?edit=1&upn='.$upn.'\',\'content\')">Edit Plot</a>';
		$ViewMap  = '|<a href="https://gis.uasingishu.go.ke/geoportal/uasingishu/gisquery.php?subject=plot&key='.$upn.'&mode=map&zoom=100%" target="_blank" ">View Map</a>';

		
		$PageID=63;
		$myRights=getrights($db,$CurrentUser,$PageID);
		if ($myRights)
		{
			$View=$myRights['View'];
			$Edit=$myRights['Edit'];
			$Add=$myRights['Add'];
			$Delete=$myRights['Delete'];
		}

		if($myRights['Add']==1)
		{
			$BPABtn = '<BR>|<a href="#" onClick="loadmypage(\'miscellaneous_bpa.php?upn='.$upn.'\',\'content\')">Building. Plan</a>';
		}else
		{
			$BPABtn='';
		}
		if($myRights['Add']==1)
		{
			$FencingBtn = '<BR>|<a href="#" onClick="loadmypage(\'fencing_application.php?upn='.$upn.'\',\'content\')">Other Applications</a>';
		}else
		{
			$FencingBtn='';
		}

		$actions='['.$StmtBtn.$EditBtn.$DNoticeBtn.$BPABtn.$FencingBtn.$ViewMap.']';
	
		$channel[] = array(
					$upn,
					$laifomsUPN,
					$LRN,
					$PlotNo,
					$AreaInHa,
					$FirmName,
					$RatesPayable,
					$Balance,
					$Owner,
					$actions	
		);		
	}
}
else if ($OptionValue=='Land')
{
	$upn='';
	$plotno='';
	$lrn='';
	$owner='';
	$FirmID='';
	if (strlen($exParam)>0)
	{
		//$sql = "select top 100 p.LocalAuthorityID,p.laifomsUPN,p.upn,p.LRN,p.PlotNo,p.LaifomsOwner [Owner],RatesPayable,p.Balance Balance from land p ";
		 
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$upn=$str3[1];
		
		$str3=explode('=',$details[1]);
		$plotno=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$lrn=$str3[1]; 
		
		$str3=explode('=',$details[3]);
		$owner=$str3[1];
		
		$str3=explode('=',$details[4]);
		$FirmID=$str3[1];

		$str3=explode('=',$details[5]);
		$idno=$str3[1];
		
		$filter=" where 1=1 ";
		
		if(!$upn==0)
		{
			if (strpos($upn, '-') !== false) 
			{		  		
		  		$filter.=" and l.laifomsUPN='$upn' ";
			}else{				
				$filter.=" and l.upn='$upn' ";
			}			
		}
		
		if(!$owner=='')
		{
			$ownername=explode(' ', $owner);
			for($i=0;$i<count($ownername);$i++){
				$filter.=" and laifomsOwner like '%$ownername[$i]%'";
			}
			
		}
		if(!$lrn=='')
		{
			$filter.=" and l.lrn='$lrn'";
		}
		if(!$plotno=='')
		{
			$filter.=" and l.plotno='$plotno'";
		}
		if(!$FirmID=='')
		{
			$filter.=" and l.FirmID='$FirmID'";
		}
		if(!$idno=='')
		{
			$filter.=" and (c.idno='$idno' or c.PIN='$idno')";
		}
		$sql.=$filter;
	}else
	{
		
	} 
	

	$sql = "select distinct top 100 isnull(sh.ServiceHeaderID,0) ApplicationID, l.LocalAuthorityID,isnull(l.laifomsUPN,upn) laifomsUPN,l.upn,l.LRN,l.PlotNo,l.LaifomsOwner [Owner],RatesPayable,(select Balance from dbo.fnlastplotrecord(l.upn)) Balance ,fn.FirmName,l.AreaInHa,isnull(f.ServiceHeaderType,'') ApplicationType,sh.ServiceStatusID 
				 
			FROM  land l 
			join LandFirms fn on l.FirmID=fn.FirmID
			left JOIN LandApplication la  on l.LRN=la.LRN and l.PlotNo=la.PlotNo 
			left JOIN ServiceHeader AS sh on la.ServiceHeaderID=sh.ServiceHeaderID and (select [Value] from fnFormData(sh.ServiceHeaderID) 
			where FormColumnID=13270)=l.LocalAuthorityID
			left JOIN dbo.Services AS s ON sh.ServiceID = s.ServiceID 
			left JOIN dbo.Customer AS c ON sh.CustomerID = c.CustomerID 
			left JOIN dbo.ServiceStatus ss ON sh.ServiceStatusID=ss.ServiceStatusID 
			left JOIN DBO.ServiceCategory sc on s.ServiceCategoryID=sc.ServiceCategoryID 
			left JOIN dbo.Forms f on sh.FormID=f.FormID 
			 ".$filter."
			";

	
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'gl_accounts_setup.php?edit=1&GlAccountID='.$GlAccountID.'\',\'content\')">Edit</a>';
		
		$StmtBtn  = '<a href="statement.php?lrn='.$LRN.'&plotno='.$PlotNo.'&upn='.$upn.'&authority='.$LocalAuthorityID.'" target="_blank">Statement</a>';


		$DNoticeBtn  = '<a href="DemandNotices.php?create2=1&ID='.$upn.'&PerWhat=3&LocalAuthorityID='.$LocalAuthorityID.'" target="_blank">Demand Notice</a>';

		$EditBtn = '<a href="#" onClick="loadpage(\'plots.php?edit=1&upn='.$upn.'\',\'content\')">Edit Plot</a>';
		if(!$ApplicationID==''){
			$app_type=$ApplicationType;
			$BillBtn =  '|<a href="#" onClick="loadoptionalpage('.$ApplicationID.','.$app_type.','.$ServiceStatusID.',\'content\',\'loader\',\'listpages\',\'\',\''.$ApplicationID.'\')">Send Bill</a>';
		}else{
			$BillBtn='';	
		}
		

		$actions='['.$StmtBtn.'|'.$EditBtn.'|'.$DNoticeBtn.$BillBtn.']';
	
		$channel[] = array(
					$upn,
					$laifomsUPN,
					$LRN,
					$PlotNo,
					$AreaInHa,
					$FirmName,
					$RatesPayable,
					$Balance,
					$Owner,
					$actions	
		);		
	}
}
else if ($OptionValue=='ChildrenPlots')
{
	
	$sql = "select p.LocalAuthorityID,isnull(p.laifomsUPN,upn) laifomsUPN,p.upn,p.LRN,p.PlotNo,p.LaifomsOwner [Owner],RatesPayable,p.Balance Balance 
	from land p where MotherUPN='$exParam'";	
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'gl_accounts_setup.php?edit=1&GlAccountID='.$GlAccountID.'\',\'content\')">Edit</a>';
		//$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'gl_accounts_list.php?delete=1&GlAccountID='.$GlAccountID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$StmtBtn  = '<a href="statement.php?lrn='.$LRN.'&plotno='.$PlotNo.'&upn='.$upn.'&authority='.$LocalAuthorityID.'" target="_blank">Statement</a>';
		$EditBtn = '<a href="#" onClick="loadpage(\'plots.php?edit=1&upn='.$upn.'\',\'content\')">Edit Plot</a>';
		//$actions='['.$StmtBtn.'|'.$EditBtn.']';
	
		$channel[] = array(
					$laifomsUPN,
					$LRN,
					$PlotNo,
					$RatesPayable,
					$Balance,
					$Owner
					
		);		
	}
}
else if ($OptionValue=='NEW_LAND_LIST')
{
		
	$sql = "select l.LRN,l.PlotNo,c.CustomerName Owner,L.Balance+L.PenaltyBalance Balance from Land l
			join LandOwner lo on lo.UPN=l.UPN 
			join Customer c on lo.CustomerID=c.CustomerID ORDER BY l.LRN,l.PlotNo";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'gl_accounts_setup.php?edit=1&GlAccountID='.$GlAccountID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'gl_accounts_list.php?delete=1&GlAccountID='.$GlAccountID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$LRN,
					$PlotNo,
					$Balance,
					$Owner
		);		
	}
}
else if ($OptionValue=='LandStatement')
{
		
	$sql="select lr.DateReceived,lr.DocumentNo,lr.Description,lr.Amount,lr.InvoiceNo,lr.Balance,lr.Penalty,lr.GroundRent,lr.OtherCharges,lr.Principal,lr.PenaltyBalance
	from LAND l join LANDRECEIPTS lr on lr.upn=l.upn
	where l.upn='$exParam' 
	order by lr.DateReceived,lr.LandReceiptsId";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'gl_accounts_setup.php?edit=1&GlAccountID='.$GlAccountID.'\',\'content\')">Edit</a>';

		$actions='['.$EditBtn.']';
	
		$channel[] = array(	
					$DateReceived,
					$DocumentNo,
					$Description,
					$Amount,
					$Principal,
					$Penalty,
					$PenaltyBalance,
					$GroundRent,
					$OtherCharges,
					$Balance,
		);		
	}
}
else if ($OptionValue=='LAIFOMS_HOUSE')
{
		
	$sql = "SELECT h.HouseNumber,h.EstateID,tn.CurrentTenant [Tenant],tn.MonthlyRent,tn.Balance
			FROM Houses h 
			join Tenancy tn on tn.HouseNumber=h.HouseNumber and tn.EstateID=h.EstateID
			
			join HouseApplication ha on ha.HouseNumber=h.HouseNumber and ha.EstateID=h.EstateID
			where ha.ServiceHeaderID=$exParam

			order by h.EstateID,h.HouseNumber";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'gl_accounts_setup.php?edit=1&GlAccountID='.$GlAccountID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'gl_accounts_list.php?delete=1&GlAccountID='.$GlAccountID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$MonthlyRent,
					$Balance,
					$Tenant					
		);		
	} 
	
}
else if ($OptionValue=='LAIFOMS_HOUSE_LIST')
{
	$HouseNumber='';
	$EstateID='';
	$CurrentTenant='';
	
	
	$sql = "SELECT top 50 h.EstateID,h.HouseNumber,es.EstateName,tn.MonthlyRent,vw.Balance,isnull(tn.CurrentTenant,c.CustomerName) [Tenant]
	FROM Houses h 
	join vwHousePayments vw on vw.HouseNumber=h.HouseNumber
	join Tenancy tn on tn.UHN=h.UHN	
	join Estates es on h.EstateID=es.EstateID
	left join Customer c on tn.CustomerID=c.CustomerID";
		
	$orderBy=" order by h.EstateID,h.HouseNumber";
	
	$filter =" where 1=1 ";

	if (strlen($exParam)>0)
	{				
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$EstateID=$str3[1];
		
		$str3=explode('=',$details[1]);
		$HouseNumber=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$CurrentTenant=$str3[1]; 
				
		
		if(!$CurrentTenant=='')
		{
			$filter .= " and tn.CurrentTenant like '%$CurrentTenant%'";
		}		
		if(!$EstateID=='')
		{
			$filter .= " and h.EstateID='$EstateID'";
		}		
		if(!$HouseNumber=='')
		{
			$filter .= " and h.HouseNumber like '%$HouseNumber%'";
		}  
	}
	
	$sql.=$filter.$orderBy;
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$StmtBtn  = '<a href="housestatement.php?EstateID='.$EstateID.'&HouseNumber='.$HouseNumber.'" target="_blank">Statement</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$EstateName,
					$HouseNumber,
					$MonthlyRent,				
					$Balance,
					$Tenant,
					$StmtBtn
		);		
	} 	
}
else if ($OptionValue=='Permits')
{
	$filter=" where  year(p.ExpiryDate)>=2018 and p.Balance<=0";
	$orderBy=" order By p.IssueDate Desc";

	if($exParam!=='')
	{	
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$fromDate=$str3[1];
		
		$str3=explode('=',$details[1]);
		$toDate=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$PermitNo=$str3[1]; 
		
		$str3=explode('=',$details[3]);
		$ServiceHeaderID=$str3[1];

		$str3=explode('=',$details[4]);
		$CustomerName=$str3[1];

		$str3=explode('=',$details[5]);
		$InvoiceHeaderID=$str3[1];
	}else{
		
		$filter=" and DATEDIFF(day,p.IssueDate,getdate())<4 ";
	}


	$wards='';
	$Subcounties='';
	$locationcondition='';
	$role='None';
	//check whether the person is a clerk or Officer
	$locsql="select iif (exists(select 1 from ClerkWard where UserID=$CurrentUser and status=1),'Clerk',
			iif (exists(select 1 from ApproverSetup where UserID=$CurrentUser and status=1),'Officer','None')) Role";

	$result=sqlsrv_query($db,$locsql);
	while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) 
	{
		$role=$row['Role'];
	}

	if($role=='Clerk')
	{
		$sql="select WardID From ClerkWard where UserID=$CurrentUser and Status=1";

		$result=sqlsrv_query($db,$sql);
		$i=0;

		while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
			if ($i==0){
				$wards='('.$row['WardID'];
			}else{
				$wards.=','.$row['WardID'];
			}
			$i+=1;
		}

		$wards.=')';

		$locationcondition=" and (select value from fnFormData(sh.ServiceHeaderID) WHERE FormColumnID=11204) in $wards ";

	}else if ($role=='Officer'){
		$sql="select SubCountyID From ApproverSetup where UserID=$CurrentUser and Status=1";

		$result=sqlsrv_query($db,$sql);
		$i=0;
		while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
			if ($i==0){
				$subcounties='('.$row['SubCountyID'];
			}else{
				$subcounties.=','.$row['SubCountyID'];
			}
			$i+=1;
		}

		$subcounties.=')';

		$locationcondition=" and (select value from fnFormData(sh.ServiceHeaderID) WHERE FormColumnID=11203) in $subcounties ";
	}
	
	if($fromDate!=''){
		$filter.=" and p.IssueDate >= '$fromDate'";
	}

	if($toDate!=''){
		$filter.=" and p.IssueDate <= '$toDate'";
	}
	if($PermitNo!=''){
		$filter.=" and p.PermitNo like '%$PermitNo%'";
	}
	if($ServiceHeaderID!=''){
		$filter.=" and p.ServiceHeaderID = '$ServiceHeaderID'";
	}
	if($InvoiceHeaderID!=''){
		$filter.=" and p.InvoiceHeaderID = '$InvoiceHeaderID'";
	}
	if($CustomerName!=''){
		$filter.=" and c.CustomerName like '%$CustomerName%'";
	}
			
	$sql = "set dateformat dmy 
	select distinct top 100  p.PermitNo,p.IssueDate,p.ExpiryDate,P.InvoiceAmount PermitCost,c.CustomerName,
	p.ServiceHeaderID,P.InvoiceHeaderID,
	(select top 1 value from fnFormData(p.ServiceHeaderID) where FormColumnID=5) BusinessActivity,
	(select w.WardName from fnFormData(p.ServiceHeaderID) fn 
	join Wards w on fn.Value=w.WardID
	where fn.formcolumnid=11204) WardName,iif(year(p.IssueDate)=year(getdate()),1,0) isCurrent		 
	from vwPermits p
	join Services s on p.ServiceID=s.ServiceID		
	join Customer c on p.CustomerID=c.CustomerID $filter ".$orderBy;

			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$IssueDate=date_create($IssueDate);
		$IssueDate=date_format($IssueDate,"d/m/Y");

		$PageID=32;
		$myRights=getrights($db,$CurrentUser,$PageID);
		if ($myRights)
		{
			$View=$myRights['View'];
			$Edit=$myRights['Edit'];
			$Add=$myRights['Add'];
			$Delete=$myRights['Delete'];
		}

		$myRights['Edit']=1;

		if($myRights['Edit']==1)
		{
			$ResendBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Resend?\',\'permits_list.php?resend=1&PermitNo='.$PermitNo.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Resend</a>';
			
			$ResendBtn='|'.$ResendBtn;
		}else{
			$ResendBtn='';
		}
		
		if($myRights['Delete']==1){
			$RevokeBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Revoke the Permit?\',\'permits_list.php?revoke=1&permitno='.$PermitNo.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Revoke</a>';
			$RevokeBtn='|'.$RevokeBtn;
		}else{
			$RevokeBtn='';
		}
		
		$ViewBtn = '<a href="#" onClick="loadpage(\'view_pdf.php?edit=1&report='.$PermitNo.'\',\'content\')">View</a>';
		$ViewMap  = '|<a href="https://gis.uasingishu.go.ke/geoportal/uasingishu/gisquery.php?subject=business&key='.$PermitNo.'&mode=map&zoom=12.5" target="_blank" ">View Map</a>';

		//$ViewBtn  = '<a href="reports.php?rptType=permit&ServiceHeaderID='.$ServiceHeaderID.'" target="_blank">View</a>'; 

		if($isCurrent==0)
		{
			$RenewBtn = '|<a href="#" onClick="loadmypage(\'permit_renewal.php?ApplicationID='.$ServiceHeaderID.'&PermitNo='.$PermitNo.'\',\'content\',\'loader\',\'listpages\',\'\',\'\',\''.$ServiceHeaderID.'\')">Renew</a>';
		}else{
			$RenewBtn='';
		}
		
		$actions='['.$ViewBtn.$ResendBtn.$RevokeBtn.$RenewBtn.$ViewMap.']';
		
		$ExpiryDate=date_format(date_create($ExpiryDate),"d/m/Y");
		$channel[] = array(	
					$PermitNo,
					$ServiceHeaderID,
					$CustomerName,
					$WardName,
					$BusinessActivity,
					$PermitCost,
					$IssueDate,					
					$ExpiryDate,
					$actions
		);		
	} 
	
}
else if ($OptionValue=='NEW_HOUS,E_TENANTS')
{
		
	$sql = "select e.EstateName,h.HouseNumber,c.CustomerName Tenant,t.Balance from Tenancy t 
			join Houses h on t.UHN=h.UHN
			join Customer c on t.CustomerID=c.CustomerID
			join Estates e on h.EstateID=e.EstateID	
			order by h.EstateID,h.HouseNumber";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'gl_accounts_setup.php?edit=1&GlAccountID='.$GlAccountID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'gl_accounts_list.php?delete=1&GlAccountID='.$GlAccountID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$EstateName,
					$HouseNumber,
					$Tenant,
					$Balance
		);		
	} 
	
}
else if ($OptionValue=='LAIFOMS_PERMIT')
{
		
	$sql = "select SBPNumber PermitNo,AmountPaid,DateIssued,BusinessName 
			from [LAIFOMS-M].dbo.IssuedSingleBusinessPermits 
			where CalenderYear='2016' 
			order by CalenderYear desc";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'gl_accounts_setup.php?edit=1&GlAccountID='.$GlAccountID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'gl_accounts_list.php?delete=1&GlAccountID='.$GlAccountID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$PermitNo,
					$BusinessName,
					$DateIssued,
					$AmountPaid
		);		
	} 
	
}
else if ($OptionValue=='FinancialYear')
{
	$sql = "select * from financialyear";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'financialyear.php?edit=1&financialyearID='.$FinancialYearID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'financialyear_list.php?delete=1&FinancialYearID='.$FinancialYearID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.','.$FinancialYearID.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(	
					$FinancialYearName,
					$TargetCollection,
					$actions
		);

		
	} 
	
}
else if ($OptionValue=='Businesses')
{
	$sql = "select b.*,w.WardName from Businesses b inner join Wards w on b.WardID=w.WardID";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		
		$EditBtn = '<a href="#" onClick="loadpage(\'business.php?edit=1&BusinessID='.$BusinessID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'businesses_list.php?delete=1&BusinessID='.$BusinessID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array(					
					$WardName,
					$BusinessName,					
					$BusinessActivity,
					$BusinessOwner,
					$IDNO,
					$PhoneNo,
					$SBP_NO,
					$actions	
		);

		
	} 
	
}
else if ($OptionValue=='TestTable')
{
/*	$sql = "select sc.SubCountyName,sum(tt.amount)Amount from SubCounty sc
	join Wards wd on wd.SubCountyID=sc.SubCountyID
	join Markets mk on mk.WardID=wd.WardID
	join TestTable tt on tt.MArketID=mk.MarketID
	
	group by sc.SubCountyName";*/
	
	$sql="set dateformat dmy exec spPeriodicCollection";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		$channel[] = array(	
					$date,
					(double)$amount
		);		
	} 
	
}
else if ($OptionValue=='Target')
{
	//$sql="SELECT sum(amount) Amount,1000000000 [Target] FROM [COUNTYREVENUE].[dbo].[vwTarget]";
	$sql="select sum(Total)Amount,10 Target from vwreceiptsperstream";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		$channel[] = array(	
					(double)$Amount,
					(double)$Target
		);		
	} 	
}
else if ($OptionValue=='TodaysCollection')
{
	$sql="exec spTodaysCollection";
	//$sql="select sum(Total)Amount,1000000000 Target from vwreceiptsperstream";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		$channel[] = array(	
					(double)$Amount,
					(double)number_format($Target,2)
		);		
	} 	
}
else if ($OptionValue=='TodaysPosCollection')
{
	$sql="exec spPosCollectionToday";	
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		$channel[] = array(	
					(double)$Amount,
					(double)$Target
		);		
	} 	
}
else if ($OptionValue=='TodaysCollection_f')
{
	$sql="exec spTodaysCollection_d";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		$channel[] = array(	
					$ServiceGroupName,
					(double)$Amount
		);		
	} 	
}
else if ($OptionValue=='ServiceRanking')
{
	$sql="select ServiceGroupName [Group],Total Amount from vwReceiptsPerStream order by Total desc";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		$channel[] = array(	
					$Group,
					(double)$Amount
		);		
	} 	
}
else if ($OptionValue=='')
{
	$filter='';
	if($exParam!=='')
	{	
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$CustomerName=$str3[1];
		
		$str3=explode('=',$details[1]);
		$IDNO=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$Email=$str3[1]; 
	}else{

		$filter=" and DATEDIFF(day,c.CreatedDate,getdate())<300 ";
	}
	

	$filter=" where c.CustomerID=$exParam";
	
	if($CustomerName!='')
	{
		$filter.=" and c.CustomerName like '%$CustomerName%'";
	}

	if($IDNO!='')
	{
		$filter.=" and c.IDNO = '$IDNO'";
	}
	if($Email!=''){
		$filter.=" and c.Email like '%$Email%'";
	}
	

	$sql="select  ud.DeviceSerialNo,c.CustomerID,c.Surname+' '+c.OtherNames Customer,ud.CreatedDate DateIssued,lm.LastReading,lm.Balance  
		from UserDevices ud
		inner join Customer c on ud.CustomerID=c.CustomerID
		cross apply fnLastMeterRecord(ud.DeviceSerialNo)lm $filter";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		
		$Meter = '<a href="#" onClick="loadpage(\'user_devices.php?CustomerID='.$CustomerID.'&CustomerName='.$CustomerName.'\',\'content\')">Meter</a>';
		$ReadMeter = '<a href="#" onClick="loadpage(\'meter_reading.php?CustomerID='.$CustomerID.'&CustomerName='.$CustomerName.'\',\'content\')">Meter</a>';
		$CustomerName = '<a href="#" onClick="loadpage(\'customer.php?edit=1&CustomerID='.$CustomerID.'\',\'content\')">'.$CustomerName.'</a>';

		$channel[] = array(	
					$DeviceSerialNo
		);		
	} 	
}
else if ($OptionValue=='Customers')
{
	$filter='';
	if($exParam!=='')
	{	
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$CustomerName=$str3[1];
		
		$str3=explode('=',$details[1]);
		$IDNO=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$Email=$str3[1]; 
	}else{
		// $fromDate=date('d/m/Y');
		// $toDate=date('d/m/Y');

		$filter=" and DATEDIFF(day,c.CreatedDate,getdate())<300 ";
	}


	$PageID=30;
	$myRights=getrights($db,$CurrentUser,$PageID);
	if ($myRights)
	{
		$View=$myRights['View'];
		$Edit=$myRights['Edit'];
		$Add=$myRights['Add'];
		$Delete=$myRights['Delete'];
	}
	

	$filter=" where 1=1 ";
	
	if($CustomerName!=''){
		$filter.=" and c.CustomerName like '%$CustomerName%'";
	}

	if($IDNO!=''){
		$filter.=" and c.IDNO = '$IDNO'";
	}
	if($Email!=''){
		$filter.=" and c.Email like '%$Email%'";
	}
	

	$sql="select distinct top 100 c.CustomerID,c.CustomerName,c.Mobile1,Email,IDNO , count(sh.ServiceID) Services
			from Customer c
			left join ServiceHeader sh on sh.CustomerID=c.CustomerID
			left join InvoiceLines il on il.ServiceHeaderID=sh.ServiceHeaderID 	
			$filter 			
			group by c.CustomerID,c.CustomerName,c.Mobile1,Email,IDNO 
			order by count(sh.ServiceID)";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		$CustomerName = '<a href="#" onClick="loadmypage(\'customer_services.php?Customer='.$CustomerName.'\',\'content\',\'loader\',\'listpages\',\'\',\'CustomerServices\',\''.$CustomerID.'\')">'.$CustomerName.'</a>';
		$ResetBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure Reset the Password?\',\'customer_services_list.php?reset=1&CustomerID='.$CustomerID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Reset Account</a>';

		if($myRights['Edit']!==1)
		{
			$ResetBtn='';
		}
		

		$channel[] = array(	
					$CustomerID,
					$CustomerName,
					$Mobile1,
					$Email,
					$IDNO,
					$Services,
					$ResetBtn
		);		
	} 	
}
else if ($OptionValue=='CustomerWater')
{
	$filter='';
	if($exParam!=='')
	{	
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$CustomerName=$str3[1];
		
		$str3=explode('=',$details[1]);
		$IDNO=$str3[1];		
		
		$str3=explode('=',$details[2]);
		$Email=$str3[1]; 
	}else{

		$filter=" and DATEDIFF(day,c.CreatedDate,getdate())<300 ";
	}
	

	$filter=" where 1=1 ";
	
	if($CustomerName!=''){
		$filter.=" and c.CustomerName like '%$CustomerName%'";
	}

	if($IDNO!='')
	{
		$filter.=" and c.IDNO = '$IDNO'";
	}
	if($Email!=''){
		$filter.=" and c.Email like '%$Email%'";
	}
	

	$sql="select distinct top 100 c.CustomerID,c.CustomerName,c.Mobile1,Email,IDNO,isnull(ct.CustomerTypeName,'Individual') [CustomerType] 
	from Customer c
	left join ServiceHeader sh on sh.CustomerID=c.CustomerID
	left join CustomerType ct on ct.CustomerTypeID=c.CustomerTypeID $filter";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		
		// $Meter = '<a href="#" onClick="loadpage(\'customer_meters_list.php?CustomerID='.$CustomerID.'&CustomerName='.$CustomerName.'\',\'content\')">Meter</a>';

		$Meter = '<a href="#" onClick="loadmypage(\'customer_meters_list.php?CustomerID='.$CustomerID.'&CustomerName='.$CustomerName.'\',\'content\',\'loader\',\'listpages\',\'\',\'CustomerMeters\',\''.$CustomerID.'\')">Meter</a>';

		$ReadMeter = '<a href="#" onClick="loadpage(\'meter_reading.php?CustomerID='.$CustomerID.'&CustomerName='.$CustomerName.'\',\'content\')">Meter</a>';
		$CustomerName = '<a href="#" onClick="loadpage(\'customer.php?edit=1&CustomerID='.$CustomerID.'\',\'content\')">'.$CustomerName.'</a>';

		$channel[] = array(	
					$CustomerID,
					$CustomerName,
					$Mobile1,
					$Email,
					$IDNO,
					$CustomerType,
					$Meter
		);		
	} 	
}
else if ($OptionValue=='SearchCustomers')
{
	$sql="select distinct c.CustomerID,c.CustomerName, IDNO,Email
			from Customer c  
			order by CustomerName";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);			
		$channel[] = array(	
					$CustomerID,
					$CustomerName,
					$IDNO,
					$Email
		);		
	} 	
}
else if ($OptionValue=='CustomerServices')
{
	$sql="select distinct sh.ServiceHeaderID,s.ServiceName from 
		services s
		join ServiceHeader sh on sh.ServiceID=s.ServiceID
		join InvoiceLines il on il.ServiceHeaderID=sh.ServiceHeaderID
		where il.InvoiceLineID not in (select InvoiceLineID from ConsolidateInvoice)
		and sh.CustomerID=$exParam and sh.ServiceStatusID=7";
			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);		
		$channel[] = array(	
					$ServiceHeaderID,
					$ServiceName
		);		
	} 	
}
else if ($OptionValue=='BusinessTypes')
{
	$sql="select * from BusinessType";			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);		
		
		$EditBtn = '<a href="#" onClick="loadpage(\'businesstype.php?edit=1&BusinessTypeID='.$BusinessTypeID.'\',\'content\')">Edit</a>';
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'businesstypes_list.php?delete=1&BusinessTypeID='.$BusinessTypeID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';
		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
		
		$channel[] = array(	
					$BusinessTypeName,
					$Notes,
					$actions
		);		
	} 	
}
else if ($OptionValue=='ServicePlus')
{
	$sql="select sp.ServicePlusID,sp.ServiceID AppliedServiceID,sp.Amount,s2.ServiceID,s2.ServiceName 
	from ServicePlus sp, services s, services s2
	where sp.ServiceID=s.ServiceID and sp.service_add=s2.ServiceID and sp.ServiceID=$exParam";			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);		
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'serviceplus_list.php?delete=1&ServicePlusID='.$ServicePlusID.'&A_ServiceID='.$AppliedServiceID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$AppliedServiceID.'\')">Delete</a>';
		$EditBtn = '<a href="#" onClick="loadmypage(\'serviceplus.php?edit=1&ServicePlusID='.$ServicePlusID.'&A_ServiceID='.$AppliedServiceID.'\',\'content\')">Edit</a>';

		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
		
		$channel[] = array(	
					$ServiceID,
					$ServiceName,
					$Amount,
					$actions
		);		
	} 	
}
else if ($OptionValue=='Miscellaneous')
{
	$fromDate=date('d/m/Y');	
	$toDate=date('d/m/Y');
	
	if(strlen($exParam)>9)
	{	
		$details=explode(':',$exParam);
		
		$str3=explode('=',$details[0]);
		$fromDate=$str3[1];
		
		$str3=explode('=',$details[1]);
		$toDate=$str3[1];
		
		$str3=explode('=',$details[2]);
		$Customer=$str3[1];		
		
	}
	//$exParam=strlen($exParam);
	$filter=" Where 1=1";
	
	if($fromDate!='')
	{
		$filter.=" and sh.CreatedDate >= '$fromDate'";
	}
	if($toDate!=''){
		$filter.=" and sh.CreatedDate <= '$toDate'";
	}
	if($Customer!=''){
		
	}

	
	
	$sql="set dateformat dmy select sh.ServiceHeaderID,m.[Description] Description,M.CustomerName,Sh.CreatedDate,sum(il.Amount) Amount 
			from miscellaneous m 
			join ServiceHeader sh on m.ServiceHeaderID=sh.ServiceHeaderID
			join InvoiceLines il on m.ServiceHeaderID=il.ServiceHeaderID ".$filter." 
			group by sh.ServiceHeaderID,m.[Description] ,M.CustomerName,Sh.CreatedDate"	;			
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);		
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'miscellaneous_list.php?delete=1&ApplicationID='.$ServiceHeaderID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$AppliedServiceID.'\')">Delete</a>';
		//'<a href="#" onClick="loadmypage(\'serviceplus_list.php?A_ServiceID='.$ServiceID.'\',\'content\',\'loader\',\'listpages\',\'\',\'ServicePlus\',\''.$ServiceID.'\')">FEES</a>';
		$actions='['.$DeleteBtn.']';
		
		$channel[] = array(	
					$ServiceHeaderID,
					$CustomerName,
					$Description,
					$Amount,
					$CreatedDate,
					$actions
		);		
	} 	
}
else if ($OptionValue=='MiscellaneousGroups')
{
	$sql = "select * from MiscellaneousGroups";
	
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);		
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'miscellaneous_group_list.php?delete=1&ApplicationID='.$ServiceHeaderID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$AppliedServiceID.'\')">Delete</a>';
		//'<a href="#" onClick="loadmypage(\'serviceplus_list.php?A_ServiceID='.$ServiceID.'\',\'content\',\'loader\',\'listpages\',\'\',\'ServicePlus\',\''.$ServiceID.'\')">FEES</a>';
		$actions='['.$DeleteBtn.']';
		
		$channel[] = array(	
					$MiscellaneousGroupID,
					$MiscellaneousGroupName,
					$CreatedDate,
					$actions
		);		
	} 	
}
else if ($OptionValue=='MiscellaneousServices')
{
	$sql = "select ms.*,s.ServiceName from MiscellaneousServices ms inner join Services s on ms.ServiceID=s.ServiceID where ms.MiscellaneousGroupID=$exParam";
	
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);		
		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'miscellaneous_services_list.php?delete=1&ApplicationID='.$ServiceHeaderID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$AppliedServiceID.'\')">Delete</a>';
		//'<a href="#" onClick="loadmypage(\'serviceplus_list.php?A_ServiceID='.$ServiceID.'\',\'content\',\'loader\',\'listpages\',\'\',\'ServicePlus\',\''.$ServiceID.'\')">FEES</a>';
		$actions='['.$DeleteBtn.']';
		
		$channel[] = array(	
					$MiscellaneousGroupID,
					$ServiceName,
					$CreatedDate,
					$actions
		);		
	} 	
}
else if ($OptionValue == 'ActiveUsers')
{
	$sql = "select ag.FirstName+' '+ag.MiddleName+' '+ag.LastName names,r.RoleCenterName,sm.Session_Start LoggedInTime 
			from Users u 
			join SessionMgr sm on u.ActiveSessionID=sm.ActiveSessionID
			join Agents ag on u.AgentID=ag.AgentID
			join RoleCenters r on u.RoleCenterID=r.RoleCenterID
			where u.LoginStatus=1";
			
	//echo $sql;
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);		
		$channel[] = array(	
					 $names
					,$RoleCenterName
					,$LoggedInTime					
					);
	}
}
else if ($OptionValue == 'AuditTrail')
{
	$sql = "select distinct saa.createddate,saa.ServiceHeaderID,ss.ServiceStatusID,ss.ServiceStatusName,saa.CreatedBy,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName UserNames 
	from ServiceApprovalActions saa 
	join agents ag on ag.AgentID=saa.CreatedBy
	join users u on u.AgentID=ag.AgentID
	join ServiceStatus ss on saa.ServiceStatusID=saa.ServiceStatusID 
	where ss.ServiceStatusID not in (0,1)
	order by saa.CreatedBy,saa.ServiceHeaderID,ss.ServiceStatusID";
			
	//echo $sql;
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);		
		$channel[] = array(
					 $names
					,$RoleCenterName					
					);
	}
}
else if ($OptionValue == 'HouseBills')
{
	$sql = "select hr.HouseNumber,es.EstateID,es.EstateName,tn.CustomerID,c.CustomerName,hr.[Description],hr.DocumentNo,hr.Amount,hr.uhn,ha.ServiceHeaderID 
			from housereceipts hr 
			join tenancy tn on hr.EstateID=tn.EstateID and hr.HouseNumber=tn.HouseNumber
			join Customer c on tn.CustomerID=c.CustomerID
			join Estates es on tn.EstateID=es.EstateID
			join HouseApplication ha on ha.HouseNumber=tn.HouseNumber and ha.EstateID=tn.EstateID
			where hr.InvoiceNo is null and Description='Monthly Rent' and hr.billsent=0 and right(DocumentNo,4)>2015";
			
	//echo $sql;
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);	
	
		$BillBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Bill?\',\'house_bills.php?bill=1&CustomerID='.$CustomerID.'&HouseNumber='.$HouseNumber.'&Amount='.$Amount.'&BillNumber='.$DocumentNo.'&uhn='.$uhn.'&EstateID='.$EstateID.'&ApplicationID='.$ServiceHeaderID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Sent Bill</a>';
		
		$channel[] = array(
					 $HouseNumber
					,$EstateName
					,$CustomerName
					,$DocumentNo
					,$Amount
					,$BillBtn
					);
	}
}
else if($OptionValue=='LandFarms')
{
	$sql = "select lf.FirmID,la.LocalAuthorityName,lf.FirmName,la.LocalAuthorityID,(select count(*) from land where FirmID=lf.FirmID) Plots 
			from LandFirms lf 
			join LocalAuthority la on lf.LocalAuthorityID=la.LocalAuthorityID
			order by la.LocalAuthorityID,lf.FirmName";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		

		$FarmName = '<a href="#" onClick="loadpage(\'landfarm.php?edit=1&FarmID='.$FirmID.'&FarmName='.$FirmName.'&LocalAuthorityID='.$LocalAuthorityID.'\',\'content\')">'.$FirmName.'</a>';			
		
		// $DemanNoteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Do Deman Notices for the Farm?\',\'DemandNotices.php?create2=1&ID='.$FirmID.'&PerWhat=3&LocalAuthorityID='.$FirmID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Create</a>';

		$DemanNoteBtn  = '<a href="DemandNotices.php?create2=1&ID='.$FirmID.'&PerWhat=1" target="_blank">Demand Notice</a>';
		
		$DownloadBtn='<a href="DemandNotices.php?download=1&FirmID='.$FirmID.'&FirmName='.$FirmName.'" target="_blank">Download</a>';

		$actions='['.$DemanNoteBtn.'|'.$DownloadBtn.']';
		$channel[] = array(			
					$FirmID,
					$FarmName,
					$LocalAuthorityName,
					$Plots,
					$actions
		);
		
	}  	
}else if($OptionValue=='Estates')
{
	$sql = "select EstateID,EstateName from Estates";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$Houses = '<a href="#" onClick="loadmypage(\'houses_list.php?EstateID='.$EstateID.'&EstateName='.$EstateName.'\',\'content\',\'loader\',\'listpages\',\'\',\'Houses\','.$EstateID.')">View Houses</a>';

		$EstateName =  '<a href="#" onClick="loadpage(\'estate.php?edit=1&EstateID='.$EstateID.'&EstateName='.$EstateName.'\',\'content\')">'.$EstateName.'</a>';		

		$actions='';
		$channel[] = array(			
					$EstateID,
					$EstateName,
					$Houses
		);
		
	}
}  	
else if($OptionValue=='Houses')
{
	$sql = "select h.HouseID,h.HouseNumber,tn.MonthlyRent from Houses h 
	join Tenancy tn on tn.HouseNumber=h.HouseNumber 
	where h.EstateID='$exParam'";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
				$actions='';
		$channel[] = array(			
					$HouseID,
					$HouseNumber,
					$MonthlyRent
		);
		
	}  	
}else if($OptionValue=='WaiverPeriods')
{
	$sql = "select PeriodID,StartDate,EndDate,MemoNo,WaiverPercentage,Status,iif(Status=1,'Active','Closed') Status from waiverperiods";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
		$EditBtn = '<a href="#" onClick="loadpage(\'waiverperiod.php?edit=1&PeriodID='.$PeriodID.'\',\'content\')">Edit</a>';			
		$DeleteBtn = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'waiverperiods_list.php?delete=1&PeriodID='.$PeriodID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';

		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
		$StartDate=date("d/m/Y",strtotime($StartDate));
		$EndDate=date("d/m/Y",strtotime($EndDate));
		$channel[] = array(			
					$StartDate,
					$EndDate,
					$MemoNo,
					$WaiverPercentage,
					$Status,
					$actions
		);
		
	}  	
}
else if($OptionValue=='ApprovalRequests')
{
	$fromDate;
	$toDate;
	$filter;
	$UserID='';
	if($exParam!=='')
	{	
		$details=explode(':',$exParam);
		$role_center=$details[0];
		$UserID=$details[1];
		
	}
	
	//$role_center=$exParam;
	
	
	$sql = "select distinct  ae.*,r.ReceiptID,R.Amount,aa.Description,p.PageName,
			p.ApproverOne,p.ApproverTwo,p.ApproverThree,
			ag.FirstName+' '+ag.MiddleName+' '+ag.LastName Sender,
			iif(p.ApproverOne=$role_center,1,iif(p.approverTwo=$role_center,2,3)) MyStage
			from approvalentry ae
			left join receipts r on ae.DocumentNo=r.ReferenceNumber		
			join Pages p on p.PageID=ae.PageID
			join Agents ag on ae.SenderID=ag.AgentID
			join ApprovalActions aa on aa.ActionID=ae.action
			where ae.ApprovalStatus=0 
			and (ae.ApprovalStage+1=iif(p.ApproverOne=$role_center,1,0) 
			or ae.ApprovalStage+1=iif(p.ApproverTwo=$role_center,2,0)
			or ae.ApprovalStage+1=iif(p.Approverthree=$role_center,3,0)) 
				
			or (ae.ApprovalStatus=2 	
			and ae.ApproverID 
			in(select ur.UserID  from UserRoles ur 
			join RoleCenters rc on ur.RoleCenterID=rc.RoleCenterID where rc.BeyondLimitApproverID=$role_center  )) 
			order by ae.CreatedDate Desc";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);		
		$Approve= '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Approve the Request?\',\'approval_requests.php?approve=1&DocumentNo='.$DocumentNo.'&PageID='.$PageID.'&Action='.$Action.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$role_center.':'.$CurrentUser.'\')">Approve</a>';

		$view  = '<a href="#" onClick="loadpage(\'receipt_reversal.php?PageID=25&Action=4&ReceiptID='.$ReceiptID.'&refno='.$DocumentNo.'&InvoiceHeaderID='.$RefNumber.'&Amount='.$Amount.'&Reason='.$Comments.'\',\'content\')">View</a>';

		$Decline= '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Decline the Request?\',\'approval_requests.php?decline=1&DocumentNo='.$DocumentNo.'&PageID='.$PageID.'&Action='.$Action.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$role_center.':'.$CurrentUser.'\')">Decline</a>';

		$ViewBtn  = '<a href="reports.php?rptType=Receipt&InvoiceHeaderID='.$RefNumber.'&ReceiptID='.$ReceiptID.'" target="_blank">View</a>';

		$DocumentNo  = '<a href="reports.php?rptType=Receipt&InvoiceHeaderID='.$RefNumber.'&ReceiptID='.$ReceiptID.'" target="_blank">'.$DocumentNo.'</a>';

		$actions='['.$view.'|'.$Approve.'|'.$Decline.']';
		
		$channel[] = array(
				$CreatedDate,	
				$Sender,			
				$Description,	
				$DocumentNo,							
				$Comments,
				$TransactionType,
				$actions		
		);			
	}  	
	//print_r($channel);
}else if ($OptionValue == 'Assets')
{
	$sql = "select a.AssetID, isnull(a.RegistrationNumber,'N/A')RegistrationNumber,a.AssetName,a.DepreciationRate,a.AcquisitionDate,a.AcquisitionCost,[at].AssetTypeName
			from Assets a 
			join AssetTypes [at] on a.AssetTypeID=[at].AssetTypeID";

	//echo $sql;
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);		
		$CreatedDate = date('Y-m-d',strtotime($CreatedDate));
		$DepreciationAmount=(double)$DepreciationRate*(double)$AcquisitionCost;
		$BookValue=$AcquisitionCost-$DepreciationAmount;
		
		$EditBtn = '<a href="#" onClick="loadmypage(\'asset.php?edit=1&AssetID='.$AssetID.'\',\'content\')">Edit</a>';
		$DeleteBtn='<a href="#" onClick="deleteConfirm2(\'Are you sure you wish to delete this record\',\'assets_list.php?delete=1&AssetID='.$AssetID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$myRights['Delete'].'\')">Delete</a>';
		
		$channel[] = array(
					 $RegistrationNumber
					,$AssetName
					,$AssetTypeName	
					,$AcquisitionCost
					,$DepreciationAmount
					,$BookValue				
					,$EditBtn
					,$DeleteBtn
					);
	}
}else if ($OptionValue == 'MatatuRoutes')
{
	$sql = "select *
			from MatatuRoutes";

	//echo $sql;
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);		
		$CreatedDate = date('Y-m-d',strtotime($CreatedDate));
		
		$EditBtn = '<a href="#" onClick="loadpage(\'matatu_route.php?edit=1&RouteID='.$RouteID.'\',\'content\')">Edit</a>';
		$DeleteBtn='<a href="#" onClick="deleteConfirm2(\'Are you sure you wish to delete this record\',\'matatu_routes_list.php?delete=1&RouteID='.$RouteID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$myRights['Delete'].'\')">Delete</a>';
		
		$channel[] = array(
					 $RouteID
					,$RouteName								
					,$EditBtn
					,$DeleteBtn
					);
	}
}else if ($OptionValue == 'BusParks')
{
	$sql = "select *
			from BusParks";

	//echo $sql;
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);		
		$CreatedDate = date('Y-m-d',strtotime($CreatedDate));
		
		$EditBtn = '<a href="#" onClick="loadpage(\'bus_park.php?edit=1&ParkID='.$ParkID.'\',\'content\')">Edit</a>';
		$DeleteBtn='<a href="#" onClick="deleteConfirm2(\'Are you sure you wish to delete this record\',\'bus_park_list.php?delete=1&ParkID='.$ParkID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$myRights['Delete'].'\')">Delete</a>';
		
		$channel[] = array(
					 $ParkID
					,$ParkName								
					,$EditBtn
					,$DeleteBtn
					);
	}
}else if($OptionValue=='RouteCharges')
{
	$sql = "select rc.*,r.RouteName 
			from RouteCharges rc join MatatuRoutes r on rc.RouteID=r.RouteID
			order by rc.RouteID,rc.[FromCapacity]";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
				
		extract($row);
		
		$EditBtn ='<a href="#" onClick="loadpage(\'route_charge.php?edit=1&ChargeID='.$ChargeID.'\',\'content\')">Edit</a>';

		$DeleteBtn  = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'route_charges_list.php?delete=1&ChargeID='.$ChargeID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\',\''.$ChargeID.'\')">Delete</a>';

		$actions='['.$EditBtn.'|'.$DeleteBtn.']';
	
		$channel[] = array
		(
			$RouteName,
			$FromCapacity,
			$ToCapacity,			
			$Amount,
			$actions					
		);
	}  	
}
else if ($OptionValue == 'ApproversList')
{
	$sql = "select ag.FirstName+' '+ag.MiddleName+' '+ag.LastName Names,ag.IDNo,u.Email,u.UserID, sc.SubCountyName,aps.ID,u.PFNo
		from ApproverSetup aps
		join users u on aps.UserID=u.AgentID
		left join SubCounty sc on aps.SubCountyID=sc.SubCountyID
		join Agents ag on u.AgentID=ag.AgentID
					
		where aps.Status=1 order by u.UserFullNames ";
	//echo $sql;	
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);	

		$EditBtn = '<a href="#" onClick="loadpage(\'Approver_subcounty.php?edit=1&SetupID='.$ID.'\',\'content\')">Edit</a>';
		$RemoveBtn = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Remove?\',\'ApproverSetup.php?remove=1&SetupID='.$ID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Remove</a>';

		$actions='['.$EditBtn.'|'.$RemoveBtn.']';		
		
		$channel[] = array(
					$ID
					,$Names
					,$IDNo
					,$Email
					,$SubCountyName
					,$actions
					);
	}
}
else if ($OptionValue=='ApplicationTypes')
{
	$sql = "select * from ApplicationTypes";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
				
		$ApplicationTypeName = '<a href="#" onClick="loadpage(\'application_type.php?edit=1&ApplicationTypeID='.$ApplicationTypeID.'\',\'content\')">'.$ApplicationTypeName.'</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'application_types_list.php?delete=1&ApplicationTypeID='.$ApplicationTypeID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';

	
		$channel[] = array(	
					$ApplicationTypeID,		
					$ApplicationTypeName,
					$DeleteBtn
		);
		
	} 
	
}
else if ($OptionValue=='FencingCharges')
{
	$sql = "select fs.SetupID,ft.ApplicationTypeID FencingTypeID,ft.ApplicationTypeName FencingTypeName,fs.Amount,fs.Minimum,s.ServiceID,s.ServiceName,
	iif(fs.fixed=0,'No','Yes') Fixed
	from FencingSetup fs 
	join ApplicationTypes ft on fs.FencingTypeID=ft.ApplicationTypeID    
	join services s on fs.ServiceID=s.ServiceID";
	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);
				
		$FencingTypeName = '<a href="#" onClick="loadpage(\'fencing_charge.php?edit=1&SetupID='.$SetupID.'\',\'content\')">'.$FencingTypeName .'</a>';
		$DeleteBtn   = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'fencing_charges_list.php?delete=1&SetupID='.$SetupID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Delete</a>';

	
		$channel[] = array(	
					$FencingTypeName,		
					$ServiceName,
					$Fixed,
					$Amount,
					$Minimum,
					$DeleteBtn
		);
		
	} 
	
}
else if ($OptionValue == 'ClerkWards')
{
	$sql = "select ag.FirstName+' '+MiddleName+ ' '+LastName Names,ag.IDNO,u.Email,u.UserID, sc.WardName,aps.ID
			from ClerkWard aps
			join users u on aps.UserID=u.AgentID			
			left join Wards sc on aps.WardID=sc.WardID
			join Agents ag on u.AgentID=ag.AgentID 
			where aps.Status=1
			order by ag.FirstName+' '+MiddleName+ ' '+LastName";
	//echo $sql;	
	$result = sqlsrv_query($db, $sql);
	while($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
	{
		extract($myrow);	

		$EditBtn = '<a href="#" onClick="loadpage(\'clerk_ward.php?edit=1&RecordID='.$ID.'\',\'content\')">Edit</a>';

		$RemoveBtn = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Remove?\',\'clerk_wards_list.php?remove=1&RecordID='.$ID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\')">Remove</a>';

		$actions='['.$EditBtn.'|'.$RemoveBtn.']';	
		
		$channel[] = array(
					$ID 
					,$Names
					,$IDNO
					,$Email
					,$WardName
					,$actions
					);
	}
}
else if($OptionValue=='Complaints')
{
	$sql = "select c.ComplaintID,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName ReportedBy,
			c.Description,c.RefNumber,c.CreatedDate,iif(c.Status=0,'Pending','Resolved') Status,c.StatusComment 
			from complaints c
			join agents ag on ag.AgentID=c.createdby";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		
		$ActionBtn = '<a href="#" onClick="loadpage(\'ComplaintLog.php?ComplaintID='.$ComplaintID.'\',\'content\')">Action</a>';
		$LogsBtn = '<a href="#" onClick="loadmypage(\'complaintlog_list.php?ComplaintID='.$ComplaintID.'\',\'content\',\'loader\',\'listpages\',\'\',\'ComplaintLogs\','.$ComplaintID.')">View Logs</a>';
		$actions='['.$ActionBtn.'|'.$LogsBtn.']';
	
		$channel[] = array(
			$CreatedDate,			
			$ReportedBy,
			$Description,
			$RefNumber,			
			$Status,			
			$StatusComment,
			$actions					
		);
		
	}  	
}
else if($OptionValue=='ComplaintLogs')
{
	$sql = "select c.ComplaintID,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName ActionBy,
				cl.Comment,cl.CreatedDate 
				from ComplaintLogs cl 
				join complaints c on cl.ComplaintID=c.ComplaintID
				join agents ag on ag.AgentID=cl.createdby
				where c.ComplaintID='$exParam'
				order by cl.LogID";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);	
		$channel[] = array(
			$CreatedDate,			
			$ActionBy,
			$Comment					
		);
		
	}  	
}
else if($OptionValue=='RequiredDocuments')
{
	$sql = "
		select rd.RequirementID, rd.DocumentID,dc.DocumentName,sc.ServiceCategoryID,sc.CategoryName 
		from RequiredDocuments rd 
		join ServiceCategory sc on rd.ServiceCategoryID=sc.ServiceCategoryID
		join Documents dc on rd.DocumentID=dc.DocumentID
		where sc.ServiceCategoryID='$exParam' order by RequirementID";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);

		$DeleteBtn = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'required_documents_list.php?delete=1&RequirementID='.$RequirementID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\','.$ServiceCategoryID.')">Delete</a>';

		$channel[] = array(
			$DocumentName,			
			$DeleteBtn					
		);		
	}  	
}
else if($OptionValue=='ChecklistParameterCategories')
{
	$sql = "select * from ChecklistParameterCategories";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);

		$EditBtn = '<a href="#" onClick="loadpage(\'checklistparametercategory.php?edit=1&ParameterCategoryID='.$ParameterCategoryID.'\',\'content\')">Edit</a>';
		$DeleteBtn = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'checklistparametercategoty_list.php?delete=1&ParameterCategoryID='.$ParameterCategoryID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\','.$ParameterCategoryID.')">Delete</a>';

		$actions='['.$EditBtn.'|'.$DeleteBtn.']';

		$channel[] = array(
			$ParameterCategoryID,	
			$ParameterCategoryName,	
			$ParameterCategoryDescription,		
			$actions					
		);		
	}  	
}
else if($OptionValue=='ChecklistParameters')
{
	$sql = "select cp.ParameterID,ParameterName,ParameterCategoryName,ParameterScore 
			from ChecklistParameters cp
			join ChecklistParameterCategories cpc on cp.ParameterCategoryID=cpc.ParameterCategoryID";

	$result = sqlsrv_query($db, $sql);	
	while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
	{
		extract($row);

		$EditBtn = '<a href="#" onClick="loadpage(\'checklistparameter.php?edit=1&ParameterID='.$ParameterID.'\',\'content\')">Edit</a>';
		$DeleteBtn = '<a href="#" onClick="deleteConfirm2(\'Are you sure you want to Delete?\',\'checklistparameters_list.php?delete=1&ParameterID='.$ParameterID.'\',\'content\',\'loader\',\'listpages\',\'\',\''.$OptionValue.'\','.$ParameterID.')">Delete</a>';

		$actions='['.$EditBtn.'|'.$DeleteBtn.']';

		$channel[] = array(
			$ParameterID,	
			$ParameterName,	
			$ParameterCategoryName,	
			$ParameterScore,		
			$actions					
		);		
	}  	
}

$channels = array($channel);
$rss = (object) array('aaData'=>$channel);
//$rss = (object) array('jData'=>$channel);
$json = json_encode($rss);
echo $json;
?>