<?php
	require 'DB_PARAMS/connect.php';
	require_once('utilities.php');
	require_once('county_details.php');
	require_once('smsgateway.php');
	require_once('SharePoint.php');


	require("PHPMailer/src/PHPMailer.php");
    require("PHPMailer/src/SMTP.php");
    require("PHPMailer/src/Exception.php");

	//require_once("dompdf/dompdf_config.inc.php");
	// require_once("mPDF/mpdf.php");
	// require_once('phpSPO/src/autoloader.php');
	require_once('vendor/autoload.php');
	// require_once('PHP-SharePoint-Lists-API-develop/SharePointAPI.php');
	
	// use Thybag\SharePointAPI;
	use Office365\PHP\Client\Runtime\Auth\NetworkCredentialContext;
	use Office365\PHP\Client\Runtime\Utilities\UserCredentials;
	use Office365\SharePoint\ClientContext;
	use Office365\Runtime\Auth\ClientCredential;
	// use Office365\PHP\Client\SharePoint\ClientContext;
	use Office365\PHP\Client\Runtime\Auth\AuthenticationContext;
	use Office365\PHP\Client\Runtime\Utilities\RequestOptions;
	use Office365\PHP\Client\SharePoint\ListCreationInformation;
	use Office365\PHP\Client\SharePoint\SPList;
	use Office365\PHP\Client\SharePoint\Web;
	use Office365\PHP\Client\SharePoint\AttachmentCreationInformation;
	// use Office365\PHP\Client\Runtime\Auth\AuthenticationContext;



	$msg="";
	
	//require("phpToPDF.php"); 

	if (!isset($_SESSION))
	{
		session_start();
	}
	$msg ='';
	$UserID = $_SESSION['UserID'];
	$UserFullNames= $_SESSION['UserFullNames'];

	function createBarCode($No)
	{
		// Including all required classes
		require('BarCode/class/BCGFont.php');
		require('BarCode/class/BCGColor.php');
		require('BarCode/class/BCGDrawing.php'); 

		// Including the barcode technology
		include('BarCode/class/BCGcode39.barcode.php'); 

		// Loading Font
		$font = new BCGFont('BarCode/class/font/Arial.ttf', 18);

		// The arguments are R, G, B for color.
		$color_black = new BCGColor(0, 0, 0);
		$color_white =new BCGColor(255, 255, 255); 

		$code = new BCGcode39();
		$code->setScale(2); // Resolution
		$code->setThickness(30); // Thickness
		$code->setForegroundColor($color_black); // Color of bars
		$code->setBackgroundColor($color_white); // Color of spaces
		$code->setFont($font); // Font (or 0)
		$code->parse($No); // Text


		/* Here is the list of the arguments
		1 - Filename (empty : display on screen)
		2 - Background color */
		$drawing = new BCGDrawing('Images/Bar_Codes/'.$No.'.png', $color_white);
		$drawing->setBarcode($code);
		$drawing->draw();

		// Header that says it is an image (remove it if you save the barcode to a file)
		//header('Content-Type: image/png');

		// Draw (or save) the image into PNG format.
		$drawing->finish($drawing->IMG_FORMAT_PNG);
	}

	function uploadFiles($db,$mName)
	{
		$UploadDirectory	= 'C:/COSBACKUP/Dev/County/'; //Upload Directory, ends with slash & make sure folder exist
		$SuccessRedirect	= 'success.html'; //Redirect to a URL after success
	
		if (!@file_exists($UploadDirectory)) {
			//destination folder does not exist
			$msg="Make sure Upload directory exist!";
			return;
		}
		
		if($_POST)
		{	
			if(!isset($mName) || strlen($mName)<1)
			{
				//required variables are empty
				$msg="Title is empty!";
				return;
			}
			
			
			if($_FILES['mFile']['error'])
			{
				//File upload error encountered
				$msg=upload_errors($_FILES['mFile']['error']);
			}
		
			$FileName			= strtolower($_FILES['mFile']['name']); //uploaded file name
			$FileTitle			= mysql_real_escape_string($mName); // file title
			$ImageExt			= substr($FileName, strrpos($FileName, '.')); //file extension
			$FileType			= $FileType; //file type
			$FileSize			= $_FILES['mFile']["size"]; //file size
			$RandNumber   		= rand(0, 9999999999); //Random number to make each filename unique.
			$uploaded_date		= date("Y-m-d H:i:s");
			
			switch(strtolower($FileType))
			{
				//allowed file types
				case 'image/png': //png file
				case 'image/gif': //gif file 
				case 'image/jpeg': //jpeg file
				case 'application/pdf': //PDF file
				case 'application/msword': //ms word file
				case 'application/vnd.ms-excel': //ms excel file
				case 'application/x-zip-compressed': //zip file
				case 'text/plain': //text file
				case 'text/html': //html file
					break;
				default:
					die('Unsupported File!'); //output error
			}
		
		  
			//File Title will be used as new File name
			$NewFileName = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), strtolower($FileTitle));
			$NewFileName = $NewFileName.'_'.$RandNumber.$ImageExt;
		   //Rename and save uploded file to destination folder.
		   if(move_uploaded_file($_FILES['mFile']["tmp_name"], $UploadDirectory . $NewFileName ))
		   {
			   $sql="INSERT INTO RequisitionFiles (FileName, FileTitle, FileSize) VALUES ('$NewFileName', '$FileTitle',$FileSize)";
			   $result=sqlsrv_query($db,$sql);
			   if ($result)
			   {
				}
				
				//header('Location: '.$SuccessRedirect); //redirect user after success
				
		   }else
		   {
			   
				$msg='error uploading File!';
		   }
		}
	
	//function outputs upload error messages, http://www.php.net/manual/en/features.file-upload.errors.php#90522
		function upload_errors($err_code) {
			switch ($err_code) { 
				case UPLOAD_ERR_INI_SIZE: 
					return 'The uploaded file exceeds the upload_max_filesize directive in php.ini'; 
				case UPLOAD_ERR_FORM_SIZE: 
					return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; 
				case UPLOAD_ERR_PARTIAL: 
					return 'The uploaded file was only partially uploaded'; 
				case UPLOAD_ERR_NO_FILE: 
					return 'No file was uploaded'; 
				case UPLOAD_ERR_NO_TMP_DIR: 
					return 'Missing a temporary folder'; 
				case UPLOAD_ERR_CANT_WRITE: 
					return 'Failed to write file to disk'; 
				case UPLOAD_ERR_EXTENSION: 
					return 'File upload stopped by extension'; 
				default: 
					$msg='Unknown upload error'; 
			} 
		}
	

	}
	function mail_attachment($filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message) 
	{
/*		echo 'While';
		 echo "$filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message";
		 exit;	*/
		$file = $path.$filename;
		$file_size = filesize($file);
		$handle = fopen($file, "r");
		$content = fread($handle, $file_size);
		fclose($handle);
		$content = chunk_split(base64_encode($content));
		$uid = md5(uniqid(time()));
		$name = basename($file);
		$header = "From: ".$from_name." <".$from_mail.">\r\n";
		$header .= "Reply-To: ".$replyto."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		$header .= "This is a multi-part message in MIME format.\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
		$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$header .= $message."\r\n\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
		$header .= "Content-Transfer-Encoding: base64\r\n";
		$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
		$header .= $content."\r\n\r\n";
		$header .= "--".$uid."--";
		
		if ($mailto=''){
			$mailto='cngeno11@gmail.com';
		}
		
		if (mail($mailto, $subject, $message, $header)) {
			$msg= "SUCCESS: Invoice Sent to $mailto"; // or use booleans here
		} else {
			print_r(error_get_last());
			//$msg= "mail send ... ERROR!";
		}
		return $msg;
	}
	function send_email($to,$subject,$txt)
	{
		$headers = "From: ".$CountyEmail."\r\n"."CC: cngeno11@gmail.com";		
		if (mail($to,$subject,$txt,$headers))
		{
			$msg="SUCCESS: Credentials set to $to";	
		}else 
		{
			$msg= "mail send ... ERROR!";
		}

		return $msg;
	}
	
	function php_mailer($toEmail,$from,$fromName,$subject,$msg,$attachment,$file_path,$item)
	{
		$feedback=null;
		
		// $toEmail='cngeno11@gmail.com';
		// echo $toEmail.'<br>'.$from.'<br>'.$subject.'<br>'.$msg.'<br>'.$attachment.'<br>'.$file_path;
		
		$mail = new PHPMailer\PHPMailer\PHPMailer(); // the true param means it will throw exceptions on errors, which we need to catch
		$mail->IsSMTP(); // telling the class to use SMTP
		try 
		{
			

			// $mail->SMTPDebug  = 2; 		
			// $mail->defaultCredentials='true';
			// // enables SMTP debug information (for testing)
				
			$mail->SMTPAuth   = true;
			$mail->Mailer = "smtp";     // enable SMTP authentication

			$mail->SMTPDebug  = false; 		
			$mail->defaultCredentials='true';
			// enables SMTP debug information (for testing)
				
			$mail->SMTPAuth   = 2;
			$mail->Mailer = "smtp";                  // enable SMTP authentication
			$mail->isSMTP();
			$mail->SMTPAutoTLS = false; 
			$mail->Host = "smtp.gmail.com"; // sets the SMTP server	
			$mail->SMTPSecure = 'ssl'; 
			$mail->Port       = 465;                    // set the SMTP port for the GMAIL server				

			$mail->Username = "passdevelopment00@gmail.com";
			$mail->Password = "cyvkhicsdngecuvf";	    
			// $mail->Username = "omonsotest@gmail.com";
			// $mail->Password = "omonso001";	    

			
			
			$mail->AddReplyTo($toEmail, $fromName);	
			$mail->AddAddress($toEmail, $fromName);	
			$mail->SetFrom($from, $fromName);	
			$mail->AddReplyTo($from, $fromName);
			
			$mail->Subject = $subject;	
			$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
			
			//$mail->MsgHTML(file_get_contents('contents.html'));
			$mail->MsgHTML($msg);
			
			if($attachment!=''){			
				$mail->AddAttachment($file_path.$attachment); // attachment
			}
			
			$mail->Send();
			if ($mail!=false)
			{
				$feedback[0]="true";
				$feedback[1]=$item." sent Successfully to $toEmail";
				return $feedback;
				
				//return "Mail Sent Successfully to $toEmail";


			}else
			{
				$feedback[0]="false";
				
				//$feedback[1]=errorMessage();
				$feedback[1]="Mail sending failed due to server mail settings, Consult the administrator";


				return $feedback;
			}
			
			
		
		} catch (phpmailerException $e) 
		{
			echo $e->errorMessage();
			//return $e->errorMessage(); //Pretty error messages from PHPMailer		
 				$feedback[0]="false";
				//$feedback[1]=$e->errorMessage();

				$feedback[1]="Mail sending failed due to server mail settings, Consult the admin.";

				return $feedback; 
			/*	//return "error two";
				return $e->errorMessage();*/
		} 	
	}
	
	function createInvoice($db,$ApplicationID,$cosmasRow,$Remark,$CustomerName,$InvoiceHeaderID	)
	{

		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		
		$row=$cosmasRow;


		$CountyName=$row['CountyName'];		
		$CountyAddress=$row['PostalAddress'];
		$CountyTown=$row['Town'];
		$CountyTelephone=$row['Telephone1'];
		$CountyMobile=$row['Mobile1'];
		$CountyEmail=$row['Email'];
		
		$ServiceCategoryID='';


		$sql="delete from InvoiceLines where InvoiceLineID not in (
						select min(InvoiceLineID) 
						from InvoiceLines where PosReceiptID is null and InvoiceHeaderID=$InvoiceHeaderID
						group by ServiceID,InvoiceHeaderID) 
						and PosReceiptID is null and InvoiceHeaderID=$InvoiceHeaderID";

		$resul=sqlsrv_query($db,$sql);  //delete a repeated service if at all there is

		
		$rsql="select sh.CustomerID,c.PostalAddress,c.PostalCode,c.Town,isnull(c.Telephone1,c.Mobile1) Telephone1,c.Mobile1,c.CustomerName,sh.ServiceID,c.Email,s.ServiceName,
		s.ServiceCategoryID,sg.ServiceGroupID  
			from ServiceHeader sh 
			join Customer c on sh.CustomerID=c.CustomerID
			join Services s on sh.ServiceID=s.ServiceID
			join ServiceCategory sc on s.ServiceCategoryID=sc.ServiceCategoryID
			join ServiceGroup sg on sc.ServiceGroupID=sg.ServiceGroupID
			where sh.ServiceHeaderID=$ApplicationID";


		//echo $rsql; exit;
			
		$rresult = sqlsrv_query($db, $rsql);	
		
		if ($rrow = sqlsrv_fetch_array( $rresult, SQLSRV_FETCH_ASSOC))
		{
			$CustomerName = $rrow['CustomerName'];
			$ServiceName = $rrow['ServiceName'];
			$InvoiceLineID=$rrow['InvoiceLineID'];
			$Email=$rrow['Email'];
			$CustomerAddress=$rrow['PostalAddress'].' '.$rrow['PostalCode'];
			$CustomerCity=$rrow['Town'];
			$CustomerMobile=$rrow['Telephone1'];
			$ServiceGroupID=$rrow['ServiceGroupID'];
			$ServiceCategoryID=$rrow['ServiceCategoryID'];
		}		
		
		$sql="select CustomerName from Miscellaneous where ServiceHeaderID=$ApplicationID";
		$Result=sqlsrv_query($db,$sql);
		while($row=sqlsrv_fetch_array($Result,SQLSRV_FETCH_ASSOC)){
			$CustomerName=$row['CustomerName'];
		}

		$tablestr = '';
		$bankrows='';
		$ILdescription='';
		$Balance=0;


		$InvoiceNo=$InvoiceNo;

		$sql="select * from fnInvoiceDetails ($InvoiceHeaderID)";
		$result=sqlsrv_query($db,$sql);

		while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
		{
			$Balance=$rw['Balance'];
		}
		
		if($ServiceCategoryID=="81")
		{
			$sql="select li.InvoiceHeaderID,li.LandInvoiceID InvoiceLineID,s.ServiceName,lp.Description,isnull(ih.Description,'') ILdescription,li.Amount,li.DateIssued,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName CreatedBy
				from Landinvoices li 
				join landratesproperties lp on li.landpropertyid=lp.landpropertyid
				join invoicelines il on li.InvoiceHeaderID=il.InvoiceHeaderID
				join InvoiceHeader ih on il.InvoiceHeaderID=ih.InvoiceHeaderID
				join services s on il.ServiceID=s.ServiceID
				left join agents ag on ih.CreatedBy=ag.AgentID 
				where il.ServiceHeaderID='$ApplicationID' and il.InvoiceHeaderID=".$InvoiceHeaderID;
	
			$tblTotals=0;
			$result=sqlsrv_query($db, $sql);
			while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
			{					
				$ServiceName = $rw['ServiceName'].'<br>'.$Remark;
				$Description=$rw['Description'];
				$ILdescription=$rw['ILdescription'];
				$ServiceAmount = $rw['Amount'];	
				$InvoiceLineID=$rw['InvoiceLineID'];
				$CreatedBy=$rw['CreatedBy'];
				$InvoiceHeaderID=$rw['InvoiceHeaderID'];
				$CreatedDate=$rw['DateIssued'];
				$InvoiceNo=$InvoiceHeaderID;
				$tblTotals+=$ServiceAmount;
				$tablestr.='<tr>
				<td align="center">'.$InvoiceLineID.'</td>
				<td align="center">1</td>
				<td>'.$Description.'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				</tr>'; 
			}

			//check if a waiver is active
			$sql="select 1 from WaiverPeriods where getdate()>=StartDate and getDate()<=EndDate";
			$s_result = sqlsrv_query($db, $sql,$params,$options);

			$rows=sqlsrv_num_rows($s_result);
			if($rows>0){
				$tablestr.='<tr>
				<td align="center"></td>
				<td align="center"></td>
				<td><font color="red">Note: Penalty to be waived</font></td>
				<td align="right"></td>
				<td align="right"></td>
				</tr>';
			}
		}
		else if($ServiceGroupID=="20")
		{
			
			$sql="select li.InvoiceHeaderID,li.HouseInvoiceID InvoiceLineID,tn.HouseNumber ServiceName,tn.MonthlyRent Amount,
				DateName( month , DateAdd( month , [Month] , -1 ) )+'-'+convert(nvarchar(20),[year]) [Description]
				,tn.balance-tn.monthlyrent Arrears,isnull(il.Description,'') ILdescription,li.DateIssued,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName CreatedBy,li.HouseNumber
				from HouseInvoices li
				left join Tenancy tn on li.HouseNumber=tn.HouseNumber				
				left join invoicelines il on li.InvoiceHeaderID=il.InvoiceHeaderID
				join InvoiceHeader ih on il.InvoiceHeaderID=ih.InvoiceHeaderID
				left join agents ag on ih.CreatedBy=ag.AgentID
				left join services s on il.ServiceID=s.ServiceID 
				where il.ServiceHeaderID='$ApplicationID' and year(il.CreatedDate)=year(getdate())";
				
			/* echo $sql;
			exit; */
	
			$tblTotals=0;
			$result=sqlsrv_query($db, $sql);
			while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
			{					
				$ServiceName = $rw['ServiceName'].'<br>'.$Remark;
				$Description=$rw['Description'];
				$HouseNumber=$rw['HouseNumber'];
				$ILdescription=$rw['ILdescription'];
				$ServiceAmount = $rw['Amount'];	
				$Arrears=$rw['Arrears'];
				$InvoiceLineID=$rw['InvoiceLineID'];
				$InvoiceHeaderID=$rw['InvoiceHeaderID'];
				$CreatedBy=$rw['CreatedBy'];
				$CreatedDate=$rw['DateIssued'];
				$InvoiceNo=$InvoiceHeaderID;
				$tblTotals+=$ServiceAmount;
				$tablestr.='<tr>
				<td align="center">'.$InvoiceLineID.'</td>
				<td align="center">1</td>
				<td>'.$Description.'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				</tr>';

				$Description="Arrears";
				
				$tblTotals+=$Arrears;
				
				$tablestr.='<tr>
				<td align="center">'.$InvoiceLineID.'</td>
				<td align="center">1</td>
				<td>'.$Description.'</td>
				<td align="right">'.number_format($Arrears,2).'</td>
				<td align="right">'.number_format($Arrears,2).'</td>
				</tr>'; 
			}

			$sql="select Balance from Tenancy where HouseNumber='$HouseNumber'";
			$result=sqlsrv_query($db,$sql);

			while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
			{
				$Balance=$rw['Balance'];
			}

		}
		else
		{
			
			$sql="select il.InvoiceLineID,il.InvoiceHeaderID,s.ServiceName+' ('+il.Description+')' ServiceName,ih.Description,isnull(ih.Description,'') ILdescription, ih.ServiceHeaderID,il.Amount,ih.InvoiceNo,il.CreatedDate,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName CreatedBy
			from InvoiceLines il
			inner join InvoiceHeader ih on il.InvoiceHeaderID=ih.InvoiceHeaderID
			left join agents ag on ih.CreatedBy=ag.AgentID
			inner join services s on il.ServiceID=s.ServiceID 				
			where ih.ServiceHeaderID=$ApplicationID and ih.InvoiceHeaderID=$InvoiceHeaderID				
			order by il.InvoiceLineID";

			//echo $sql; exit;
	
			$tblTotals=0;
			$result=sqlsrv_query($db, $sql);
			while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
			{	
				$Description=$ServiceName.'('.$rw['Description'].')';	
				$ILdescription=$rw['ILdescription'];				
				$ServiceName = $rw['ServiceName'].'<br>'.$Remark;
				$ServiceAmount = $rw['Amount'];	
				$InvoiceLineID=$rw['InvoiceLineID'];
				$InvoiceHeaderID=$rw['InvoiceHeaderID'];
				$CreatedDate=$rw['CreatedDate'];
				$CreatedBy=$rw['CreatedBy'];
				$InvoiceNo=$rw['InvoiceNo'];
				$tblTotals+=$ServiceAmount;
				$tablestr.='<tr>
				<td align="center">'.$InvoiceLineID.'</td>
				<td align="center">1</td>
				<td>'.$ServiceName.'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				</tr>'; 
			}

		}

		$SerialNo=$InvoiceHeaderID;



		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

		//run penalties
		$sql="select CustomerName from Miscellaneous where ServiceHeaderID=$ApplicationID";
		$s_result = sqlsrv_query($db, $sql,$params,$options);

		$rows=sqlsrv_num_rows($s_result);

		if($rows>0){
			while($rows=sqlsrv_fetch_array($s_result,SQLSRV_FETCH_ASSOC))
			{
				$CustomerName=$rows['CustomerName'];
				$CustomerMobile='';
				$CustomerAddress='';
				
			}
		}

	
		
		$sqlb="select BankName,AccountNumber from Banks where active=1 and bankid in (1003,1018)";
		$bnkr=sqlsrv_query($db,$sqlb);
		while($bnks=sqlsrv_fetch_array($bnkr,SQLSRV_FETCH_ASSOC))
		{
			$bankrows.='<tr>
				<td>'.sentence_case($bnks['BankName']).'</td>
				<td>'.sentence_case($bnks['AccountNumber']).'</td>
				</tr>
			';
		}
		//echo $bankrows;
		//exit;
		$OtherCharge=0;
		//With other Charges?
		$sql="select sum (distinct sc.amount)Amount
		from ServiceCharges sc
		join ServicePlus sp on sp.service_add=sc.ServiceID
		join FinancialYear fy on sc.FinancialYearId=fy.FinancialYearID
		join ServiceHeader sh on sh.ServiceID=sp.ServiceID
		and sh.ServiceHeaderID=$ApplicationID
		and fy.isCurrentYear=1";
		$s_result = sqlsrv_query($db, $sql);
		while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
		{							
			$OtherCharge=$row["Amount"];												
		}

		$ServiceAmount=$ServiceAmount+$OtherCharge;		
		
		createBarCode($InvoiceHeaderID);
		
		$mpdf=new mPDF('win-1252','A4','','',20,15,48,25,10,10);
		$mpdf->useOnlyCoreFonts = true;    // false is default
		$mpdf->SetProtection(array('print'));
		$mpdf->SetTitle($CustomerName." - Invoice");
		$mpdf->SetAuthor($CountyName);
		$mpdf->SetWatermarkText('Tourism Regulatory Authority');
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$html = '
		<html>
		<head>
			<link rel="stylesheet" type="text/css" href="css/my_css.css"/>		
		</head>
		<body>

		<!--mpdf
		<htmlpageheader name="myheader">
		<table width="100%">
				
		<tr>
			<td align="Center" colspan="2">
				<img src="images/logo1.png" alt="TRA Logo">
			</td>
		</tr>
		<tr>
			<td align="Center" colspan="2" style="font-size:5mm">
				<b>SERVICE APPLICATION INVOICE</b>
			</td>
		</tr>
			
		<tr>
			<td width="50%" style="color:#0000BB;">
				Address: '.$CountyAddress.'<br />
				'.$CountyTown.'<br /> 
				Telephone: '.$CountyTelephone.'</td>
			<td width="50%" style="text-align: right;">			
			Invoice No.<br/><span style="font-weight: bold; font-size: 10pt;">'.$SerialNo.'</span>
			</td>
		</tr></table>
		
		</htmlpageheader>

		<htmlpagefooter name="myfooter">
		<div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
		powered by      <img src="images/attain_logo_2.jpg" alt="County Logo">
		</div>
		</htmlpagefooter>

		<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
		<sethtmlpagefooter name="myfooter" value="on" />
		mpdf-->
		<br/><br/><br/><br/>
		<div style="text-align: right">Invoice Date: '.date_format(date_create($CreatedDate),"d/m/Y").'</div>
		
		<table width="100%" style="font-family: serif;" cellpadding="10">
		<tr>
			<td width="45%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">TO:</span><br /><br />'.$CustomerName.'<br /> Postal Address: '.$CustomerAddress.'<br />'.$CustomerCity.'<br />'.$CustomerMobile.'
			</td>
			<td width="10%">&nbsp;</td>
			<td width="45%"></td>
		</tr>
		</table>

		<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
		<thead>
		<tr>
		<td width="15%">REF. NO.</td>
		<td width="15%">QUANTITY</td>
		<td width="40%">DESCRIPTION</td>
		<td width="15%">UNIT PRICE</td>
		<td width="15%">AMOUNT</td>
		</tr>
		</thead>
		<tbody>
		
		<!-- ITEMS HERE -->'.
		
		
		$tablestr.
										
		'<!-- END ITEMS HERE -->
		
		<tr>
			<td class="blanktotal" colspan="2" rowspan="6"></td>
			<td class="totals" align="left">'.$ILdescription.'</td>
			<td class="totals">Subtotal:</td>
			<td class="totals">'.number_format($tblTotals,2).'</td>
		</tr>
		<tr>
			<td class="blanktotal" rowspan="6"></td>
			<td class="totals"><b>TOTAL:</b></td>
			<td class="totals"><b>'.number_format($tblTotals,2).'</b></td>
		</tr>
		<tr>
		
		<td class="totals"><b>Balance due:</b></td>
		<td class="totals"><b>'.number_format($Balance,2).'</b></td>
		</tr>
		</tbody>
		</table>
		Created By <strong>'.$CreatedBy.'</strong><br>
		<div style="font-style: italic; font-size: 10;">
			Payment terms: payment due in 30 days<br>
			Payment by MPESA
			<ol>
			<li> Go to MPESA menu and select <b>Lipa na MPESA</b></li>
			<li> Enter <b>522522</b> as the paybill number and the Invoice Number as the account number</li>
			<li> Pay the amount and enter your MPESA pin number when printed</li>
			</ol>							
			<b>Payment by Bank</b>
			<ol>
				<li>Enter the TRA revenue account invoice number as the account number</li>
			</ol>
			<b>Bank Accounts</b>
			<table width="50%" style="font-family: serif; font-size: 11;">'
			.$bankrows.
			'</table><br>							
			Contact us on <b>0720646464</b> for any assistance
		</div>
		<br>
		<div style="text-align: center;">
			<img src="Images/Bar_Codes/'.$InvoiceNo.'.PNG">
		</div>
		</body>
		</html>
		';
