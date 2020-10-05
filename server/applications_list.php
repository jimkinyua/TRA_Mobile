<?php
require 'DB_PARAMS/connect.php';
// require_once('utilities.php');
// require_once('GlobalFunctions.php');
// require_once('county_details.php');
// require_once('smsgateway.php');

if (!isset($_SESSION))
{
	session_start();
}
$msg ='';
$CustomerEmail='';
$Sawa=false;
$Remark='';
$UserID = $_SESSION['UserID'];
$ServiceHeaderType='';
$mail=false;
$InvoiceNo='';
$SubSystemID=1;
//$DateLine=date('d/m/Y',strtotime('2018-03-31'));

$DateLine=$cosmasRow['SBPDateline'];
$DateLine=date('d/m/Y',strtotime($DateLine));


//checkSession($db,$UserID);

if (isset($_REQUEST['save']) && $_REQUEST['NextStatus']!='')
{
	
	$ApplicationID=$_REQUEST['ApplicationID'];
	$CustomerID=$_REQUEST['CustomerID'];
	$CurrentStatus=$_REQUEST['CurrentStatus'];
	$NextStatus=$_REQUEST['NextStatus'];
	$Notes=$_REQUEST['Notes'];
	$NextStatusID=$NextStatus;
	$InvoiceNo=$_REQUEST['InvoiceNo'];

	$ConservancyServiceID=1696;
	
	if ($NextStatus=='')
	{
		break;		
	}
	
	$sql="select f.serviceheadertype from Forms f 
	  join ServiceHeader sh on sh.FormID=f.formid 
	  where sh.ServiceHeaderID='$ApplicationID'";
	$s_result=sqlsrv_query($db,$sql);
	//echo $s_sql;
	if ($s_result)
	{					
		while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
		{			
			$ServiceHeaderType=$row['serviceheadertype'];
		}
	}

		
	  
	
	$s_sql="select * from Customer where CustomerID=$CustomerID";
	$s_result=sqlsrv_query($db,$s_sql);
	//echo $s_sql;
	if ($s_result)
	{					
		while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
		{			
			$CustomerEmail=$row['Email'];
			$CustomerName=$row['CustomerName'];
		}
	}
	
	$s_sql="select ServiceStatusID from ServiceStatus where ServiceStatusID='$NextStatus'";
	$s_result=sqlsrv_query($db,$s_sql);

	if ($s_result){
		while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
		{			
			$NextStatusID=$row['ServiceStatusID'];
		}
	}


	$initQry="Insert into ServiceApprovalActions(ServiceHeaderID,ServiceStatusID,NextServiceStatusID,Notes,CreatedBy) 
	Values ($ApplicationID,$CurrentStatus,$NextStatusID,'$Notes','$UserID')";	
	//echo 'insert actions';
	$s_result = sqlsrv_query($db, $initQry);
	
	if ($s_result) 
	{	
		if ($NextStatusID=='')
		{
			//echo 'Step Checking..';
			exit;	
		}		
		
		if($NextStatusID==5)
		{		
			$InvoiceHeader="";
			$ServiceAmount=0;
			$InvoiceAmount=0;
			$InvoiceDate= date("d/m/Y");
			$Chargeable=0;
			$Sawa=true;
			$msg='';
			//Check whether the service is chargable and the chargeamount
			
			//get the subsystem
			
			$sql="select fd.ServiceHeaderID,fd.FormColumnID,fd.Value,fc.FormColumnName from 
				FormData fd join FormColumns fc on fd.FormColumnID=fc.FormColumnID
				where fc.FormColumnID=12237 and fd.ServiceHeaderID=$ApplicationID";
				//echo ($sql);
			$res=sqlsrv_query($db,$sql);
			while($row=sqlsrv_fetch_array($res,SQLSRV_FETCH_ASSOC))
			{
				$SubSystemID=$row['Value'];
			}
		
			
			$s_sql="select sc.amount,s.chargeable,sh.ServiceID,sh.CreatedDate
			 from servicecharges sc 
			 inner join services s on sc.serviceid=s.serviceid 
			 inner join serviceheader sh on sh.serviceid=s.serviceid
			 join FinancialYear fy on sc.FinancialYearId=sc.FinancialYearId
			where sh.ServiceHeaderID=$ApplicationID and fy.isCurrentYear=1 and sc.SubSystemID='$SubSystemID'";

			//echo $s_sql;

			//echo '<br><br>';
			
			$s_result=sqlsrv_query($db,$s_sql);
			
			if ($s_result)
			{
					
				while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
				{						
					$ServiceID=$row['ServiceID'];
					$Chargeable=$row['chargeable'];						
					$ApplicationDate=$row['CreatedDate'];//date('d/m/Y',strtotime($date));
					$ApplicationDate=date('d/m/Y',strtotime($ApplicationDate));
				}
			}else
			{
				DisplayErrors();
			}		
			
			if ($Chargeable==0 && $ServiceHeaderType!=1)
			{
				$msg='The Service is set not to have charges, hence cannot be invoiced';
				$Sawa=true;
			}
			else
			{
			
				//echo 'Service Cost Level Ok';
				$sql1="select * from fnServiceCost($ServiceID,$SubSystemID)";

				$rs=sqlsrv_query($db,$sql1);
				if ($rs)
				{
					while($row=sqlsrv_fetch_array($rs,SQLSRV_FETCH_ASSOC))
					{									
						$ServiceAmount=$row['Amount'];
						$PermitCost=$ServiceAmount;
					}	
				}
				
				if ($ServiceAmount<=0)
				{
					$msg="The cost of the service is not set, the process therefore aborts";
				}else
				{

					if(sqlsrv_begin_transaction($db)===false)
					{
						$msg=sqlsrv_errors();
						$Sawa=false;
					}				
					
					$s_sql="set dateformat dmy insert into InvoiceHeader (InvoiceDate,InvoiceNo,CustomerID,CreatedBy) Values('$InvoiceDate','$InvoiceNo',$CustomerID,'$UserID') SELECT SCOPE_IDENTITY() AS ID";
					$s_result1 = sqlsrv_query($db, $s_sql);
					//echo 'invoiceheader done';		
					if ($s_result1)
					{
						$InvoiceHeader=lastid($s_result1);				
										
						//insert into invoiceLines
			
						$s_sql="set dateformat dmy insert into InvoiceLines (InvoiceHeaderID,ServiceHeaderID,ServiceID,Description,Amount,CreatedBy) 
								Values($InvoiceHeader,$ApplicationID,$ServiceID,' Year $PermitYear',$ServiceAmount,'$UserID')";						
						$s_result2 = sqlsrv_query($db, $s_sql);
						//echo 'invoiceheader lines done';	
						$loopOkey=true;
						$PermitCost=$ServiceAmount;
						$InvoiceAmount+=$ServiceAmount;
						if ($s_result2)
						{								
							//check whether there are carrier
						    $sql="select s.ServiceID,s.ServiceName, Amount 
						            from ServiceCharges sc
						            join services s on sc.ServiceID=s.serviceid                                 
						            join FinancialYear fy on sc.FinancialYearId=fy.FinancialYearID                                      
						            and fy.isCurrentYear=1
						            and sc.SubSystemId=$SubSystemID
						            and sc.serviceid=281";

							//echo $sql;

							$s_result = sqlsrv_query($db, $sql);
							while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
							{									
								$ServiceAmount=$row["Amount"];
								$ServiceID=$row['ServiceID'];
								$InvoiceAmount+=$ServiceAmount;
								
								$s_sql="set dateformat dmy insert into InvoiceLines (InvoiceHeaderID,ServiceHeaderID,ServiceID,Amount,CreatedBy) 
										Values($InvoiceHeader,$ApplicationID,$ServiceID,$ServiceAmount,$UserID)";
								$result3 = sqlsrv_query($db, $s_sql);
								if (!$result3)
								{
									///DisplayErrors();
									$loopOkey=false;
									break;
								}else{
									//echo 'Hamna Shida';
								}									
							}
							if($loopOkey==true)
							{
								$mail=true;																	
							}

							//Conservancy Fees
							$sql1="select * from fnConservancyCost($PermitCost,$SubSystemID)";

							$rs=sqlsrv_query($db,$sql1);
							if ($rs)
							{
								while($row=sqlsrv_fetch_array($rs,SQLSRV_FETCH_ASSOC))
								{									
									$ServiceAmount=$row["Amount"];										
									$InvoiceAmount+=$ServiceAmount;
									$ServiceID=1696;
									
									$s_sql="set dateformat dmy insert into InvoiceLines (InvoiceHeaderID,ServiceHeaderID,ServiceID,Amount,CreatedBy) 
											Values($InvoiceHeader,$ApplicationID,$ServiceID,$ServiceAmount,$UserID)";
									$result4 = sqlsrv_query($db, $s_sql);
									if (!$result4)
									{
										DisplayErrors();
										$loopOkey=false;
										break;
									}else{
										//echo 'Conservancy Fees Done';
									}
								}	
							}

						}else
						{
							
							DisplayErrors().'<BR>';
							$Sawa=false;
							
						}

						//Application Charges
					    $sql="select distinct s1.ServiceID,s1.ServiceName ,sc.Amount 
					            from ApplicationCharges sc 
					            join ServiceHeader sh on sh.serviceheaderid=sc.serviceheaderid 
					            join Services s1 on sc.ServiceID=s1.ServiceID 
					            where sh.ServiceHeaderID=$ApplicationID";

					    //echo $sql;

					    $result=sqlsrv_query($db,$sql);
					    while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
					    {
					        $ServiceAmount=$row["Amount"];
							$ServiceID=$row['ServiceID'];
							$InvoiceAmount+=$ServiceAmount;
							
							$s_sql="set dateformat dmy insert into InvoiceLines (InvoiceHeaderID,ServiceHeaderID,ServiceID,Amount,CreatedBy) 
									Values($InvoiceHeader,$ApplicationID,$ServiceID,$ServiceAmount,$UserID)";
							$result3 = sqlsrv_query($db, $s_sql);
							if (!$result3)
							{
								DisplayErrors();
								$loopOkey=false;
								break;
							}else{
								//echo 'Hamna Shida';
							}
					    }
						
						//penalties
						
						
						if(strtotime($ApplicationDate)>strtotime($DateLine) and $BusinessIsOld==1){
							$ServiceID='283';
							$ServiceAmount=.50*(double)$PermitCost;
							$InvoiceAmount+=$ServiceAmount;
							$s_sql="set dateformat dmy insert into InvoiceLines (InvoiceHeaderID,ServiceHeaderID,ServiceID,Amount,CreatedBy) 
											Values($InvoiceHeader,$ApplicationID,$ServiceID,$ServiceAmount,$UserID)";
							//echo $s_sql;
							$rslt=sqlsrv_query($db,$s_sql);
							if(!$rslt){
								$Sawa=false;
							}
						}
						
												
					}
					
					$s_sql="set dateformat dmy update InvoiceHeader set Amount='$InvoiceAmount' where InvoiceHeaderID='$InvoiceHeader'";
					//echo $s_sql;
					$s_result3=sqlsrv_query($db,$s_sql);
					if(!$s_result3){
						$Sawa=false;
					}
					
					if($s_result1 && $s_result2 && $s_result3 && $loopOkey==true && $mail==true)
					{	
						$rst=SaveTransaction($db,$UserID," Created Invoice Number ".$InvoiceHeader);				
						sqlsrv_commit($db);
						$msg="Invoice Created Successfull";


						$sql="select c.Telephone1 from Customer c
						join serviceheader sh on sh.CustomerID=c.CustomerID
						 where sh.ServiceHeaderID=$ApplicationID";

						 $MobileNo='';

						 $result=sqlsrv_query($db,$sql);
						 while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
						 	$MobileNo=$row['Telephone1'];
						 }
						
						$name=explode(" ", $CustomerName);
						$fname= ucfirst(strtolower($name[0]));
						$InvoiceAmount=number_format($InvoiceAmount,2);

						$SmsText="Dear $fname, your application No. $ApplicationID has been approved. An invoice No. $InvoiceHeader of KSh. $InvoiceAmount has been issued to you. You may now proceed to pay";

						sendSms($MobileNo,$SmsText); 
							
						$Sawa=true;
					}else
					{
						sqlsrv_rollback($db);
						$Sawa=false;
					}
				}
			}
			
		}
		else if ($NextStatusID==6)
		{
			//Inform the customer of the rejection
			$txt=$Notes;//"Your Service application have been rejected. Contact the county for the explanation";			
			if($txt=="")
			{
				$msg="Kindly State the reason for rejection";
				$Sawa=false;					
			}else
			{
				$result=php_mailer($CustomerEmail,$cosmasRow['Email'],$cosmasRow['CountyName'],'Service Rejection',$txt,'','','Message');
				$msg=$result[1];
				$Sawa=true;
			}				
		}	
		else if ($NextStatusID==7)
		{
			$Balance=0;	
			$InvoiceHeaderID='';
			
			$sql="Select top 1 InvoiceHeaderID,Balance From vwPayments where ServiceHeaderID=$ApplicationID 
			AND InvoiceHeaderID	 in (select InvoiceHeaderID from Permits where ServiceHeaderID=$ApplicationID)";
			//echo $sql;
			$s_result = sqlsrv_query($db, $sql);
			if ($s_result)
			{	
				while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
				{							
					$Balance=$row['Balance'];
					$InvoiceHeaderID=$row['InvoiceHeaderID'];
				}
			}
			if($Balance>0)
			{
				$msg= "The service is not fully paid for";
				$Sawa=false;
			}else
			{
				
				if(sqlsrv_begin_transaction($db)===false)
				{
					$msg=sqlsrv_errors();
					$Sawa=false;
				} 
				
				$validity=date('Y');
				if($today>$FirstDec){
					$validity=date('Y')+1;
				}else{
					$validity=date('Y');
				}
				$expiryDate="31/12/$validity";
				
				$mdate=date('d/m/Y');

				
				$permitNo=randomNumber();//time();				
				$expiryDate="31/12/{$validity}";

				$sql="set dateformat dmy insert into Permits(permitNo,ServiceHeaderID,Validity,ExpiryDate,CreatedBy,InvoiceHeaderID) values('$permitNo',$ApplicationID,'$validity','$expiryDate','$UserID','$InvoiceHeaderID')";
				
				$s_result1 = sqlsrv_query($db, $sql);
				if ($s_result1)
				{			
					createPermit($db,$ApplicationID,$cosmasRow);
					$rst=SaveTransaction($db,$UserID," Generated Permit Number ".$permitNo);
					$msg="Permit Created Successfully";
					$mail=true;
					
				}
				if($s_result1 && $mail==true)
				{						
					sqlsrv_commit($db);
					$Sawa=true;
				}else
				{
					echo 'Issues';
					DisplayErrors();
					sqlsrv_rollback($db);
					$Sawa=false;
				}
								
			}						
		}else
		{
			$msg='Approval Successful';
			$Sawa=true;
		}		
		//move to the next status
		if($Sawa==true)
		{			
			$sql="Update ServiceHeader set ServiceStatusID=$NextStatus where ServiceHeaderID=$ApplicationID";	
			$s_result = sqlsrv_query($db, $sql);	
		}			
	}else
	{
		
		DisplayErrors();
		$msg="Transaction failed to initialize";
	}
}