/* 		echo $html;
		exit; */
		$mpdf->WriteHTML($html);
 		$mpdf->Output();
		exit; 
		
		//$mpdf->Output('pdfdocs/invoices/'.$SerialNo.'.pdf','F'); 
		
		//send email
		$my_file = $SerialNo.'.pdf';
		$file_path = "pdfdocs/invoices/";
		$my_name = $CountyName;
		$toEmail = $Email;
		$fromEmail = $CountyEmail;
		$my_subject = "Service Application Invoice";
		$my_message="Kindly receive the invoice for your applied Service";
		//$my_mail = 'cngeno11@gmail.com';
		$result=php_mailer($toEmail,$fromEmail,$CountyName,$my_subject,$my_message,$my_file,$file_path,"Invoice");
		
		
		$SmsText="Invoice No ".$InvoiceNo." Amount: ".$tblTotals;
		sendSms($MobileNo,$SmsText);

		$sql="Insert Into SMS (MobileNo,Message,Subject) Values ('$MobileNo','$SmsText','Invoice')";
		$result=sqlsrv_query($db,$sql);
		
		return $result;			
	}

	function invoiceStatement($db,$ApplicationID,$cosmasRow,$Remark,$CustomerName,$InvoiceHeaderID	)
	{

		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		
		$row=$cosmasRow;	
		$CountyName=$row['CountyName'];		
		$CountyAddress=$row['PostalAddress'];
		$CountyTown=$row['Town'];
		$CountyTelephone=$row['Telephone1'];
		$CountyMobile=$row['Mobile1'];
		$CountyEmail=$row['Email'];
		
		$ServiceCategoryID='';
		
		$rsql="select sh.CustomerID,c.PostalAddress,c.PostalCode,c.Town,c.Telephone1,c.Mobile1,c.CustomerName,sh.ServiceID,c.Email,s.ServiceName,
		s.ServiceCategoryID,sg.ServiceGroupID  
			from ServiceHeader sh 
			join Customer c on sh.CustomerID=c.CustomerID
			join Services s on sh.ServiceID=s.ServiceID
			join ServiceCategory sc on s.ServiceCategoryID=sc.ServiceCategoryID
			join ServiceGroup sg on sc.ServiceGroupID=sg.ServiceGroupID
			where sh.ServiceHeaderID=$ApplicationID";
			
		$rresult = sqlsrv_query($db, $rsql);	
		
		if ($rrow = sqlsrv_fetch_array( $rresult, SQLSRV_FETCH_ASSOC))
		{
			$CustomerName = $rrow['CustomerName'];
			$ServiceName = $rrow['ServiceName'];
			$InvoiceLineID=$rrow['InvoiceLineID'];
			$Email=$rrow['Email'];
			$CustomerAddress=$rrow['PostalAddress'].' '.$rrow['PostalCode'];
			$CustomerCity=$rrow['Town'];
			$CustomerMobile=$rrow['Telephone1'];
			$ServiceGroupID=$rrow['ServiceGroupID'];
			$ServiceCategoryID=$rrow['ServiceCategoryID'];
		}		
		
		$sql="select CustomerName from Miscellaneous where ServiceHeaderID=$ApplicationID";
		$Result=sqlsrv_query($db,$sql);
		while($row=sqlsrv_fetch_array($Result,SQLSRV_FETCH_ASSOC)){
			$CustomerName=$row['CustomerName'];
		}

		$tablestr = '';
		$bankrows='';
		$ILdescription='';
		
		if($ServiceCategoryID=="81")
		{
			$sql="select li.InvoiceHeaderID,li.LandInvoiceID InvoiceLineID,s.ServiceName,lp.Description,isnull(ih.Description,'') ILdescription,li.Amount,li.DateIssued
				from Landinvoices li 
				join landratesproperties lp on li.landpropertyid=lp.landpropertyid
				join invoicelines il on li.InvoiceHeaderID=il.InvoiceHeaderID
				join InvoiceHeader ih on il.InvoiceHeaderID=ih.InvoiceHeaderID
				join services s on il.ServiceID=s.ServiceID 
				where il.ServiceHeaderID='$ApplicationID' and il.InvoiceHeaderID=".$InvoiceHeaderID;
	
			$tblTotals=0;
			$result=sqlsrv_query($db, $sql);
			while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
			{					
				$ServiceName = $rw['ServiceName'].'<br>'.$Remark;
				$Description=$rw['Description'];
				$ILdescription=$rw['ILdescription'];
				$ServiceAmount = $rw['Amount'];	
				$InvoiceLineID=$rw['InvoiceLineID'];
				$InvoiceHeaderID=$rw['InvoiceHeaderID'];
				$CreatedDate=$rw['DateIssued'];
				$InvoiceNo=$InvoiceHeaderID;
				$tblTotals+=$ServiceAmount;
				$tablestr.='<tr>
				<td align="center">'.$InvoiceLineID.'</td>
				<td align="center">1</td>
				<td>'.$Description.'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				</tr>'; 
			}
		}
		else if($ServiceGroupID=="20")
		{
			
			$sql="select li.InvoiceHeaderID,li.HouseInvoiceID InvoiceLineID,tn.HouseNumber ServiceName,tn.MonthlyRent Amount,
				DateName( month , DateAdd( month , [Month] , -1 ) )+'-'+convert(nvarchar(20),[year]) [Description]
				,tn.balance-tn.monthlyrent Arrears,isnull(ih.Description,'') ILdescription
				from HouseInvoices li,li.DateIssued
				left join Tenancy tn on li.HouseNumber=tn.HouseNumber				
				left join invoicelines il on li.InvoiceHeaderID=il.InvoiceHeaderID
				join InvoiceHeader ih on il.InvoiceHeaderID=ih.InvoiceHeaderID
				left join services s on il.ServiceID=s.ServiceID 
				where il.ServiceHeaderID='$ApplicationID'";
				
			/* echo $sql;
			exit; */
	
			$tblTotals=0;
			$result=sqlsrv_query($db, $sql);
			while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
			{					
				$ServiceName = $rw['ServiceName'].'<br>'.$Remark;
				$Description=$rw['Description'];
				$ILdescription=$rw['ILdescription'];
				$ServiceAmount = $rw['Amount'];	
				$Arrears=$rw['Arrears'];
				$InvoiceLineID=$rw['InvoiceLineID'];
				$InvoiceHeaderID=$rw['InvoiceHeaderID'];
				$CreatedDate=$rw['DateIssued'];
				$InvoiceNo=$InvoiceHeaderID;
				$tblTotals+=$ServiceAmount;
				$tablestr.='<tr>
				<td align="center">'.$InvoiceLineID.'</td>
				<td align="center">1</td>
				<td>'.$Description.'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				</tr>';

				$Description="Arrears";
				
				$tblTotals+=$Arrears;
				
				$tablestr.='<tr>
				<td align="center">'.$InvoiceLineID.'</td>
				<td align="center">1</td>
				<td>'.$Description.'</td>
				<td align="right">'.number_format($Arrears,2).'</td>
				<td align="right">'.number_format($Arrears,2).'</td>
				</tr>'; 
			}		
		}
		else
		{
			
			$sql="select il.InvoiceLineID,il.InvoiceHeaderID,s.ServiceName,ih.Description,isnull(ih.Description,'') ILdescription, il.ServiceHeaderID,il.Amount,ih.InvoiceNo,il.CreatedDate
			from InvoiceLines il
			inner join InvoiceHeader ih on il.InvoiceHeaderID=ih.InvoiceHeaderID
			inner join services s on il.ServiceID=s.ServiceID 				
			where il.ServiceHeaderID=$ApplicationID and ih.InvoiceHeaderID=$InvoiceHeaderID				
			order by il.InvoiceLineID";
	
			$tblTotals=0;
			$result=sqlsrv_query($db, $sql);
			while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
			{	
				$Description=$ServiceName.'('.$rw['Description'].')';	
				$ILdescription=$rw['ILdescription'];				
				$ServiceName = $rw['ServiceName'].'<br>'.$Remark;
				$ServiceAmount = $rw['Amount'];	
				$InvoiceLineID=$rw['InvoiceLineID'];
				$InvoiceHeaderID=$rw['InvoiceHeaderID'];
				$CreatedDate=$rw['CreatedDate'];
				$InvoiceNo=$rw['InvoiceNo'];
				$tblTotals+=$ServiceAmount;
				$tablestr.='<tr>
				<td align="center">'.$InvoiceLineID.'</td>
				<td align="center">1</td>
				<td>'.$ServiceName.'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				</tr>'; 
			}
		}
		
		// echo ($sql);
		// exit

		$InvoiceNo=$InvoiceNo;
		$SerialNo=$InvoiceHeaderID;


		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

		//run penalties
		$sql="select CustomerName from Miscellaneous where ServiceHeaderID=$ApplicationID";
		$s_result = sqlsrv_query($db, $sql,$params,$options);

		$rows=sqlsrv_num_rows($s_result);

		if($rows>0){
			while($rows=sqlsrv_fetch_array($s_result,SQLSRV_FETCH_ASSOC))
			{
				$CustomerName=$rows['CustomerName'];
				$CustomerMobile='';
				$CustomerAddress='';
				
			}
		}

	
		
		$sqlb="select BankName,AccountNumber from Banks where active=1";
		$bnkr=sqlsrv_query($db,$sqlb);
		while($bnks=sqlsrv_fetch_array($bnkr,SQLSRV_FETCH_ASSOC))
		{
			$bankrows.='<tr>
				<td>'.sentence_case($bnks['BankName']).'</td>
				<td>'.sentence_case($bnks['AccountNumber']).'</td>
				</tr>
			';
		}
		//echo $bankrows;
		//exit;
		$OtherCharge=0;
		//With other Charges?
		$sql="select sum (distinct sc.amount)Amount
		from ServiceCharges sc
		join ServicePlus sp on sp.service_add=sc.ServiceID
		join FinancialYear fy on sc.FinancialYearId=fy.FinancialYearID
		join ServiceHeader sh on sh.ServiceID=sp.ServiceID
		and sh.ServiceHeaderID=$ApplicationID
		and fy.isCurrentYear=1";
		$s_result = sqlsrv_query($db, $sql);
		while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
		{							
			$OtherCharge=$row["Amount"];												
		}

		$ServiceAmount=$ServiceAmount+$OtherCharge;		
		
		createBarCode($InvoiceHeaderID);
		
		$mpdf=new mPDF('win-1252','A4','','',20,15,48,25,10,10);
		$mpdf->useOnlyCoreFonts = true;    // false is default
		$mpdf->SetProtection(array('print'));
		$mpdf->SetTitle($CustomerName." - Invoice");
		$mpdf->SetAuthor($CountyName);
		$mpdf->SetWatermarkText($CountyName);
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$html = '
		<html>
		<head>
			<link rel="stylesheet" type="text/css" href="css/my_css.css"/>		
		</head>
		<body>

		<!--mpdf
		<htmlpageheader name="myheader">
		<table width="100%">
		<tr>
			<td align="Center" colspan="2" style="font-size:10mm">
				<b>SERVICE APPLICATION INVOICE</b>
			</td>
		</tr>		
		<tr>
			<td align="Center" colspan="2">
				<img src="images/logo1.png" alt="County Logo">
			</td>
		</tr>
		<tr>
			<td colspan="2" align="Center"><span style="font-weight: bold; font-size: 14pt;">'.$CountyName.'</span></td>
		</tr>		
		<tr>
			<td width="50%" style="color:#0000BB;">
				Address: '.$CountyAddress.'<br />
				'.$CountyTown.'<br /> 
				Telephone: '.$CountyTelephone.'</td>
			<td width="50%" style="text-align: right;">			
			Invoice No.<br/><span style="font-weight: bold; font-size: 12pt;">'.$SerialNo.'</span>
			</td>
		</tr></table>
		
		</htmlpageheader>

		<htmlpagefooter name="myfooter">
		<div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
		powered by      <img src="images/attain_logo_2.jpg" alt="County Logo">
		</div>
		</htmlpagefooter>

		<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
		<sethtmlpagefooter name="myfooter" value="on" />
		mpdf-->
		<br/><br/><br/><br/><br/><br/><br/><br/>
		<div style="text-align: right">Invoice Date: '.date_format(date_create($CreatedDate),"d/m/Y").'</div>
		
		<table width="100%" style="font-family: serif;" cellpadding="10">
		<tr>
			<td width="45%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">TO:</span><br /><br />'.$CustomerName.'<br />'.$CustomerAddress.'<br />'.$CustomerCity.'<br />'.$CustomerMobile.'
			</td>
			<td width="10%">&nbsp;</td>
			<td width="45%"></td>
		</tr>
		</table>


		<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
		<thead>
		<tr>
		<td width="15%">REF. NO.</td>
		<td width="15%">QUANTITY</td>
		<td width="40%">DESCRIPTION</td>
		<td width="15%">UNIT PRICE</td>
		<td width="15%">AMOUNT</td>
		</tr>
		</thead>
		<tbody>
		
		<!-- ITEMS HERE -->'.
		
		
		$tablestr.
										
		'<!-- END ITEMS HERE -->
		
		<tr>
		<td class="blanktotal" colspan="2" rowspan="6"></td>
		<td class="totals" align="left">'.$ILdescription.'</td>
		<td class="totals">Subtotal:</td>
		<td class="totals">'.number_format($tblTotals,2).'</td>
		</tr>
		<tr>
		<td class="blanktotal" rowspan="6"></td>
		<td class="totals"><b>TOTAL:</b></td>
		<td class="totals"><b>'.number_format($tblTotals,2).'</b></td>
		</tr>
		<tr>
		
		<td class="totals"><b>Balance due:</b></td>
		<td class="totals"><b>'.number_format($tblTotals,2).'</b></td>
		</tr>
		</tbody>
		</table>
		<div style="font-style: italic; font-size: 10;">
							Payment terms: payment due in 30 days<br>
							Payment by MPESA
							<ol>
							<li> Go to MPESA menu and select <b>Lipa na MPESA</b></li>
							<li> Enter <b>646464</b> as the paybill number and the Invoice Number as the account number</li>
							<li> Pay the amount and enter your MPESA pin number when printed</li>
							</ol>							
							<b>Payment by Bank</b>
							<ol>
								<li>Enter the Uasin Gishu county revenue account invoice number as the account number</li>
							</ol>
							<b>Bank Accounts</b>
							<table width="75%" style="font-family: serif; font-size: 11;">'
							.$bankrows.
							'</table><br>							
							Contact us on <b>0720646464</b> for any assistance
		</div>
		<br>
		<div style="text-align: center;">
			<img src="Images/Bar_Codes/'.$InvoiceNo.'.PNG">
		</div>
		</body>
		</html>
		';
/* 		echo $html;
		exit; */
		$mpdf->WriteHTML($html);
 		$mpdf->Output();
		exit; 
		
		$mpdf->Output('pdfdocs/invoices/'.$SerialNo.'.pdf','F'); 
		
		//send email
		$my_file = $SerialNo.'.pdf';
		$file_path = "pdfdocs/invoices/";
		$my_name = $CountyName;
		$toEmail = $Email;
		$fromEmail = $CountyEmail;
		$my_subject = "Service Application Invoice";
		$my_message="Kindly receive the invoice for your applied Service";
		//$my_mail = 'cngeno11@gmail.com';
		$result=php_mailer($toEmail,$fromEmail,$CountyName,$my_subject,$my_message,$my_file,$file_path,"Invoice");
		
		/* $MobileNo="+254725463120";
		$SmsText="Invoice No ".$InvoiceNo." Amount: ".$tblTotals;
		sendSMS($MobileNo,$SmsText); */
		
		return $result;			
	}
	function createReceipt($db,$ApplicationID222,$cosmasRow,$Remark,$CustomerName,$ReceiptID,$CreatedBy)
	{
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		
		$row=$cosmasRow;	
		$CountyName=$row['CountyName'];		
		$CountyAddress=$row['PostalAddress'];
		$CountyTown=$row['Town'];
		$CountyTelephone=$row['Telephone1'];
		$CountyMobile=$row['Mobile1'];
		$CountyEmail=$row['Email'];
		$CountyPostalCode=$row['PostalCode'];

		
		$ServiceCategoryID='';
		$tblTotals=0;
		$InvoiceTotal=0;
		
		$rsql="select sh.CustomerID,c.PostalAddress,c.PostalCode,c.Town,c.Telephone1,c.Mobile1,c.CustomerName,sh.ServiceID,c.Email,s.ServiceName,
		s.ServiceCategoryID,sg.ServiceGroupID  
			from ServiceHeader sh 
			join Customer c on sh.CustomerID=c.CustomerID
			join Services s on sh.ServiceID=s.ServiceID
			join ServiceCategory sc on s.ServiceCategoryID=sc.ServiceCategoryID
			join ServiceGroup sg on sc.ServiceGroupID=sg.ServiceGroupID
			where sh.ServiceHeaderID=$ApplicationID";
			
		$rresult = sqlsrv_query($db, $rsql);

		//print_r($rsql);exit;	
		
		if ($rrow = sqlsrv_fetch_array( $rresult, SQLSRV_FETCH_ASSOC))
		{
			$CustomerName = $rrow['CustomerName'];
			$ServiceName = $rrow['ServiceName'];
			$InvoiceLineID=$rrow['InvoiceLineID'];
			$Email=$rrow['Email'];
			$CustomerAddress=$rrow['PostalAddress'].' '.$rrow['PostalCode'];
			$CustomerCity=$rrow['Town'];
			$CustomerMobile=$rrow['Telephone1'];
			$ServiceGroupID=$rrow['ServiceGroupID'];
			$ServiceCategoryID=$rrow['ServiceCategoryID'];
		}		
		
		$sql="select CustomerName from Miscellaneous where ServiceHeaderID=$ApplicationID";
		$Result=sqlsrv_query($db,$sql);
		while($row=sqlsrv_fetch_array($Result,SQLSRV_FETCH_ASSOC)){
			$CustomerName=$row['CustomerName'];
		}

		$tablestr = '';
		$bankrows='';
		$CreatedDate='';
		
		if($ServiceCategoryID=="81")
		{
			$sql="select li.InvoiceHeaderID,li.LandInvoiceID InvoiceLineID,s.ServiceName,lp.Description,li.Amount ilAmount,rl.Amount PaidAmount,rl.CreatedDate,r.ReceiptDate,ag.FirstName +' '+ag.MiddleName+' '+ag.LastName CreatedBy 
				from Landinvoices li 
				join landratesproperties lp on li.landpropertyid=lp.landpropertyid
				join invoicelines il on li.InvoiceHeaderID=il.InvoiceHeaderID
				join InvoiceHeader ih on il.InvoiceHeaderID=ih.InvoiceHeaderID
				join ReceiptLines rl on rl.InvoiceHeaderID=ih.InvoiceHeaderID
				join Receipts r on rl.receiptid=r.ReceiptID
				join services s on il.ServiceID=s.ServiceID 
				left join Agents ag on r.CreatedBy=ag.AgentID
				where rl.ReceiptID='$ReceiptID'";
	
			$tblTotals=0;
			$result=sqlsrv_query($db, $sql);
			while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
			{					
				$ServiceName = $rw['ServiceName'].'<br>'.$Remark;
				$CreatedBy=$rw['CreatedBy'];
				$CreatedDate=$rw['CreatedDate'];
				$Description=$rw['Description'];
				$ServiceAmount = $rw['Amount'];
				$ReferenceNumber=$rw['ReferenceNumber'];
				$InvoiceHeaderID=$rw['InvoiceHeaderID'];
				$InvoiceNo=$InvoiceHeaderID;
				$tblTotals+=$rw['paidAmount'];;
				$InvoiceTotal+=$rw['ilAmount'];;
				$tablestr.='<tr>
				<td align="center">'.$InvoiceHeaderID.'</td>
				<td align="center">'.$ReceiptDate.'</td>
				<td>'.$Description.'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				</tr>'; 
			}
		}
		else if($ServiceGroupID=="20")
		{
			
			$sql="select li.InvoiceHeaderID,li.HouseInvoiceID InvoiceLineID,tn.HouseNumber ServiceName,rl.Amount,rl.CreatedDate,r.ReceiptID,
				DateName( month , DateAdd( month , [Month] , -1 ) )+'-'+convert(nvarchar(20),[year]) [Description],r.ReferenceNumber,r.ReceiptDate,ag.FirstName +' '+ag.MiddleName+' '+ag.LastName CreatedBy 				
				from HouseInvoices li
				join Tenancy tn on li.HouseNumber=tn.HouseNumber				
				join invoicelines il on li.InvoiceHeaderID=il.InvoiceHeaderID
				join services s on il.ServiceID=s.ServiceID 
				join ReceiptLines rl on rl.InvoiceHeaderID=il.InvoiceHeaderID
				join Receipts r on rl.ReceiptID=r.ReceiptID
				left join Agents ag on r.CreatedBy=ag.AgentID
				where  r.ReceiptID=$ReceiptID";
				
			  
	
			$tblTotals=0;
			$result=sqlsrv_query($db, $sql);
			while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
			{					
				$ServiceName = $rw['ServiceName'].'<br>'.$Remark;
				$CreatedBy=$rw['CreatedBy'];
				$CreatedDate=$rw['CreatedDate'];
				$Description=$rw['Description'];
				$ServiceAmount = $rw['Amount'];	
				$ReferenceNumber=$rw['ReferenceNumber'];
				$Arrears=$rw['Arrears'];
				$ReceiptDate=$rw['ReceiptDate'];
				$InvoiceHeaderID=$rw['InvoiceHeaderID'];
				$InvoiceNo=$InvoiceHeaderID;
				$tblTotals+=$ServiceAmount;
				$tablestr.='<tr>
				<td align="center">'.$InvoiceHeaderID.'</td>
				<td align="center">'.$ReceiptDate.'</td>
				<td>'.$Description.'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>
				</tr>'; 
			}		
		}
		else
		{
			
			$sql=" 	select il.InvoiceHeaderID,s.ServiceName+'('+ isnull(ih.Description,'')+')' ServiceName,rl.Amount,rl.CreatedDate,r.ReferenceNumber,
			r.ReceiptDate,s.ServiceID,ISNULL(IH.CustomerName, c.CustomerName) CustomerName,ih.ServiceHeaderID,ag.FirstName +' '+ag.MiddleName+' '+ag.LastName CreatedBy 
			from ReceiptLines rl 
				inner join Receipts r on rl.ReceiptID=r.receiptid 
				join InvoiceHeader ih on ih.InvoiceHeaderID=rl.InvoiceHeaderID 
				join (select distinct InvoiceHeaderID, ServiceID,sum(Amount) Amount  from InvoiceLines group by InvoiceHeaderID, ServiceID) il on il.InvoiceHeaderID=ih.InvoiceHeaderID 
				inner join services s on il.ServiceID=s.ServiceID 
				join Customer c on ih.CustomerID=c.CustomerID
				left join Agents ag on r.CreatedBy=ag.AgentID
				where r.receiptid='$ReceiptID' 
				order by il.InvoiceHeaderID";
	
			$tblTotals=0;
			$result=sqlsrv_query($db, $sql);
			while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
			{
				$CreatedBy=$rw['CreatedBy'];
				$CreatedDate=$rw['CreatedDate'];
				$CustomerName=$rw['CustomerName'];					
				$ServiceName = $rw['ServiceName'].'<br>'.$Remark;
				$ServiceAmount = $rw['Amount'];
				$ReceiptDate=date('d/m/Y',strtotime($rw['ReceiptDate']));
				$ReferenceNumber=$rw['ReferenceNumber'];
				$InvoiceHeaderID=$rw['InvoiceHeaderID'];
				$InvoiceNo=$rw['InvoiceHeaderID'];
				$tblTotals+=$ServiceAmount;
				$tablestr.='<tr>
				<td align="center">'.$InvoiceHeaderID.'</td>
				<td align="center">'.$ReceiptDate.'</td>
				<td>'.$ServiceName.'</td>
				<td align="right">'.number_format($ServiceAmount,2).'</td>				
				</tr>'; 
			}
		}
		// echo $sql;
		// exit;
		$InvoiceNo=$InvoiceNo;
		$SerialNo=$InvoiceHeaderID;
		//echo $bankrows;
		//exit;
		$OtherCharge=0;
		//With other Charges?
		$sql="select sum (distinct sc.amount)Amount
		from ServiceCharges sc
		join ServicePlus sp on sp.service_add=sc.ServiceID
		join FinancialYear fy on sc.FinancialYearId=fy.FinancialYearID
		join ServiceHeader sh on sh.ServiceID=sp.ServiceID
		and sh.ServiceHeaderID=$ApplicationID
		and fy.isCurrentYear=1";
		$s_result = sqlsrv_query($db, $sql);
		while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
		{							
			$OtherCharge=$row["Amount"];												
		}

		$ServiceAmount=$ServiceAmount+$OtherCharge;		
		
		createBarCode($ReceiptID);
		
		$mpdf=new mPDF('win-1252','A4','','',20,15,48,25,10,10);
		$mpdf->useOnlyCoreFonts = true;    // false is default
		$mpdf->SetProtection(array('print'));
		$mpdf->SetTitle("Acme Trading Co. - Invoice");
		$mpdf->SetAuthor("Acme Trading Co.");
		$mpdf->SetWatermarkText("County Government Of Uasin Gishu");
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$html = '
		<html>
		<head>
			<link rel="stylesheet" type="text/css" href="css/my_css.css"/>		
		</head>
		<body>

		<!--mpdf
		<htmlpageheader name="myheader">
		<table width="100%">
		<tr>
			<td align="Center" colspan="2" style="font-size:10mm">
				<b>PAYMENT RECEIPT</b>
			</td>
		</tr>		
		<tr>
			<td align="Center" colspan="2">
				<img src="images/logo1.png" alt="County Logo">
			</td>
		</tr>
		<tr>
			<td colspan="2" align="Center"><span style="font-weight: bold; font-size: 14pt;">'.$CountyName.'</span></td>
		</tr>		
		<tr>
			<td width="50%" syle="color:#0000BB;">
				P.O Box: '.$CountyAddress.'-'.$CountyPostalCode.'<br />
				'.$CountyTown.'<br /> 
				Telephone: '.$CountyTelephone.'</td>
			<td width="50%" style="text-align: right;">			
			Receipt No.<br/><span style="font-weight: bold; font-size: 12pt;">'.$ReceiptID.'</span> <br/>
			Reference No.<br/><span style="font-weight: bold; font-size: 12pt;">'.$ReferenceNumber.'</span>
			</td>
		</tr></table>
		
		</htmlpageheader>

		<htmlpagefooter name="myfooter">
		<div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
		powered by      <img src="images/attain_logo_2.jpg" alt="County Logo">
		</div>
		</htmlpagefooter>

		<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
		<sethtmlpagefooter name="myfooter" value="on" />
		mpdf-->
		<br/><br/><br/><br/><br/><br/><br/><br/>
		<div style="text-align: right">Date: '.date('jS F Y').'</div>
		
		<table width="100%" style="font-family: serif;" cellpadding="10">
		<tr>
			<td width="45%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">RECEIPT TO:</span><br /><br />'.$CustomerName.'<br />'.$CustomerAddress.'<br />'.$CustomerCity.'<br />'.$CustomerMobile.'
			</td>
			<td width="10%">&nbsp;</td>
			<td width="45%"></td>
		</tr>
		</table>


		<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
		<thead>
		<tr>
			<td>REF. NO.</td>
			<td>DEPOSIT DATE</td>
			<td>DESCRIPTION</td>
			<td>AMOUNT</td>
		</tr>
		</thead>
		<tbody>
		
		<!-- ITEMS HERE -->'.
		
		
		$tablestr.
										
		'<!-- END ITEMS HERE -->
		
		<tr>
			<td colspan="2" class="blanktotal" rowspan="6"></td>
			<td class="totals">Total:</td>
			<td class="totals">'.number_format($tblTotals,2).'</td>
		</tr>
		
		</tbody>
		</table>
		<br>
		<div style="text-align: center;">
			<img src="Images/Bar_Codes/'.$ReceiptID.'.PNG">
		</div>
		<div style="text-align: center;">
			<br>Received By: <i>'.strtolower($CreatedBy).'</i>
			<br>Receipt Date: <i>'.date('d/m/Y',strtotime($CreatedDate)).'</i> Time: '.date('H:i',strtotime($CreatedDate)).'
		</div>
		</body>
		</html>
		';
		/* echo $html;
		exit; */
		$mpdf->WriteHTML($html);
 		$mpdf->Output();
		exit;
		
		$mpdf->Output('pdfdocs/invoices/'.$SerialNo.'.pdf','F'); 
		
		//send email
		$my_file = $SerialNo.'.pdf';
		$file_path = "pdfdocs/invoices/";
		$my_name = $CountyName;
		$toEmail = $Email;
		$fromEmail = $CountyEmail;
		$my_subject = "Service Application Invoice";
		$my_message="Kindly receive the invoice for your applied Service";
		//$my_mail = 'cngeno11@gmail.com';
		$result=php_mailer($toEmail,$fromEmail,$CountyName,$my_subject,$my_message,$my_file,$file_path,"Invoice");
		
		/* $MobileNo="+254725463120";
		$SmsText="Invoice No ".$InvoiceNo." Amount: ".$tblTotals;
		sendSMS($MobileNo,$SmsText); */
		
		return $result;			
	}

// function customervehicle($db)
// {
// 	return 'A';
// }

function customervehicles($db,$cosmasRow,$CustomerID,$CustomerName)
	{
	

		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		
		$row=$cosmasRow;	
		$CountyName=$row['CountyName'];		
		$CountyAddress=$row['PostalAddress'];
		$CountyTown=$row['Town'];
		$CountyTelephone=$row['Telephone1'];
		$CountyMobile=$row['Mobile1'];
		$CountyEmail=$row['Email'];
		$CountyPostalCode=$row['PostalCode'];

		
		$ServiceCategoryID='';
		$tblTotals=0;
		$NoOfVehicles=0;
		
		$rsql="select sh.CustomerID,c.PostalAddress,c.PostalCode,c.Town,c.Telephone1,c.Mobile1,c.CustomerName,sh.ServiceID,c.Email,s.ServiceName,
		s.ServiceCategoryID,sg.ServiceGroupID  
			from ServiceHeader sh 
			join Customer c on sh.CustomerID=c.CustomerID
			join Services s on sh.ServiceID=s.ServiceID
			join ServiceCategory sc on s.ServiceCategoryID=sc.ServiceCategoryID
			join ServiceGroup sg on sc.ServiceGroupID=sg.ServiceGroupID
			where sh.ServiceHeaderID=$ApplicationID";
			
		$rresult = sqlsrv_query($db, $rsql);

		//print_r($rsql);exit;	
		
		if ($rrow = sqlsrv_fetch_array( $rresult, SQLSRV_FETCH_ASSOC))
		{
			$CustomerName = $rrow['CustomerName'];
			$ServiceName = $rrow['ServiceName'];
			$InvoiceLineID=$rrow['InvoiceLineID'];
			$Email=$rrow['Email'];
			$CustomerAddress=$rrow['PostalAddress'].' '.$rrow['PostalCode'];
			$CustomerCity=$rrow['Town'];
			$CustomerMobile=$rrow['Telephone1'];
			$ServiceGroupID=$rrow['ServiceGroupID'];
			$ServiceCategoryID=$rrow['ServiceCategoryID'];
		}		
		
		

		$tablestr = '';
		$bankrows='';
		$CreatedDate='';
		
		
			
		$sql=" select cv.RegNo,cv.SittingCapacity,bp.ParkName,mr.RouteName from CustomerVehicles cv
				join BusParks bp on cv.BusParkID=bp.ParkID 
				join MatatuRoutes mr on cv.[Route]=mr.RouteID
				where CustomerID=$CustomerID";

		$tblTotals=0;
		$result=sqlsrv_query($db, $sql);
		while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
		{
			$RegNo=$rw['RegNo'];
			$SittingCapacity=$rw['SittingCapacity'];
			$ParkName=$rw['ParkName'];					
			$RouteName = $rw['RouteName'];
			$NoOfVehicles+=1;
			$tablestr.='<tr>
			<td align="center">'.$RegNo.'</td>
			<td align="center">'.$SittingCapacity.'</td>
			<td>'.$ParkName.'</td>
			<td align="right">'.$RouteName.'</td>				
			</tr>'; 
		}
		
		
		$mpdf=new mPDF('win-1252','A4','','',20,15,48,25,10,10);
		$mpdf->useOnlyCoreFonts = true;    // false is default
		$mpdf->SetProtection(array('print'));
		$mpdf->SetTitle($CustomerName);
		//$mpdf->SetAuthor("Acme Trading Co.");
		//$mpdf->SetWatermarkText("County Government Of Uasin Gishu");
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$html = '
		<html>
		<head>
			<link rel="stylesheet" type="text/css" href="css/my_css.css"/>		
		</head>
		<body>

		<!--mpdf
		<htmlpageheader name="myheader">
		<table width="100%">
		<tr>
			<td align="Center" colspan="2" style="font-size:10mm">
				<b>MATATU SACCO VEHICLES</b>
			</td>
		</tr>		
		</table>
		
		</htmlpageheader>

		<htmlpagefooter name="myfooter">
		<div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
		powered by      <img src="images/attain_logo_2.jpg" alt="County Logo">
		</div>
		</htmlpagefooter>

		<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
		<sethtmlpagefooter name="myfooter" value="on" />
		mpdf-->
		<br/><br/><br/><br/><br/><br/><br/><br/>
		<div style="text-align: right">Date: '.date('jS F Y').'</div>


		<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
		<thead>
		<tr>
			<td>Reg No</td>
			<td>Sitting Capacity</td>
			<td>Bus Park</td>
			<td>Route</td>
		</tr>
		</thead>
		<tbody>
		
		<!-- ITEMS HERE -->'.
		
		
		$tablestr.
										
		'<!-- END ITEMS HERE -->
		
		<tr>
			<td colspan="2" class="blanktotal" rowspan="6"></td>
			<td class="totals">Total:</td>
			<td class="totals">'.number_format($NoOfVehicles,0).'</td>
		</tr>
		
		</tbody>
		</table>
		<br>

		</body>
		</html>
		';
		/* echo $html;
		exit; */
		$mpdf->WriteHTML($html);
 		$mpdf->Output();
		exit;		
}

function check(){
	echo 'sawa';
}

function viewreceipt_2($db,$cosmasRow,$rid,$hid) 
  {
  	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	
	$row=$cosmasRow;	
	$CountyName=$row['CountyName'];		
	$CountyAddress=$row['PostalAddress'];
	$CountyTown=$row['Town'];
	$CountyTelephone=$row['Telephone1'];
	$CountyMobile=$row['Mobile1'];
	$CountyEmail=$row['Email'];
	$CountyPostalCode=$row['PostalCode'];

	$CustomerName='';
	$CustomerCity='';
	$CustomerMobile='';
	$CustomerAddress='';

	$ReceiptID=$rid;
	$ReferenceNumber='';
	$ReceiptDate='';
	$CreatedDate='';
	$InvoiceAmount=0;
	$PaidAmount=0;

	$ReferenceNo=$hid;

	$tblTotals=0;


  	$Amount=0;

  	$iType='';

	$sql="select ServiceHeaderType from vwInvoiceType where InvoiceHeaderID=$hid";
	$result=sqlsrv_query($db,$sql);
	while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
		$iType=$row['ServiceHeaderType'];
	}

	$sql="select top 1 PosReceiptID from InvoiceLines where invoiceheaderid=$hid and PosReceiptID is not null";
	$result=sqlsrv_query($db,$sql);
	while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
		$iType=6;		
	}