?>
    <link href="css/metro-bootstrap.css" rel="stylesheet">
    <link href="css/metro-bootstrap-responsive.css" rel="stylesheet">
    <link href="css/iconFont.css" rel="stylesheet">
    <link href="css/docs.css" rel="stylesheet">
    <link href="js/prettify/prettify.css" rel="stylesheet">
	<script src="js/metro/metro-datepicker.js"></script>   
	<script src="js/metro/metro-calendar.js"></script>	

	<script type="text/javascript">
	    	$(".datepicker").datepicker();
	    </script>   

        <div class="example">
        <legend>SBP Applications</legend>
       <!--  <input type="text" id="session" name="session" /> -->

		<form>
            <table class="table striped hovered dataTable" id="dataTables-1">
                <thead>
                  <tr>                    
                    <th colspan="6" class="text-center" style="color:#F00"><?php echo $msg; ?></th>
                  </tr>
				<tr>
					<td colspan="6">
						<table width="100%">
							<tr>
								<td width="20%"><label>From Date </label>
										<div class="input-control text datepicker" data-role="input-control">						
											<input type="text" id="fromDate" name="fromDate" value="<?php echo $fromDate ?>"></input>	
											<button class="btn-date" type="button"></button>			
										</div>
								</td>
								<td width="20%"><label>To Date </label>
									<div class="input-control text datepicker" data-role="input-control">						
										<input type="text" id="toDate" name="toDate" value="<?php echo $toDate ?>"></input>	
										<button class="btn-date" type="button"></button>			
									</div>
								</td>
								<td width="20%"><label>Application No</label>
									<div class="input-control text" data-role="input-control">						
										<input type="text" id="ServiceHeaderID" name="ServiceHeaderID" value="<?php echo $ServiceHeaderID ?>"></input>									
									</div>
								</td>								
								<td><label>&nbsp;</label>
								<input name="btnSearch" type="button" onclick="loadmypage('clients_list.php?'+
											'&fromDate='+this.form.fromDate.value+								
											'&toDate='+this.form.toDate.value+
											'&search=1','content','loader','listpages','','applications','rolecenter=<?php echo $_SESSION['RoleCenter']; ?>:fromDate='+this.form.fromDate.value+':toDate='+this.form.toDate.value+':ServiceHeaderID='+this.form.ServiceHeaderID.value+'')" value="Search">
								</td>
							</tr>
						</table>
					</td>							  
				</tr>
                <tr>
                    <th  class="text-left"> ID</th>
                    <th  class="text-left">CustomerName</th>                   
                    <th  class="text-left" width="30%">Service Name</th>
                    <th  class="text-left">Application Date</th>
                    <th  class="text-left">Ward</th>
					
                </tr>
                </thead>

                <tbody>
                </tbody>

                <!-- <tfoot>
                <tr>
                    <th class="text-left">Application ID</th>
                    <th class="text-left">Customer Name</th>                    
                    <th class="text-left">Service Name</th>
                    <th class="text-left">Application Date</th>   
                    <th class="text-left">Ward</th>   
					
                </tr>
                </tfoot> -->
            </table>
		</form>

		</div>