// echo $iType;
// exit;
	

  	$sql="select rl2.Amount,r.Amount ReceiptAmount,r.ReferenceNumber,r.ReceiptDate,rl2.ServiceID,s.ServiceName, c.CustomerName,c.Town,c.Mobile1,c.PhysicalAddress,
			(select sum(amount) from InvoiceLines where InvoiceHeaderID=rl2.InvoiceHeaderID) InvoiceAmount,
			(select sum(amount) from ReceiptLines where InvoiceHeaderID=rl2.InvoiceHeaderID) PaidAmount, 
			isnull(ag.FirstName+ ' '+ ag.MiddleName+' '+ ag.LastName,'') CreatedBy,r.CreatedDate,sh.ServiceHeaderID,
			(select distinct Description from InvoiceLines  where InvoiceHeaderID=rl2.InvoiceHeaderID and isnull(Description,'')<>'') [Description]
			from receipts r 
			join ReceiptLines2 rl2 on rl2.ReceiptID=r.ReceiptID 
			join invoicelines  il on il.Invoiceheaderid=rl2.Invoiceheaderid 
			join InvoiceHeader ih on il.InvoiceHeaderID=ih.InvoiceHeaderID
			join ServiceHeader sh on sh.ServiceHeaderID=ih.ServiceHeaderID 
			join Services s on rl2.ServiceID=s.ServiceID 
			join Customer c on sh.CustomerID=c.CustomerID 
			left join Agents ag on r.CreatedBy=ag.AgentID 
			where rl2.InvoiceHeaderID=$hid and r.ReceiptID=$rid and r.Status=1";
  	

  	// echo $sql;
  	// exit;

	$result=sqlsrv_query($db,$sql);
	while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
		$Amount=$row['Amount'];	
		$InvoiceAmount=$row['InvoiceAmount'];
		$PaidAmount=$row['PaidAmount'];
		$ReceiptAmount=$row['ReceiptAmount'];			
		$ReferenceNo=$hid;		
		$ReferenceNumber=$row['ReferenceNumber'];
		$ReceiptDate=$row['ReceiptDate'];
		$CreatedDate=$row['CreatedDate'];
		$CreatedBy=$row['CreatedBy'];
		$Description=$row['Description'];
		$ServiceID=$row['ServiceID'];
		$ServiceName=$row['ServiceName'];
		$CustomerName=$row['CustomerName'];
		$CustomerCity=$row['Town'];
		$CustomerMobile=$row['Mobile1'];
		$CustomerAddress=$row['PhysicalAddress'];
		$ServiceHeaderID=$row['ServiceHeaderID'];

		$ServiceName.=$Description;
  		$Description=$ServiceName;

  		$tblTotals+=$Amount;

		$tablestr.='<tr>
				<td align="center">'.$ReferenceNo.'</td>
				<td align="center">'.date_format(date_create($ReceiptDate),"d/m/Y").'</td>
				<td>'.$ServiceName.'</td>
				<td align="right">'.number_format($Amount,2).'</td>				
				</tr>'; 	
	}

	// echo $sql;
	// exit;
  	$tablestr.='<tr>
					<td colspan="2" class="blanktotal" rowspan="6"></td>
					<td class="totals">Total:</td>
					<td class="totals">'.number_format($tblTotals,2).'</td>
				</tr>';
  	$Balance=0;
  	if($iType=='1')//land
  	{
  		$sql="select li.upn,l.Balance 
			from LandInvoices li join Land l on li.upn=l.upn  
			where li.InvoiceHeaderID=$hid";
		$result=sqlsrv_query($db,$sql);
		while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
		{
			$Balance=$row['Balance'];
		}		
  	}else if($iType=='2')//house
  	{
  		$sql="select hi.EstateID,hi.HouseNumber,tn.Balance,hr.Balance RBalance 
  								from HouseInvoices hi
  								join ReceiptLines rl on rl.InvoiceHeaderID=hi.InvoiceHeaderID
  								join Receipts r on rl.ReceiptID=r.ReceiptID 
  								join Tenancy tn on hi.HouseNumber=tn.HouseNumber and hi.EstateID=tn.EstateID 
  								left join HouseReceipts hr on hr.DocumentNo=r.referencenumber and hr.HouseNumber=hi.HouseNumber 
  								where hi.InvoiceHeaderID=$hid and r.ReceiptID=$rid";

		$result=sqlsrv_query($db,$sql);
		while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
			$HouseNumber=$row['HouseNumber'];
			$EstateID=$row['EstateID'];
			$Balance=$row['Balance'];
		}
  	}else if($iType=='5')//Miscellaneous
  	{
  		$sql="SELECT CustomerName FROM Miscellaneous WHERE ServiceHeaderID=$ServiceHeaderID";

		$result=sqlsrv_query($db,$sql);
		while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
			$CustomerName=$row['CustomerName'];		
			$CustomerAddress='';
			$CustomerMobile	='';
		}
		$Balance=$InvoiceAmount-$PaidAmount;
  	}else{
  		$Balance=$InvoiceAmount-$PaidAmount;
  	}


  	$sql="select CustomerName from Miscellaneous where ServiceHeaderID=$ServiceHeaderID";
	$s_result = sqlsrv_query($db, $sql,$params,$options);

	$rows=sqlsrv_num_rows($s_result);

	if($rows>0){
		while($rows=sqlsrv_fetch_array($s_result,SQLSRV_FETCH_ASSOC))
		{
			$CustomerName=$rows['CustomerName'];
			$CustomerMobile='';
			$CustomerAddress='';			
		}
	}


  	//createreceipt
  	$ServiceAmount=$Amount;		
		
		createBarCode($ReceiptID);
		
		$mpdf=new mPDF('win-1252','A4','','',20,15,48,25,10,10);
		$mpdf->useOnlyCoreFonts = true;    // false is default
		$mpdf->SetProtection(array('print'));
		$mpdf->SetTitle($CustomerName."- Receipt");
		$mpdf->SetAuthor($CustomerName);
		$mpdf->SetWatermarkText($CountyName);
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$html = '
		<html>
		<head>
			<link rel="stylesheet" type="text/css" href="css/my_css.css"/>		
		</head>
		<body>

		<!--mpdf
		<htmlpageheader name="myheader">
		<table width="100%">
			
		<tr>
			<td align="Center" colspan="2">
				<img src="images/logo1.png" alt="County Logo">
			</td>
		</tr>
		<tr>
			<td align="Center" colspan="2" style="font-size:5mm">
				<b>PAYMENT RECEIPT</b>
			</td>
		</tr>				
		<tr>
			<td width="50%" style="color:#0000BB;">
				P.O Box: '.$CountyAddress.'-'.$CountyPostalCode.'<br />
				'.$CountyTown.'<br /> 
				Telephone: '.$CountyTelephone.'</td>
			<td width="50%" style="text-align: right;">			
			Receipt No.<br/><span style="font-weight: bold; font-size: 12pt;">'.$ReceiptID.'</span> <br/>			
			Reference No.<br/><span style="font-weight: bold; font-size: 12pt;">'.$ReferenceNumber.'</span><br/>
			Receipt Amount.<br/><span style="font-weight: bold; font-size: 12pt;">'.number_format($ReceiptAmount,2).'</span><br/>
			</td>
		</tr></table>
		
		</htmlpageheader>

		<htmlpagefooter name="myfooter">
		
		<div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
		<small>This document is computer generated and is not therefore signed. It is a valid document issued under the authority of the County Government of Uasin Gishu County.</small><br>
		powered by      <img src="images/attain_logo_2.jpg" alt="County Logo">
		</div>
		</htmlpagefooter>

		<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
		<sethtmlpagefooter name="myfooter" value="on" />
		mpdf-->
		<br/><br/><br/><br/><br/><br/><br/><br/><br/>
		<div style="text-align: right">Date Receipted: '.date('d/m/Y',strtotime($CreatedDate)).'</div>
		
		<table width="100%" style="font-family: serif;" cellpadding="10">
		<tr>
			<td width="45%" style="border: 0.1mm solid #888888;">
				<span style="font-size: 7pt; color: #555555; font-family: sans;">RECEIPT TO:</span><br /><br />'.$CustomerName.'<br />'.$CustomerAddress.'<br />'.$CustomerCity.'<br />'.$CustomerMobile.'
			</td>
			<td width="10%">&nbsp;</td>
			<td width="45%"></td>
		</tr>
		</table>


		<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
		<thead>
		<tr>
			<td>REF. NO.</td>
			<td>DEPOSIT DATE</td>
			<td>INVOICE DETAILS</td>
			<td>AMOUNT</td>
		</tr>
		</thead>
		<tbody>
		
		<!-- ITEMS HERE -->'.
		
		
		$tablestr.
										
		'<!-- END ITEMS HERE -->
		
		
		
		</tbody>
		</table>
		<br>
		<div style="text-align: center;">
			<img src="images/Bar_Codes/'.$ReceiptID.'.PNG">
		</div>
		<div style="text-align: center;">
			<br>Received By: <i>'.strtolower($CreatedBy).'</i>
			<br>Receipt Date: <i>'.date('d/m/Y',strtotime($CreatedDate)).'</i> Time: '.date('H:i',strtotime($CreatedDate)).'
		</div>		
		</body>
		</html>
		';



		/* echo $html;
		exit; */
		$mpdf->WriteHTML($html);
 		$mpdf->Output();
		exit;
    	
		return;

    
  }

  function createDNotice($db,$row,$ID,$PerWhat,$LocalAuthorityID)
  {

  		if($PerWhat=="3"){
  			$sql="select count(*)/500+1 pages from land where upn='$ID'";
  		}else if($PerWhat=="2"){
  			$sql="select count(*)/500+1 pages from land where lrn='$ID' and LocalAuthorityID='$LocalAuthorityID'";
  		}else{
  			$sql="select count(*)/500+1 pages from land where FirmID='$ID' and LocalAuthorityID='$LocalAuthorityID'";
  		}

		// echo $sql;
		// exit;

		$qry_result=sqlsrv_query($db,$sql);	
		if($qry_result){
			while ($rrow = sqlsrv_fetch_array($qry_result,SQLSRV_FETCH_ASSOC)) 
			{				
		 		$pages=$rrow['pages'];
		 		
				for ($k=0;$k<=$pages;$k++)
				{
					createDemandNotice($db,$row,$ID,$k,$PerWhat,$LocalAuthorityID);
				}
			}
			return 'sawa';
		}else{
			DisplayErrors();
		}

		//ECHO 'SAWA';
		return "fdfd";	
  }
	
	function createDemandNotice($db,$row,$ID,$page,$PerWhat,$LocalAuthorityID)
		{

		$CustomerName = '';
		$ServiceName = '';
		$ServiceAmount = '';	
		$InvoiceHeaderID='';	
		$CountyName=$row['CountyName'];
		$CountyAddress=$row['PostalAddress'];
		$CountyTown=$row['Town'];
		$CountyTelephone=$row['Telephone1'];
		$CountyMobile=$row['Mobile1'];
		$CountyEmail=$row['Email'];	
		$CountyPostalCode=$row['PostalCode'];
		$PlotNo="";

		$count=0;
		
		$PermitNo='';
		$BusinessID="";
		$CustomerID="";
		$Validity="";
		$Expiry="";
		$ExpityDate="";
		$CustomerName="";
		$BusinessName="";
		$ServiceName="";
		$ServiceCost="";
		$ServiceCost_Words="";
		$PostalAdress="";
		$PhysicalAddress="";
		$PostalCode="";
		$Penalty2="";
		$Vat="";
		$PIN="";
		$Town="";
		$PenaltyBalance2=0;
		$CurrentYear=0;



		$header='<html 
					<head>
						<link rel="stylesheet" href="css/my_css.css" type="text/css"/>			
					</head>			
					<body>';
		$body='';
		$tail='</body>
			</html>';


		$sqlb="select BankName,AccountNumber,Branch from Banks where active=1 and bankid=1";
		$bnkr=sqlsrv_query($db,$sqlb);
		while($bnks=sqlsrv_fetch_array($bnkr,SQLSRV_FETCH_ASSOC))
		{
			$bankrows.='<tr>
				<td>'.sentence_case($bnks['BankName']).'</td>
				<td> Account No. <b>'.sentence_case($bnks['AccountNumber']).'</b> Branch: '. $bnks['Branch'] .'  Referring to Your <b>Invoice Number</b></td>
				</tr>
			';
		}
			
		$sql="exec splands '$ID',$page,500,$PerWhat,'$LocalAuthorityID'";
		 //echo $sql; exit;

		$qry_result=sqlsrv_query($db,$sql);	

		if(!$qry_result)
		{		
			DisplayErrors();
		}

			while ($rrow = sqlsrv_fetch_array($qry_result,SQLSRV_FETCH_ASSOC)) {
				
			$upn=$rrow['upn'];
			$lrn=$rrow['lrn'];
			$plotno=$rrow['plotno'];
			$LocalAuthorityID=	$rrow['LocalAuthorityID'];
			$BlockNo=$rrow['lrn'];
			$plotno=$rrow['plotno'];
			$FirmID=$rrow['FirmID'];
			$farmName=$rrow['FirmName'];
			$Owner=$rrow['LaifomsOwner'];
			$RatesPayable=$rrow['RatesPayable'];
			$CurrentYear=$rrow['CurrentYear'];
			$RatesBalance=$rrow['Arrears'];
			$Othercharges=$rrow['Othercharges'];
			$OtherChargesBalance=$rrow['OtherChargesBalance'];
			$GroundRent=$rrow['GroundRent'];
			$GroundRentBalance=$rrow['GroundRentBalance'];				
			$PenaltyBalance=$rrow['PenaltyBalance'];
			$PenaltyBalance2=$rrow['Penalty2'];	
			$GrandTotal=$rrow['Balance'];			
			// $GrandTotal=(double)$PenaltyBalance+(double)$GroundRentBalance+(double)$OtherChargesBalance+(double)$RatesBalance+(double)$CurrentYear;			
			

		$printDate=date('d/m/Y');

		$date = new DateTime('now');
		$date->modify('last day of this month');
		$dueDate= $date->format('Y-m-d');

		//exit;

		$bankrows='';

		$bankrows.='<tr>
				<td>MPESA PAYBILL</td>
				<td>Business No: <B>646464</B>, Account Number: Your <b>Invoice Number</b></td>
				</tr>
			';

		


		// echo $sql; exit;
		$mpdf=new mPDF('win-1252','A4-L','','',20,15,48,25,10,10);
		$mpdf->useOnlyCoreFonts = true;    // false is default
		$mpdf->debugfonts = true; 
		$mpdf->SetProtection(array('print'));
		$mpdf->SetTitle($CountyName."- D Notice");
		$mpdf->SetAuthor($CountyName);		
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');
		$mpdf->useSubstitutions=false;
		$mpdf->simpleTables = true;


		//$body.='<table><tr><td>The Table</td></tr></table>';
		$body.='
				<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; border-top:thick; " cellpadding="1">
					<thead>
						<tr>
							<td align="Center" colspan="5" style="font-size:4mm">
								<b>DEMAND NOTICE</b>
							</td>
						</tr>
						<tr>
							<td align="Center" colspan="5" border-bottom-width: 0px;>
							<img src="images/logo1.png" alt="County Logo"><br><b>'.$CountyName.'</b><br>P.O BOX: '.$CountyAddress.' - '.$CountyPostalCode.',<br/> '.$CountyTown.'<BR>
								Telephone: '.$CountyTelephone.'<BR>  
								Mobile: '.$CountyMobile.'
							</td>
						</tr>																														
					</thead>					
						<tr>
							<td align="Center" colspan="5" style="font-size:4mm"><b>THE RATING ACT (CAP 267)</b></td>
						</tr>
						<tr>
							<td colspan="2" style="border-right:0mm;border-top:0.1mm solid #000000">U.P.N: '.$upn.'</td>
							<td style="border-left:0mm;border-right:0mm; border-top:0.1mm solid #000000">Block No: '.$lrn.'</td>
							<td colspan="2" style="border-left:0mm; border-top:0.1mm solid #000000"> Plot No: '.$plotno.'</td>
						</tr>
						<tr>
							<td colspan="2" style="border-right:0mm;">Division: '.$farmName.'</td>
							<td style="border-left:0mm;border-right:0mm;"></td>
							<td colspan="2" style="border-left:0mm;"> '.$FirmName.'</td>
						</tr>
						<tr>
							<td colspan="2" style="border-right:0mm;">Rate Payer: <B>'.$Owner.'</B></td>
							<td colspan="3" style="border-left:0mm;">Print Date '. $printDate .'</td>						
						</tr>
						<tr>
							<td colspan="2" style="border-right:0mm;">P.O Box: </td>
							<td style="border-right:0mm;border-left:0mm">Postal Code: </td>
							<td colspan="2" style="border-left:0mm;"> Town: </td>
						</tr>
						<tr>
							<td colspan="5" style="border-left:0mm;border:right:0mm;border-top:0.1mm solid #000000;" align="center">
							Tabulated Below, please find a breakdown of your outstanding plot rate bills</td>
						</tr>
					<THEAD>
						<tr>
							<td align="Center" colspan="5" style="font-size:4mm">
							<b>DETAIL OF CHARGES</b>
							</td>
						</tr>
					</THEAD>
						<tr>
							<td colspan="5" style="border-left:0mm;border-right:0mm;">
								<table width="100%">
									<tr>
										<td style="border-left:0mm;border-right:0mm;" align="center">Land Rates<br>Arrears</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">Ground Rent<br>Arrears</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">Accumulated<br>Penalty</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">Annual Rates Payable</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">Annual<br>Ground Rent</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">Due Date</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">Other Charges</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">Outstanding<br>Balance</td>
									</tr>
									<tr>
										<td style="border-left:0mm;border-right:0mm;" align="center">'.number_format($RatesBalance,2).'</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">'.number_format($GroundRentBalance,2).'</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">'.number_format($PenaltyBalance,2).'</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">'.number_format($RatesPayable,2).'</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">'.number_format($GroundRent,2).'</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">'.$dueDate.'</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">'.number_format($Othercharges,2).'</td>
										<td style="border-left:0mm;border-right:0mm;" align="center">'.number_format($GrandTotal,2).'</td>
									</tr>
									<tr>
										<td style="border-left:0mm;border-right:0mm;" colspan="7" align="right">TOTAL AMOUNT DUE (KSh)</td>								
										<td style="border-left:0mm;border-top:0.1mm solid #000000;border-right:0mm;border-bottom-style: solid #000000" align="center" coslspan="2">'.number_format($GrandTotal,2).'</td>									
									</tr>
									<tr>
										<td style="border-left:0mm;border-right:0mm;" colspan="3">Annual Rates: '.number_format($CurrentYear,2).'</td>									
										<td style="border-left:0mm;border-right:0mm;" align="center" colspan="2">Ground Rent: '.number_format($GroundRent,2).'</td>
										<td style="border-left:0mm;border-right:0mm;" align="center" >Other Charges: '.number_format($Othercharges,2).'</td>
										<td style="border-left:0mm;border-right:0mm;" align="center" coslspan="2">Total '.number_format($GrandTotal,2).'</td>									
									</tr>
								</table>
							<td>
						</tr>					
						<thead>
						<tr>
							<td Colspan="5" style="text-align:justified;"> 
							<small>Please note that the outstanding amount is payable to the '.$CountyName.'. The total outstanding amount shall continue to accrue late payment penalty at a rate of (3.00%) per month</small>
							</td>
						</tr>
					</thead>
				</table>

		<div style="font-style: italic; font-size: 10;">
							<b>payment due by '.$dueDate.'</b><br>
							<U>PAYMENT METHODS</b><br>
							<table width="75%" style="font-family: serif; font-size: 11;">'
							.$bankrows.
							'</table><br>							
		</div>
';

}
$html=$header.$body.$tail;
//createPDF($header,$body,$tail);

// echo $html; 
// exit;


$mpdf->WriteHTML($html);

// $mpdf->Output();
// exit;

if($PerWhat=='3'){
	$PermitNo=$upn;
}else if($PerWhat=='1'){
	$PermitNo=$farmName;
}else{
	$PermitNo='Block '.$ID;
}

$mpdf->Output('pdfdocs/DemandNotices/'.$PermitNo.'-'.$page.'.pdf','F');
return;
}

function createPDF($header,$body,$tail){

	$mpdf=new mPDF('win-1252','A4-L','','',20,15,48,25,10,10);
	$mpdf->useOnlyCoreFonts = true;    // false is default
	$mpdf->debugfonts = true; 
	$mpdf->SetProtection(array('print'));
	
	$mpdf->SetTitle($CountyName."- Invoice");

	$mpdf->SetAuthor($CountyName);		
	$mpdf->showWatermarkText = true;
	$mpdf->watermark_font = 'DejaVuSansCondensed';
	$mpdf->watermarkTextAlpha = 0.1;
	$mpdf->SetDisplayMode('fullpage');
	$mpdf->WriteHTML($html);

	// $mpdf->Output();
	// exit;

	$PermitNo=$farmName;// $LocalAuthorityID.'-'.$FirmID.'-'.$upn;

	$mpdf->Output('pdfdocs/DemandNotices/'.$PermitNo.'.pdf','F');	

}

function resendPermit($db, $PermitNo,$cosmasRow)
{
//exit($PermitNo);
$row=$cosmasRow;
$CustomerName = '';
$ServiceName = '';
$ServiceAmount = '';	
$InvoiceHeaderID='';	
$CountyName=$row['CountyName'];
$CountyAddress=$row['PostalAddress'];
$CountyTown=$row['Town'];
$CountyTelephone=$row['Telephone1'];
$CountyMobile=$row['Mobile1'];
$CountyEmail=$row['Email'];	
$CountyPostalCode=$row['PostalCode'];
$PlotNo="";

$CountyName='County Government of Uasin Gishu';


$BusinessID="";
$CustomerID="";
$Validity="";
$Expiry="";
$IssueDate="";
$ExpityDate="";
$CustomerName="";
$BusinessName="";
$ServiceName="";
$ServiceCost="";
$ServiceCost_Words="";
$PostalAdress="";
$PhysicalAddress="";
$PostalCode="";
$Vat="";
$PIN="";
$Town="";
$Printed='';


//get the details for this application

$sql = "select distinct sh.ServiceHeaderID,p.PermitNo,sh.ServiceID,p.Validity,p.IssueDate,p.ExpiryDate,p.Printed,
ih.InvoiceHeaderID, ih.CustomerID,ih.InvoiceDate,ih.Paid,
c.CustomerName,c.Mobile1,c.BusinessID,c.BusinessRegistrationNumber,C.CustomerID,c.PostalAddress,c.PhysicalAddress,c.Telephone1,c.Telephone2,c.PostalCode,c.VatNumber,c.PIN,c.Town,c.Email,
s.ServiceName,
il.Amount,a.FirstName+' '+a.MiddleName+' '+a.LastName IssuedBy

from InvoiceHeader ih
join InvoiceLines il on il.InvoiceHeaderID=ih.InvoiceHeaderID
join ServiceHeader sh on il.ServiceHeaderID=sh.ServiceHeaderID
join Customer c on sh.CustomerID=c.CustomerID	
join Services s on sh.ServiceID=s.ServiceID and  il.ServiceID=sh.ServiceID			
join Permits p on p.ServiceHeaderID=sh.ServiceHeaderID  AND P.InvoiceHeaderID=IL.InvoiceHeaderID
left join Agents a on p.CreatedBy=a.AgentID
where p.PermitNo = '$PermitNo'";

//exit($sql);
//AND P.InvoiceHeaderID=IL.InvoiceHeaderID

$qry_result=sqlsrv_query($db,$sql);	

if (($rrow = sqlsrv_fetch_array($qry_result,SQLSRV_FETCH_ASSOC))==false)
{
	$sql="Update Permits set Printed=5 where ServiceHeaderID=$ApplicationID";
	$result=sqlsrv_query($db,$sql);

	DisplayErrors();

	return "Issue Here";
}else
{

$ApplicationID=$rrow['ServiceHeaderID'];
$BusinessRegNo=$rrow['BusinessRegistrationNumber'];
$PermitNo=$rrow['PermitNo'];
$BusinessID=$rrow['BusinessID'];
$CustomerID=$rrow['CustomerID'];
$Validity=$rrow['Validity'];
$InvoiceHeaderID=$rrow['InvoiceHeaderID'];
$IssueDate=$rrow['IssueDate'];
$Expiry=$rrow['ExpiryDate'];
$ExpiryDate=$rrow['ExpiryDate'];
$CustomerName=$rrow['CustomerName'];
$BusinessName=$rrow['CustomerName'];
$ServiceName=$rrow['ServiceName'];
$ServiceCost=$rrow['Amount'];
$PostalAdress=$rrow['PostalAddress'];
$Telephone1=$rrow['Telephone1'];
$Telephone2=$rrow['Telephone2'];
$CustomerEmail=$rrow['Email'];
$PostalCode=$rrow['PostalCode'];
$PIN=$rrow['PIN'];
$Vat=$rrow['VatNumber'];
$Town=$rrow['Town'];
$IssuedBy=$rrow['IssuedBy'];
$MobileNo=$rrow['Telephone1'];
$Printed=$rrow['Printed'];

$IssueDate=date_create($IssueDate);
$IssueDate=date_format($IssueDate,"Y/m/d H:i");

$ServiceCost_Words=convertNumber($ServiceCost);				
}



//get the receipts used
$receipts='';
$sql="select r.ReferenceNumber 
		from receipts r join ReceiptLines rl on rl.ReceiptID=r.ReceiptID 
		where rl.InvoiceHeaderID=$InvoiceHeaderID";

		//echo $sql; exit;

$result=sqlsrv_query($db,$sql);
if($result)
{	
	while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
	{
		if(!$receipts==''){
			$receipts.=';'.$row['ReferenceNumber'];
		}else{
			$receipts=$row['ReferenceNumber'];
		}
	}
}else{
	DisplayErrors();
}

$pmntInfo="ApplicationNo: ".$ApplicationID."; InvoiceNo: ".$InvoiceHeaderID."; Receipts: ".$receipts;

//$Validity='2016';
$mdate=date_create($Expiry);
$Expiry=date_format($mdate,"d/m/Y");
$Validity=date_format($mdate,'Y');
$PostalTown='';

//echo $PermitNo;

$rsql="select sh.CustomerID,c.CustomerName BusinessName,c.PostalAddress,c.PhysicalAddress,c.PostalCode,sh.ServiceID,s.ServiceName,s.ServiceCode, 
il.ServiceHeaderID,il.ServiceHeaderID,il.Amount,ih.InvoiceHeaderID,c.Email,fd.Value BDescription,wd.WardName
,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName CustomerName  
from invoiceLines il 
inner join InvoiceHeader ih on il.InvoiceHeaderID=ih.InvoiceHeaderID 
inner join ServiceHeader sh on	il.ServiceHeaderID=sh.ServiceHeaderID 
inner join Services s on sh.ServiceID=s.ServiceID and il.ServiceID=sh.ServiceID
inner join Customer c on sh.CustomerID=c.CustomerID
left join Wards wd on c.Ward=wd.WardID
inner join Permits p on p.InvoiceHeaderID=ih.InvoiceHeaderID 
join FormData fd on fd.ServiceHeaderID=sh.ServiceheaderID
join CustomerAgents ca on c.CustomerID=ca.CustomerID
join Agents ag on ca.AgentID=ag.AgentID
where fd.FormColumnID=5 and p.PermitNo='$PermitNo'";

$rresult = sqlsrv_query($db, $rsql);	


if ($rrow = sqlsrv_fetch_array( $rresult, SQLSRV_FETCH_ASSOC))
{
	$CustomerName = $rrow['CustomerName'];
	$ServiceName = $rrow['ServiceName'];
	$ServiceAmount = $rrow['Amount'];	
	$InvoiceHeaderID=$rrow['InvoiceHeaderID'];	
	$Email=$rrow['Email'];
	$BDescription=$rrow['BDescription'];
	$ServiceCode=$rrow['ServiceCode'];
	$PostalAddress=$rrow['PostalAddress'];
	$PostalTown=$rrow['Town'];
	$WardName=$rrow['WardName'];
	$PostalCode=$rrow['PostalCode'];
	$PhysicalAddress=$rrow['PhysicalAddress'];
}		
//exit($rsql);
$PlotNo="";
$sql="select
(select distinct Value from fnFormData ($ApplicationID) where formcolumnid=12242) PlotNo,
(select distinct Value from fnFormData ($ApplicationID) where formcolumnid=12243)VatNo,
(select distinct Value  from fnFormData ($ApplicationID) where formcolumnid=13288)Building,
 (select distinct Value  from fnFormData ($ApplicationID) where formcolumnid=13289)Floor,
 (select distinct Value  from fnFormData ($ApplicationID) where formcolumnid=13290)Room,
 (select distinct Value  from fnFormData ($ApplicationID) where formcolumnid=123)Road";

$result=sqlsrv_query($db,$sql);
while($rww=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
{
	$PlotNo=$rww['PlotNo'];
	$Vat=$rww['VatNo'];
	$Building=$rww['Building'];
	$Floor=$rww['Floor'];
	$Room=$rww['Room'];	
	$Road=$rww['Road'];	
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

createBarCode($PermitNo);	

$mpdf=new mPDF(['format' => 'Legal']);
$mpdf->useOnlyCoreFonts = true;    // false is default
$mpdf->debugfonts = true; 
$mpdf->SetProtection(array('print'));
$mpdf->SetTitle($CountyName."- Invoice");
$mpdf->SetAuthor($CountyName);

// if($Printed==true){
// 	$mpdf->SetWatermarkText('COPY');
// }else{
	$mpdf->SetWatermarkText($CountyName);
//}

$mpdf->showWatermarkText = true;
$mpdf->watermark_font = 'DejaVuSansCondensed';
$mpdf->watermarkTextAlpha = 0.1;
$mpdf->SetDisplayMode('fullpage');

$html='<html 
<head>
<link rel="stylesheet" href="css/my_css.css" type="text/css"/>			
</head>			
<body>
<hr>
<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; border-top:thick; " cellpadding="1">
<tr>
<td align="Center" colspan="5" style="font-size:10mm; border-top:thick;">
<b>SINGLE BUSINESS PERMIT</b>
</td>
</tr>
<tr>
<td align="Center" colspan="5">
<img src="images/logo1.png" alt="County Logo">
</td>
</tr>					
<tr>
<td style="border-right:0pt"></td>
<td colspan="3" align="Center"><span style="font-weight: bold; font-size: 14pt;">'.$CountyName.'</span></td>
<td><span style="font-weight: bold; font-size: 14pt;">'.$Validity.'</span></h3></td>
</tr>
<tr>
<td colspan="5" align="Center"><span style="font-weight: bold; font-size: 14pt;">
<br>
GRANTS THIS BUSINESS PERMIT <BR>
TO
</span></td>
</tr>
<thead>
<tr>							
<td colspan="5"><B>'.$BusinessName.'</B></td>
</tr>
<tr>
<td colspan="2">Certificate of Registration NO./ID No.: <br>'.$BusinessRegNo.'</td>
<td width=20%>Business ID No:'.$BusinessID.'</td>
<td>PIN No.: '.$PIN.'</td>
<td>VAT No.: '.$Vat.'</td>
</tr>
</thead>
<tr>
<td colspan="5" align="center">
	<br><p><strong>To engage in the Activity/Business/Profession or Occupation of:</strong></p><br><br>									
</td>
</tr>
<thead>
<tr>
<td align="left" colspan="3"><strong>Business Activity Code & Description:</strong><br>('.$ServiceCode.') '.$ServiceName.'</td>
<td align="right" colspan="2"><strong>Detailed Activity Description:</strong><br>'.$BDescription.'</td>
</tr>
</thead>	
<tr>
<td colspan="5" align="center">
<br><p><strong>Having Paid a Single Business Permit Fee of:</strong></p><br><br>
</td>					
</tr>
<tr>
<td></td> 
<td colspan="3"  align="center" style="background-color: #BEBABA; font-size:5mm">(KSh.)<br>'.number_format($ServiceCost,2).'<br>('.$ServiceCost_Words.' Only)</td>
<td></td> 
</tr>
<tr>
<td></td> 
<td colspan="3"  align="center" style="background-color: #BEBABA"> <i>'.$pmntInfo.'</i></td>
<td></td> 
</tr>
<thead>
<tr>
<td>P.O. Box <br> '.$PostalAddress.'</td>
<td>Postal Code <br> '.$PostalCode.'</td>
<td>Town <br> '.$PostalTown.'</td>
<td>Business Physical Address<br> '.$PhysicalAddress.'</td>
<td>Plot No. <br> '.$PlotNo.'</td>
</tr>
<tr>
<td>Ward<br> '.$WardName.'</td>
<td>Road/Street <br> '.$Road.'</td>
<td>Building <br> '.$Building.'</td>
<td>Floor <br> '.$Floor.'</td>
<td>Room<br> '.$Room.'</td>
</tr>
<tr>
<td><strong>Mobile No</strong> <br> '.$Telephone1.'</td>
<td><strong>Telephone</strong> <br> '.$Telephone2.'</td>
<td><strong>Fax</strong> <br> '.$Fax.'</td>
<td colspan="2" align="left"><strong>Email Address</strong><br> '.$CustomerEmail.'</td>						
</tr>
</thead>
<tr>
	<td colspan="2"><strong>Validity Period </strong>'.$Validity.'</td>
	<td  align="center"><strong>Issue Date:</strong><br>'.$IssueDate.'</td>
	<td colspan="2" align="center"><strong>Expiry Date:</strong><br>'.$Expiry.'</td>
</tr>
<tr>
	<td colspan="2"><strong>Approved By:</strong><br>'.$IssuedBy.'</td>	
	<td></td>						
	<td colspan="2"></td>
</tr>
<tr>
<td colspan="2"><br><strong>For The Chief Officer<br>Trade & Industrialization</strong></td>
<td></td>
<td colspan="2"><br><strong><br></td>
</tr>
<tr>
<td colspan="5"><hr></td>
</tr>
<tr>						
<td colspan="5" align="center"><img src="Images/Bar_Codes/'.$PermitNo.'.PNG"></td>
</tr>					
<thead>
<tr>
<td Colspan="5" style="text-align:justified;"> 
<small><strong>Notice:</strong> Granting this permit does not exempt the business identified above from
	complying with the current regulations on Health and Safety as established by the Government of Kenya 
	and the '.$CountyName.'.</small>
</td>
<tr>
<td Colspan="5" style="text-align:justified;"> 
<small><strong>Disclaimer:</strong>  This is a system generated Business Permit and does not require signature.</small>
</td>
</tr>
</thead>
</table>

</body>
</html>';
//echo $html; 
$mpdf->WriteHTML($html);

//$mpdf->Output();

//exit;


$mpdf->Output('pdfdocs/sbps/'.$PermitNo.'.pdf','F');
$msg[0]="0";
$msg[1]="Report Resent Successfully";
return $msg;

}

function renewPermit($db, $ApplicationID,$cosmasRow)
{

$row=$cosmasRow;
$CustomerName = '';
$ServiceName = '';
$ServiceAmount = '';	
$InvoiceHeaderID='';	
$CountyName=$row['CountyName'];
$CountyAddress=$row['PostalAddress'];
$CountyTown=$row['Town'];
$CountyTelephone=$row['Telephone1'];
$CountyMobile=$row['Mobile1'];
$CountyEmail=$row['Email'];	
$CountyPostalCode=$row['PostalCode'];
$PlotNo="";

$CountyName='County Government of Uasin Gishu';


$BusinessID="";
$CustomerID="";
$Validity="";
$Expiry="";
$IssueDate="";
$ExpityDate="";
$CustomerName="";
$BusinessName="";
$ServiceName="";
$ServiceCost="";
$ServiceCost_Words="";
$PostalAdress="";
$PhysicalAddress="";
$PostalCode="";
$Vat="";
$PIN="";
$Town="";
$Printed='';


//get the details for this application

$sql = "select distinct sh.ServiceHeaderID,p.PermitNo,sh.ServiceID,p.Validity,p.IssueDate,p.ExpiryDate,p.Printed,
ih.InvoiceHeaderID, ih.CustomerID,ih.InvoiceDate,ih.Paid,
c.CustomerName,c.Mobile1,c.BusinessID,c.BusinessRegistrationNumber,C.CustomerID,c.PostalAddress,c.PhysicalAddress,c.Telephone1,c.Telephone2,c.PostalCode,c.VatNumber,c.PIN,c.Town,c.Email,
s.ServiceName,
il.Amount,a.FirstName+' '+a.MiddleName+' '+a.LastName IssuedBy

from InvoiceHeader ih
join InvoiceLines il on il.InvoiceHeaderID=ih.InvoiceHeaderID
join ServiceHeader sh on il.ServiceHeaderID=sh.ServiceHeaderID
join Customer c on sh.CustomerID=c.CustomerID	
join Services s on sh.ServiceID=s.ServiceID and  il.ServiceID=sh.ServiceID			
join Permits p on p.ServiceHeaderID=sh.ServiceHeaderID  AND P.InvoiceHeaderID=IL.InvoiceHeaderID
left join Agents a on p.CreatedBy=a.AgentID
where sh.ServiceHeaderID = $ApplicationID  and sh.serviceheaderid not in (Select Serviceheaderid from Miscellaneous) 
and year(p.ExpiryDate)=year(getdate())";

// echo $sql;
// exit;
//AND P.InvoiceHeaderID=IL.InvoiceHeaderID

$qry_result=sqlsrv_query($db,$sql);	

if (($rrow = sqlsrv_fetch_array($qry_result,SQLSRV_FETCH_ASSOC))==false)
{
	$sql="Update Permits set Printed=5 where ServiceHeaderID=$ApplicationID";
	$result=sqlsrv_query($db,$sql);

	DisplayErrors();

	return "Issue Here";
}else
{

$BusinessRegNo=$rrow['BusinessRegistrationNumber'];
$PermitNo=$rrow['PermitNo'];
$BusinessID=$rrow['BusinessID'];
$CustomerID=$rrow['CustomerID'];
$Validity=$rrow['Validity'];
$InvoiceHeaderID=$rrow['InvoiceHeaderID'];
$IssueDate=$rrow['IssueDate'];
$Expiry=$rrow['ExpiryDate'];
$ExpiryDate=$rrow['ExpiryDate'];
$CustomerName=$rrow['CustomerName'];
$BusinessName=$rrow['CustomerName'];
$ServiceName=$rrow['ServiceName'];
$ServiceCost=$rrow['Amount'];
$PostalAdress=$rrow['PostalAddress'];
$Telephone1=$rrow['Telephone1'];
$Telephone2=$rrow['Telephone2'];
$CustomerEmail=$rrow['Email'];
$PostalCode=$rrow['PostalCode'];
$PIN=$rrow['PIN'];
$Vat=$rrow['VatNumber'];
$Town=$rrow['Town'];
$IssuedBy=$rrow['IssuedBy'];
$MobileNo=$rrow['Telephone1'];
$Printed=$rrow['Printed'];

$IssueDate=date_create($IssueDate);
$IssueDate=date_format($IssueDate,"Y/m/d H:i");

$ServiceCost_Words=convertNumber($ServiceCost);				
}



//get the receipts used
$receipts='';
$sql="select r.ReferenceNumber 
		from receipts r join ReceiptLines rl on rl.ReceiptID=r.ReceiptID 
		where rl.InvoiceHeaderID=$InvoiceHeaderID";

		//echo $sql; exit;

$result=sqlsrv_query($db,$sql);
if($result)
{	
	while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
	{
		if(!$receipts==''){
			$receipts.=';'.$row['ReferenceNumber'];
		}else{
			$receipts=$row['ReferenceNumber'];
		}
	}
}else{
	DisplayErrors();
}

$pmntInfo="ApplicationNo: ".$ApplicationID."; InvoiceNo: ".$InvoiceHeaderID."; Receipts: ".$receipts;

//$Validity='2016';
$mdate=date_create($Expiry);
$Expiry=date_format($mdate,"d/m/Y");
$Validity=date_format($mdate,'Y');
$PostalTown='';

//echo $PermitNo;

$rsql="select sh.CustomerID,c.CustomerName BusinessName,c.PostalAddress,c.PhysicalAddress,c.PostalCode,sh.ServiceID,s.ServiceName,s.ServiceCode, 
il.ServiceHeaderID,il.ServiceHeaderID,il.Amount,ih.InvoiceHeaderID,c.Email,fd.Value BDescription,wd.WardName
,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName CustomerName  
from invoiceLines il 
inner join InvoiceHeader ih on il.InvoiceHeaderID=ih.InvoiceHeaderID 
inner join ServiceHeader sh on	il.ServiceHeaderID=sh.ServiceHeaderID 
inner join Services s on sh.ServiceID=s.ServiceID and il.ServiceID=sh.ServiceID
inner join Customer c on sh.CustomerID=c.CustomerID
left join Wards wd on c.Ward=wd.WardID
inner join Permits p on p.InvoiceHeaderID=ih.InvoiceHeaderID 
join FormData fd on fd.ServiceHeaderID=sh.ServiceheaderID
join CustomerAgents ca on c.CustomerID=ca.CustomerID
join Agents ag on ca.AgentID=ag.AgentID
where fd.FormColumnID=5 and p.PermitNo='$PermitNo'";

$rresult = sqlsrv_query($db, $rsql);	


if ($rrow = sqlsrv_fetch_array( $rresult, SQLSRV_FETCH_ASSOC))
{
	$CustomerName = $rrow['CustomerName'];
	$ServiceName = $rrow['ServiceName'];
	$ServiceAmount = $rrow['Amount'];	
	$InvoiceHeaderID=$rrow['InvoiceHeaderID'];	
	$Email=$rrow['Email'];
	$BDescription=$rrow['BDescription'];
	$ServiceCode=$rrow['ServiceCode'];
	$PostalAddress=$rrow['PostalAddress'];
	$PostalTown=$rrow['Town'];
	$WardName=$rrow['WardName'];
	$PostalCode=$rrow['PostalCode'];
	$PhysicalAddress=$rrow['PhysicalAddress'];
}		
//exit($rsql);
$PlotNo="";
$sql="select
(select distinct Value from fnFormData ($ApplicationID) where formcolumnid=12242) PlotNo,
(select distinct Value from fnFormData ($ApplicationID) where formcolumnid=12243)VatNo,
(select distinct Value  from fnFormData ($ApplicationID) where formcolumnid=13288)Building,
 (select distinct Value  from fnFormData ($ApplicationID) where formcolumnid=13289)Floor,
 (select distinct Value  from fnFormData ($ApplicationID) where formcolumnid=13290)Room,
 (select distinct Value  from fnFormData ($ApplicationID) where formcolumnid=123)Road";

$result=sqlsrv_query($db,$sql);
while($rww=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
{
	$PlotNo=$rww['PlotNo'];
	$Vat=$rww['VatNo'];
	$Building=$rww['Building'];
	$Floor=$rww['Floor'];
	$Room=$rww['Room'];	
	$Road=$rww['Road'];	
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

createBarCode($PermitNo);	

$mpdf=new mPDF(['format' => 'Legal']);
$mpdf->useOnlyCoreFonts = true;    // false is default
$mpdf->debugfonts = true; 
$mpdf->SetProtection(array('print'));
$mpdf->SetTitle($CountyName."- Invoice");
$mpdf->SetAuthor($CountyName);

// if($Printed==true){
// 	$mpdf->SetWatermarkText('COPY');
// }else{
	$mpdf->SetWatermarkText($CountyName);
//}

$mpdf->showWatermarkText = true;
$mpdf->watermark_font = 'DejaVuSansCondensed';
$mpdf->watermarkTextAlpha = 0.1;
$mpdf->SetDisplayMode('fullpage');

$html='<html 
<head>
<link rel="stylesheet" href="css/my_css.css" type="text/css"/>			
</head>			
<body>
<hr>
<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; border-top:thick; " cellpadding="1">
<tr>
<td align="Center" colspan="5" style="font-size:10mm; border-top:thick;">
<b>SINGLE BUSINESS PERMIT</b>
</td>
</tr>
<tr>
<td align="Center" colspan="5">
<img src="images/logo1.png" alt="County Logo">
</td>
</tr>					
<tr>
<td style="border-right:0pt"></td>
<td colspan="3" align="Center"><span style="font-weight: bold; font-size: 14pt;">'.$CountyName.'</span></td>
<td><span style="font-weight: bold; font-size: 14pt;">'.$Validity.'</span></h3></td>
</tr>
<tr>
<td colspan="5" align="Center"><span style="font-weight: bold; font-size: 14pt;">
<br>
GRANTS THIS BUSINESS PERMIT <BR>
TO
</span></td>
</tr>
<thead>
<tr>							
<td colspan="5"><B>'.$BusinessName.'</B></td>
</tr>
<tr>
<td colspan="2">Certificate of Registration NO./ID No.: <br>'.$BusinessRegNo.'</td>
<td width=20%>Business ID No:'.$BusinessID.'</td>
<td>PIN No.: '.$PIN.'</td>
<td>VAT No.: '.$Vat.'</td>
</tr>
</thead>
<tr>
<td colspan="5" align="center">
	<br><p><strong>To engage in the Activity/Business/Profession or Occupation of:</strong></p><br><br>									
</td>
</tr>
<thead>
<tr>
<td align="left" colspan="3"><strong>Business Activity Code & Description:</strong><br>('.$ServiceCode.') '.$ServiceName.'</td>
<td align="right" colspan="2"><strong>Detailed Activity Description:</strong><br>'.$BDescription.'</td>
</tr>
</thead>	
<tr>
<td colspan="5" align="center">
<br><p><strong>Having Paid a Single Business Permit Fee of:</strong></p><br><br>
</td>					
</tr>
<tr>
<td></td> 
<td colspan="3"  align="center" style="background-color: #BEBABA; font-size:5mm">(KSh.)<br>'.number_format($ServiceCost,2).'<br>('.$ServiceCost_Words.' Only)</td>
<td></td> 
</tr>
<tr>
<td></td> 
<td colspan="3"  align="center" style="background-color: #BEBABA"> <i>'.$pmntInfo.'</i></td>
<td></td> 
</tr>
<thead>
<tr>
<td>P.O. Box <br> '.$PostalAddress.'</td>
<td>Postal Code <br> '.$PostalCode.'</td>
<td>Town <br> '.$PostalTown.'</td>
<td>Business Physical Address<br> '.$PhysicalAddress.'</td>
<td>Plot No. <br> '.$PlotNo.'</td>
</tr>
<tr>
<td>Ward<br> '.$WardName.'</td>
<td>Road/Street <br> '.$Road.'</td>
<td>Building <br> '.$Building.'</td>
<td>Floor <br> '.$Floor.'</td>
<td>Room<br> '.$Room.'</td>
</tr>
<tr>
<td><strong>Mobile No</strong> <br> '.$Telephone1.'</td>
<td><strong>Telephone</strong> <br> '.$Telephone2.'</td>
<td><strong>Fax</strong> <br> '.$Fax.'</td>
<td colspan="2" align="left"><strong>Email Address</strong><br> '.$CustomerEmail.'</td>						
</tr>
</thead>
<tr>
	<td colspan="2"><strong>Validity Period </strong>'.$Validity.'</td>
	<td  align="center"><strong>Issue Date:</strong><br>'.$IssueDate.'</td>
	<td colspan="2" align="center"><strong>Expiry Date:</strong><br>'.$Expiry.'</td>
</tr>
<tr>
	<td colspan="2"><strong>Approved By:</strong><br>'.$IssuedBy.'</td>	
	<td></td>						
	<td colspan="2"></td>
</tr>
<tr>
<td colspan="2"><br><strong>For The Chief Officer<br>Trade & Industrialization</strong></td>
<td></td>
<td colspan="2"><br><strong><br></td>
</tr>
<tr>
<td colspan="5"><hr></td>
</tr>
<tr>						
<td colspan="5" align="center"><img src="Images/Bar_Codes/'.$PermitNo.'.PNG"></td>
</tr>					
<thead>
<tr>
<td Colspan="5" style="text-align:justified;"> 
<small><strong>Notice:</strong> Granting this permit does not exempt the business identified above from
	complying with the current regulations on Health and Safety as established by the Government of Kenya 
	and the '.$CountyName.'.</small>
</td>
<tr>
<td Colspan="5" style="text-align:justified;"> 
<small><strong>Disclaimer:</strong>  This is a system generated Business Permit and does not require signature.</small>
</td>
</tr>
</thead>
</table>

</body>
</html>';
//echo $html; 
$mpdf->WriteHTML($html);

//$mpdf->Output();

//exit;


$mpdf->Output('pdfdocs/sbps/'.$PermitNo.'.pdf','F');

// $sql="update ServiceHeader set Printed=1,ServiceStatusID=7 where ServiceHeaderID=$ApplicationID";
// $result=sqlsrv_query($db,$sql);

$sql="update Permits set Printed=1 where PermitNo='$PermitNo'";
$result=sqlsrv_query($db,$sql);

//echo $sql;

$MobileNo=$MobileNo;//"0725463120";//$CustomerMobile;
//$MobileNo="0725463120";//$CustomerMobile;
					
$name=explode(" ", $CustomerName);
$fname= ucfirst(strtolower($name[0]));

$cnt=time();

$SmsText="Dear $fname, your permit (No. $PermitNo) is now ready. Please collect the printed copy from the officer who served you. For inquiries please call 0700-646464";

///sendSms($MobileNo,$SmsText);

$sql="Insert Into SMS (MobileNo,Message,Subject) Values ('$MobileNo','$SmsText','Permit')";
$result=sqlsrv_query($db,$sql);




return "Permit No ".$PermitNo." sent to ".$MobileNo;

//send Email
$my_file = $PermitNo.'.pdf';
$my_path = "pdfdocs/sbps/";
$my_name = $CountyName;
$my_mail = $Email;
$my_replyto = $CountyEmail;
$my_subject = "Service Permit";
$my_message="Kindly receive the Permit for your approved Service";

//mail_attachment($my_file, $my_path, $my_mail, $my_replyto, $my_name, $my_replyto, $my_subject, $my_message);
//echo 'before'.'<br>';
$result=php_mailer($my_mail,$CountyEmail,$CountyName,$my_subject,$my_message,$my_file,$my_path,"Permit");
//echo 'after';
return $result;

}


function receiptToInvoice($db,$ReceiptID,$InvoiceHeaderID,$ReferenceNumber,$Amount,$splitamount)
{

$total=0;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
$ReceiptID='';

$ServiceHeaderID=0;

$sql="Select Distinct ServiceHeaderID from InvoiceLines where InvoiceHeaderID='$InvoiceHeaderID'";

$result3=sqlsrv_query($db,$sql,$params,$options);
if($result3)
{

$records=sqlsrv_num_rows($result3);
if($records>0)
{				
while($row=sqlsrv_fetch_array($result3,SQLSRV_FETCH_ASSOC))
{
$ServiceHeaderID=$row['ServiceHeaderID'];

print_r('here'); exit;

$query4="Insert into ReceiptLines (ReceiptID,InvoiceHeaderID,Amount,CreatedBy)
VALUES('$ReceiptID','$InvoiceHeaderID','$Amount','1')";		
$result2 = sqlsrv_query($db, $query4);
if($result2)
{												

}else
{
DisplayErrors();
}

if($result2)
{						
$msg1 = 'Payment Matched';
} else 
{
DisplayErrors();
$msg1 = 'Error in Receipting';
}										
}
}else
{
$msg1="The Invoice Number entered cannot be matched with any Invoice from the county";
}
}else
{
DisplayErrors();
$msg1="Error in the Query";			
}

return $msg1;		
}
function deleteReceipt($db,$ReferenceNumber,$UserID)
{
	

	$sql="select * from receipts where ReferenceNumber='$ReferenceNumber'";
	$result=sqlsrv_query($db,$sql);
	while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
	{
		$DateReceived=date("d/m/Y");
		$ReceiptID=$row['ReceiptID'];
		$ReceiptAmount=(double)$row['Amount']*(-1);
		$PaymentMethod=$row['ReceiptMethodID'];
		$RefNumber=$row['ReferenceNumber'];
		$BankID=$row['BankID'];
		$CreatedBy=$UserID;
		$msg="";
		
		if ($ReceiptAmount=0){
			$msg="The receipt Amount is not set";
		}else if($RefNumber==""){
			$msg="The Reference Number is not set";
		}else if ($PaymentMethod=="0"){
			$msg="The Payment Method is not set";
		}else if($BankID=="0"){
			$msg="The Receiving Bank is not set";
		}else{
			$ReceiptAmount=	(double)$row['Amount']*(-1);
			
			$Result=ReverseMoney($db,$ReceiptID,$UserID);
			$msg=$Result[1];
		}
		
	}

	return $msg;
}
function AdjustPlot($db,$ReferenceNumber,$UserID)
{
	$sql="select la.*,l.LocalAuthorityID,l.LaifomsUPN from LandAdjustments la join land l on la.upn=l.upn where la.referencenumber='$ReferenceNumber'";
	$result=sqlsrv_query($db,$sql);
	while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
	{
		$upn=$row['Upn'] ;
		$Authority=$row['LocalAuthorityID'];
		$LaifomsUPN=$row['LaifomsUPN'];
		$object= $row['object'] ;
		$Amount=$row['Amount'] ;
		$adjustmentDate=$row['adjustmentDate'];
		$Description='Adjustment ('.$row['Description'].')';

		$principalAmount=0;
		$penaltyAmount=0;
		$GroundRentAmount=0;
		$OtherChargesAmount=0;

		$TotalBalance=0;
		$PenaltyBalance=0;
		$GroundRentBalance=0;
		$OtherChargesBalance=0;

		if($object=='Principal'){
			$principalAmount=$Amount;
		}elseif($object=='Penalty'){
			$penaltyAmount=$Amount;
		}elseif($object=='GroundRent'){
			$GroundRentAmount=$Amount;
		}elseif($object=='OtherCharges'){
			$OtherChargesAmount=$Amount;
		}

		$sql="select * from  fnLastPlotRecord ($upn)";

		$query=sqlsrv_query($db,$sql);
		while($row=sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
		{
			$TotalBalance=$row['Balance'];
		}


		$PrincipalBalance+=$principalAmount;
		$PenaltyBalance+=$penaltyAmount;
		$GroundRentBalance+=$GroundRentAmount;
		$OtherChargesBalance+=$OtherChargesAmount;
		$TotalBalance+=$Amount;

		$s_sql="set dateformat dmy insert into LandReceipts (DateReceived, LocalAuthorityID,UPN,LaifomsUPN,[Description],DocumentNo,Amount,Principal,Penalty,GroundRent,OtherCharges,PenaltyBalance,GroundRentBalance,OtherChargesBalance,Balance,CreatedBy,Adjustment) 
				Values(GETDATE(),'$Authority','$upn','$LaifomsUPN','$Description','$ReferenceNumber','$Amount','$principalAmount','$penaltyAmount','$GroundRentAmount','$OtherChargesAmount','$PenaltyBalance','$GroundRentBalance','$OtherChargesBalance','$TotalBalance','$CreatedBy',$Amount)";

		$query=sqlsrv_query($db,$s_sql);
		if($query)
		{
			$sql="update land set balance=$TotalBalance,PenaltyBalance=$PenaltyBalance,GroundRentBalance=$GroundRentBalance,OtherChargesBalance=$OtherChargesBalance where upn=$upn";
			$query=sqlsrv_query($db,$sql);
			if($query)
			{
				$sql="Update ApprovalEntry set ApprovalStatus=1 where DocumentNo='$ReferenceNumber'";
				$result=sqlsrv_query($db,$sql);

				$rst=SaveTransaction($db,$UserID,"Adjusted Plot statement for the Plot Upn No. ".$upn); 

				$feedBack[0]=1;
				$feedBack[1]="Adjustment Successful";
			}else{
				DisplayErrors();
				$feedBack[0]=0;
				$feedBack[1]="Adjustment Not Successful";
			}
			
		}else{
			DisplayErrors();
			$feedBack[0]=0;
			$feedBack[1]="Adjustment Not Successful";
		}

	}
	
	return $feedBack;
	
}

function ReverseMoney($db,$ReferenceNumber,$UserID)
{
	$total=0;
	$NewReceiptID=0;
	$params = array();
	$ReceiptAmount=0;

	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

	$sql="select * from receipts where ReferenceNumber='$ReferenceNumber'";
	$result=sqlsrv_query($db,$sql);
	while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
	{
		$DateReceived=date("d/m/Y");
		$ReceiptID=$row['ReceiptID'];
		$ReceiptAmount=(double)$row['Amount']*(-1);
		$PaymentMethod=$row['ReceiptMethodID'];
		$RefNumber=$row['ReferenceNumber'];
		$BankID=$row['BankID'];
		$CreatedBy=$UserID;
		$msg="";
	}

	$query2 = "set dateformat dmy insert into  Receipts ([ReceiptDate],[ReceiptMethodID],[ReferenceNumber],BankID,[Amount],[ReceiptStatusID],CreatedBy) 
	VALUES('$DateReceived','$PaymentMethod','$RefNumber','$BankID','$ReceiptAmount','1','$UserID') SELECT SCOPE_IDENTITY() AS ID";

	$result1 = sqlsrv_query($db, $query2);
	if ($result1)
	{
		$NewReceiptID=lastid($result1);

		$sql="select InvoiceHeaderID,Amount from ReceiptLines where ReceiptID='$ReceiptID'";
		$rsult=sqlsrv_query($db,$sql);
		while($row=sqlsrv_fetch_array($rsult,SQLSRV_FETCH_ASSOC))
		{
			$InvoiceHeaderID=$row['InvoiceHeaderID'];
			$Amount=$row['Amount'] *(-1);

			$query4="Insert into ReceiptLines (ReceiptID,InvoiceHeaderID,Amount,CreatedBy)
			VALUES('$NewReceiptID','$InvoiceHeaderID','$Amount','$CreatedBy')";		
			$result2 = sqlsrv_query($db, $query4);
			if($result2)
			{
				//Negate the payment distribution Earlier Done
				$sql="insert into ReceiptLines2(ReceiptID,InvoiceHeaderID,ServiceID,Amount,CreatedBy)
						select ReceiptID,InvoiceHeaderID,ServiceID,Amount*(-1),CreatedBy from ReceiptLines2 
						where ReceiptID=$ReceiptID";

				$result=sqlsrv_query($db,$sql);

				if(!$result){
					DisplayErrors();
				}

			}else
			{
				DisplayErrors();
			}
		}

		$query3 = "Update  Receipts set Status=2 where ReferenceNumber='$RefNumber'";
		$result1 = sqlsrv_query($db, $query3);
		if(!$result1){
			DisplayErrors();
		}else{
			$sql="Update ApprovalEntry set ApprovalStatus=1 where DocumentNo='$ReferenceNumber'";
			$result=sqlsrv_query($db,$sql);
		}		
		
	}else
	{
		DisplayErrors();
	}

	if($result1 and $result2)
	{

		$msg1[0] = '1';
		$msg1[1] = $NewReceiptID;
	} else 
	{
		//DisplayErrors();
		$msg1[0] = '0';
		$msg1[1] = 'Receipting Failed';
	}

	return $msg1;		
}
function mpesaToInvoice($db,$mpesa_acc,$mpesa_code,$mpesa_amt,$CreatedBy)
{
	$total=0;
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );


	$ServiceHeaderID=0;

	$sql="Select Distinct ServiceHeaderID from InvoiceLines where InvoiceHeaderID='$mpesa_acc'";

	$result3=sqlsrv_query($db,$sql,$params,$options);
	if($result3)
	{

	$records=sqlsrv_num_rows($result3);
	if($records>0)
	{				
	while($row=sqlsrv_fetch_array($result3,SQLSRV_FETCH_ASSOC))
	{
	$ServiceHeaderID=$row['ServiceHeaderID'];


	$delqry="Delete from Receipts where ReferenceNumber='$mpesa_code'";
	$qresult=sqlsrv_query($db,$delqry);
	if($qresult){

	}else{
	DisplayErrors();
	}

	$query2 = "insert into  Receipts ([ReceiptDate],[ReceiptMethodID],[ReferenceNumber],[Amount],[ReceiptStatusID],CreatedBy,BankID) 
	VALUES(convert(date,getdate()),'1','$mpesa_code','$mpesa_amt','1','$CreatedBy','1013') SELECT SCOPE_IDENTITY() AS ID";

	$result1 = sqlsrv_query($db, $query2);
	if ($result1)
	{

	$ReceiptID=lastid($result1);							

	$query4="Insert into ReceiptLines (ReceiptID,InvoiceHeaderID,Amount,CreatedBy)
	VALUES('$ReceiptID','$mpesa_acc','$mpesa_amt','$CreatedBy')";		
	$result2 = sqlsrv_query($db, $query4);
	if($result2)
	{												

	}else
	{
	DisplayErrors();
	}

	$delqry="update mpesa set mpesa_acc='$mpesa_acc' where mpesa_code='$mpesa_code'";
	$qresult=sqlsrv_query($db,$delqry);
	if($qresult){
	while ($rw=sqlsrv_fetch_array($delqry,SQLSRV_FETCH_ASSOC))
	{
	$total=$rw['amount'];
	}								
	}else{
	DisplayErrors();
	}

	if($splitamount>$total)
	{
	echo $delqry.'<br>splitamount: '. $splitamount.' Total: '.$total;
	$msg="The receipt is overallocated!";
	return $msg;
	}
	}else
	{						
	DisplayErrors();
	}

	if($result1 and $result2 and $result3)
	{
	//echo $query4;
	$msg1 = 'Payment Matched';
	} else 
	{
	DisplayErrors();
	$msg1 = 'Error in Receipting';
	}
				
	}
	}else
	{
	//DisplayErrors();
	$msg1="The reference number entered cannot be matched with any Invoice from the county";
	}
	}else
	{
	DisplayErrors();
	$msg1="Error in the Query";			
	}

	return $msg1;		
}
function SplitReceipt($db,$ReceiptNumber,$InvoiceHeaderID,$InvoiceHeaderID_B,$Amount)
{
$total=0;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

$ServiceHeaderID=0;

$sql="Select ReceiptID,sum(Amount) Amount from ReceiptLines where InvoiceHeaderID='$InvoiceHeaderID'";

$result3=sqlsrv_query($db,$sql,$params,$options);
if($result3)
{			
$records=sqlsrv_num_rows($result3);
if($records>0)
{				
while($row=sqlsrv_fetch_array($result3,SQLSRV_FETCH_ASSOC))
{
$ServiceHeaderID=$row['ServiceHeaderID'];


$delqry="Delete from Receipts where ReferenceNumber='$mpesa_code'";
$qresult=sqlsrv_query($db,$delqry);
if($qresult){

}else{
	DisplayErrors();
}

$query2 = "insert into  Receipts ([ReceiptDate],[ReceiptMethodID],[ReferenceNumber],[Amount],[ReceiptStatusID],CreatedBy) 
VALUES(convert(date,getdate()),'1','$mpesa_code','$mpesa_amt','1','1') SELECT SCOPE_IDENTITY() AS ID";

$result1 = sqlsrv_query($db, $query2);
if ($result1)
{

$ReceiptID=lastid($result1);							

$query4="Insert into ReceiptLines (ReceiptID,InvoiceHeaderID,Amount,CreatedBy)
VALUES('$ReceiptID','$mpesa_acc','$mpesa_amt','1')";		
$result2 = sqlsrv_query($db, $query4);
if($result2)
{												

}else
{
DisplayErrors();
}

$delqry="update mpesa set mpesa_acc='$mpesa_acc' where mpesa_code='$mpesa_code'";
$qresult=sqlsrv_query($db,$delqry);
if($qresult){
while ($rw=sqlsrv_fetch_array($delqry,SQLSRV_FETCH_ASSOC))
{
$total=$rw['amount'];
}								
}else{
DisplayErrors();
}

if($splitamount>$total)
{
echo $delqry.'<br>splitamount: '. $splitamount.' Total: '.$total;
$msg="The receipt is overallocated!";
return $msg;
}
}else
{						
DisplayErrors();
}

if($result1 and $result2 and $result3)
{
//echo $query4;
$msg1 = 'Payment Matched';
} else 
{
DisplayErrors();
$msg1 = 'Error in Receipting';
}
			
}
}else
{
//DisplayErrors();
$msg1="The reference number entered cannot be matched with any Invoice from the county";
}
}else
{
DisplayErrors();
$msg1="Error in the Query";			
}

return $msg1;		
}
function ReceiptMoney($db,$DepositDate,$BankID,$RefNumber,$PaymentMethod,$InvoiceHeaderID,$SlipAmount,$InvoiceAmount,$CreatedBy)
{
	$total=0;
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

	$sql="select r.ReceiptID,r.ReferenceNumber,sum(r.Amount)-sum(rl.Amount) Balance
	from Receipts r 
	join (select receiptid,sum(Amount) Amount from ReceiptLines group by ReceiptID) rl on  rl.ReceiptID=r.ReceiptID 
	where r.ReferenceNumber='$RefNumber' and BankID='$BankID' and [Status]=1
	group by r.ReferenceNumber,r.ReceiptID";
	$s_result = sqlsrv_query($db, $sql,$params,$options);

	$rows=sqlsrv_num_rows($s_result);

	if($rows>0) //Document Already exists
	{
		while($row=sqlsrv_fetch_array($s_result,SQLSRV_FETCH_ASSOC))
		{
			$Balance=(double)$row['Balance'];
			if($Balance<=0){
				$msg1[0] = '0';
				$msg1[1] = 'The Receipt is already in the system, unless it is cancelled, you cannot use';
				return $msg1;
			}else{
				if($InvoiceAmount>$Balance)
				{
					$msg1[0] = '0';
					$msg1[1] = 'The total Invoice Amount is more than the Receipt Amount';
					return $msg1;
				}
				$ReceiptID=$row['ReceiptID'];
			}
		}
	}else
	{
		$query2 = "set dateformat dmy insert into  Receipts ([ReceiptDate],[ReceiptMethodID],[ReferenceNumber],BankID,[Amount],[ReceiptStatusID],CreatedBy) 
		VALUES('$DepositDate','$PaymentMethod','$RefNumber','$BankID','$SlipAmount','1','$CreatedBy') SELECT SCOPE_IDENTITY() AS ID";

		$result1 = sqlsrv_query($db, $query2);
		if ($result1)
		{
			$ReceiptID=lastid($result1);
		}

	}

	if($ReceiptID==0)
	{
		$msg1[0] = '0';
		$msg1[1] = 'The Receipt is badly formed. Kindly repost or consult the admin';
		return $msg1;
	}

	$query4="Insert into ReceiptLines (ReceiptID,InvoiceHeaderID,Amount,CreatedBy)
	VALUES('$ReceiptID','$InvoiceHeaderID','$InvoiceAmount','$CreatedBy')";		
	$result2 = sqlsrv_query($db, $query4);
	if($result2)
	{

	}else
	{
		DisplayErrors();
	}
	

	if($result2)
	{
		$rst=SaveTransaction($db,$CreatedBy," Receipted Reference Number ".$RefNumber." Costing ".$Amount);

		$msg1[0] = '1';
		$msg1[1] = 'Receipting Done';
	} else 
	{
		//DisplayErrors();
		$msg1[0] = '0';
		$msg1[1] = 'Receipting Failed';
	}

	return $msg1;		
}
function BillHouse($db,$ApplicationID,$CustomerID,$EstateID,$HouseNumber,$DocumentNo,$uhn,$BillAmount,$UserID,$cosmasRow)
{
$InvoiceNo='';
$ServiceID='';


$sql="select ServiceID,es.EstateName from Services s 
join Estates es on s.ServiceName=es.EstateName
where es.EstateID=$EstateID";

$result=sqlsrv_query($db,$sql);

while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC))
{			
$ServiceID=$row['ServiceID'];
$EstateName=$row['EstateName'];			
}


$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

if ($BillAmount<=0)
{
$msg="The cost of the service is not set, the process therefore aborts";
return $msg;
}


$sql="select sbc.SubCountyName,sbc.SubCountyID,w.WardID,isnull(w.WardName,'')WardName,isnull(bz.ZoneName ,'')ZoneName
from Customer c 				
join BusinessZones bz on c.BusinessZone=bz.ZoneID
join Wards w on bz.WardID=W.WardID
join subcounty sbc on w.SubCountyID=sbc.SubCountyID	
where c.CustomerID='$CustomerID'";
$s_result=sqlsrv_query($db,$sql);


$Ward='';
$WD='';
$SC='';

if ($s_result)
{					
while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
{			
$SubCounty=$row['SubCountyName'];
$Ward=$row['WardName'];
$Zone=$row['ZoneName'];
$WardID=$row['WardID'];

$SC=str_pad($SubCountyID,2,'0',STR_PAD_LEFT);
$WD=str_pad($WardID,2,'0',STR_PAD_LEFT);

}
}

$sql="select SubCountyID,WardID,InvoiceCount+1 InvoiceCount from [dbo].[fnInvoiceCount] ($WardID)";
$invoices=sqlsrv_query($db,$sql);
//echo $sql;
if ($invoices)
{	
//echo 'invoices';
while ($row = sqlsrv_fetch_array( $invoices, SQLSRV_FETCH_ASSOC))
{		
$SC=str_pad($row['SubCountyID'],2,'0',STR_PAD_LEFT);
$WD=str_pad($row['WardID'],2,'0',STR_PAD_LEFT);
$ICount=str_pad($row['InvoiceCount'],4,'0',STR_PAD_LEFT);

$InvoiceNo=$SC.$WD.$ICount;
}
}		

$Location=$SubCounty.'/'.$WardName.'/'.$Zone;
$Description='(House No'.$HouseNumber.'Estate: '.$EstateName.'),'.$Location;

$s_sql="select * from Customer where CustomerID=$CustomerID";
$s_result=sqlsrv_query($db,$s_sql);
//echo $s_sql;
if ($s_result)
{					
while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
{			
$CustomerEmail=$row['Email'];
}
}
if(sqlsrv_begin_transaction($db)===false)
{
$msg=sqlsrv_errors();
$Sawa=false;
}	

if($InvoiceNo=='')
{
$msg="You must enter the Invoice Number";
}else
{
$InvoiceHeader="";
$InvoiceDate= date("d/m/Y");				
$Sawa=true;
$msg='';					

$BillAmount=str_replace(',','',$BillAmount);

$s_sql="set dateformat dmy insert into InvoiceHeader (InvoiceDate,InvoiceNo,CustomerID,CreatedBy) Values('$InvoiceDate','$InvoiceNo',$CustomerID,'$UserID') SELECT SCOPE_IDENTITY() AS ID";

$s_result1 = sqlsrv_query($db, $s_sql);
//echo 'invoiceheader done';		
if ($s_result1)
{	
//echo 'after invoiceheader';
$InvoiceHeaderID=lastid($s_result1);

//insert into invoiceLines

$s_sql="set dateformat dmy insert into InvoiceLines (InvoiceHeaderID,ServiceHeaderID,ServiceID,Description,Amount,CreatedBy) 
Values($InvoiceHeaderID,'$ApplicationID',$ServiceID,'$Description',$BillAmount,'$UserID')";						
$s_result2 = sqlsrv_query($db, $s_sql);
if($s_result2){
//echo 'invoice lines done';

}else{
DisplayErrors();
echo $s_sql;
}


$sql="Set dateformat dmy update HouseReceipts set BillSent=1, InvoiceNo='$InvoiceHeaderID' 
where HouseNumber='$HouseNumber' and EstateID='$EstateID' and DocumentNo='$DocumentNo'";

//echo $sql;
$s_result4 = sqlsrv_query($db, $sql);	
if($s_result4){
//echo 'update HouseReceipts';
}					

}else{
//echo $s_sql.'<br>';
DisplayErrors();
}

//echo 'one<br>'.$s_result1 .'two<br>'. $s_result2 .'three<br>'.  $s_result3 .'four<br>'. $s_result4;

//echo 'Nje';
if($s_result1 && $s_result2 && $s_result4 )
{
//echo 'all is well';
sqlsrv_commit($db);

$Sawa=true;							
$Remark=$Description;
$feedBack=createInvoice($db,$ApplicationID,$cosmasRow,$Remark,'',$InvoiceHeaderID);
$msg=$feedBack[1];
$mail=true;	
$feedBack='Sent';

}else
{
$feedBack='Not Sent';
sqlsrv_rollback($db);
$Sawa=false;
}

return $feedBack;

}

}

// function sendSMS($MobileNo,$Message){
// 	sendSms($MobileNo,$Message);
// }

function SaveTransaction($db,$UserID,$Description){
	$result=false;
	if ($UserID==""){
		$result[0]=0;
		$result[1]="The session has expired, please restart your account";
		return $result;
	}

	$mac= GetClientMac();
	$sql="insert into Logs (UserID,Description,MacAddress) values($UserID,'$Description','$mac')";
	$fdback=sqlsrv_query($db,$sql);
	if(!$fdback){
		DisplayErrors();
		$result[0]=0;
		$result[1]="Transaction failed to save";
	}else{
		$result[0]=1;
		$result[1]="Transaction saved Successfully";
	}
	return $result;
}

function GetUser($db,$AgentID){

	$UserName='';
	$sql="select FirstName+' '+MiddleName+' '+LastName subject from Agents where agentID='$AgentID'";

	$result=sqlsrv_query($db,$sql);

	while($rw=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
		$UserName=$rw['subject'];
	}

	return	$UserName;	
}

function GetClientMac(){
    $ip=$_SERVER['REMOTE_ADDR'];
	$mac_string = shell_exec("arp -a $ip");
	$mac_array = explode(" ",$mac_string);
	$mac = $mac_array[3];

    return ($ip." - ".$mac);
}

function GetRemoteMac(){
    $ip=$_SERVER['HTTP_USER_AGENT'];
	return $ip;
}

function GenerateInvoice($db,$ApplicationID,$UserID='')
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
		

	$res=sqlsrv_query($db,$sql);
	while($row=sqlsrv_fetch_array($res,SQLSRV_FETCH_ASSOC))
	{
		$SubSystemID=$row['Value'];
	}

	
	$s_sql="select sc.amount,s.chargeable,sh.ServiceID,sh.CreatedDate,sh.CustomerID
	 from servicecharges sc 
	 inner join services s on sc.serviceid=s.serviceid 
	 inner join serviceheader sh on sh.serviceid=s.serviceid
	 join FinancialYear fy on sc.FinancialYearId=sc.FinancialYearId
	where sh.ServiceHeaderID=$ApplicationID and fy.isCurrentYear=1 and sc.SubSystemID='$SubSystemID'";

	

	//echo '<br><br>'; exit($s_sql);
	
	$s_result=sqlsrv_query($db,$s_sql);
	
	if ($s_result)
	{
			
		while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
		{						
			$ServiceID=$row['ServiceID'];
			$Chargeable=$row['chargeable'];						
			$ApplicationDate=$row['CreatedDate'];//date('d/m/Y',strtotime($date));
			$ApplicationDate=date('d/m/Y',strtotime($ApplicationDate));
			$CustomerID=$row['CustomerID'];
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

		//exit($sql1);

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
			
			$s_sql="set dateformat dmy insert into InvoiceHeader (InvoiceDate,ServiceHeaderID,InvoiceNo,CustomerID,CreatedBy) 
			Values('$InvoiceDate','$ApplicationID','$InvoiceNo',$CustomerID,'$UserID') SELECT SCOPE_IDENTITY() AS ID";
			$s_result1 = sqlsrv_query($db, $s_sql);
					
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
							from ServicePlus sc 
							join services s on sc.service_add=s.serviceid 
							join FinancialYear fy on sc.FinancialYearId=fy.FinancialYearID 
							and fy.isCurrentYear=1 
							and sc.serviceid=$ApplicationID";
						
					$s_result = sqlsrv_query($db, $sql);
					while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
					{									
						$ServiceAmount=$row["Amount"];
						$ServiceID=$row['ServiceID'];
						$InvoiceAmount+=$ServiceAmount;
						
						$s_sql="set dateformat dmy insert into InvoiceLines (InvoiceHeaderID,ServiceHeaderID,ServiceID,Amount,CreatedBy) 
								Values($InvoiceHeader,$ApplicationID,$ServiceID,$ServiceAmount,'$UserID')";
						//echo $s_sql; exit;
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

				}else
				{
					
					DisplayErrors().'<BR>';
					$Sawa=false;
					
				}

				//Application Charges
			    $sql="select distinct s1.ServiceID,s1.ServiceName,sp.Amount 
						from ServicePlus sp 
						join ServiceHeader sh on sh.ServiceID=sp.ServiceID 
						join Services s on sp.ServiceID=sh.ServiceID
						join Services s1 on sp.service_add=s1.ServiceID 
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
										
			}
			
			$s_sql="set dateformat dmy update InvoiceHeader set Amount='$InvoiceAmount' where InvoiceHeaderID='$InvoiceHeader'";
			//echo $s_sql;
			$s_result3=sqlsrv_query($db,$s_sql);
			if(!$s_result3){
				$Sawa=false;
			}

			//Create Permit, not Paid for
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

			$sql="set dateformat dmy insert into Permits(permitNo,ServiceHeaderID,Validity,
			ExpiryDate,CreatedBy,InvoiceHeaderID,Printed) 
			values('$permitNo',$ApplicationID,'$validity','$expiryDate','$UserID','$InvoiceHeaderID',0)";
			
			$s_result1 = sqlsrv_query($db, $sql);						
			
			if($s_result1)
			{

				$rst=SaveTransaction($db,$UserID," Created a Permit Invoice Number ".$InvoiceHeaderID);				
				

				sqlsrv_commit($db);
				$msg="Invoice Created Successfullye -$InvoiceHeaderID";
											
				$Sawa=true;
			}else
			{
				sqlsrv_rollback($db);
				$Sawa=false;
			}
			
			if($s_result1 && $s_result2 && $s_result3 && $loopOkey==true && $mail==true)
			{	
				$rst=SaveTransaction($db,$UserID," Created Invoice Number ".$InvoiceHeader);				
				sqlsrv_commit($db);
				$msg="Invoice Created Successfullrr - $InvoiceHeader";

				
				
				$name=explode(" ", $CustomerName);
				$fname= ucfirst(strtolower($name[0]));
				$InvoiceAmount=number_format($InvoiceAmount,2);

				$SmsText="Dear $fname, your application No. $ApplicationID has been approved. An invoice No. $InvoiceHeader of KSh. $InvoiceAmount has been issued to you. You may now proceed to pay";

				//sendSms($MobileNo,$SmsText); 

				$Sawa=true;
			}else
			{
				sqlsrv_rollback($db);
				$Sawa=false;
			}
		}
	}
	
	return $msg;
}

function GenerateLicenceApplicationInvoice($db,$ServiceHeaderID,$UserID)
{	

	$InvoiceHeader="";
	$ServiceAmount=0;
	$InvoiceAmount=0;
	$InvoiceDate= date("d/m/Y");
	$Chargeable=0;
	$Sawa=true;
	$msg='';
	
	//Get the ServiceId 
	$GetServiceIDSQL = "select ServiceID
	from ServiceHeader  WHERE ServiceHeaderId = $ServiceHeaderID";
	// exit($ServiceHeaderID);

	//Get the LicenceNo 
	$GetLicenceNoSQL = "select PermitNo
	from ServiceHeader  WHERE ServiceHeaderId = $ServiceHeaderID";
	// exit($GetServiceIDSQL);


	$GetServiceIDSQLresult = sqlsrv_query($db, $GetServiceIDSQL);

	while ($row = sqlsrv_fetch_array( $GetServiceIDSQLresult, SQLSRV_FETCH_ASSOC))
	{							
		$ServiceID=$row["ServiceID"];												
	}

	$GetLicenceNoSQLresult = sqlsrv_query($db, $GetLicenceNoSQL);

	while ($row = sqlsrv_fetch_array( $GetLicenceNoSQLresult, SQLSRV_FETCH_ASSOC))
	{							
		$PermitNo=$row["PermitNo"];												
	}

	//Get Banks 
	$sqlb="select BankName,AccountNumber from Banks";
	$bnkr=sqlsrv_query($db,$sqlb);
	while($bnks=sqlsrv_fetch_array($bnkr,SQLSRV_FETCH_ASSOC))
	{
		$bankrows.='<tr>
			<td>'.sentence_case($bnks['BankName']).'</td>
			<td>'.sentence_case($bnks['AccountNumber']).'</td>
			</tr>
		';
	}

	//Get Service Charge
	$s_sql="select sc.amount,s.chargeable,sh.ServiceID,sh.CreatedDate,sh.CustomerID
	 from servicecharges sc 
	 inner join services s on sc.serviceid=s.serviceid 
	 inner join serviceheader sh on sh.serviceid=s.serviceid
	 join FinancialYear fy on sc.FinancialYearId=sc.FinancialYearId
	 where sh.ServiceHeaderID=$ServiceHeaderID and fy.isCurrentYear=1";

	
	$s_result=sqlsrv_query($db,$s_sql);

	if ($s_result)
	{
			
		while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
		{						
			$ServiceID=$row['ServiceID'];
			$ServiceAmount +=$row['amount'];						
			$ApplicationDate=$row['CreatedDate'];//date('d/m/Y',strtotime($date));
			$ApplicationDate=date('d/m/Y',strtotime($ApplicationDate));
			$CustomerID=$row['CustomerID'];
		}
	}else
	{
		DisplayErrors();
	}

	if ($ServiceAmount==0)
	{				
		$msg='The Service is set not to have charges, hence cannot be invoiced';
		$Sawa=true;
	}
	else
	{
	      
		
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
			
			$s_sql="set dateformat dmy insert into InvoiceHeader (
					Amount,
					InvoiceDate,
					ServiceHeaderID,
					InvoiceNo,
					CustomerID,
					CreatedBy) 
				Values(
					'$ServiceAmount',
					'$InvoiceDate',
					'$ServiceHeaderID',
					'$InvoiceNo',
					 $CustomerID,
					'$UserID') SELECT SCOPE_IDENTITY() AS ID";

			$s_result1 = sqlsrv_query($db, $s_sql);
					
			if ($s_result1)
			{
				$InvoiceHeader=lastid($s_result1);				
								
				//insert into invoiceLines
	
				$s_sql="set dateformat dmy insert into InvoiceLines (
							InvoiceHeaderID,
							ServiceHeaderID,
							ServiceID,
							Description,
							Amount,CreatedBy) 
						Values($InvoiceHeader,$ServiceHeaderID,$ServiceID,
						'Service Charge',
							$ServiceAmount,
							'$UserID')";						
				$s_result2 = sqlsrv_query($db, $s_sql);
				//echo 'invoiceheader lines done';	
				$loopOkey=true;
				$PermitCost=$ServiceAmount;
				$InvoiceAmount+=$ServiceAmount;
				if ($s_result2)
				{								
					//check whether there are carrier
				    $sql="select s.ServiceID,s.ServiceName, Amount 
							from ServicePlus sc 
							join services s on sc.service_add=s.serviceid 
							join FinancialYear fy on sc.FinancialYearId=fy.FinancialYearID 
							and fy.isCurrentYear=1 
							and sc.serviceid=$ServiceID";
							// exit($sql);
						
					$s_result = sqlsrv_query($db, $sql);
					while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
					{									
						$ServiceAmount=$row["Amount"];
						$ServiceID=$row['ServiceID'];
						$InvoiceAmount+=$ServiceAmount;
						
						$s_sql="set dateformat dmy insert into InvoiceLines (
							InvoiceHeaderID,
						ServiceHeaderID,ServiceID,Amount,CreatedBy) 
								Values($InvoiceHeader,$ServiceHeaderID,
								$ServiceID,$ServiceAmount,
								'$UserID')";
						//echo $s_sql; exit;
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

				}else
				{
					
					DisplayErrors().'<BR>';
					$Sawa=false;
					
				}

				//Application Fees
			    $sql="select distinct s1.ServiceID,s1.ServiceName,sp.Amount 
						from ServicePlus sp 
						join ServiceHeader sh on sh.ServiceID=sp.ServiceID 
						join Services s on sp.ServiceID=sh.ServiceID
						join Services s1 on sp.service_add=s1.ServiceID 
						where sh.ServiceHeaderID=$ServiceHeaderID";

			    //echo $sql;

			    $result=sqlsrv_query($db,$sql);
			    while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
			    {
			        $ServiceAmount=$row["Amount"];
					$ServiceID=$row['ServiceID'];
					$InvoiceAmount+=$ServiceAmount;
					$ServiceName=$row['ServiceName'];
					$s_sql="set dateformat dmy insert into InvoiceLines (
						InvoiceHeaderID,
						ServiceHeaderID,
						ServiceID,
						Description,
						Amount,
						CreatedBy) 
							Values(
								$InvoiceHeader,
								$ServiceHeaderID,
								$ServiceID,
								'$ServiceName',
								$ServiceAmount,
								'$UserID')";
								// exit($s_sql);
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
										
			}
			
			$s_sql="set dateformat dmy update
			 InvoiceHeader set Amount='$InvoiceAmount' 
			 where InvoiceHeaderID='$InvoiceHeader'";
		
			$s_result3=sqlsrv_query($db,$s_sql);
			if(!$s_result3){
				$Sawa=false;
			}
			$InvoiceHeader=lastid($s_result1);				

			$ChangeStatussql="Update Permits set InvoiceHeaderID='$InvoiceHeader',
	  			where ServiceHeaderID='$ServiceHeaderID'";

			$result4=sqlsrv_query($db,$ChangeStatussql);

						
			
			if($s_result2 && $s_result3 &&  $s_result2 && $loopOkey==true && $mail==true)
			{	
				$rst=SaveTransaction($db,$UserID," Created Invoice Number ".$InvoiceHeader);				
				sqlsrv_commit($db);
				$msg="Invoice Created Successfullrr - $InvoiceHeader";
				// EXIT('Me');
						// 
				// createPermit($db, $ServiceHeaderID);

			
				$name=explode(" ", $CustomerName);
				$fname= ucfirst(strtolower($name[0]));
				$InvoiceAmount=number_format($InvoiceAmount,2);

				$SmsText="Dear $fname, your application No. $ServiceHeaderID has been approved. An invoice No. $InvoiceHeader of KSh. $InvoiceAmount has been issued to you. You may now proceed to pay";

				//sendSms($MobileNo,$SmsText); 

				$Sawa=true;
			}else
			{
				sqlsrv_rollback($db);
				$Sawa=false;
			}
		}
	}
	
	return $msg;
}


function uploadFileAlt(ClientContext $ctx, $sourceFilePath, $targetFileUrl)
{
	$fileContent = file_get_contents($sourceFilePath);

    try {
		Office365\SharePoint\File::saveBinary($ctx, $targetFileUrl, $fileContent);
		exit('Nding');
        print "File has been uploaded\r\n";
    } catch (Exception $e) {
		echo $e->getMessage();
        print "File upload failed:\r\n";
    }
}

function UploadDocsToSharePoint($db, $ServiceHeaderID, $UserId){
	// InitiliaseSharepoint();
	// exit('Hapa');

	$SharepointUrl = 'http://tra-edms/home/';//'http://tra-edms/home/Classification%20and%20Grading/Forms/AllItems.aspx';
	$SharepointUsername ='TRA-EDMS\Administrator';
	$SharepointPassword ='Admin@support12018';



	
		
		// $VerifiedContextTest = new ClientContext($Url,$authCtx);

		// echo 'Logged In Well';	

	
		// // exit;
		// $web = $VerifiedContext->getWeb();
		// $lists = $web->getLists(); //init List resource
		// // $items = $list->getItems();  //prepare a query to retrieve from the 
		// $VerifiedContext->load($lists);  //save a query to retrieve list items from the server 
		// $VerifiedContext->executeQuery(); //submit query to SharePoint Online REST service
		// /** @var ListItem $item */
		// foreach($lists->getData() as $list) {
		// 	print "List title: '{$list->Title}'\r\n";
		// }
		// exit;
		try{
		
			$TestPath = "C:/Users/Administrator/Downloads/New folder/A Sample PDF.pdf";

			$authCtx = new NetworkCredentialContext($SharepointUsername, $SharepointPassword);
			$authCtx->AuthType = CURLAUTH_NTLM; //NTML Auth schema
			// $VerifiedContextTest = new ClientContext($SharepointUrl,$authCtx);
			$VerifiedContextTest = ClientContext::connectWithUserCredentials($SharepointUrl,$SharepointUsername,$SharepointPassword);
			uploadFileAlt($VerifiedContextTest,$TestPath, 'http://tra-edms/home/Sample/Test');
			try {
				// $localPath = "../data/";
				$targetLibraryTitle = "Sample";
				$targetList = $VerifiedContextTest->getWeb()->getLists()->getByTitle($targetLibraryTitle);
			
				// $searchPrefix = $localPath . '*.*';
				// foreach(glob($searchPrefix) as $filename) {
					$uploadFile = $targetList->getRootFolder()->uploadFile(basename($TestPath),file_get_contents($TestPath));
					$VerifiedContextTest->executeQuery();
					print "File {$uploadFile->getServerRelativeUrl()} has been uploaded\r\n";
				// }
			
			}
			catch (Exception $e) {
				echo 'Error: ',  $e->getMessage(), "\n";
			}


			// uploadFileAlt($VerifiedContextTest, $TestPath, urlencode('http://tra-edms/home/Sample/Test'));
			exit;

			$DocumentMetadata = array(
				'Name' => 'SampleName',
				'DocumentName' => 'DocumentName',
				'DocumentCategoryName' => 'FileName',
				'DocumentTypeName' => 'FileName'  
			);
			$TargetFolderTest ='Licensing';// \Yii::$app->params['BenefitsFolderUrl'];
			// $list = ensureList($VerifiedContextTest->getWeb(), $TargetLibrary, \Office365\PHP\Client\SharePoint\ListTemplateType::DocumentLibrary);
			// $UploadContext = $VerifiedContextTest->getWeb();

			$fileName = basename($TestPath); //The Uploaded Document Name
			
			$fileCreationInformation = new \Office365\PHP\Client\SharePoint\FileCreationInformation();
			$fileCreationInformation->Content = file_get_contents($TestPath);
			// echo '<pre>';
			// print_r(file_get_contents($TestPath));
			// exit;

			$fileCreationInformation->Url = basename($TestPath);
			// echo '<pre>';
			// print_r($fileCreationInformation);
			// exit;

		// 	echo '<pre>';
		// 	print_r($UploadContext
		// 	->getFolderByServerRelativeUrl($TargetFolderTest)
		// 	->getFiles()
		// );
			// exit;
		
			$uploadFile = $VerifiedContextTest->getweb() //->getRootFolder()->getFiles()->add($fileCreationInformation);
					->getFolderByServerRelativeUrl($TargetFolderTest)
					->getFiles()->add($fileCreationInformation);

			

			$VerifiedContextTest->executeQuery(); //Upload Document
			// $uploadFile->getListItemAllFields() ; //Returns associated list item entity
			// $uploadFile->getListItemAllFields()->setProperty('Description',basename($TestPath));
			// $uploadFile->getListItemAllFields()->update();
			// $VerifiedContextTest->executeQuery(); //Upload Document

			print "File {$uploadFile->getProperty('Name')} has been uploaded\r\n";
			

		}
		
		catch (Exception $e) {
			// echo $e->getMessage();
			// var_dump($e->getMessage());
			// $data['exception'] = $e->getMessage();
			// echo json_encode($data);
			// exit;
			print 'SharePoint Upload failed Because : ' .  $e->getMessage(). "\n";
		}
		exit;

	

	//Get Details of Appliation
	$GetApplicationDetailSQL="Select Services.ServiceName,
	ServiceHeader.ServiceID,
	ServiceGroup.ServiceGroupID,
	ServiceGroup.ServiceGroupName,
	ServiceCategory.CategoryName,
	ServiceCategory.ServiceCategoryID,
	ServiceHeader.CustomerID,
	ServiceHeader.ServiceHeaderID as ApplicationNumber
	from ServiceHeader
	left join Services on Services.ServiceId  = ServiceHeader.ServiceID
	left join ServiceCategory on ServiceCategory.ServiceCategoryID = Services.ServiceCategoryID
	left join ServiceGroup on ServiceGroup.ServiceGroupID = ServiceCategory.ServiceGroupID
	where ServiceHeader.ServiceHeaderID=$ServiceHeaderID";
	$GetApplicationDetailResult=sqlsrv_query($db,$GetApplicationDetailSQL);
	if($rw=sqlsrv_fetch_array($GetApplicationDetailResult,SQLSRV_FETCH_ASSOC)){
		$ServiceName=$rw['ServiceName'];
		$ServiceID=$rw['ServiceID'];
		$ServiceClass=$rw['ServiceGroupName'];
		$ServiceCategory=$rw['CategoryName'];
		$CustomerID=$rw['CustomerID'];
		$ApplicationNumber=$rw['ApplicationNumber'];
	}

			//Upload Docs to Sharepoint
			
	//Get Attached Docs to this Application If Any
	$GetAttachedDocsSQL="select d.DocumentName,att.*
		from Attachments att
		join Documents d on d.DocumentID=att.DocumentID
		where att.ApplicationNo=$ServiceHeaderID";

	$s_result=sqlsrv_query($db,$GetAttachedDocsSQL);
	

	$s = actionSend_to_sharepoint($TestPath,$DocumentMetadata, $VerifiedContext);
	exit;
	if ($s_result){
		while($myrow=sqlsrv_fetch_array($s_result,SQLSRV_FETCH_ASSOC)){
			$DocumentID = $myrow['ID'];
			$ApplicationNo = $myrow['ApplicationNo'];
			$DocumentName = $myrow['DocumentName'];
			$FileName = $myrow['AttachmentName'];
			$FilePath = $myrow['FilePath'];

			$destination= $FilePath;
			$DocumentMetadata=array();

			$localFilePath = realpath ($FilePath);
			// exit($localFilePath);

			$DocumentMetadata = array(
				'Name' => $DocumentID,
				'DocumentName' => $DocumentName,
				'DocumentCategoryName' => $FileName,
				'DocumentTypeName' => $FileName  
			);


			// echo '<pre>';
			// print_r($DocumentMetadata);
			// exit;
		}
	}
	

}

//SHAREPOINT UPLOAD
function actionSend_to_sharepoint($filepath, $MetaData, $ctx)
{  //read list
	//$this->actionShrpnt_attach($target_file,$desc,$applicantno,$docno);
	$localPath = $filepath;
	try {
	
		
		$targetFolderUrl ='Licensing';// \Yii::$app->params['BenefitsFolderUrl'];
		$list = ensureList($ctx->getWeb(), $targetFolderUrl, \Office365\PHP\Client\SharePoint\ListTemplateType::DocumentLibrary);
		// print_r($list);
		// exit;
		$UploadContext = $list->getContext();

		$fileName = basename($localPath); //The Uploaded Document Name
		// exit($fileName);
		
		$fileCreationInformation = new \Office365\PHP\Client\SharePoint\FileCreationInformation();
		$fileCreationInformation->Content = file_get_contents($localPath);
		// echo '<pre>';
		// print_r(file_get_contents($localPath));
		// exit;

		$fileCreationInformation->Url = $fileName;

		$uploadFile = $list->getRootFolder()->getFiles()->add($fileCreationInformation);
		//->getFolderByServerRelativeUrl($targetFolderUrl)->getFiles()->add($fileCreationInformation);
	
		$UploadContext->executeQuery(); //Upload Document

		ECHO 'Uploaded Succesfully!';
		// return true;
	


	} 
	
	catch (Exception $e) {
		print 'Upload Failed '. $e->getMessage() ;
	}
}



function InitiliaseSharepoint(){
	try{
	
		$authCtx = new NetworkCredentialContext($SharepointUsername, $SharepointPassword);
		$authCtx->AuthType = CURLAUTH_NTLM; //NTML Auth schema
		$ctx = new ClientContext($SharepointUrl, $authCtx);
		$site = $ctx->getSite();
		$ctx->load($site); //load site settings     
		$ctx->executeQuery();

	}
	catch (Exception $e) {
        print 'SharePoint Authentication failed Because : ' .  $e->getMessage(). "\n";
	}

}

function ensureList(Web $web, $listTitle, $type, $clearItems = true) {
	$ctx = $web->getContext();
	$lists = $web->getLists()->filter("Title eq '$listTitle'")->top(1);
	$ctx->load($lists);
	$ctx->executeQuery();
	if ($lists->getCount() == 1) {
		$existingList = $lists->getData()[0];
		if ($clearItems) {
			//self::deleteListItems($existingList);
		}
		return $existingList;
	}
	return createList($web, $listTitle, $type);
}

 function createList(Web $web, $listTitle, $type)
{
	$ctx = $web->getContext();
	$info = new ListCreationInformation($listTitle);
	$info->BaseTemplate = $type;
	$list = $web->getLists()->add($info);
	$ctx->executeQuery();
	return $list;
}

function createPermit($db, $ApplicationID)
	{
		// exit('jfu');
		$CustomerName = '';
		$ServiceName = '';
		$ServiceAmount = '';	
		$InvoiceHeaderID='';	
		// $CountyName=$row['CountyName'];
		// $CountyAddress=$row['PostalAddress'];
		// $CountyTown=$row['Town'];
		// $CountyTelephone=$row['Telephone1'];
		// $CountyMobile=$row['Mobile1'];
		// $CountyEmail=$row['Email'];	
		// $CountyPostalCode=$row['PostalCode'];
		$PlotNo="";
		
		$PermitNo='';
		$BusinessID="";
		$CustomerID="";
		$Validity="";
		$Expiry="";
		$ExpityDate="";
		$CustomerName="";
		$BusinessName="";
		$ServiceName="";
		$ServiceCost="";
		$ServiceCost_Words="";
		$PostalAdress="";
		$PhysicalAddress="";
		$PostalCode="";
		$Vat="";
		$PIN="";
		$Town="";
	

		//get the details for this application

		$sql = "select distinct sh.ServiceHeaderID,sh.PermitNo,
		sh.ServiceID,sh.Validity,sh.ExpiryDate,
		ih.InvoiceHeaderID, ih.CustomerID,ih.InvoiceDate,ih.Paid,
		 c.CustomerName,c.Mobile1,
		c.BusinessID,c.BusinessRegistrationNumber,
		C.CustomerID,c.PostalAddress,
		c.PhysicalAddress,c.Telephone1,c.Telephone2,c.PostalCode,c.VatNumber,
		c.PIN,c.Town,c.Email, s.ServiceName, il.Amount,a.FirstName+' '+a.MiddleName+' '+a.LastName 
		IssuedBy from InvoiceHeader ih
		join InvoiceLines il on il.InvoiceHeaderID=ih.InvoiceHeaderID 
		join ServiceHeader sh on il.ServiceHeaderID=sh.ServiceHeaderID 
		join Customer c on sh.CustomerID=c.CustomerID 
		join Services s on sh.ServiceID=s.ServiceID and il.ServiceID=sh.ServiceID
		left join Agents a on sh.CreatedBy=a.AgentID where sh.ServiceHeaderID = $ApplicationID";

			// exit($sql);
			

			$qry_result=sqlsrv_query($db,$sql);	
			  
			if (($rrow = sqlsrv_fetch_array($qry_result,SQLSRV_FETCH_ASSOC))==false)
			{
				DisplayErrors();
				die;
			}else
			{

				$BusinessRegNo=$rrow['BusinessRegistrationNumber'];
				$PermitNo=$rrow['PermitNo'];
				$BusinessID=$rrow['BusinessID'];
				$CustomerID=$rrow['CustomerID'];
				$Validity=$rrow['Validity'];
				$Expiry=$rrow['ExpiryDate'];
				$ExpiryDate=$rrow['ExpiryDate'];
				$CustomerName=$rrow['CustomerName'];
				$BusinessName=$rrow['CustomerName'];
				$ServiceName=$rrow['ServiceName'];
				$ServiceCost=$rrow['Amount'];
				$PostalAdress=$rrow['PostalAddress'];
				$Telephone1=$rrow['Telephone1'];
				$Telephone2=$rrow['Telephone2'];
				$CustomerEmail=$rrow['Email'];
				$PostalCode=$rrow['PostalCode'];
				$PIN=$rrow['PIN'];
				$Vat=$rrow['VatNumber'];
				$Town=$rrow['Town'];
				$IssuedBy=$rrow['IssuedBy'];
				$MobileNo=$rrow['Mobile1'];
				
				$ServiceCost_Words=convertNumber($ServiceCost);				
			}

		//$Validity='2016';
		$mdate=date_create($Expiry);
		$Expiry=date_format($mdate,"d/m/Y");
		$Validity=date_format($mdate,'Y');
		$PostalTown='';
		/*$Expiry='2015';	
		$PostalAdress=0;
		$PostalCode=0;
		$Vat=0;
		$PIN=0;
		$Town='';
		$Email='amail';*/
		
		$rsql="select sh.CustomerID,c.CustomerName,c.Email, c.PostalAddress,c.PhysicalAddress,c.PostalCode,sh.ServiceID,s.ServiceName,s.ServiceCode, il.ServiceHeaderID,il.ServiceHeaderID,il.Amount,ih.InvoiceHeaderID,c.Email,fd.Value BDescription  
			from invoiceLines il 
			inner join InvoiceHeader ih on il.InvoiceHeaderID=ih.InvoiceHeaderID 
			inner join ServiceHeader sh on	il.ServiceHeaderID=sh.ServiceHeaderID 
			inner join Services s on sh.ServiceID=s.ServiceID and il.ServiceID=sh.ServiceID
			inner join Customer c on sh.CustomerID=c.CustomerID 
			join FormData fd on fd.ServiceHeaderID=sh.ServiceheaderID
			where fd.FormColumnID=5 and sh.ServiceHeaderID=$ApplicationID";
			
			$rresult = sqlsrv_query($db, $rsql);	
			

			if ($rrow = sqlsrv_fetch_array( $rresult, SQLSRV_FETCH_ASSOC))
			{
				$CustomerName = $rrow['CustomerName'];
				$ServiceName = $rrow['ServiceName'];
				$ServiceAmount = $rrow['Amount'];	
				$InvoiceHeaderID=$rrow['InvoiceHeaderID'];	
				$Email=$rrow['Email'];
				$BDescription=$rrow['BDescription'];
				$ServiceCode=$rrow['ServiceCode'];
				$PostalAddress=$rrow['PostalAddress'];
				$PostalTown=$rrow['Town'];
				$PostalCode=$rrow['PostalCode'];
				$PhysicalAddress=$rrow['PhysicalAddress'];
				$CustomerEmail = $rrow['Email'];
			}		
		

		
		// createBarCode($PermitNo);	

		$mpdf=new mPDF('win-1252','A4','','',20,15,48,25,10,10);
		$mpdf->useOnlyCoreFonts = true;    // false is default
		$mpdf->debugfonts = true; 
		$mpdf->SetProtection(array('print'));
		$mpdf->SetTitle('Title Goes Here');
		$mpdf->SetAuthor('Author Goes Here');
		$mpdf->SetWatermarkText("Tourisim Regulatory Authority");
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');
		
		$html='<html 
		  <head>
				<link rel="stylesheet" href="css/my_css.css" type="text/css"/>			
		  </head>			
		<body>
				<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; border-top:thick; " cellpadding="1">
					<tr>
						<td align="Center" colspan="5" style="font-size:10mm">
							<b> '.$ServiceName.' PERMIT</b>
						</td>
					</tr>
					<tr>
						<td align="Center" colspan="5">
							<img src="images/logo.png" alt="TRA Logo">
						</td>
					</tr>					
					<tr>
						<td style="border-right:0pt"></td>
						<td colspan="3" align="Center"><span style="font-weight: bold; font-size: 14pt;"> TRA </span></td>
						<td><span style="font-weight: bold; font-size: 14pt;">'.$Validity.'</span></h3></td>
					</tr>
					<tr>
						<td colspan="5" align="Center"><span style="font-weight: bold; font-size: 14pt;">
						<br>
						GRANTS THIS BUSINESS PERMIT <BR>
								TO
						</span></td>
					</tr>
					<thead>
						<tr>							
							<td colspan="5"><B>'.$BusinessName.'</B></td>
						</tr>
						<tr>
							<td colspan="2">Certificate of Registration NO/ID No: <br>'.$BusinessRegNo.'</td>
							<td width=20%>Business ID No:'.$BusinessID.'</td>
							<td>PIN NO: '.$PIN.'</td>
							
						</tr>
					</thead>
						<tr>
							<td colspan="5" align="center">
									<br><p><strong>To engage in the Activity/Business/Profession or Occupation of:</strong></p><br><br>									
							</td>
						</tr>
					<thead>
						<tr>
							<td align="left" colspan="3"><strong>Business Activity Code & Description:</strong><br>('.$ServiceCode.') '.$ServiceName.'</td>
							// <td align="right" colspan="2"><strong>Detailed Activity Description:</strong><br>'.$ServiceName.'</td>
						</tr>
					</thead>	
					<tr>
						<td colspan="5" align="center">
							<br><p><strong>Having Paid a  Licence Fee of:</strong></p><br><br>
						</td>					
					</tr>
					<tr>
						<td></td> 
						<td colspan="3"  align="center" style="background-color: #BEBABA; font-size:5mm">(Ksh.)<br>'.number_format($ServiceCost,2).'<br>('.$ServiceCost_Words.' only)</td>
						<td></td> 
					</tr>
					<thead>
						<tr>
							<td>P.O Box <br> '.$PostalAddress.'</td>
							<td>Postal Code <br> '.$PostalCode.'</td>
							<td>Postal Town <br> '.$PostalTown.'</td>
							<td>Business Physical Address<br> '.$PhysicalAddress.'</td>
						</tr>
					
						<tr>
							<td><strong>Mobile No</strong> <br> '.$Telephone1.'</td>
							<td><strong>Telephone</strong> <br> '.$Telephone2.'</td>
							<td colspan="2" align="left"><strong>Email Address</strong><br> '.$CustomerEmail.'</td>						
						</tr>
					</thead>
					<tr>
						<td colspan="2"><strong>Validity Period </strong>'.$Validity.'</td>
						<td></td>
						<td colspan="2" align="center"><strong>Expiry Date:</strong>'.$Expiry.'</td>
					</tr>
					<tr>
						<td colspan="2"><strong>Issued By:</strong><br>Jim Kinyua</td>	
						<td></td>						
						<td colspan="2"></td>
					</tr>
					<tr>
						<td colspan="2"><br><strong>For The Chief Officer<br>Finance And Economic Planning</strong></td>
						<td></td>
						<td colspan="2"><br><strong><br></td>
					</tr>
					<tr>
						<td colspan="5"><hr></td>
					</tr>
					<tr>						
						<td colspan="5" align="center"><img src="Images/Bar_Codes/'.$PermitNo.'.PNG"></td>
					</tr>					
					<thead>
						<tr>
							<td Colspan="5" style="text-align:justified;"> 
							<small><strong>Notice:</strong> Granting this permit does not exempt the business identified above from
									complying with the current regulations on Health and Safety as established by the Government of Kenya 
									</small>
							</td>
						</tr>
					</thead>
				</table>
				<I>Served by <B>'.$IssuedBy.'</B></I>
		</body>
		</html>';

		/* 		echo $html;
		exit; */
		$mpdf->WriteHTML($html);
		$mpdf->Output('pdfdocs/sbps/'."$ApplicationID".'.pdf','F'); 


		//send email
		$my_file = $ApplicationID.'.pdf';
		$file_path = "pdfdocs/sbps/";
		$my_name ='TRA'; //$CountyName;
		$toEmail = $CustomerEmail; //'jimkinyua25@gmail.com';// ;
		$fromEmail ='passdevelopment00@gmail.com';// $CountyEmail;
		$my_subject = "TRA Licence";
		$my_message="Kindly your receive the Licence for your applied Service";
		//$my_mail = 'cngeno11@gmail.com';
		$result=php_mailer($toEmail,$fromEmail,
		$CountyName,$my_subject,$my_message,$my_file,$file_path,"Permit");
	 	return $result;

		
		
	}


?>